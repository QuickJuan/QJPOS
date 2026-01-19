<?php
namespace App\Http\Controllers;

use Exception;
use App\Enums\CurrentRole;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\TableRoom;
use Illuminate\Http\Request;
use App\Models\TableRoomLocation;
use Illuminate\Http\JsonResponse;
use App\Services\TableRoomService;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\TableRoomRequest;
use App\Http\Resources\TableLocationResource;
use App\Http\Requests\TableReservationRequest;
use Illuminate\Support\Str;

class TableRoomController extends Controller
{
    public function __construct(protected TableRoomService $tableRoomService)
    {
        $this->tableRoomService = $tableRoomService;
    }

    public function index(Request $request)
    {
        // Get branch from active cashier session or fall back to user's selected branch
        $cashierSession = $request->user()->cashierSession;

        if ($cashierSession) {
            $branchId = $cashierSession->branch_id;
        } else {
            $branchId = $request->user()->branch_id ?? session('active_branch.id');
            if (! $branchId) {
                return redirect()->route('home')->with('error', 'No active branch selected.');
            }
        }

        $tableRooms = $this->tableRoomService->list($branchId);

        return Inertia::render('Resto/Tables', [
            'tableRooms' => json_decode(json_encode(TableLocationResource::collection($tableRooms)), true),
        ]);
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

        return Inertia::render('Resto/TableManagement/List', [
            'tables'    => $tableRooms,
            'locations' => $locations,
        ]);
    }

    public function getAllTables(): JsonResponse
    {
        $tables = TableRoom::all();

        return response()->json([
            'success' => true,
            'data'    => $tables,
        ], 201);
    }

    public function store(TableRoomRequest $request): RedirectResponse
    {
        try {
            $table = $this->tableRoomService->store($request);

            if ($request->hasFile('featured_image')) {
                $file = $request->file('featured_image');
                if ($file && $file->isValid()) {
                    $table->clearMediaCollection('featured_image');
                    $table->addMedia($file)
                        ->toMediaCollection('featured_image');
                }
            }

            return redirect()->back()->with('success', 'Table/Room created successfully.');
        } catch (Exception $e) {
            \Log::error('TableRoomController::store error: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Failed to create Table/Room: ' . $e->getMessage());
        }
    }

    public function update(TableRoomRequest $request, int $tableId): RedirectResponse
    {
        try {
            $tableRoom = $this->tableRoomService->update($request, $tableId);

            if ($request->hasFile('featured_image')) {
                $file = $request->file('featured_image');
                if ($file && $file->isValid()) {
                    $tableRoom->clearMediaCollection('featured_image');
                    $tableRoom->addMedia($file)
                        ->toMediaCollection('featured_image');
                }
            }

            return redirect()->back()->with('success', 'Table updated successfully.');
        } catch (Exception $e) {
            \Log::error('TableRoomController::update error: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Failed to update table: ' . $e->getMessage());
        }
    }

    public function mergeTable(Request $request, int $tableId): RedirectResponse
    {
        $validated = $request->validate([
            'merge_to' => 'required|integer|exists:table_rooms,id',
        ]);

        try {
            $this->tableRoomService->mergeTable($tableId, $validated['merge_to']);

            return redirect()->route('resto.tables')->with('success', 'Table merged successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reserveTable(TableReservationRequest $request): RedirectResponse
    {
        try {
            $this->tableRoomService->reserveTable($request);

            return redirect()->route('resto.tables')->with('success', 'Table reservation created successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function unmergeTable(Request $request, int $tableId): RedirectResponse
    {
        try {
            $this->tableRoomService->unmergeTable($tableId);

            $redirectUrl = route('table-rooms.index');
            if ($request->has('locationId')) {
                $redirectUrl .= '?locationId=' . $request->query('locationId');
            }

            return redirect($redirectUrl)->with('success', 'Table unmerged successfully.');
        } catch (Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function unmergeAllTables(Request $request, int $tableId): RedirectResponse
    {
        try {
            // Get the table to unmerge all its merged tables
            $table = $this->tableRoomService->model->find($tableId);
            if (! $table) {
                return redirect()->back()->with('error', 'Table not found.');
            }

            // Unmerge all tables that are merged to this table
            $mergedTables = $table->mergedTables;
            foreach ($mergedTables as $mergedTable) {
                $this->tableRoomService->unmergeTable($mergedTable->id);
            }

            $redirectUrl = route('table-rooms.index');
            if ($request->has('locationId')) {
                $redirectUrl .= '?locationId=' . $request->query('locationId');
            }

            return redirect($redirectUrl)->with('success', 'All merged tables unmerged successfully.');
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

    public function vacantTable(int $tableId): RedirectResponse
    {
        try {
            $this->tableRoomService->vacantTable($tableId);

            return redirect()->back()->with('success', 'Table has been set to Available successfully.');

        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Failed to set table to Available: ' . $e->getMessage());
        }
    }
}
