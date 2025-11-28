import { Buffer } from 'buffer';

interface PrinterDevice {
    gatt?: {
        connected?: boolean;
        connect(): Promise<BluetoothRemoteGATTServer>;
        disconnect(): void;
    };
}

interface BluetoothPrintService {
    device: BluetoothDevice | null;
    server: BluetoothRemoteGATTServer | null;
    service: BluetoothRemoteGATTService | null;
    characteristic: BluetoothRemoteGATTCharacteristic | null;
}

class ThermalPrinterService {
    private bluetooth: BluetoothPrintService = {
        device: null,
        server: null,
        service: null,
        characteristic: null,
    };

    private readonly PRINTER_SERVICE_UUID = '000018f0-0000-1000-8000-00805f9b34fb';
    private readonly PRINTER_CHARACTERISTIC_UUID = '00002af1-0000-1000-8000-00805f9b34fb';

    /**
     * Check if Web Bluetooth is supported
     */
    public isBluetoothSupported(): boolean {
        return typeof navigator !== 'undefined' && 'bluetooth' in navigator;
    }

    /**
     * Connect to a Bluetooth thermal printer
     */
    public async connectToPrinter(): Promise<boolean> {
        if (!this.isBluetoothSupported()) {
            throw new Error('Web Bluetooth is not supported in this browser');
        }

        try {
            // Request device with printer service
            this.bluetooth.device = await navigator.bluetooth.requestDevice({
                filters: [
                    { services: [this.PRINTER_SERVICE_UUID] },
                    { namePrefix: 'POS' },
                    { namePrefix: 'EPSON' },
                    { namePrefix: 'CITIZEN' },
                    { namePrefix: 'STAR' },
                ],
                optionalServices: [this.PRINTER_SERVICE_UUID],
            });

            if (!this.bluetooth.device) {
                throw new Error('No device selected');
            }

            // Connect to GATT server
            this.bluetooth.server = await this.bluetooth.device.gatt?.connect();

            if (!this.bluetooth.server) {
                throw new Error('Failed to connect to GATT server');
            }

            // Get printer service
            this.bluetooth.service = await this.bluetooth.server.getPrimaryService(
                this.PRINTER_SERVICE_UUID
            );

            // Get write characteristic
            this.bluetooth.characteristic = await this.bluetooth.service.getCharacteristic(
                this.PRINTER_CHARACTERISTIC_UUID
            );

            console.log('✅ Successfully connected to thermal printer');
            return true;
        } catch (error) {
            console.error('❌ Failed to connect to printer:', error);
            return false;
        }
    }

    /**
     * Disconnect from printer
     */
    public async disconnectFromPrinter(): Promise<void> {
        if (this.bluetooth.device?.gatt?.connected) {
            this.bluetooth.device.gatt.disconnect();
        }

        this.bluetooth = {
            device: null,
            server: null,
            service: null,
            characteristic: null,
        };

        console.log('🔌 Disconnected from thermal printer');
    }

    /**
     * Check if printer is connected
     */
    public isConnected(): boolean {
        return !!(
            this.bluetooth.device?.gatt?.connected &&
            this.bluetooth.characteristic
        );
    }

    /**
     * Send raw data to printer
     */
    private async sendToPrinter(data: Uint8Array): Promise<void> {
        if (!this.isConnected() || !this.bluetooth.characteristic) {
            throw new Error('Printer not connected');
        }

        try {
            // Split data into chunks (most printers have ~20 byte limit per write)
            const chunkSize = 20;
            for (let i = 0; i < data.length; i += chunkSize) {
                const chunk = data.slice(i, i + chunkSize);
                await this.bluetooth.characteristic.writeValue(chunk);
                // Small delay between chunks to avoid overwhelming the printer
                await new Promise(resolve => setTimeout(resolve, 10));
            }
        } catch (error) {
            console.error('❌ Failed to send data to printer:', error);
            throw error;
        }
    }

    /**
     * Print receipt using ESC/POS commands
     */
    public async printReceipt(receiptData: {
        storeName: string;
        storeAddress: string;
        orderNumber: string;
        cashier: string;
        date: string;
        items: Array<{
            name: string;
            quantity: number | string;
            price: number | string;
            amount: number | string;
        }>;
        subtotal: number | string;
        tax?: number | string;
        discount?: number | string;
        total: number | string;
        payment: {
            method: string;
            amount: number | string;
            change?: number | string;
        };
        footer?: string;
    }): Promise<void> {
        if (!this.isConnected()) {
            throw new Error('Printer not connected. Please connect first.');
        }

        try {
            // Build ESC/POS commands
            const commands: number[] = [];

            // Initialize printer
            commands.push(...[0x1b, 0x40]); // ESC @

            // Set character encoding to UTF-8
            commands.push(...[0x1b, 0x74, 0x06]); // ESC t 6

            // Store header - centered, bold, large
            commands.push(...[0x1b, 0x61, 0x01]); // Center align
            commands.push(...[0x1b, 0x45, 0x01]); // Bold on
            commands.push(...[0x1d, 0x21, 0x11]); // Double size
            commands.push(...this.stringToBytes(receiptData.storeName));
            commands.push(...[0x0a, 0x0a]); // Line feeds

            // Store address - normal size
            commands.push(...[0x1d, 0x21, 0x00]); // Normal size
            commands.push(...[0x1b, 0x45, 0x00]); // Bold off
            commands.push(...this.stringToBytes(receiptData.storeAddress));
            commands.push(...[0x0a, 0x0a]);

            // Order info - left align
            commands.push(...[0x1b, 0x61, 0x00]); // Left align
            commands.push(...this.stringToBytes(`Order #: ${receiptData.orderNumber}`));
            commands.push(0x0a);
            commands.push(...this.stringToBytes(`Cashier: ${receiptData.cashier}`));
            commands.push(0x0a);
            commands.push(...this.stringToBytes(`Date: ${receiptData.date}`));
            commands.push(...[0x0a, 0x0a]);

            // Separator line (48 characters for 76mm paper)
            commands.push(...this.stringToBytes('------------------------------------------------'));
            commands.push(0x0a);

            // Items
            receiptData.items.forEach(item => {
                const line = this.formatItemLine(item.name, item.quantity, item.price, item.amount);
                commands.push(...this.stringToBytes(line));
                commands.push(0x0a);
            });

            // Separator line
            commands.push(...this.stringToBytes('------------------------------------------------'));
            commands.push(0x0a);

            // Totals - right align for amounts
            const subtotal = typeof receiptData.subtotal === 'string' ? parseFloat(receiptData.subtotal) || 0 : receiptData.subtotal;
            commands.push(...this.stringToBytes(this.formatTotalLine('Subtotal:', subtotal)));
            commands.push(0x0a);

            if (receiptData.tax) {
                const tax = typeof receiptData.tax === 'string' ? parseFloat(receiptData.tax) || 0 : receiptData.tax;
                commands.push(...this.stringToBytes(this.formatTotalLine('Tax:', tax)));
                commands.push(0x0a);
            }

            if (receiptData.discount) {
                const discount = typeof receiptData.discount === 'string' ? parseFloat(receiptData.discount) || 0 : receiptData.discount;
                commands.push(...this.stringToBytes(this.formatTotalLine('Discount:', -discount)));
                commands.push(0x0a);
            }

            // Total - bold
            commands.push(...[0x1b, 0x45, 0x01]); // Bold on
            const total = typeof receiptData.total === 'string' ? parseFloat(receiptData.total) || 0 : receiptData.total;
            commands.push(...this.stringToBytes(this.formatTotalLine('TOTAL:', total)));
            commands.push(...[0x1b, 0x45, 0x00]); // Bold off
            commands.push(...[0x0a, 0x0a]);

            // Payment info
            const paymentAmount = typeof receiptData.payment.amount === 'string' ? parseFloat(receiptData.payment.amount) || 0 : receiptData.payment.amount;
            commands.push(...this.stringToBytes(this.formatTotalLine('Payment:', paymentAmount)));
            commands.push(0x0a);
            commands.push(...this.stringToBytes(`Method: ${receiptData.payment.method}`));
            commands.push(0x0a);

            if (receiptData.payment.change) {
                const change = typeof receiptData.payment.change === 'string' ? parseFloat(receiptData.payment.change) || 0 : receiptData.payment.change;
                commands.push(...this.stringToBytes(this.formatTotalLine('Change:', change)));
                commands.push(...[0x0a, 0x0a]);
            }

            // Footer
            if (receiptData.footer) {
                commands.push(...[0x1b, 0x61, 0x01]); // Center align
                commands.push(...this.stringToBytes(receiptData.footer));
                commands.push(...[0x0a, 0x0a]);
            }

            // Add extra spacing before cut to prevent text cutoff
            commands.push(...[0x0a, 0x0a, 0x0a, 0x0a, 0x0a]); // 5 line feeds for proper spacing

            // Cut paper
            commands.push(...[0x1d, 0x56, 0x00]); // Full cut

            // Send to printer
            const data = new Uint8Array(commands);
            await this.sendToPrinter(data);

            console.log('✅ Receipt printed successfully');
        } catch (error) {
            console.error('❌ Failed to print receipt:', error);
            throw error;
        }
    }

    /**
     * Convert string to byte array
     */
    private stringToBytes(str: string): number[] {
        return Array.from(new TextEncoder().encode(str));
    }

    /**
     * Format item line for receipt (76mm paper - 48 characters width)
     */
    private formatItemLine(name: string, quantity: number, price: number | string, amount: number | string): string {
        const maxWidth = 48; // 76mm paper width
        const numPrice = typeof price === 'string' ? parseFloat(price) || 0 : price;
        const numAmount = typeof amount === 'string' ? parseFloat(amount) || 0 : amount;
        const numQuantity = typeof quantity === 'string' ? parseInt(quantity) || 0 : quantity;

        const qtyPriceText = `${numQuantity}x${numPrice.toFixed(2)}`;
        const amountText = numAmount.toFixed(2);

        // Truncate name if too long (allow more characters for 76mm)
        let itemName = name.length > 30 ? name.substring(0, 27) + '...' : name;

        // Calculate spaces needed
        const spacesNeeded = maxWidth - itemName.length - qtyPriceText.length - amountText.length;
        const spaces = ' '.repeat(Math.max(1, spacesNeeded));

        return `${itemName} ${qtyPriceText}${spaces}${amountText}`;
    }    /**
     * Format total line for receipt (76mm paper - 48 characters width)
     */
    private formatTotalLine(label: string, amount: number | string): string {
        const maxWidth = 48; // 76mm paper width
        const numAmount = typeof amount === 'string' ? parseFloat(amount) || 0 : amount;
        const amountText = numAmount.toFixed(2);
        const spaces = ' '.repeat(maxWidth - label.length - amountText.length);
        return `${label}${spaces}${amountText}`;
    }

    /**
     * Test printer connection with a simple test print
     */
    public async testPrint(): Promise<void> {
        if (!this.isConnected()) {
            throw new Error('Printer not connected');
        }

        const commands: number[] = [];

        // Initialize
        commands.push(...[0x1b, 0x40]); // ESC @

        // Center align, bold
        commands.push(...[0x1b, 0x61, 0x01]); // Center
        commands.push(...[0x1b, 0x45, 0x01]); // Bold on

        // Test text
        commands.push(...this.stringToBytes('TEST PRINT - 76mm'));
        commands.push(...[0x0a, 0x0a]);

        // Normal text
        commands.push(...[0x1b, 0x45, 0x00]); // Bold off
        commands.push(...[0x1b, 0x61, 0x00]); // Left align
        commands.push(...this.stringToBytes('Printer connected successfully!'));
        commands.push(...[0x0a]);
        commands.push(...this.stringToBytes('Paper width: 76mm (48 characters)'));
        commands.push(...[0x0a]);
        commands.push(...this.stringToBytes('------------------------------------------------'));
        commands.push(...[0x0a, 0x0a]);

        // Add proper spacing before cut
        commands.push(...[0x0a, 0x0a, 0x0a, 0x0a, 0x0a]); // 5 line feeds

        // Cut
        commands.push(...[0x1d, 0x56, 0x00]);

        const data = new Uint8Array(commands);
        await this.sendToPrinter(data);
    }
}

// Export singleton instance
export const thermalPrinter = new ThermalPrinterService();
export default ThermalPrinterService;
