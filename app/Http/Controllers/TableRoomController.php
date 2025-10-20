<?php
namespace App\Http\Controllers;

use App\Http\Requests\TableRoomRequest;
use App\Models\TableRoomLocation;
use App\Services\TableRoomService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TableRoomController extends Controller
{
    public function __construct(protected TableRoomService $tableRoomService)
    {
        $this->tableRoomService = $tableRoomService;
    }

    public function list(): Response
    {
        $tableRooms = $this->tableRoomService->model
            ->with(['tableRoomLocation', 'branch'])
            ->activeBranch()
            ->get()
            ->map(fn($tableRoom) => [
                'id'                     => $tableRoom->id,
                'name'                   => $tableRoom->name,
                'chairs'                 => $tableRoom->chairs,
                'status'                 => $tableRoom->status,
                'table_x'                => $tableRoom->table_x ?? 0,
                'table_y'                => $tableRoom->table_y ?? 0,
                'table_width'            => $tableRoom->table_width ?? 150,
                'table_height'           => $tableRoom->table_height ?? 100,
                'getFeaturedImageUrl'    => $tableRoom->getFeaturedImageUrl(),
                'tableRoomLocation'      => $tableRoom->tableRoomLocation,
                'table_room_location_id' => $tableRoom->table_room_location_id,
                'branch_id'              => $tableRoom->branch_id,
            ]);

        $locations = TableRoomLocation::all()
            ->map(fn($location) => [
                'id'   => $location->id,
                'name' => $location->name,
            ]);

        return Inertia::render('TableManagement/List', [
            'tables'    => $tableRooms,
            'locations' => $locations,
        ]);
    }

    public function store(TableRoomRequest $request): RedirectResponse
    {
        try {
            $this->tableRoomService->store($request);

            return redirect()->back()->with('success', 'Table/Room created successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to create Table/Room.');
        }
    }

    public function update(TableRoomRequest $request, int $tableId): RedirectResponse
    {
        try {
            $this->tableRoomService->update($request, $tableId);

            return redirect()->back()->with('success', 'Table updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to update table.');
        }
    }

    public function destroy(int $tableId): RedirectResponse
    {
        try {
            $table = $this->tableRoomService->model->find($tableId);

            $table->delete();
            return redirect()->back()->with('success', 'Table deleted successfully.');

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete table.');
        }
    }
}
