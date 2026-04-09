<?php
namespace App\Http\Controllers;

use App\Models\PrinterConfig;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrinterConfigController extends Controller
{
    /**
     * Display printer configurations
     */
    public function index(?int $tableId = null)
    {
        $cashierId = (int) auth()->id();

        $printers = PrinterConfig::query()
            ->where('cashier_id', $cashierId)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        // Backward-compat: if this cashier has no personal configs yet, clone legacy configs (cashier_id = null).
        if ($printers->isEmpty()) {
            $legacyPrinters = PrinterConfig::query()
                ->whereNull('cashier_id')
                ->orderBy('type')
                ->orderBy('name')
                ->get();

            if ($legacyPrinters->isNotEmpty()) {
                foreach ($legacyPrinters as $legacy) {
                    PrinterConfig::create([
                        'cashier_id' => $cashierId,
                        'name' => $legacy->name,
                        'type' => $legacy->type,
                        'bluetooth_name' => $legacy->bluetooth_name,
                        'bluetooth_address' => $legacy->bluetooth_address,
                        'service_uuid' => $legacy->service_uuid,
                        'characteristic_uuid' => $legacy->characteristic_uuid,
                        'paper_size' => $legacy->paper_size,
                        'character_width' => $legacy->character_width,
                        'is_active' => $legacy->is_active,
                        'auto_cut' => $legacy->auto_cut,
                        'cut_spacing' => $legacy->cut_spacing,
                        'print_categories' => $legacy->print_categories,
                        'notes' => $legacy->notes,
                    ]);
                }

                $printers = PrinterConfig::query()
                    ->where('cashier_id', $cashierId)
                    ->orderBy('type')
                    ->orderBy('name')
                    ->get();
            }
        }

        return Inertia::render('PrinterConfig/Index', [
            'printers' => $printers,
            'tableId'  => $tableId,
        ]);
    }

    /**
     * Store a new printer configuration
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'type'                => 'required|in:kitchen,bar,receipt',
            'bluetooth_name'      => 'nullable|string|max:255',
            'bluetooth_address'   => 'nullable|string|max:255',
            'service_uuid'        => 'required|string|max:255',
            'characteristic_uuid' => 'required|string|max:255',
            'paper_size'          => 'required|in:36mm,76mm',
            'character_width'     => 'nullable|integer|min:20|max:80',
            'is_active'           => 'boolean',
            'auto_cut'            => 'boolean',
            'cut_spacing'         => 'integer|min:0|max:10',
            'print_categories'    => 'nullable|array',
            'notes'               => 'nullable|string|max:1000',
        ]);

        // Set character width based on paper size if not provided
        if (! $validated['character_width']) {
            $validated['character_width'] = $validated['paper_size'] === '36mm' ? 32 : 48;
        }

        $validated['cashier_id'] = (int) auth()->id();

        if (($validated['is_active'] ?? true) === true) {
            PrinterConfig::query()
                ->where('cashier_id', $validated['cashier_id'])
                ->where('type', $validated['type'])
                ->update(['is_active' => false]);
        }

        PrinterConfig::create($validated);

        return redirect()->back()->with('success', 'Printer configuration created successfully.');
    }

    /**
     * Update printer configuration
     */
    public function update(Request $request, PrinterConfig $printerConfig)
    {
        abort_unless((int) $printerConfig->cashier_id === (int) auth()->id(), 403);

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'type'                => 'required|in:kitchen,bar,receipt',
            'bluetooth_name'      => 'nullable|string|max:255',
            'bluetooth_address'   => 'nullable|string|max:255',
            'service_uuid'        => 'required|string|max:255',
            'characteristic_uuid' => 'required|string|max:255',
            'paper_size'          => 'required|in:36mm,76mm',
            'character_width'     => 'nullable|integer|min:20|max:80',
            'is_active'           => 'boolean',
            'auto_cut'            => 'boolean',
            'cut_spacing'         => 'integer|min:0|max:10',
            'print_categories'    => 'nullable|array',
            'notes'               => 'nullable|string|max:1000',
        ]);

        // Update character width based on paper size if not provided
        if (! $validated['character_width']) {
            $validated['character_width'] = $validated['paper_size'] === '36mm' ? 32 : 48;
        }

        if (($validated['is_active'] ?? $printerConfig->is_active) === true) {
            PrinterConfig::query()
                ->where('cashier_id', (int) auth()->id())
                ->where('type', $validated['type'])
                ->where('id', '!=', $printerConfig->id)
                ->update(['is_active' => false]);
        }

        $printerConfig->update($validated);

        return redirect()->back()->with('success', 'Printer configuration updated successfully.');
    }

    /**
     * Delete printer configuration
     */
    public function destroy(PrinterConfig $printerConfig)
    {
        abort_unless((int) $printerConfig->cashier_id === (int) auth()->id(), 403);

        $printerConfig->delete();

        return redirect()->back()->with('success', 'Printer configuration deleted successfully.');
    }

    /**
     * Get printer configuration for API use
     */
    public function getConfig($type)
    {
        $cashierId = (int) auth()->id();
        $printer = PrinterConfig::getForTypeForCashier($type, $cashierId);

        if (! $printer) {
            return response()->json(['error' => 'No active printer configuration found for type: ' . $type], 404);
        }

        return response()->json($printer);
    }

    /**
     * Test printer connection
     */
    public function testPrinter(Request $request, PrinterConfig $printerConfig)
    {
        abort_unless((int) $printerConfig->cashier_id === (int) auth()->id(), 403);

        // Return printer configuration for frontend to test
        return response()->json([
            'printer' => $printerConfig,
            'message' => 'Use this configuration to test printer connection from frontend',
        ]);
    }
}
