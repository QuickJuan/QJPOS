<?php
namespace App\Http\Controllers;

use App\Http\Requests\TableRoomLocationRequest;
use App\Http\Requests\TableRoomRequest;
use App\Models\TableRoom;
use App\Models\TableRoomLocation;
use App\Services\TableManagementService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TableManagementController extends Controller
{
    public function __construct(protected TableManagementService $tableManagementService)
    {
        $this->tableManagementService = $tableManagementService;
    }

    public function index(Request $request): Response
    {
        $selectedLocation = $request->get('location_id');

        $tableRooms = TableRoom::with(['tableRoomLocation', 'branch'])
            ->activeBranch()
            ->selectedLocation($selectedLocation)
            ->get()
            ->map(fn($tableRoom) => [
                'id'                     => $tableRoom->id,
                'name'                   => $tableRoom->name,
                'chairs'                 => $tableRoom->chairs,
                'status'                 => $tableRoom->status,
                'x'                      => $tableRoom->table_x ?? 0,
                'y'                      => $tableRoom->table_y ?? 0,
                'width'                  => $tableRoom->table_width ?? 150,
                'height'                 => $tableRoom->table_height ?? 100,
                'img'                    => $tableRoom->getFeaturedImageUrl() ?: $tableRoom->getDefaultTableImage($tableRoom->chairs),
                'customer'               => $tableRoom->customer ?? '',
                'orders'                 => $tableRoom->orders ?? [],
                'table_room_location_id' => $tableRoom->table_room_location_id,
                'branch_id'              => $tableRoom->branch_id,
            ]);

        $locations = TableRoomLocation::all()
            ->map(fn($location) => [
                'id'   => $location->id,
                'name' => $location->name,
            ]);

        return Inertia::render('TableManagement/Index', [
            'tables'           => $tableRooms,
            'locations'        => $locations,
            'selectedLocation' => $selectedLocation,
        ]);
    }

    public function storeLocation(TableRoomLocationRequest $request): RedirectResponse
    {
        try {
            $this->tableManagementService->storeLocation($request);

            return redirect()->route('table-management.index')->with('success', 'Table/Room created successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to create Table/Room.');
        }
    }

    public function updateLocation(TableRoomLocationRequest $request, TableRoomLocation $location): RedirectResponse
    {
        try {
            $this->tableManagementService->updateLocation($request, $location);

            return redirect()->route('table-management.index')->with('success', 'Table/Room Location updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to update Table/Room Location.');
        }
    }

    public function destroyLocation(TableRoomLocation $location): RedirectResponse
    {
        if ($location->tableRooms()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete location with existing tables.');
        }

        $location->delete();

        return redirect()->route('table-management.index')->with('success', 'Table/Room Location deleted successfully.');
    }
}
