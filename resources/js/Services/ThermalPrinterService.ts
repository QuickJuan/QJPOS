import { Buffer } from 'buffer';
import axios from 'axios';

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

interface PrinterConfig {
    id: number;
    name: string;
    type: 'kitchen' | 'bar' | 'receipt';
    bluetooth_name?: string;
    bluetooth_address?: string;
    service_uuid: string;
    characteristic_uuid: string;
    paper_size: '36mm' | '76mm';
    character_width: number;
    is_active: boolean;
    auto_cut: boolean;
    cut_spacing: number;
    print_categories?: string[];
    notes?: string;
}

class ThermalPrinterService {
    private bluetooth: BluetoothPrintService = {
        device: null,
        server: null,
        service: null,
        characteristic: null,
    };

    private currentConfig: PrinterConfig | null = null;
    private readonly DEFAULT_SERVICE_UUID = '000018f0-0000-1000-8000-00805f9b34fb';
    private readonly DEFAULT_CHARACTERISTIC_UUID = '00002af1-0000-1000-8000-00805f9b34fb';

    /**
     * Check if Web Bluetooth is supported
     */
    public isBluetoothSupported(): boolean {
        return typeof navigator !== 'undefined' && 'bluetooth' in navigator;
    }

    /**
     * Load printer configuration from API
     */
    public async loadPrinterConfig(type: 'kitchen' | 'bar' | 'receipt'): Promise<PrinterConfig | null> {
        try {
            const response = await axios.get(`/api/printer-config/${type}`);
            this.currentConfig = response.data;
            return this.currentConfig;
        } catch (error) {
            console.error('Failed to load printer config:', error);
            return null;
        }
    }

    /**
     * Connect to a Bluetooth thermal printer with optional configuration
     * If device is provided, reuse existing connection instead of requesting new pairing
     */
    public async connectToPrinter(config?: PrinterConfig, existingDevice?: BluetoothDevice): Promise<boolean> {
        if (!this.isBluetoothSupported()) {
            throw new Error('Web Bluetooth is not supported in this browser');
        }

        // Use provided config or current config
        const printerConfig = config || this.currentConfig;

        try {
            // If we have an existing device, try to reuse it
            if (existingDevice && existingDevice.gatt) {
                this.bluetooth.device = existingDevice;

                // Check if already connected
                if (existingDevice.gatt.connected) {
                    console.log('♻️ Reusing existing Bluetooth connection');
                    this.bluetooth.server = existingDevice.gatt;
                } else {
                    console.log('🔄 Reconnecting to existing device');
                    this.bluetooth.server = await existingDevice.gatt.connect();
                }
            } else {
                // Request new device
                console.log('🔍 Requesting new Bluetooth device');

                // Build device filters based on configuration
                const filters: BluetoothLEScanFilter[] = [
                    { services: [printerConfig?.service_uuid || this.DEFAULT_SERVICE_UUID] }
                ];

                // Add name filters if bluetooth_name is specified
                if (printerConfig?.bluetooth_name) {
                    filters.push({ name: printerConfig.bluetooth_name });
                } else {
                    // Default name prefixes
                    filters.push(
                        { namePrefix: 'POS' },
                        { namePrefix: 'EPSON' },
                        { namePrefix: 'CITIZEN' },
                        { namePrefix: 'STAR' }
                    );
                }

                // Request device with printer service
                this.bluetooth.device = await navigator.bluetooth.requestDevice({
                    filters,
                    optionalServices: [printerConfig?.service_uuid || this.DEFAULT_SERVICE_UUID],
                });

                if (!this.bluetooth.device) {
                    throw new Error('No device selected');
                }

                // Connect to GATT server
                this.bluetooth.server = await this.bluetooth.device.gatt?.connect();
            }

            if (!this.bluetooth.server) {
                throw new Error('Failed to connect to GATT server');
            }

            // Store the config for this connection
            if (printerConfig) {
                this.currentConfig = printerConfig;
            }

            // Get printer service
            const serviceUuid = printerConfig?.service_uuid || this.DEFAULT_SERVICE_UUID;
            this.bluetooth.service = await this.bluetooth.server.getPrimaryService(serviceUuid);

            // Get write characteristic
            const characteristicUuid = printerConfig?.characteristic_uuid || this.DEFAULT_CHARACTERISTIC_UUID;
            this.bluetooth.characteristic = await this.bluetooth.service.getCharacteristic(characteristicUuid);

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

            // Separator line (dynamic width based on printer config)
            const separatorChar = '-';
            const separatorWidth = this.currentConfig?.character_width || 48;
            const separatorLine = separatorChar.repeat(separatorWidth);
            commands.push(...this.stringToBytes(separatorLine));
            commands.push(0x0a);

            // Items
            receiptData.items.forEach(item => {
                const line = this.formatItemLine(item.name, item.quantity, item.price, item.amount);
                commands.push(...this.stringToBytes(line));
                commands.push(0x0a);
            });

            // Separator line
            commands.push(...this.stringToBytes(separatorLine));
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

            // Add spacing before cut based on printer configuration
            const cutSpacing = this.currentConfig?.cut_spacing || 5;
            for (let i = 0; i < cutSpacing; i++) {
                commands.push(0x0a);
            }

            // Cut paper if auto_cut is enabled
            if (this.currentConfig?.auto_cut !== false) {
                commands.push(...[0x1d, 0x56, 0x00]); // Full cut
            }            // Send to printer
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
    private formatItemLine(name: string, quantity: number | string, price: number | string, amount: number | string): string {
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
     * Format total line for receipt with dynamic width based on printer config
     */
    private formatTotalLine(label: string, amount: number | string): string {
        const maxWidth = this.currentConfig?.character_width || 48;
        const numAmount = typeof amount === 'string' ? parseFloat(amount) || 0 : amount;
        const amountText = numAmount.toFixed(2);
        const spaces = ' '.repeat(Math.max(0, maxWidth - label.length - amountText.length));
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
        const configInfo = this.currentConfig ? `${this.currentConfig.name} - ${this.currentConfig.paper_size}` : 'DEFAULT CONFIG';
        commands.push(...this.stringToBytes(`TEST PRINT - ${configInfo}`));
        commands.push(...[0x0a, 0x0a]);

        // Normal text
        commands.push(...[0x1b, 0x45, 0x00]); // Bold off
        commands.push(...[0x1b, 0x61, 0x00]); // Left align
        commands.push(...this.stringToBytes('Printer connected successfully!'));
        commands.push(...[0x0a]);

        if (this.currentConfig) {
            commands.push(...this.stringToBytes(`Config: ${this.currentConfig.name}`));
            commands.push(...[0x0a]);
            commands.push(...this.stringToBytes(`Paper: ${this.currentConfig.paper_size} (${this.currentConfig.character_width} chars)`));
            commands.push(...[0x0a]);
        }

        // Dynamic separator
        const separatorWidth = this.currentConfig?.character_width || 48;
        commands.push(...this.stringToBytes('-'.repeat(separatorWidth)));
        commands.push(...[0x0a, 0x0a]);

        // Add proper spacing before cut based on config
        const cutSpacing = this.currentConfig?.cut_spacing || 5;
        for (let i = 0; i < cutSpacing; i++) {
            commands.push(0x0a);
        }

        // Cut if auto_cut is enabled
        if (this.currentConfig?.auto_cut !== false) {
            commands.push(...[0x1d, 0x56, 0x00]);
        }

        const data = new Uint8Array(commands);
        await this.sendToPrinter(data);
    }

    /**
     * Connect to a specific printer type (kitchen, bar, receipt)
     */
    public async connectToPrinterType(type: 'kitchen' | 'bar' | 'receipt'): Promise<boolean> {
        const config = await this.loadPrinterConfig(type);
        if (!config) {
            throw new Error(`No active ${type} printer configuration found`);
        }
        return this.connectToPrinter(config);
    }

    /**
     * Get current printer configuration
     */
    public getCurrentConfig(): PrinterConfig | null {
        return this.currentConfig;
    }

    /**
     * Set printer configuration manually
     */
    public setConfig(config: PrinterConfig): void {
        this.currentConfig = config;
    }
}

// Export singleton instance
export const thermalPrinter = new ThermalPrinterService();
export default ThermalPrinterService;
export type { PrinterConfig };
