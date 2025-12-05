import { Buffer } from 'buffer';
import axios from 'axios';

interface BillData {
    storeName: string;
    branchName?: string;
    storeAddress: string;
    storePhone?: string;
    billNumber: string;
    cashier: string;
    date: string;
    time?: string;
    tableInfo: {
        name?: string;
        number_of_pax?: number;
    };
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
    discountAmount?: number | string;
    discountName?: string;
    discountType?: string;
    removeTax?: boolean;
    total: number | string;
    payment?: {
        method: string;
        amountPaid: number | string;
        change?: number | string;
    };
    receiptHeader?: any;
    billFooter?: {
        footer_notes?: string;
    };
    footerMessage?: string;
}

interface ReceiptData {
    storeName: string;
    branchName?: string;
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
    receiptHeader?: any;
    receiptFooter?: {
        footer_notes?: string;
    };
    footerMessage?: string;
}

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

interface SessionSummaryData {
    session_number: string | null;
    or_number_start: string | null;
    or_number_end: string | null;
    bill_number_start: string | null;
    bill_number_end: string | null;
    gross_sales: number | null;
    regular_discount: number | null;
    senior_discount: number | null;
    pwd_discount: number | null;
    net_sales: number | null;
    non_vat_sales: number | null;
    vat_sales: number | null;
    vat_amount: number | null;
    less_tax: number | null;
    cancelled_count: number | null;
    cancelled_amount: number | null;
    transactions_count: number | null;
    sku_count: number | null;
    total_quantity: number | null;
    previous_reading: number | null;
    running_total: number | null;
    beginning_cash: number | null;
    closing_cash: number | null;
    cash_denomination: number | null;
    cash_denomination_details?: Record<string, number> | null;
    counter_id_start?: string | null;
    counter_id_end?: string | null;
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
    font_sizes?: {
        company_name?: 'small' | 'medium' | 'large';
        headers?: 'small' | 'medium' | 'large';
        items?: 'small' | 'medium' | 'large';
        totals?: 'small' | 'medium' | 'large';
        footer?: 'small' | 'medium' | 'large';
    };
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

    // ESC/POS Command Constants for better readability
    private readonly ESC_POS = {
        // Basic Commands
        INIT: [0x1b, 0x40],           // ESC @ - Initialize printer
        LINE_FEED: [0x0a],            // LF - Line feed

        // Text Alignment
        ALIGN_LEFT: [0x1b, 0x61, 0x00],     // ESC a 0 - Left align
        ALIGN_CENTER: [0x1b, 0x61, 0x01],   // ESC a 1 - Center align
        ALIGN_RIGHT: [0x1b, 0x61, 0x02],    // ESC a 2 - Right align

        // Text Emphasis
        BOLD_ON: [0x1b, 0x45, 0x01],        // ESC E 1 - Bold on
        BOLD_OFF: [0x1b, 0x45, 0x00],       // ESC E 0 - Bold off

        // Font Sizes
        FONT_NORMAL: [0x1d, 0x21, 0x00],    // GS ! 0 - Normal size
        FONT_DOUBLE_WIDTH: [0x1d, 0x21, 0x01], // GS ! 1 - Double width
        FONT_DOUBLE_HEIGHT: [0x1d, 0x21, 0x10], // GS ! 16 - Double height
        FONT_DOUBLE_BOTH: [0x1d, 0x21, 0x11],   // GS ! 17 - Double width & height

        // Paper Control
        FULL_CUT: [0x1d, 0x56, 0x00],       // GS V 0 - Full cut
        PARTIAL_CUT: [0x1d, 0x56, 0x01],    // GS V 1 - Partial cut

        // Character Encoding
        SET_UTF8: [0x1b, 0x74, 0x06],       // ESC t 6 - Set UTF-8 encoding
    };

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
    public async printReceipt(receiptData: ReceiptData): Promise<void> {
        if (!this.isConnected()) {
            throw new Error('Printer not connected. Please connect first.');
        }

        try {
            // Build ESC/POS commands
            const commands: number[] = [];

            // Initialize printer
            commands.push(...this.ESC_POS.INIT);

            // Set character encoding to UTF-8
            commands.push(...this.ESC_POS.SET_UTF8);

            // Header - Business Info
            commands.push(...this.ESC_POS.ALIGN_CENTER);
            commands.push(...this.ESC_POS.BOLD_ON);

            // Company name with configurable font size (default: medium for better fit)
            const companyNameSize = this.currentConfig?.font_sizes?.company_name || 'medium';
            this.addTextWithSize(commands, receiptData.storeName, companyNameSize);
            commands.push(...this.ESC_POS.LINE_FEED);

            // Branch name (if provided)
            if (receiptData.branchName) {
                commands.push(...this.ESC_POS.BOLD_OFF);
                commands.push(...this.stringToBytes(receiptData.branchName));
                commands.push(...this.ESC_POS.LINE_FEED);
                commands.push(...this.ESC_POS.BOLD_ON);
            }

            // Receipt Headers (from branch configuration)
            if (receiptData.receiptHeader && Array.isArray(receiptData.receiptHeader)) {
                commands.push(...this.ESC_POS.BOLD_OFF);
                receiptData.receiptHeader.forEach((header: string) => {
                    commands.push(...this.stringToBytes(header));
                    commands.push(...this.ESC_POS.LINE_FEED);
                });
            }

            // Store address and phone - small size
            commands.push(...this.ESC_POS.BOLD_OFF);
            commands.push(...this.stringToBytes(receiptData.storeAddress));
            commands.push(...this.ESC_POS.LINE_FEED);
            if (receiptData.storePhone) {
                commands.push(...this.stringToBytes(receiptData.storePhone));
                commands.push(...this.ESC_POS.LINE_FEED);
            }

            // Header separator
            const separatorWidth = this.currentConfig?.character_width || 47;
            commands.push(...this.stringToBytes('-'.repeat(separatorWidth)));
            commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

            // Receipt Info - left align
            commands.push(...this.ESC_POS.ALIGN_LEFT);
            commands.push(...this.stringToBytes(this.formatInfoLine('Receipt #:', receiptData.orderNumber)));
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.stringToBytes(this.formatInfoLine('Date:', this.formatReceiptDate(receiptData.date))));
            commands.push(...this.ESC_POS.LINE_FEED);
            if (receiptData.time) {
                commands.push(...this.stringToBytes(this.formatInfoLine('Time:', receiptData.time)));
                commands.push(...this.ESC_POS.LINE_FEED);
            }
            if (receiptData.tableNumber) {
                commands.push(...this.stringToBytes(this.formatInfoLine('Table:', receiptData.tableNumber)));
                commands.push(...this.ESC_POS.LINE_FEED);
            }
            if (receiptData.cashier) {
                commands.push(...this.stringToBytes(this.formatInfoLine('Cashier:', receiptData.cashier)));
                commands.push(...this.ESC_POS.LINE_FEED);
            }
            if (receiptData.orderType) {
                commands.push(...this.stringToBytes(this.formatInfoLine('Order Type:', receiptData.orderType)));
                commands.push(...this.ESC_POS.LINE_FEED);
            }
            commands.push(...this.ESC_POS.LINE_FEED);

            // Items separator
            const separatorLine = '-'.repeat(separatorWidth);
            commands.push(...this.stringToBytes(separatorLine));
            commands.push(...this.ESC_POS.LINE_FEED);

            // ORDER ITEMS header - centered
            commands.push(...this.ESC_POS.ALIGN_CENTER);
            commands.push(...this.ESC_POS.BOLD_ON);

            // Headers with configurable font size
            const headerSize = this.currentConfig?.font_sizes?.headers || 'small';
            this.addTextWithSize(commands, 'ORDER ITEMS', headerSize);

            commands.push(...this.ESC_POS.BOLD_OFF);
            commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

            // Left align for items
            commands.push(...this.ESC_POS.ALIGN_LEFT);

            // Group items by order type (matching ReceiptLayout)
            const groupedItems = this.groupItemsByOrderType(receiptData.items);

            Object.entries(groupedItems).forEach(([orderType, items]) => {
                // Order type header if there are multiple types
                if (Object.keys(groupedItems).length > 1) {
                    commands.push(...this.ESC_POS.ALIGN_CENTER);
                    commands.push(...this.ESC_POS.BOLD_ON);
                    commands.push(...this.stringToBytes(this.getOrderTypeLabel(orderType)));
                    commands.push(...this.ESC_POS.BOLD_OFF);
                    commands.push(...this.ESC_POS.ALIGN_LEFT);
                    commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);
                }

                // Print items in this group
                items.forEach(item => {
                    // Main item line
                    commands.push(...this.stringToBytes(item.name));
                    commands.push(...this.ESC_POS.LINE_FEED);

                    // Quantity and price line (indented) - always show if we have price and amount
                    const quantity = typeof item.quantity === 'string' ? parseInt(item.quantity) || 1 : (item.quantity || 1);
                    if (item.price !== undefined && item.amount !== undefined) {
                        const qtyLine = this.formatQuantityLine(quantity, item.price, item.amount);
                        commands.push(...this.stringToBytes(`  ${qtyLine}`));
                        commands.push(...this.ESC_POS.LINE_FEED);
                    }

                    // Selected options (if any)
                    if (item.selectedOptions && item.selectedOptions.length > 0) {
                        item.selectedOptions.forEach(option => {
                            const optionLine = this.formatOptionLine(option.name, option.price);
                            commands.push(...this.stringToBytes(`  ${optionLine}`));
                            commands.push(...this.ESC_POS.LINE_FEED);
                        });
                    }

                    // Less tax (if any)
                    if (item.less_tax !== undefined && item.less_tax !== null && item.less_tax !== '') {
                        const lessTaxValue = typeof item.less_tax === 'string' ? parseFloat(item.less_tax) : item.less_tax;
                        if (lessTaxValue !== 0) {
                            const taxLine = this.formatDeductionLine('Less Tax:', lessTaxValue);
                            commands.push(...this.stringToBytes(`  ${taxLine}`));
                            commands.push(...this.ESC_POS.LINE_FEED);
                        }
                    }

                    // Less discount (if any)
                    if (item.discount !== undefined && item.discount !== null && item.discount !== '') {
                        const discountValue = typeof item.discount === 'string' ? parseFloat(item.discount) : item.discount;
                        if (discountValue !== 0) {
                            const discountLine = this.formatDeductionLine('Less Discount:', discountValue);
                            commands.push(...this.stringToBytes(`  ${discountLine}`));
                            commands.push(...this.ESC_POS.LINE_FEED);
                        }
                    }

                    commands.push(...this.ESC_POS.LINE_FEED); // Space between items
                });
            });

            // Items separator
            commands.push(...this.stringToBytes(separatorLine));
            commands.push(...this.ESC_POS.LINE_FEED);

            // Totals section
            const subtotal = typeof receiptData.subtotal === 'string' ? parseFloat(receiptData.subtotal) || 0 : receiptData.subtotal;
            commands.push(...this.stringToBytes(this.formatTotalLine('Subtotal:', subtotal)));
            commands.push(...this.ESC_POS.LINE_FEED);

            // Less Tax (matching ReceiptLayout naming)
            if (receiptData.lessTax !== undefined && receiptData.lessTax !== null && receiptData.lessTax !== '') {
                const lessTaxValue = typeof receiptData.lessTax === 'string' ? parseFloat(receiptData.lessTax) : receiptData.lessTax;
                if (lessTaxValue !== 0) {
                    commands.push(...this.stringToBytes(this.formatTotalLine('Less Tax:', -lessTaxValue)));
                    commands.push(...this.ESC_POS.LINE_FEED);
                }
            }

            // Less Discount (matching ReceiptLayout naming)
            if (receiptData.lessDiscount !== undefined && receiptData.lessDiscount !== null && receiptData.lessDiscount !== '') {
                const lessDiscountValue = typeof receiptData.lessDiscount === 'string' ? parseFloat(receiptData.lessDiscount) : receiptData.lessDiscount;
                if (lessDiscountValue !== 0) {
                    commands.push(...this.stringToBytes(this.formatTotalLine('Less Discount:', -lessDiscountValue)));
                    commands.push(...this.ESC_POS.LINE_FEED);
                }
            }

            // Total separator and amount - bold
            commands.push(...this.stringToBytes('-'.repeat(separatorWidth)));
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.ESC_POS.BOLD_ON);

            // Total with configurable font size
            const total = typeof receiptData.total === 'string' ? parseFloat(receiptData.total) || 0 : receiptData.total;
            const totalSize = this.currentConfig?.font_sizes?.totals || 'medium';
            this.addTextWithSize(commands, this.formatTotalLine('TOTAL:', total), totalSize);

            commands.push(...this.ESC_POS.BOLD_OFF);
            commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

            // Payment info (matching ReceiptLayout format)
            if (receiptData.payment) {
                commands.push(...this.ESC_POS.ALIGN_CENTER);
                commands.push(...this.ESC_POS.BOLD_ON);
                commands.push(...this.stringToBytes('PAYMENT'));
                commands.push(...this.ESC_POS.BOLD_OFF);
                commands.push(...this.ESC_POS.ALIGN_LEFT);
                commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

                const paymentAmount = typeof receiptData.payment.amountPaid === 'string' ? parseFloat(receiptData.payment.amountPaid) || 0 : receiptData.payment.amountPaid;
                commands.push(...this.stringToBytes(this.formatTotalLine('Amount Paid:', paymentAmount)));
                commands.push(...this.ESC_POS.LINE_FEED);

                if (receiptData.payment.change && parseFloat(String(receiptData.payment.change)) > 0) {
                    const change = typeof receiptData.payment.change === 'string' ? parseFloat(receiptData.payment.change) || 0 : receiptData.payment.change;
                    commands.push(...this.stringToBytes(this.formatTotalLine('Change:', change)));
                    commands.push(...this.ESC_POS.LINE_FEED);
                }

                commands.push(...this.stringToBytes(this.formatTotalLine('Payment Method:', receiptData.payment.method)));
                commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);
            }

            // Final separator
            commands.push(...this.stringToBytes('-'.repeat(separatorWidth)));
            commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

            // Footer (matching ReceiptLayout)
            commands.push(...this.ESC_POS.ALIGN_CENTER);
            const footerNotes = receiptData.receiptFooter?.footer_notes || 'Thank you for dining with us! Please settle your bill at the counter.';
            commands.push(...this.stringToBytes(footerNotes));
            commands.push(...this.ESC_POS.LINE_FEED);

            // Receipt Footers (from branch configuration - displayed at bottom)
            if (receiptData.receiptFooter && receiptData.receiptFooter.footer_text && Array.isArray(receiptData.receiptFooter.footer_text)) {
                receiptData.receiptFooter.footer_text.forEach((footer: string) => {
                    commands.push(...this.stringToBytes(footer));
                    commands.push(...this.ESC_POS.LINE_FEED);
                });
            }

            commands.push(...this.ESC_POS.LINE_FEED);

            if (receiptData.footerMessage) {
                commands.push(...this.stringToBytes(receiptData.footerMessage));
                commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);
            }

            // Add spacing before cut based on printer configuration
            const cutSpacing = this.currentConfig?.cut_spacing || 5;
            for (let i = 0; i < cutSpacing; i++) {
                commands.push(...this.ESC_POS.LINE_FEED);
            }

            // Cut paper if auto_cut is enabled
            if (this.currentConfig?.auto_cut !== false) {
                commands.push(...this.ESC_POS.FULL_CUT);
            }

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
     * Get font size ESC/POS commands
     */
    private getFontSizeCommands(size: 'small' | 'medium' | 'large' = 'small'): number[] {
        switch (size) {
            case 'small':
                return this.ESC_POS.FONT_NORMAL;
            case 'medium':
                return this.ESC_POS.FONT_DOUBLE_WIDTH;
            case 'large':
                return this.ESC_POS.FONT_DOUBLE_BOTH;
            default:
                return this.ESC_POS.FONT_NORMAL;
        }
    }

    /**
     * Apply font size and add text with automatic size reset
     */
    private addTextWithSize(commands: number[], text: string, size: 'small' | 'medium' | 'large' = 'small'): void {
        // Set font size
        commands.push(...this.getFontSizeCommands(size));
        // Add text
        commands.push(...this.stringToBytes(text));
        // Reset to normal size if not small
        if (size !== 'small') {
            commands.push(...this.getFontSizeCommands('small'));
        }
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
        const amountText = this.formatNumberWithComma(numAmount.toFixed(2));
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

        const qtyPriceText = `${quantity} x ${this.formatNumberWithComma(numPrice.toFixed(2))}`;
        const amountText = this.formatNumberWithComma(numAmount.toFixed(2));
        const spaces = ' '.repeat(Math.max(1, maxWidth - qtyPriceText.length - amountText.length));

        return `${qtyPriceText}${spaces}${amountText}`;
    }

    /**
     * Format option line for selected options
     */
    private formatOptionLine(name: string, price: number | string): string {
        const maxWidth = (this.currentConfig?.character_width || 47) - 2; // Account for indentation
        const numPrice = typeof price === 'string' ? parseFloat(price) || 0 : price;
        const priceText = this.formatNumberWithComma(numPrice.toFixed(2));
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
        const amountText = `-${this.formatNumberWithComma(numAmount.toFixed(2))}`;
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
     * Print bill using ESC/POS commands
     */
    public async printBill(billData: BillData): Promise<void> {
        if (!this.isConnected()) {
            throw new Error('Printer not connected. Please connect first.');
        }

        try {
            // Build ESC/POS commands
            const commands: number[] = [];

            // Initialize printer
            commands.push(...this.ESC_POS.INIT);

            // Set character encoding to UTF-8
            commands.push(...this.ESC_POS.SET_UTF8);

            // Header - Business Info
            commands.push(...this.ESC_POS.ALIGN_CENTER);
            commands.push(...this.ESC_POS.BOLD_ON);

            // Company name with configurable font size (default: medium for better fit)
            const companyNameSize = this.currentConfig?.font_sizes?.company_name || 'medium';
            this.addTextWithSize(commands, billData.storeName, companyNameSize);
            commands.push(...this.ESC_POS.LINE_FEED);

            // Branch name (if provided)
            if (billData.branchName) {
                commands.push(...this.ESC_POS.BOLD_OFF);
                commands.push(...this.stringToBytes(billData.branchName));
                commands.push(...this.ESC_POS.LINE_FEED);
                commands.push(...this.ESC_POS.BOLD_ON);
            }

            // Receipt Headers (from branch configuration)
            if (billData.receiptHeader && Array.isArray(billData.receiptHeader)) {
                commands.push(...this.ESC_POS.BOLD_OFF);
                billData.receiptHeader.forEach((header: string) => {
                    commands.push(...this.stringToBytes(header));
                    commands.push(...this.ESC_POS.LINE_FEED);
                });
            }

            // Store address and phone - normal size
            commands.push(...this.ESC_POS.BOLD_OFF);
            commands.push(...this.stringToBytes(billData.storeAddress));
            commands.push(...this.ESC_POS.LINE_FEED);
            if (billData.storePhone) {
                commands.push(...this.stringToBytes(billData.storePhone));
                commands.push(...this.ESC_POS.LINE_FEED);
            }

            // Header separator
            const separatorWidth = this.currentConfig?.character_width || 47;
            commands.push(...this.stringToBytes('-'.repeat(separatorWidth)));
            commands.push(...this.ESC_POS.LINE_FEED);

            // Table header - medium size and centered
            commands.push(...this.ESC_POS.ALIGN_CENTER);
            this.addTextWithSize(commands, ` ${billData.tableInfo.name || 'N/A'}`, 'medium');
            commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

            // Bill Info - left align
            commands.push(...this.ESC_POS.ALIGN_LEFT);
            commands.push(...this.stringToBytes(this.formatInfoLine('Date:', this.formatReceiptDate(billData.date))));
            commands.push(...this.ESC_POS.LINE_FEED);
            if (billData.time) {
                commands.push(...this.stringToBytes(this.formatInfoLine('Time:', billData.time)));
                commands.push(...this.ESC_POS.LINE_FEED);
            }
            commands.push(...this.stringToBytes(this.formatInfoLine('Bill #:', billData.billNumber)));
            commands.push(...this.ESC_POS.LINE_FEED);
            if (billData.cashier) {
                commands.push(...this.stringToBytes(this.formatInfoLine('Cashier:', billData.cashier)));
                commands.push(...this.ESC_POS.LINE_FEED);
            }

            commands.push(...this.ESC_POS.LINE_FEED);

            // Items separator
            const separatorLine = '-'.repeat(separatorWidth);
            commands.push(...this.stringToBytes(separatorLine));
            commands.push(...this.ESC_POS.LINE_FEED);

            // GROUP ITEMS header - centered
            // commands.push(...this.ESC_POS.ALIGN_CENTER);
            // commands.push(...this.ESC_POS.BOLD_ON);
            // const headerSize = this.currentConfig?.font_sizes?.headers || 'small';
            // this.addTextWithSize(commands, 'ORDER ITEMS', headerSize);
            // commands.push(...this.ESC_POS.BOLD_OFF);
            commands.push(...this.ESC_POS.LINE_FEED);

            // Group items by order type (matching BillLayout)
            const groupedItems = this.groupItemsByOrderType(billData.items);

            Object.entries(groupedItems).forEach(([orderType, items]) => {
                // Order type header - always show to match BillLayout
                commands.push(...this.ESC_POS.ALIGN_CENTER);
                commands.push(...this.stringToBytes(this.getOrderTypeLabel(orderType)));
                commands.push(...this.ESC_POS.ALIGN_LEFT);
                commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

                // Print items in this group
                items.forEach(item => {
                    // Main item line
                    commands.push(...this.stringToBytes(item.name));
                    commands.push(...this.ESC_POS.LINE_FEED);

                    // Quantity and price line (indented) - always show if we have price and amount
                    const quantity = typeof item.quantity === 'string' ? parseInt(item.quantity) || 1 : (item.quantity || 1);
                    if (item.price !== undefined && item.amount !== undefined) {
                        const qtyLine = this.formatQuantityLine(quantity, item.price, item.amount);
                        commands.push(...this.stringToBytes(`  ${qtyLine}`));
                        commands.push(...this.ESC_POS.LINE_FEED);
                    }

                    // Selected options (if any)
                    if (item.selectedOptions && item.selectedOptions.length > 0) {
                        item.selectedOptions.forEach(option => {
                            const optionLine = this.formatOptionLine(option.name, option.price);
                            commands.push(...this.stringToBytes(`  ${optionLine}`));
                            commands.push(...this.ESC_POS.LINE_FEED);
                        });
                    }

                    // Less tax (if any)
                    if (item.less_tax !== undefined && item.less_tax !== null && item.less_tax !== '') {
                        const lessTaxValue = typeof item.less_tax === 'string' ? parseFloat(item.less_tax) : item.less_tax;
                        if (lessTaxValue !== 0) {
                            const taxLine = this.formatDeductionLine('Less Tax:', lessTaxValue);
                            commands.push(...this.stringToBytes(`  ${taxLine}`));
                            commands.push(...this.ESC_POS.LINE_FEED);
                        }
                    }

                    // Less discount (if any)
                    if (item.discount !== undefined && item.discount !== null && item.discount !== '') {
                        const discountValue = typeof item.discount === 'string' ? parseFloat(item.discount) : item.discount;
                        if (discountValue !== 0) {
                            const discountLine = this.formatDeductionLine('Less Discount:', discountValue);
                            commands.push(...this.stringToBytes(`  ${discountLine}`));
                            commands.push(...this.ESC_POS.LINE_FEED);
                        }
                    }

                    commands.push(...this.ESC_POS.LINE_FEED); // Space between items
                });
            });

            // Items separator
            commands.push(...this.stringToBytes(separatorLine));
            commands.push(...this.ESC_POS.LINE_FEED);

            //only show the sub total if they are not equal with the total
            // Totals section
            if (billData.subtotal !== billData.total) {
                const subtotal = typeof billData.subtotal === 'string' ? parseFloat(billData.subtotal) || 0 : billData.subtotal;
                commands.push(...this.stringToBytes(this.formatTotalLine('Subtotal:', subtotal)));
                commands.push(...this.ESC_POS.LINE_FEED);
            }

            // Less Tax (if applicable)
            if (billData.lessTax !== undefined && billData.lessTax !== null && billData.lessTax !== '') {
                const lessTaxValue = typeof billData.lessTax === 'string' ? parseFloat(billData.lessTax) : billData.lessTax;
                if (lessTaxValue !== 0) {
                    commands.push(...this.stringToBytes(this.formatTotalLine('Less Tax:', -lessTaxValue)));
                    commands.push(...this.ESC_POS.LINE_FEED);
                }
            }

            // Less Discount (if applicable)
            if (billData.lessDiscount !== undefined && billData.lessDiscount !== null && billData.lessDiscount !== '') {
                const lessDiscountValue = typeof billData.lessDiscount === 'string' ? parseFloat(billData.lessDiscount) : billData.lessDiscount;
                if (lessDiscountValue !== 0) {
                    commands.push(...this.stringToBytes(this.formatTotalLine('Less Discount:', -lessDiscountValue)));
                    commands.push(...this.ESC_POS.LINE_FEED);
                }
            }

            // Discount details (if applicable)
            if (billData.discountAmount && parseFloat(String(billData.discountAmount)) > 0) {
                const discountAmount = typeof billData.discountAmount === 'string' ? parseFloat(billData.discountAmount) || 0 : billData.discountAmount;
                const discountLabel = billData.discountName ? `${billData.discountName}:` : 'Discount:';
                commands.push(...this.stringToBytes(this.formatTotalLine(discountLabel, -discountAmount)));
                commands.push(...this.ESC_POS.LINE_FEED);
            }

            // Total separator and amount - bold with configurable font size
            commands.push(...this.stringToBytes('-'.repeat(separatorWidth)));
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.ESC_POS.BOLD_ON);
            const total = typeof billData.total === 'string' ? parseFloat(billData.total) || 0 : billData.total;
            const totalSize = this.currentConfig?.font_sizes?.totals || 'medium';
            this.addTextWithSize(commands, this.formatTotalLine('TOTAL:', total), totalSize);
            commands.push(...this.ESC_POS.BOLD_OFF);
            commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

            // Payment info (if provided)
            if (billData.payment) {
                commands.push(...this.ESC_POS.ALIGN_CENTER);
                commands.push(...this.ESC_POS.BOLD_ON);
                commands.push(...this.stringToBytes('PAYMENT'));
                commands.push(...this.ESC_POS.BOLD_OFF);
                commands.push(...this.ESC_POS.ALIGN_LEFT);
                commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

                const paymentAmount = typeof billData.payment.amountPaid === 'string' ? parseFloat(billData.payment.amountPaid) || 0 : billData.payment.amountPaid;
                commands.push(...this.stringToBytes(this.formatTotalLine('Amount Paid:', paymentAmount)));
                commands.push(...this.ESC_POS.LINE_FEED);

                if (billData.payment.change && parseFloat(String(billData.payment.change)) > 0) {
                    const change = typeof billData.payment.change === 'string' ? parseFloat(billData.payment.change) || 0 : billData.payment.change;
                    commands.push(...this.stringToBytes(this.formatTotalLine('Change:', change)));
                    commands.push(...this.ESC_POS.LINE_FEED);
                }

                commands.push(...this.stringToBytes(this.formatTotalLine('Payment Method:', billData.payment.method)));
                commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);
            }

            // Final separator
            commands.push(...this.stringToBytes('-'.repeat(separatorWidth)));
            commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

            // Footer (matching BillLayout)
            commands.push(...this.ESC_POS.ALIGN_CENTER);
            const footerNotes = billData.billFooter?.footer_notes || 'Thank you for dining with us!';
            commands.push(...this.stringToBytes(footerNotes));
            commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

            if (billData.footerMessage) {
                commands.push(...this.stringToBytes(billData.footerMessage));
                commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);
            }

            // Add spacing before cut based on printer configuration
            const cutSpacing = this.currentConfig?.cut_spacing || 5;
            for (let i = 0; i < cutSpacing; i++) {
                commands.push(...this.ESC_POS.LINE_FEED);
            }

            // Cut paper if auto_cut is enabled
            if (this.currentConfig?.auto_cut !== false) {
                commands.push(...this.ESC_POS.FULL_CUT);
            }

            // Send to printer
            const data = new Uint8Array(commands);
            await this.sendToPrinter(data);

            console.log('✅ Bill printed successfully');
        } catch (error) {
            console.error('❌ Failed to print bill:', error);
            throw error;
        }
    }

    /**
     * Print session summary (X Reading Report)
     */
    public async printSessionSummary(
        sessionSummary: SessionSummaryData,
        merchantName: string = 'QUICKJUAN POS',
        merchantAddress: string = '',
        operatorName: string = '',
        merchantTin: string = '',
        registrationNumber: string = ''
    ): Promise<void> {
        if (!this.isConnected()) {
            throw new Error('Printer not connected. Please connect first.');
        }

        try {
            // Build ESC/POS commands
            const commands: number[] = [];

            // Initialize printer
            commands.push(...this.ESC_POS.INIT);

            // Set character encoding to UTF-8
            commands.push(...this.ESC_POS.SET_UTF8);

            // Header - Business Info
            commands.push(...this.ESC_POS.ALIGN_CENTER);
            commands.push(...this.ESC_POS.BOLD_ON);

            // Company name
            const companyNameSize = this.currentConfig?.font_sizes?.company_name || 'medium';
            this.addTextWithSize(commands, merchantName, companyNameSize);
            commands.push(...this.ESC_POS.LINE_FEED);

            // Store address
            commands.push(...this.ESC_POS.BOLD_OFF);
            if (merchantAddress) {
                commands.push(...this.stringToBytes(merchantAddress));
                commands.push(...this.ESC_POS.LINE_FEED);
            }

            // Operator name
            if (operatorName) {
                commands.push(...this.stringToBytes(`Operated By: ${operatorName}`));
                commands.push(...this.ESC_POS.LINE_FEED);
            }

            // TIN and Registration
            if (merchantTin) {
                commands.push(...this.stringToBytes(`TIN: ${merchantTin}`));
                commands.push(...this.ESC_POS.LINE_FEED);
            }
            if (registrationNumber) {
                commands.push(...this.stringToBytes(`Registration No.: ${registrationNumber}`));
                commands.push(...this.ESC_POS.LINE_FEED);
            }

            // Header separator
            const separatorWidth = this.currentConfig?.character_width || 47;
            commands.push(...this.stringToBytes('-'.repeat(separatorWidth)));
            commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

            // X Reading Report header
            commands.push(...this.ESC_POS.ALIGN_CENTER);
            commands.push(...this.ESC_POS.BOLD_ON);
            commands.push(...this.stringToBytes('X Reading Report'));
            commands.push(...this.ESC_POS.BOLD_OFF);
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.stringToBytes(new Date().toLocaleDateString()));
            commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

            // Left align for details
            commands.push(...this.ESC_POS.ALIGN_LEFT);

            // Sales summary
            commands.push(...this.stringToBytes(this.formatTotalLine('Gross Sales:', sessionSummary.gross_sales || 0)));
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.stringToBytes(this.formatTotalLine('Regular Discount:', sessionSummary.regular_discount || 0)));
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.stringToBytes(this.formatTotalLine('Senior Discount:', sessionSummary.senior_discount || 0)));
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.stringToBytes(this.formatTotalLine('PWD Discount:', sessionSummary.pwd_discount || 0)));
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.stringToBytes(this.formatTotalLine('Less Tax:', sessionSummary.less_tax || 0)));
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.ESC_POS.BOLD_ON);
            commands.push(...this.stringToBytes(this.formatTotalLine('Net Sales:', sessionSummary.net_sales || 0)));
            commands.push(...this.ESC_POS.BOLD_OFF);
            commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

            // VAT breakdown
            commands.push(...this.stringToBytes(this.formatTotalLine('VAT Sales:', sessionSummary.vat_sales || 0)));
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.stringToBytes(this.formatTotalLine('Non-VAT Sales:', sessionSummary.non_vat_sales || 0)));
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.stringToBytes(this.formatTotalLine('VAT:', sessionSummary.vat_amount || 0)));
            commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

            // Session details
            commands.push(...this.stringToBytes(this.formatInfoLine('Session Number:', sessionSummary.session_number || '')));
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.stringToBytes(this.formatInfoLine('OR Number Start:', sessionSummary.or_number_start || '')));
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.stringToBytes(this.formatInfoLine('OR Number End:', sessionSummary.or_number_end || '')));
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.stringToBytes(this.formatInfoLine('Bill Number Start:', sessionSummary.bill_number_start || '')));
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.stringToBytes(this.formatInfoLine('Bill Number End:', sessionSummary.bill_number_end || '')));
            commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

            // Counter IDs
            if (sessionSummary.counter_id_start || sessionSummary.counter_id_end) {
                commands.push(...this.stringToBytes(this.formatInfoLine('Counter ID Start:', sessionSummary.counter_id_start || '-')));
                commands.push(...this.ESC_POS.LINE_FEED);
                commands.push(...this.stringToBytes(this.formatInfoLine('Counter ID End:', sessionSummary.counter_id_end || '-')));
                commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);
            }

            // Cancellation info
            commands.push(...this.stringToBytes(this.formatTotalLine('Cancelled Amount:', sessionSummary.cancelled_amount || 0)));
            commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

            // Transaction summary
            commands.push(...this.stringToBytes(this.formatTotalLine('No of Transactions:', sessionSummary.transactions_count || 0)));
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.stringToBytes(this.formatTotalLine('Number of SKU:', sessionSummary.sku_count || 0)));
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.stringToBytes(this.formatTotalLine('Total Quantity:', sessionSummary.total_quantity || 0)));
            commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

            // Reading summary
            commands.push(...this.stringToBytes(this.formatTotalLine('Previous Reading:', sessionSummary.previous_reading || 0)));
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.ESC_POS.BOLD_ON);
            commands.push(...this.stringToBytes(this.formatTotalLine('Net Sales:', sessionSummary.net_sales || 0)));
            commands.push(...this.ESC_POS.BOLD_OFF);
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.stringToBytes(this.formatTotalLine('Running Total:', sessionSummary.running_total || 0)));
            commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

            // Cash summary
            commands.push(...this.stringToBytes(this.formatTotalLine('Beginning Cash:', sessionSummary.beginning_cash || 0)));
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.stringToBytes(this.formatTotalLine('Closing Cash:', sessionSummary.closing_cash || 0)));
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.stringToBytes(this.formatTotalLine('Cash Denomination:', sessionSummary.cash_denomination || 0)));
            commands.push(...this.ESC_POS.LINE_FEED);

            // Cash denomination details
            if (sessionSummary.cash_denomination_details) {
                commands.push(...this.stringToBytes('Cash Breakdown:'));
                commands.push(...this.ESC_POS.LINE_FEED);
                Object.entries(sessionSummary.cash_denomination_details).forEach(([denom, count]) => {
                    const numDenom = parseFloat(denom);
                    const numCount = count as number;
                    if (numCount > 0) {
                        const line = `${this.formatNumberWithComma(numDenom.toFixed(2))} x ${numCount} = ${this.formatNumberWithComma((numDenom * numCount).toFixed(2))}`;
                        commands.push(...this.stringToBytes(`  ${line}`));
                        commands.push(...this.ESC_POS.LINE_FEED);
                    }
                });
                commands.push(...this.ESC_POS.LINE_FEED);
            }

            // End marker
            commands.push(...this.ESC_POS.ALIGN_CENTER);
            commands.push(...this.ESC_POS.BOLD_ON);
            commands.push(...this.stringToBytes('X Reading End'));
            commands.push(...this.ESC_POS.BOLD_OFF);
            commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

            // Add spacing before cut based on printer configuration
            const cutSpacing = this.currentConfig?.cut_spacing || 5;
            for (let i = 0; i < cutSpacing; i++) {
                commands.push(...this.ESC_POS.LINE_FEED);
            }

            // Cut paper if auto_cut is enabled
            if (this.currentConfig?.auto_cut !== false) {
                commands.push(...this.ESC_POS.FULL_CUT);
            }

            // Send to printer
            const data = new Uint8Array(commands);
            await this.sendToPrinter(data);

            console.log('✅ Session summary printed successfully');
        } catch (error) {
            console.error('❌ Failed to print session summary:', error);
            throw error;
        }
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
        commands.push(...this.ESC_POS.INIT);

        // Center align, bold
        commands.push(...this.ESC_POS.ALIGN_CENTER);
        commands.push(...this.ESC_POS.BOLD_ON);

        // Test text
        const configInfo = this.currentConfig ? `${this.currentConfig.name} - ${this.currentConfig.paper_size}` : 'DEFAULT CONFIG';
        commands.push(...this.stringToBytes(`TEST PRINT - ${configInfo}`));
        commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

        // Normal text
        commands.push(...this.ESC_POS.BOLD_OFF);
        commands.push(...this.ESC_POS.ALIGN_LEFT);
        commands.push(...this.stringToBytes('Printer connected successfully!'));
        commands.push(...this.ESC_POS.LINE_FEED);

        if (this.currentConfig) {
            commands.push(...this.stringToBytes(`Config: ${this.currentConfig.name}`));
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.stringToBytes(`Paper: ${this.currentConfig.paper_size} (${this.currentConfig.character_width} chars)`));
            commands.push(...this.ESC_POS.LINE_FEED);
        }

        // Dynamic separator
        const separatorWidth = this.currentConfig?.character_width || 47;
        commands.push(...this.stringToBytes('-'.repeat(separatorWidth)));
        commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

        // Add proper spacing before cut based on config
        const cutSpacing = this.currentConfig?.cut_spacing || 5;
        for (let i = 0; i < cutSpacing; i++) {
            commands.push(...this.ESC_POS.LINE_FEED);
        }

        // Cut if auto_cut is enabled
        if (this.currentConfig?.auto_cut !== false) {
            commands.push(...this.ESC_POS.FULL_CUT);
        }

        const data = new Uint8Array(commands);
        await this.sendToPrinter(data);
    }

    /**
     * Print placed order items grouped by order type
     * Used for kitchen/bar printers to show new orders
     */
    public async printPlacedOrder(orderNumber: number, tableName: string, orderGroups: Array<{
        orderType: string;
        items: Array<{
            id: number;
            description: string;
            packaging: string;
            qty: number | string;
            modifiers: Array<{ name: string; value: string }>;
            notes: string;
        }>;
        totalItems: number;
    }>): Promise<void> {
        if (!this.isConnected()) {
            throw new Error('Printer not connected. Please connect first.');
        }

        try {
            const commands: number[] = [];

            // Initialize printer
            commands.push(...this.ESC_POS.INIT);

            // Set character encoding to UTF-8
            commands.push(...this.ESC_POS.SET_UTF8);

            // Header - Table name and order number (centered and bold)
            commands.push(...this.ESC_POS.ALIGN_CENTER);
            commands.push(...this.ESC_POS.BOLD_ON);
            this.addTextWithSize(commands, tableName, 'large');
            commands.push(...this.ESC_POS.BOLD_OFF);
            commands.push(...this.ESC_POS.LINE_FEED);

            // Order number
            commands.push(...this.ESC_POS.BOLD_ON);
            commands.push(...this.stringToBytes(`Order #${orderNumber}`));
            commands.push(...this.ESC_POS.BOLD_OFF);
            commands.push(...this.ESC_POS.LINE_FEED);

            // Timestamp
            const currentTime = new Date().toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });
            commands.push(...this.stringToBytes(currentTime));
            commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

            const separatorWidth = this.currentConfig?.character_width || 47;
            const separatorLine = '-'.repeat(separatorWidth);

            // Process each order type group
            orderGroups.forEach((group, groupIndex) => {
                // Order type header - large, bold, centered
                commands.push(...this.ESC_POS.ALIGN_CENTER);
                commands.push(...this.ESC_POS.BOLD_ON);
                this.addTextWithSize(commands, this.getOrderTypeLabel(group.orderType), 'large');
                commands.push(...this.ESC_POS.BOLD_OFF);
                commands.push(...this.ESC_POS.LINE_FEED);

                // Separator
                commands.push(...this.ESC_POS.ALIGN_LEFT);
                commands.push(...this.stringToBytes(separatorLine));
                commands.push(...this.ESC_POS.LINE_FEED);

                // Print items in this group
                group.items.forEach(item => {
                    // Item description - quantity and name
                    const qty = typeof item.qty === 'string' ? item.qty : item.qty;
                    const itemLine = `${qty}x ${item.description}`;
                    commands.push(...this.stringToBytes(itemLine));
                    commands.push(...this.ESC_POS.LINE_FEED);

                    // Packaging info if available
                    if (item.packaging) {
                        commands.push(...this.stringToBytes(`  Pkg: ${item.packaging}`));
                        commands.push(...this.ESC_POS.LINE_FEED);
                    }

                    // Modifiers (options/add-ons)
                    if (item.modifiers && item.modifiers.length > 0) {
                        item.modifiers.forEach(modifier => {
                            const modLine = `  + ${modifier.name}`;
                            if (modifier.value) {
                                commands.push(...this.stringToBytes(`${modLine}: ${modifier.value}`));
                            } else {
                                commands.push(...this.stringToBytes(modLine));
                            }
                            commands.push(...this.ESC_POS.LINE_FEED);
                        });
                    }

                    // Notes (special instructions)
                    if (item.notes) {
                        commands.push(...this.stringToBytes(`  Note: ${item.notes}`));
                        commands.push(...this.ESC_POS.LINE_FEED);
                    }

                    commands.push(...this.ESC_POS.LINE_FEED);
                });

                // Separator between groups
                if (groupIndex < orderGroups.length - 1) {
                    commands.push(...this.stringToBytes(separatorLine));
                    commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);
                }
            });

            // Footer separator and total items count
            commands.push(...this.stringToBytes(separatorLine));
            commands.push(...this.ESC_POS.LINE_FEED);

            const totalItems = orderGroups.reduce((sum, group) => sum + group.totalItems, 0);
            commands.push(...this.ESC_POS.ALIGN_CENTER);
            commands.push(...this.stringToBytes(`Total Items: ${totalItems}`));
            commands.push(...this.ESC_POS.LINE_FEED, ...this.ESC_POS.LINE_FEED);

            // Add spacing before cut to prevent cutting off last content
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.ESC_POS.LINE_FEED);
            commands.push(...this.ESC_POS.LINE_FEED);

            // Cut if auto_cut is enabled
            if (this.currentConfig?.auto_cut !== false) {
                commands.push(...this.ESC_POS.FULL_CUT);
            }

            const data = new Uint8Array(commands);
            await this.sendToPrinter(data);
        } catch (error) {
            console.error('Failed to print placed order:', error);
            throw error;
        }
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

    /**
     * Format number with comma separators (e.g., 1000.00 → 1,000.00)
     */
    private formatNumberWithComma(numberString: string): string {
        const parts = numberString.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return parts.join('.');
    }
}

// Export singleton instance
export const thermalPrinter = new ThermalPrinterService();
export default ThermalPrinterService;
export type { PrinterConfig, BillData, ReceiptData, SessionSummaryData };
