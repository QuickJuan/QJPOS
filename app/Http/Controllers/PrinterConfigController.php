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
        $printers = PrinterConfig::orderBy('type')->orderBy('name')->get();

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

        PrinterConfig::create($validated);

        return redirect()->back()->with('success', 'Printer configuration created successfully.');
    }

    /**
     * Update printer configuration
     */
    public function update(Request $request, PrinterConfig $printerConfig)
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

        // Update character width based on paper size if not provided
        if (! $validated['character_width']) {
            $validated['character_width'] = $validated['paper_size'] === '36mm' ? 32 : 48;
        }

        $printerConfig->update($validated);

        return redirect()->back()->with('success', 'Printer configuration updated successfully.');
    }

    /**
     * Delete printer configuration
     */
    public function destroy(PrinterConfig $printerConfig)
    {
        $printerConfig->delete();

        return redirect()->back()->with('success', 'Printer configuration deleted successfully.');
    }

    /**
     * Get printer configuration for API use
     */
    public function getConfig($type)
    {
        $printer = PrinterConfig::getForType($type);

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
        // Return printer configuration for frontend to test
        return response()->json([
            'printer' => $printerConfig,
            'message' => 'Use this configuration to test printer connection from frontend',
        ]);
    }
}
