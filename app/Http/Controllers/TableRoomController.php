<?php
namespace App\Http\Controllers;

use App\Http\Requests\TableReservationRequest;
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
                'featured_image_url'     => $tableRoom->getFeaturedImageUrl(),
                'tableRoomLocation'      => $tableRoom->tableRoomLocation,
                'table_room_location_id' => $tableRoom->table_room_location_id,
                'branch_id'              => $tableRoom->branch_id,
                'location_type'          => $tableRoom->tableRoomLocation?->location_type,
            ]);

        $locations = TableRoomLocation::all()
            ->map(fn($location) => [
                'id'            => $location->id,
                'name'          => $location->name,
                'location_type' => $location->location_type,
            ]);

        return Inertia::render('TableManagement/List', [
            'tables'    => $tableRooms,
            'locations' => $locations,
        ]);
    }

    public function store(TableRoomRequest $request): RedirectResponse
    {
        try {
            $table = $this->tableRoomService->store($request);

            if ($request->hasFile('featured_image')) {
                $table->clearMediaCollection('featured_image');
                $table->addMediaFromRequest('featured_image')
                    ->toMediaCollection('featured_image');
            }

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

    public function mergeTable(Request $request, int $tableId): RedirectResponse
    {
        $validated = $request->validate([
            'merge_to' => 'required|integer|exists:table_rooms,id',
        ]);

        try {
            $this->tableRoomService->mergeTable($tableId, $validated['merge_to']);

            return redirect()->route('retail-cashier.tables')->with('success', 'Table merged successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reserveTable(TableReservationRequest $request): RedirectResponse
    {
        try {
            $this->tableRoomService->reserveTable($request);

            return redirect()->route('retail-cashier.tables')->with('success', 'Table reservation created successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function unmergeTable(Request $request, int $tableId): RedirectResponse
    {
        try {
            $this->tableRoomService->unmergeTable($tableId);

            return redirect()->route('retail-cashier.tables')->with('success', 'Table unmerged successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
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

    public function bulkUpdatePositions(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'positions'           => ['required', 'array'],
            'positions.*.id'      => ['required', 'integer', 'exists:table_rooms,id'],
            'positions.*.table_x' => ['required', 'integer'],
            'positions.*.table_y' => ['required', 'integer'],
        ]);

        try {
            foreach ($validated['positions'] as $pos) {
                $table = $this->tableRoomService->model->find($pos['id']);
                if ($table) {
                    $table->update([
                        'table_x' => $pos['table_x'],
                        'table_y' => $pos['table_y'],
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Table positions updated successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to bulk update positions.');
        }
    }
}
