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
        storePhone?: string;
        orderNumber: string;
        cashier: string;
        date: string;
        time?: string;
        tableNumber?: string;
        orderType?: string;
        items: Array<{
            name: string;
            quantity: number | string;
            price: number | string;
            amount: number | string;
            selectedOptions?: Array<{
                name: string;
                price: number | string;
            }>;
            lessTax?: number | string;
            discount?: number | string;
            orderType?: string;
        }>;
        subtotal: number | string;
        lessTax?: number | string;
        lessDiscount?: number | string;
        total: number | string;
        payment?: {
            method: string;
            amountPaid: number | string;
            change?: number | string;
        };
        receiptFooter?: {
            footer_notes?: string;
        };
        footerMessage?: string;
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

            // Header - Business Info
            commands.push(...[0x1b, 0x61, 0x01]); // Center align
            commands.push(...[0x1b, 0x45, 0x01]); // Bold on
            commands.push(...[0x1d, 0x21, 0x11]); // Double size
            commands.push(...this.stringToBytes(receiptData.storeName));
            commands.push(...[0x0a]);

            // Store address and phone - normal size
            commands.push(...[0x1d, 0x21, 0x00]); // Normal size
            commands.push(...[0x1b, 0x45, 0x00]); // Bold off
            commands.push(...this.stringToBytes(receiptData.storeAddress));
            commands.push(0x0a);
            if (receiptData.storePhone) {
                commands.push(...this.stringToBytes(receiptData.storePhone));
                commands.push(0x0a);
            }

            // Header separator
            const separatorWidth = this.currentConfig?.character_width || 47;
            commands.push(...this.stringToBytes('-'.repeat(separatorWidth)));
            commands.push(...[0x0a, 0x0a]);

            // Receipt Info - left align
            commands.push(...[0x1b, 0x61, 0x00]); // Left align
            commands.push(...this.stringToBytes(this.formatInfoLine('Receipt #:', receiptData.orderNumber)));
            commands.push(0x0a);
            commands.push(...this.stringToBytes(this.formatInfoLine('Date:', this.formatReceiptDate(receiptData.date))));
            commands.push(0x0a);
            if (receiptData.time) {
                commands.push(...this.stringToBytes(this.formatInfoLine('Time:', receiptData.time)));
                commands.push(0x0a);
            }
            if (receiptData.tableNumber) {
                commands.push(...this.stringToBytes(this.formatInfoLine('Table:', receiptData.tableNumber)));
                commands.push(0x0a);
            }
            if (receiptData.cashier) {
                commands.push(...this.stringToBytes(this.formatInfoLine('Cashier:', receiptData.cashier)));
                commands.push(0x0a);
            }
            if (receiptData.orderType) {
                commands.push(...this.stringToBytes(this.formatInfoLine('Order Type:', receiptData.orderType)));
                commands.push(0x0a);
            }
            commands.push(0x0a);

            // Items separator
            const separatorLine = '-'.repeat(separatorWidth);
            commands.push(...this.stringToBytes(separatorLine));
            commands.push(0x0a);

            // ORDER ITEMS header - centered
            commands.push(...[0x1b, 0x61, 0x01]); // Center align
            commands.push(...[0x1b, 0x45, 0x01]); // Bold on
            commands.push(...this.stringToBytes('ORDER ITEMS'));
            commands.push(...[0x1b, 0x45, 0x00]); // Bold off
            commands.push(...[0x0a, 0x0a]);

            // Left align for items
            commands.push(...[0x1b, 0x61, 0x00]);

            // Group items by order type (matching ReceiptLayout)
            const groupedItems = this.groupItemsByOrderType(receiptData.items);

            Object.entries(groupedItems).forEach(([orderType, items]) => {
                // Order type header if there are multiple types
                if (Object.keys(groupedItems).length > 1) {
                    commands.push(...[0x1b, 0x61, 0x01]); // Center align
                    commands.push(...[0x1b, 0x45, 0x01]); // Bold on
                    commands.push(...this.stringToBytes(this.getOrderTypeLabel(orderType)));
                    commands.push(...[0x1b, 0x45, 0x00]); // Bold off
                    commands.push(...[0x1b, 0x61, 0x00]); // Left align
                    commands.push(...[0x0a, 0x0a]);
                }

                // Print items in this group
                items.forEach(item => {
                    // Main item line
                    
                    commands.push(...this.stringToBytes(item.name));
                    commands.push(0x0a);

                    // Quantity and price line (indented)
                    if (typeof item.quantity === 'number' && item.quantity >= 1) {
                        const qtyLine = this.formatQuantityLine(item.quantity, item.price, item.amount);
                        commands.push(...this.stringToBytes(`  ${qtyLine}`));
                        commands.push(0x0a);
                    }

                    // Selected options (if any)
                    if (item.selectedOptions && item.selectedOptions.length > 0) {
                        item.selectedOptions.forEach(option => {
                            const optionLine = this.formatOptionLine(option.name, option.price);
                            commands.push(...this.stringToBytes(`  ${optionLine}`));
                            commands.push(0x0a);
                        });
                    }

                    // Less tax (if any)
                    if (item.lessTax && parseFloat(String(item.lessTax)) > 0) {
                        const taxLine = this.formatDeductionLine('Less Tax:', item.lessTax);
                        commands.push(...this.stringToBytes(`  ${taxLine}`));
                        commands.push(0x0a);
                    }

                    // Less discount (if any)
                    if (item.discount && parseFloat(String(item.discount)) > 0) {
                        const discountLine = this.formatDeductionLine('Less Discount:', item.discount);
                        commands.push(...this.stringToBytes(`  ${discountLine}`));
                        commands.push(0x0a);
                    }

                    commands.push(0x0a); // Space between items
                });
            });

            // Items separator
            commands.push(...this.stringToBytes(separatorLine));
            commands.push(0x0a);

            // Totals section
            const subtotal = typeof receiptData.subtotal === 'string' ? parseFloat(receiptData.subtotal) || 0 : receiptData.subtotal;
            commands.push(...this.stringToBytes(this.formatTotalLine('Subtotal:', subtotal)));
            commands.push(0x0a);

            // Less Tax (matching ReceiptLayout naming)
            if (receiptData.lessTax && parseFloat(String(receiptData.lessTax)) > 0) {
                const lessTax = typeof receiptData.lessTax === 'string' ? parseFloat(receiptData.lessTax) || 0 : receiptData.lessTax;
                commands.push(...this.stringToBytes(this.formatTotalLine('Less Tax:', -lessTax)));
                commands.push(0x0a);
            }

            // Less Discount (matching ReceiptLayout naming)
            if (receiptData.lessDiscount && parseFloat(String(receiptData.lessDiscount)) > 0) {
                const lessDiscount = typeof receiptData.lessDiscount === 'string' ? parseFloat(receiptData.lessDiscount) || 0 : receiptData.lessDiscount;
                commands.push(...this.stringToBytes(this.formatTotalLine('Less Discount:', -lessDiscount)));
                commands.push(0x0a);
            }

            // Total separator and amount - bold
            commands.push(...this.stringToBytes('-'.repeat(separatorWidth)));
            commands.push(0x0a);
            commands.push(...[0x1b, 0x45, 0x01]); // Bold on
            const total = typeof receiptData.total === 'string' ? parseFloat(receiptData.total) || 0 : receiptData.total;
            commands.push(...this.stringToBytes(this.formatTotalLine('TOTAL:', total)));
            commands.push(...[0x1b, 0x45, 0x00]); // Bold off
            commands.push(...[0x0a, 0x0a]);

            // Payment info (matching ReceiptLayout format)
            if (receiptData.payment) {
                commands.push(...[0x1b, 0x61, 0x01]); // Center align
                commands.push(...[0x1b, 0x45, 0x01]); // Bold on
                commands.push(...this.stringToBytes('PAYMENT'));
                commands.push(...[0x1b, 0x45, 0x00]); // Bold off
                commands.push(...[0x1b, 0x61, 0x00]); // Left align
                commands.push(...[0x0a, 0x0a]);

                const paymentAmount = typeof receiptData.payment.amountPaid === 'string' ? parseFloat(receiptData.payment.amountPaid) || 0 : receiptData.payment.amountPaid;
                commands.push(...this.stringToBytes(this.formatTotalLine('Amount Paid:', paymentAmount)));
                commands.push(0x0a);

                if (receiptData.payment.change && parseFloat(String(receiptData.payment.change)) > 0) {
                    const change = typeof receiptData.payment.change === 'string' ? parseFloat(receiptData.payment.change) || 0 : receiptData.payment.change;
                    commands.push(...this.stringToBytes(this.formatTotalLine('Change:', change)));
                    commands.push(0x0a);
                }

                commands.push(...this.stringToBytes(this.formatTotalLine('Payment Method:', receiptData.payment.method)));
                commands.push(...[0x0a, 0x0a]);
            }

            // Final separator
            commands.push(...this.stringToBytes('-'.repeat(separatorWidth)));
            commands.push(...[0x0a, 0x0a]);

            // Footer (matching ReceiptLayout)
            commands.push(...[0x1b, 0x61, 0x01]); // Center align
            const footerNotes = receiptData.receiptFooter?.footer_notes || 'Thank you for dining with us! Please settle your bill at the counter.';
            commands.push(...this.stringToBytes(footerNotes));
            commands.push(...[0x0a, 0x0a]);

            if (receiptData.footerMessage) {
                commands.push(...this.stringToBytes(receiptData.footerMessage));
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
     * Format item line for receipt (76mm paper - 47 characters width)
     */
    private formatItemLine(name: string, quantity: number | string, price: number | string, amount: number | string): string {
        const maxWidth = 47; // 76mm paper width
        const numPrice = typeof price === 'string' ? parseFloat(price) || 0 : price;
        const numAmount = typeof amount === 'string' ? parseFloat(amount) || 0 : amount;
        const numQuantity = typeof quantity === 'string' ? parseInt(quantity) || 0 : quantity;

        const qtyPriceText = `${numQuantity}x${numPrice.toFixed(2)}`;
        const amountText = numAmount.toFixed(2);

        // Truncate name if too long (allow more characters for 76mm)
        let itemName = name.length > 29 ? name.substring(0, 26) + '...' : name;

        // Calculate spaces needed
        const spacesNeeded = maxWidth - itemName.length - qtyPriceText.length - amountText.length;
        const spaces = ' '.repeat(Math.max(1, spacesNeeded));

        return `${itemName} ${qtyPriceText}${spaces}${amountText}`;
    }    /**
     * Format total line for receipt with dynamic width based on printer config
     */
    private formatTotalLine(label: string, amount: number | string): string {
        const maxWidth = this.currentConfig?.character_width || 47;

        // Handle special case for Payment Method (no amount formatting)
        if (label === 'Payment Method:') {
            const valueText = String(amount);
            const spaces = ' '.repeat(Math.max(0, maxWidth - label.length - valueText.length));
            return `${label}${spaces}${valueText}`;
        }

        const numAmount = typeof amount === 'string' ? parseFloat(amount) || 0 : amount;
        const amountText = numAmount.toFixed(2);
        const spaces = ' '.repeat(Math.max(0, maxWidth - label.length - amountText.length));
        return `${label}${spaces}${amountText}`;
    }

    /**
     * Format info line for receipt details (Receipt #, Date, etc.)
     */
    private formatInfoLine(label: string, value: string): string {
        const maxWidth = this.currentConfig?.character_width || 47;
        const spaces = ' '.repeat(Math.max(1, maxWidth - label.length - value.length));
        return `${label}${spaces}${value}`;
    }

    /**
     * Format quantity and price line for items
     */
    private formatQuantityLine(quantity: number, price: number | string, amount: number | string): string {
        const maxWidth = (this.currentConfig?.character_width || 47) - 2; // Account for indentation
        const numPrice = typeof price === 'string' ? parseFloat(price) || 0 : price;
        const numAmount = typeof amount === 'string' ? parseFloat(amount) || 0 : amount;

        const qtyPriceText = `${quantity} x ${numPrice.toFixed(2)}`;
        const amountText = numAmount.toFixed(2);
        const spaces = ' '.repeat(Math.max(1, maxWidth - qtyPriceText.length - amountText.length));

        return `${qtyPriceText}${spaces}${amountText}`;
    }

    /**
     * Format option line for selected options
     */
    private formatOptionLine(name: string, price: number | string): string {
        const maxWidth = (this.currentConfig?.character_width || 47) - 2; // Account for indentation
        const numPrice = typeof price === 'string' ? parseFloat(price) || 0 : price;
        const priceText = numPrice.toFixed(2);
        const optionText = `+ ${name}`;
        const spaces = ' '.repeat(Math.max(1, maxWidth - optionText.length - priceText.length));

        return `${optionText}${spaces}${priceText}`;
    }

    /**
     * Format deduction line (Less Tax, Less Discount)
     */
    private formatDeductionLine(label: string, amount: number | string): string {
        const maxWidth = (this.currentConfig?.character_width || 47) - 2; // Account for indentation
        const numAmount = typeof amount === 'string' ? parseFloat(amount) || 0 : amount;
        const amountText = `-${numAmount.toFixed(2)}`;
        const spaces = ' '.repeat(Math.max(1, maxWidth - label.length - amountText.length));

        return `${label}${spaces}${amountText}`;
    }

    /**
     * Group items by order type (matching ReceiptLayout behavior)
     */
    private groupItemsByOrderType(items: Array<{
        name: string;
        quantity: number | string;
        price: number | string;
        amount: number | string;
        selectedOptions?: Array<{
            name: string;
            price: number | string;
        }>;
        lessTax?: number | string;
        discount?: number | string;
        orderType?: string;
    }>): { [key: string]: any[] } {
        const groups: { [key: string]: any[] } = {};

        items.forEach(item => {
            const orderType = item.orderType || 'dine-in';
            if (!groups[orderType]) {
                groups[orderType] = [];
            }
            groups[orderType].push(item);
        });

        return groups;
    }

    /**
     * Get order type label (matching ReceiptLayout)
     */
    private getOrderTypeLabel(orderType: string): string {
        const labels: { [key: string]: string } = {
            'dine-in': 'Dine-in',
            'takeout': 'Takeout',
            'delivery': 'Delivery'
        };
        return labels[orderType] || orderType;
    }

    /**
     * Format date for receipt (matching ReceiptLayout)
     */
    private formatReceiptDate(dateString: string): string {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
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
        const separatorWidth = this.currentConfig?.character_width || 47;
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
