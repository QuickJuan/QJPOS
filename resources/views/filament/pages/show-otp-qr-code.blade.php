<x-filament-panels::page>
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold mb-4">OTP QR Code</h2>

            <div class="text-center mb-8">
                <p class="text-gray-600 mb-6">Scan this QR code with your authenticator app:</p>

                @if($qrCode)
                    <div class="flex justify-center mb-8">
                        <img src="{{ $qrCode }}" alt="OTP QR Code" class="w-64 h-64 border-2 border-gray-300 rounded-lg">
                    </div>
                @endif
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
                <p class="text-sm text-gray-700 mb-3 font-semibold">Or enter the secret manually:</p>
                <div class="bg-white border border-gray-300 rounded p-4 overflow-x-auto">
                    <code class="text-sm font-mono break-all">{{ $secret }}</code>
                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <p class="text-sm text-gray-700">
                    <strong>Important:</strong> Make sure the user saves this in their authenticator app. They will use the time-based codes to log in at the waiter terminal.
                </p>
            </div>
        </div>
    </div>
</x-filament-panels::page>
