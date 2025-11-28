<template>
    <TransactionsLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Printer Configuration
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                    <!-- Header with Add Button -->
                    <div
                        class="bg-white px-4 py-5 border-b border-gray-200 sm:px-6"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <h3
                                    class="text-lg font-medium leading-6 text-gray-900"
                                >
                                    Printer Configurations
                                </h3>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                                    Manage your thermal printers for kitchen,
                                    bar, and receipt printing.
                                </p>
                            </div>
                            <PrimaryButton @click="showAddDialog = true">
                                <i class="pi pi-plus mr-2"></i>
                                Add Printer
                            </PrimaryButton>
                        </div>
                    </div>

                    <!-- Printers List -->
                    <div class="bg-gray-50 px-4 py-5 sm:p-6">
                        <div
                            v-if="printers.length === 0"
                            class="text-center py-12"
                        >
                            <i
                                class="pi pi-print text-4xl text-gray-400 mb-4"
                            ></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">
                                No printers configured
                            </h3>
                            <p class="text-gray-500 mb-4">
                                Get started by adding your first printer
                                configuration.
                            </p>
                            <PrimaryButton @click="showAddDialog = true">
                                Add First Printer
                            </PrimaryButton>
                        </div>

                        <div
                            v-else
                            class="grid gap-6 md:grid-cols-2 lg:grid-cols-3"
                        >
                            <div
                                v-for="printer in printers"
                                :key="printer.id"
                                class="bg-white rounded-lg shadow-sm border border-gray-200 p-6"
                            >
                                <!-- Printer Header -->
                                <div
                                    class="flex items-start justify-between mb-4"
                                >
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            <i
                                                :class="
                                                    getPrinterIcon(printer.type)
                                                "
                                                class="text-2xl"
                                                :style="{
                                                    color: getPrinterColor(
                                                        printer.type
                                                    ),
                                                }"
                                            ></i>
                                        </div>
                                        <div class="ml-3">
                                            <h4
                                                class="text-lg font-medium text-gray-900"
                                            >
                                                {{ printer.name }}
                                            </h4>
                                            <p
                                                class="text-sm text-gray-500 capitalize"
                                            >
                                                {{ printer.type }} Printer
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span
                                            :class="
                                                printer.is_active
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-gray-100 text-gray-800'
                                            "
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        >
                                            {{
                                                printer.is_active
                                                    ? "Active"
                                                    : "Inactive"
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Printer Details -->
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500"
                                            >Paper Size:</span
                                        >
                                        <span class="font-medium">{{
                                            printer.paper_size
                                        }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500"
                                            >Width:</span
                                        >
                                        <span class="font-medium"
                                            >{{
                                                printer.character_width
                                            }}
                                            chars</span
                                        >
                                    </div>
                                    <div
                                        v-if="printer.bluetooth_name"
                                        class="flex justify-between"
                                    >
                                        <span class="text-gray-500"
                                            >Device:</span
                                        >
                                        <span
                                            class="font-medium truncate ml-2"
                                            >{{ printer.bluetooth_name }}</span
                                        >
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div
                                    class="mt-6 flex items-center justify-between"
                                >
                                    <div class="flex space-x-2">
                                        <SecondaryButton
                                            size="small"
                                            @click="testPrinter(printer)"
                                            :disabled="!printer.is_active"
                                        >
                                            <i class="pi pi-send mr-1"></i>
                                            Test
                                        </SecondaryButton>
                                        <SecondaryButton
                                            size="small"
                                            @click="editPrinter(printer)"
                                        >
                                            <i class="pi pi-pencil mr-1"></i>
                                            Edit
                                        </SecondaryButton>
                                    </div>
                                    <DangerButton
                                        size="small"
                                        @click="confirmDelete(printer)"
                                    >
                                        <i class="pi pi-trash"></i>
                                    </DangerButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Dialog -->
        <Dialog
            v-model:visible="showAddDialog"
            :header="editingPrinter ? 'Edit Printer' : 'Add New Printer'"
            modal
            class="w-full md:w-[50rem]"
        >
            <form @submit.prevent="savePrinter" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Printer Name *
                            </label>
                            <InputText
                                v-model="form.name"
                                placeholder="e.g., Kitchen Printer 1"
                                class="w-full"
                                required
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Printer Type *
                            </label>
                            <Dropdown
                                v-model="form.type"
                                :options="printerTypes"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Select printer type"
                                class="w-full"
                                required
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Paper Size *
                            </label>
                            <Dropdown
                                v-model="form.paper_size"
                                :options="paperSizes"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Select paper size"
                                class="w-full"
                                @change="updateCharacterWidth"
                                required
                            />
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Character Width
                            </label>
                            <InputNumber
                                v-model="form.character_width"
                                :min="20"
                                :max="80"
                                placeholder="Auto-calculated"
                                class="w-full"
                            />
                            <small class="text-gray-500"
                                >Leave empty for auto-calculation based on paper
                                size</small
                            >
                        </div>
                    </div>

                    <!-- Device Discovery -->
                    <div class="space-y-4">
                        <div
                            class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4 md:p-6"
                        >
                            <div class="flex items-center gap-3 mb-3">
                                <div
                                    class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg"
                                >
                                    <i class="pi pi-search text-blue-600"></i>
                                </div>
                                <div>
                                    <h4
                                        class="text-base font-semibold text-blue-900"
                                    >
                                        Find Your Printer
                                    </h4>
                                    <p class="text-sm text-blue-600">
                                        Automatically detect and connect to
                                        nearby printers
                                    </p>
                                </div>
                            </div>
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-4"
                            >
                                <button
                                    @click="scanBluetoothDevices"
                                    :disabled="
                                        !isBluetoothSupported ||
                                        scanningBluetooth
                                    "
                                    class="relative flex flex-col items-center p-4 bg-white border-2 border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed group"
                                    :class="{
                                        'border-blue-400 bg-blue-50':
                                            scanningBluetooth,
                                    }"
                                >
                                    <div
                                        class="flex items-center justify-center w-12 h-12 bg-blue-100 rounded-full mb-2 group-hover:bg-blue-200 transition-colors"
                                    >
                                        <i
                                            class="pi pi-bluetooth text-blue-600 text-lg"
                                            :class="{
                                                'pi-spin pi-spinner':
                                                    scanningBluetooth,
                                            }"
                                        ></i>
                                    </div>
                                    <span
                                        class="text-sm font-medium text-gray-900"
                                        >Bluetooth</span
                                    >
                                    <span
                                        class="text-xs text-gray-500 text-center"
                                        >Wireless thermal printers</span
                                    >
                                    <div
                                        v-if="scanningBluetooth"
                                        class="absolute inset-0 flex items-center justify-center bg-white/80 rounded-lg"
                                    >
                                        <div
                                            class="flex items-center gap-2 text-blue-600"
                                        >
                                            <i
                                                class="pi pi-spinner pi-spin"
                                            ></i>
                                            <span class="text-sm"
                                                >Scanning...</span
                                            >
                                        </div>
                                    </div>
                                </button>

                                <button
                                    @click="scanUSBDevices"
                                    :disabled="!isUSBSupported || scanningUSB"
                                    class="relative flex flex-col items-center p-4 bg-white border-2 border-gray-200 rounded-lg hover:border-green-300 hover:bg-green-50 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed group"
                                    :class="{
                                        'border-green-400 bg-green-50':
                                            scanningUSB,
                                    }"
                                >
                                    <div
                                        class="flex items-center justify-center w-12 h-12 bg-green-100 rounded-full mb-2 group-hover:bg-green-200 transition-colors"
                                    >
                                        <i
                                            class="pi pi-desktop text-green-600 text-lg"
                                            :class="{
                                                'pi-spin pi-spinner':
                                                    scanningUSB,
                                            }"
                                        ></i>
                                    </div>
                                    <span
                                        class="text-sm font-medium text-gray-900"
                                        >USB</span
                                    >
                                    <span
                                        class="text-xs text-gray-500 text-center"
                                        >Wired printers</span
                                    >
                                    <div
                                        v-if="scanningUSB"
                                        class="absolute inset-0 flex items-center justify-center bg-white/80 rounded-lg"
                                    >
                                        <div
                                            class="flex items-center gap-2 text-green-600"
                                        >
                                            <i
                                                class="pi pi-spinner pi-spin"
                                            ></i>
                                            <span class="text-sm"
                                                >Scanning...</span
                                            >
                                        </div>
                                    </div>
                                </button>

                                <button
                                    @click="scanNetworkPrinters"
                                    :disabled="scanningNetwork"
                                    class="relative flex flex-col items-center p-4 bg-white border-2 border-gray-200 rounded-lg hover:border-purple-300 hover:bg-purple-50 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed group sm:col-span-2 lg:col-span-1"
                                    :class="{
                                        'border-purple-400 bg-purple-50':
                                            scanningNetwork,
                                    }"
                                >
                                    <div
                                        class="flex items-center justify-center w-12 h-12 bg-purple-100 rounded-full mb-2 group-hover:bg-purple-200 transition-colors"
                                    >
                                        <i
                                            class="pi pi-wifi text-purple-600 text-lg"
                                            :class="{
                                                'pi-spin pi-spinner':
                                                    scanningNetwork,
                                            }"
                                        ></i>
                                    </div>
                                    <span
                                        class="text-sm font-medium text-gray-900"
                                        >Network</span
                                    >
                                    <span
                                        class="text-xs text-gray-500 text-center"
                                        >WiFi & LAN printers</span
                                    >
                                    <div
                                        v-if="scanningNetwork"
                                        class="absolute inset-0 flex items-center justify-center bg-white/80 rounded-lg"
                                    >
                                        <div
                                            class="flex items-center gap-2 text-purple-600"
                                        >
                                            <i
                                                class="pi pi-spinner pi-spin"
                                            ></i>
                                            <span class="text-sm"
                                                >Scanning...</span
                                            >
                                        </div>
                                    </div>
                                </button>
                            </div>

                            <!-- Available Devices List -->
                            <div
                                v-if="availableDevices.length > 0"
                                class="space-y-3"
                            >
                                <div class="flex items-center gap-2">
                                    <i
                                        class="pi pi-check-circle text-green-500"
                                    ></i>
                                    <h5
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        Found {{ availableDevices.length }}
                                        {{
                                            availableDevices.length === 1
                                                ? "Device"
                                                : "Devices"
                                        }}
                                    </h5>
                                </div>
                                <div
                                    class="max-h-48 overflow-y-auto space-y-2 pr-2"
                                >
                                    <div
                                        v-for="device in availableDevices"
                                        :key="device.id || device.address"
                                        class="group relative bg-white border-2 border-gray-200 rounded-xl p-4 hover:border-blue-300 hover:shadow-md transition-all duration-200 cursor-pointer"
                                        :class="{
                                            'border-green-300 bg-green-50/30':
                                                device.connected,
                                            'hover:bg-blue-50/30':
                                                !device.connected,
                                        }"
                                    >
                                        <div class="flex items-start gap-4">
                                            <!-- Device Icon -->
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="flex items-center justify-center w-12 h-12 rounded-xl transition-colors"
                                                    :class="{
                                                        'bg-blue-100':
                                                            device.type ===
                                                            'bluetooth',
                                                        'bg-green-100':
                                                            device.type ===
                                                            'usb',
                                                        'bg-purple-100':
                                                            device.type ===
                                                            'network',
                                                        'bg-gray-100': ![
                                                            'bluetooth',
                                                            'usb',
                                                            'network',
                                                        ].includes(device.type),
                                                    }"
                                                >
                                                    <i
                                                        :class="[
                                                            getDeviceIcon(
                                                                device.type
                                                            ),
                                                            'text-lg',
                                                            {
                                                                'text-blue-600':
                                                                    device.type ===
                                                                    'bluetooth',
                                                                'text-green-600':
                                                                    device.type ===
                                                                    'usb',
                                                                'text-purple-600':
                                                                    device.type ===
                                                                    'network',
                                                                'text-gray-600':
                                                                    ![
                                                                        'bluetooth',
                                                                        'usb',
                                                                        'network',
                                                                    ].includes(
                                                                        device.type
                                                                    ),
                                                            },
                                                        ]"
                                                    ></i>
                                                </div>
                                            </div>

                                            <!-- Device Info -->
                                            <div class="flex-1 min-w-0">
                                                <div
                                                    class="flex items-start justify-between gap-4"
                                                >
                                                    <div class="flex-1 min-w-0">
                                                        <h6
                                                            class="text-sm font-semibold text-gray-900 truncate"
                                                        >
                                                            {{ device.name }}
                                                        </h6>
                                                        <div
                                                            class="flex flex-wrap items-center gap-2 mt-1"
                                                        >
                                                            <span
                                                                class="text-xs text-gray-500 font-mono bg-gray-100 px-2 py-1 rounded"
                                                            >
                                                                {{
                                                                    device.address
                                                                }}
                                                            </span>
                                                            <span
                                                                v-if="
                                                                    device.connected
                                                                "
                                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
                                                            >
                                                                <i
                                                                    class="pi pi-check-circle"
                                                                ></i>
                                                                Connected
                                                            </span>
                                                            <span
                                                                v-if="
                                                                    device.manufacturer &&
                                                                    device.manufacturer !==
                                                                        'Unknown'
                                                                "
                                                                class="text-xs text-gray-600 bg-gray-50 px-2 py-1 rounded"
                                                            >
                                                                {{
                                                                    device.manufacturer
                                                                }}
                                                            </span>
                                                        </div>
                                                        <div
                                                            class="flex items-center gap-1 mt-2 text-xs text-gray-400"
                                                        >
                                                            <i
                                                                :class="
                                                                    getDeviceIcon(
                                                                        device.type
                                                                    )
                                                                "
                                                                class="text-xs"
                                                            ></i>
                                                            <span
                                                                class="capitalize"
                                                                >{{
                                                                    device.type
                                                                }}
                                                                Connection</span
                                                            >
                                                        </div>
                                                    </div>

                                                    <!-- Action Button -->
                                                    <div class="flex-shrink-0">
                                                        <PrimaryButton
                                                            v-if="
                                                                !device.connected
                                                            "
                                                            size="small"
                                                            @click.stop="
                                                                connectToDevice(
                                                                    device
                                                                )
                                                            "
                                                            class="whitespace-nowrap"
                                                        >
                                                            <i
                                                                class="pi pi-link mr-1"
                                                            ></i>
                                                            Connect
                                                        </PrimaryButton>
                                                        <button
                                                            v-else
                                                            @click.stop="
                                                                selectDevice(
                                                                    device
                                                                )
                                                            "
                                                            class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200"
                                                        >
                                                            <i
                                                                class="pi pi-check"
                                                            ></i>
                                                            Use This Printer
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- No devices found message -->
                            <div
                                v-else-if="hasScanned"
                                class="text-center py-8"
                            >
                                <div
                                    class="flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mx-auto mb-4"
                                >
                                    <i
                                        class="pi pi-search text-2xl text-gray-400"
                                    ></i>
                                </div>
                                <h6
                                    class="text-sm font-medium text-gray-900 mb-2"
                                >
                                    No Printers Found
                                </h6>
                                <p
                                    class="text-sm text-gray-500 mb-4 max-w-xs mx-auto"
                                >
                                    Make sure your printer is powered on and
                                    discoverable, then try scanning again.
                                </p>
                                <div class="space-y-2 text-xs text-gray-400">
                                    <p>
                                        <strong>Bluetooth:</strong> Enable
                                        pairing mode on your printer
                                    </p>
                                    <p>
                                        <strong>USB:</strong> Check cable
                                        connection
                                    </p>
                                    <p>
                                        <strong>Network:</strong> Ensure same
                                        WiFi network
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Manual Configuration -->
                    <div class="border-t border-gray-200 pt-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div
                                class="flex items-center justify-center w-8 h-8 bg-amber-100 rounded-lg"
                            >
                                <i class="pi pi-cog text-amber-600"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">
                                    Manual Configuration
                                </h4>
                                <p class="text-xs text-gray-500">
                                    Enter printer details manually if
                                    auto-detection doesn't work
                                </p>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Bluetooth Device Name
                                </label>
                                <InputText
                                    v-model="form.bluetooth_name"
                                    placeholder="e.g., POS-5890"
                                    class="w-full"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Bluetooth Address (MAC)
                                </label>
                                <InputText
                                    v-model="form.bluetooth_address"
                                    placeholder="e.g., 00:11:22:33:44:55"
                                    class="w-full"
                                />
                            </div>

                            <div
                                class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-4"
                            >
                                <div class="flex items-start gap-3">
                                    <div
                                        class="flex items-center justify-center w-8 h-8 bg-amber-100 rounded-lg flex-shrink-0"
                                    >
                                        <i
                                            class="pi pi-info-circle text-amber-600"
                                        ></i>
                                    </div>
                                    <div class="flex-1">
                                        <h5
                                            class="text-sm font-semibold text-amber-900 mb-1"
                                        >
                                            Technical Settings (Auto-Detected)
                                        </h5>
                                        <p class="text-xs text-amber-700">
                                            These values are automatically
                                            detected when you connect a device
                                            above. Only modify if you know the
                                            specific values for your printer.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Service UUID
                                    </label>
                                    <InputText
                                        v-model="form.service_uuid"
                                        placeholder="Auto-detected from device"
                                        class="w-full text-sm font-mono"
                                        :class="{
                                            'bg-gray-50 text-gray-600':
                                                form.service_uuid,
                                        }"
                                    />
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Characteristic UUID
                                    </label>
                                    <InputText
                                        v-model="form.characteristic_uuid"
                                        placeholder="Auto-detected from device"
                                        class="w-full text-sm font-mono"
                                        :class="{
                                            'bg-gray-50 text-gray-600':
                                                form.characteristic_uuid,
                                        }"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Settings -->
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="flex items-center">
                                <Checkbox
                                    v-model="form.is_active"
                                    inputId="is_active"
                                    binary
                                />
                                <label
                                    for="is_active"
                                    class="ml-2 text-sm text-gray-700"
                                >
                                    Active
                                </label>
                            </div>

                            <div class="flex items-center">
                                <Checkbox
                                    v-model="form.auto_cut"
                                    inputId="auto_cut"
                                    binary
                                />
                                <label
                                    for="auto_cut"
                                    class="ml-2 text-sm text-gray-700"
                                >
                                    Auto Cut Paper
                                </label>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Cut Spacing (lines)
                                </label>
                                <InputNumber
                                    v-model="form.cut_spacing"
                                    :min="0"
                                    :max="10"
                                    class="w-full"
                                />
                            </div>
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Notes
                            </label>
                            <Textarea
                                v-model="form.notes"
                                rows="3"
                                placeholder="Additional notes about this printer..."
                                class="w-full"
                            />
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 pt-4">
                    <SecondaryButton type="button" @click="cancelEdit">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton
                        type="submit"
                        :disabled="!form.name || !form.type || !form.paper_size"
                    >
                        {{ editingPrinter ? "Update" : "Create" }} Printer
                    </PrimaryButton>
                </div>
            </form>
        </Dialog>

        <!-- Delete Confirmation Dialog -->
        <Dialog
            v-model:visible="showDeleteDialog"
            header="Confirm Delete"
            modal
            class="w-full md:w-[30rem]"
        >
            <div class="flex items-start space-x-4">
                <i
                    class="pi pi-exclamation-triangle text-orange-500 text-2xl"
                ></i>
                <div>
                    <p class="text-gray-900">
                        Are you sure you want to delete
                        <strong>{{ printerToDelete?.name }}</strong
                        >?
                    </p>
                    <p class="text-gray-600 text-sm mt-2">
                        This action cannot be undone.
                    </p>
                </div>
            </div>
            <div class="flex justify-end space-x-4 mt-6">
                <SecondaryButton @click="showDeleteDialog = false">
                    Cancel
                </SecondaryButton>
                <DangerButton @click="deletePrinter">
                    Delete Printer
                </DangerButton>
            </div>
        </Dialog>
    </TransactionsLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import TransactionsLayout from "@/Layouts/TransactionsLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import Dialog from "primevue/dialog";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import Dropdown from "primevue/dropdown";
import Checkbox from "primevue/checkbox";
import Textarea from "primevue/textarea";
import { thermalPrinter } from "@/Services/ThermalPrinterService";

const props = defineProps({
    printers: Array,
});

// Dialog states
const showAddDialog = ref(false);
const showDeleteDialog = ref(false);
const editingPrinter = ref(null);
const printerToDelete = ref(null);

// Form data
const form = reactive({
    name: "",
    type: "",
    bluetooth_name: "",
    bluetooth_address: "",
    service_uuid: "000018f0-0000-1000-8000-00805f9b34fb",
    characteristic_uuid: "00002af1-0000-1000-8000-00805f9b34fb",
    paper_size: "76mm",
    character_width: null,
    is_active: true,
    auto_cut: true,
    cut_spacing: 5,
    notes: "",
    selectedDevice: null, // Reference to the selected device for connection reuse
});

// Device discovery state
const scanningBluetooth = ref(false);
const scanningUSB = ref(false);
const scanningNetwork = ref(false);
const availableDevices = ref([]);
const hasScanned = ref(false);
const isBluetoothSupported = ref(false);
const isUSBSupported = ref(false);

// Options
const printerTypes = [
    { label: "Kitchen Printer", value: "kitchen" },
    { label: "Bar Printer", value: "bar" },
    { label: "Receipt Printer", value: "receipt" },
];

const paperSizes = [
    { label: "36mm (32 chars)", value: "36mm" },
    { label: "76mm (47 chars)", value: "76mm" },
];

// Methods
const getPrinterIcon = (type) => {
    switch (type) {
        case "kitchen":
            return "pi pi-home";
        case "bar":
            return "pi pi-star";
        case "receipt":
            return "pi pi-receipt";
        default:
            return "pi pi-print";
    }
};

const getPrinterColor = (type) => {
    switch (type) {
        case "kitchen":
            return "#f59e0b";
        case "bar":
            return "#8b5cf6";
        case "receipt":
            return "#10b981";
        default:
            return "#6b7280";
    }
};

const updateCharacterWidth = () => {
    if (form.paper_size === "36mm") {
        form.character_width = 32;
    } else if (form.paper_size === "76mm") {
        form.character_width = 47;
    }
};

const resetForm = () => {
    Object.assign(form, {
        name: "",
        type: "",
        bluetooth_name: "",
        bluetooth_address: "",
        service_uuid: "000018f0-0000-1000-8000-00805f9b34fb",
        characteristic_uuid: "00002af1-0000-1000-8000-00805f9b34fb",
        paper_size: "76mm",
        character_width: null,
        is_active: true,
        auto_cut: true,
        cut_spacing: 5,
        notes: "",
    });
};

const editPrinter = (printer) => {
    editingPrinter.value = printer;
    Object.assign(form, { ...printer });
    showAddDialog.value = true;
};

const cancelEdit = () => {
    showAddDialog.value = false;
    editingPrinter.value = null;
    resetForm();
};

const savePrinter = () => {
    const url = editingPrinter.value
        ? `/printer-config/${editingPrinter.value.id}`
        : "/printer-config";

    const method = editingPrinter.value ? "put" : "post";

    router[method](
        url,
        { ...form },
        {
            onSuccess: () => {
                showAddDialog.value = false;
                editingPrinter.value = null;
                resetForm();
            },
        }
    );
};

const confirmDelete = (printer) => {
    printerToDelete.value = printer;
    showDeleteDialog.value = true;
};

const deletePrinter = () => {
    router.delete(`/printer-config/${printerToDelete.value.id}`, {
        onSuccess: () => {
            showDeleteDialog.value = false;
            printerToDelete.value = null;
        },
    });
};

const testPrinter = async (printer) => {
    try {
        // Update thermal printer service with this printer's config
        const success = await thermalPrinter.connectToPrinter();
        if (success) {
            await thermalPrinter.testPrint();
            alert("Test print sent successfully!");
        } else {
            alert("Failed to connect to printer");
        }
    } catch (error) {
        console.error("Test print failed:", error);
        alert("Test print failed: " + error.message);
    }
};

// Device discovery methods
const scanBluetoothDevices = async () => {
    if (!isBluetoothSupported.value) {
        alert(
            "Bluetooth is not supported in this browser. Please use Chrome, Edge, or another Chromium-based browser."
        );
        return;
    }

    scanningBluetooth.value = true;
    hasScanned.value = true;

    try {
        // Request Bluetooth device with printer-specific filters
        const device = await navigator.bluetooth.requestDevice({
            filters: [
                { services: ["000018f0-0000-1000-8000-00805f9b34fb"] }, // Generic printer service
                { services: ["49535343-fe7d-4ae5-8fa9-9fafd205e455"] }, // Serial port service (common for thermal printers)
                { namePrefix: "POS" }, // Common prefix for POS printers
                { namePrefix: "TM-" }, // Epson thermal printers
                { namePrefix: "RP" }, // Star printers
                { namePrefix: "CT" }, // Citizen printers
                { namePrefix: "MTP" }, // Mobile thermal printer
                { namePrefix: "Printer" }, // Generic printer names
            ],
            optionalServices: [
                "000018f0-0000-1000-8000-00805f9b34fb",
                "49535343-fe7d-4ae5-8fa9-9fafd205e455",
                "6e400001-b5a3-f393-e0a9-e50e24dcca9e", // Nordic UART Service
                "0000fff0-0000-1000-8000-00805f9b34fb", // Another common service
            ],
        });

        if (device) {
            // Try to connect and discover services automatically
            const newDevice = {
                id: device.id,
                name: device.name || "Unknown Bluetooth Printer",
                address: device.id,
                type: "bluetooth",
                manufacturer: "Unknown",
                connected: false,
                device: device,
                services: [],
                characteristics: [],
            };

            // Try to get more device info
            try {
                if (device.gatt) {
                    await device.gatt.connect();
                    newDevice.connected = true;

                    // Get available services
                    const services = await device.gatt.getPrimaryServices();
                    newDevice.services = services.map((s) => s.uuid);

                    // Disconnect for now - user will connect when they select the device
                    device.gatt.disconnect();
                    newDevice.connected = false;
                }
            } catch (connectError) {
                console.log(
                    "Could not auto-connect to device for service discovery:",
                    connectError
                );
            }

            // Check if device already exists
            const exists = availableDevices.value.find(
                (d) => d.id === device.id
            );
            if (!exists) {
                availableDevices.value.push(newDevice);
            }
        }
    } catch (error) {
        if (error.name === "NotFoundError") {
            alert(
                "No Bluetooth printers found. Make sure your printer is in pairing mode and try again."
            );
        } else {
            console.log("Bluetooth scan cancelled or failed:", error);
        }
    } finally {
        scanningBluetooth.value = false;
    }
};
const scanUSBDevices = async () => {
    if (!isUSBSupported.value) {
        alert(
            "USB device access is not supported in this browser. Please use Chrome, Edge, or another Chromium-based browser."
        );
        return;
    }

    scanningUSB.value = true;
    hasScanned.value = true;

    try {
        // Request USB device with printer vendor IDs
        const device = await navigator.usb.requestDevice({
            filters: [
                { vendorId: 0x04b8 }, // Epson
                { vendorId: 0x154f }, // Citizen
                { vendorId: 0x0fe6 }, // ICS Advent (Star Micronics)
                { vendorId: 0x0519 }, // Star Micronics
                { vendorId: 0x2730 }, // Rugtek
                { vendorId: 0x20d1 }, // Rongta
                { vendorId: 0x0dd4 }, // Deltec
                // Add more printer vendor IDs as needed
            ],
        });

        if (device) {
            const newDevice = {
                id: device.serialNumber || `usb-${Date.now()}`,
                name: device.productName || "USB Printer",
                address: `USB Port ${device.deviceAddress || "Unknown"}`,
                type: "usb",
                manufacturer: device.manufacturerName || "Unknown",
                connected: true,
                device: device,
                vendorId: device.vendorId,
                productId: device.productId,
            };

            // Check if device already exists
            const exists = availableDevices.value.find(
                (d) => d.id === device.serialNumber
            );
            if (!exists) {
                availableDevices.value.push(newDevice);
            }
        }
    } catch (error) {
        if (error.name === "NotFoundError") {
            alert(
                "No USB printers found. Make sure your printer is connected and powered on."
            );
        } else {
            console.log("USB scan cancelled or failed:", error);
        }
    } finally {
        scanningUSB.value = false;
    }
};

const scanNetworkPrinters = async () => {
    scanningNetwork.value = true;
    hasScanned.value = true;

    try {
        // Simulate network printer discovery
        // In a real implementation, this would scan the local network
        await new Promise((resolve) => setTimeout(resolve, 2000));

        // Mock network printers (in real app, this would come from network scan)
        const mockNetworkPrinters = [
            {
                name: "HP LaserJet Pro",
                address: "192.168.1.100",
                type: "network",
                id: "hp-laserjet-192.168.1.100",
            },
            {
                name: "Epson TM-T20III",
                address: "192.168.1.101",
                type: "network",
                id: "epson-tm-192.168.1.101",
            },
        ];

        // Add mock printers to available devices
        mockNetworkPrinters.forEach((printer) => {
            const exists = availableDevices.value.find(
                (d) => d.id === printer.id
            );
            if (!exists) {
                availableDevices.value.push(printer);
            }
        });
    } catch (error) {
        console.error("Network scan failed:", error);
    } finally {
        scanningNetwork.value = false;
    }
};

const connectToDevice = async (device) => {
    try {
        if (device.type === "bluetooth" && device.device) {
            // Check if already connected
            if (device.device.gatt && device.device.gatt.connected) {
                device.connected = true;
                alert(`${device.name} is already connected!`);
                return;
            }

            // Connect to Bluetooth device
            await device.device.gatt.connect();
            device.connected = true;

            // Auto-discover services and characteristics
            const services = await device.device.gatt.getPrimaryServices();
            device.characteristics = device.characteristics || [];

            for (const service of services) {
                try {
                    const characteristics = await service.getCharacteristics();
                    const newCharacteristics = characteristics.map((c) => ({
                        serviceUuid: service.uuid,
                        characteristicUuid: c.uuid,
                        properties: c.properties,
                    }));
                    device.characteristics.push(...newCharacteristics);
                } catch (e) {
                    console.log(
                        "Could not get characteristics for service:",
                        service.uuid
                    );
                }
            }

            // Add disconnection listener
            device.device.addEventListener("gattserverdisconnected", () => {
                device.connected = false;
                console.log(`${device.name} disconnected`);
            });

            alert(
                `✅ Connected to ${device.name}!\n\nYou can now select this printer or test it directly.`
            );
        } else if (device.type === "usb" && device.device) {
            // USB devices are typically already "connected" when detected
            device.connected = true;
            alert(`✅ USB printer ${device.name} is ready to use!`);
        }
    } catch (error) {
        device.connected = false;
        console.error("Failed to connect to device:", error);
        if (error.name === "NetworkError") {
            alert(
                `❌ Connection failed: ${device.name} is not available or already connected to another application.`
            );
        } else {
            alert(
                `❌ Failed to connect to ${device.name}. Please try again.\n\nError: ${error.message}`
            );
        }
    }
};

const selectDevice = (device) => {
    // Auto-fill form with device information
    form.bluetooth_name = device.name;
    form.bluetooth_address = device.address;

    // Auto-generate printer name based on device type
    if (!form.name) {
        const typePrefix = form.type
            ? form.type.charAt(0).toUpperCase() + form.type.slice(1)
            : "Printer";
        form.name = `${typePrefix} - ${device.name}`;
    }

    // Auto-detect and set UUIDs for Bluetooth devices
    if (
        device.type === "bluetooth" &&
        device.characteristics &&
        device.characteristics.length > 0
    ) {
        // Find the best service/characteristic pair for printing
        const printCharacteristic = device.characteristics.find(
            (c) => c.properties.write || c.properties.writeWithoutResponse
        );

        if (printCharacteristic) {
            form.service_uuid = printCharacteristic.serviceUuid;
            form.characteristic_uuid = printCharacteristic.characteristicUuid;
        }
    }

    // Store the selected device reference for future testing
    form.selectedDevice = device;

    // Keep the device in available devices for future reference
    // Don't clear the list so user can see the connected device

    alert(
        `✅ Printer configuration updated!\n\nDevice: ${device.name}\nType: ${
            device.type
        }\nStatus: ${
            device.connected ? "Connected" : "Detected"
        }\n\nPlease review the settings and save.`
    );
};
const getDeviceIcon = (type) => {
    switch (type) {
        case "bluetooth":
            return "pi pi-bluetooth";
        case "network":
            return "pi pi-wifi";
        case "usb":
            return "pi pi-desktop";
        default:
            return "pi pi-print";
    }
};

// Check device support capabilities on mount
onMounted(() => {
    isBluetoothSupported.value = "bluetooth" in navigator;
    isUSBSupported.value = "usb" in navigator;
});
</script>
