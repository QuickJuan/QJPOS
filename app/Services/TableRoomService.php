<?php
namespace App\Services;

use Exception;
use Carbon\Carbon;
use App\Models\TableRoom;
use Illuminate\Http\Request;
use App\Models\TableRoomLocation;
use App\Enums\TableRoomStatusType;
use Illuminate\Support\Facades\Auth;

class TableRoomService
{
    public function __construct(public TableRoom $model)
    {
        $this->model = $model;
    }

    public function store(Request $request): TableRoom
    {
        $tableRoom = TableRoom::create([
            'branch_id'              => session('active_branch')['id'],
            'name'                   => $request->name,
            'chairs'                 => $request->chairs,
            'table_room_location_id' => $request->table_room_location_id,
            'table_width'            => $request->table_width,
            'table_height'           => $request->table_height,
            'table_x'                => $request->table_x,
            'table_y'                => $request->table_y,
        ]);

        if (! $tableRoom) {
            throw new Exception('Failed to create table/room.');
        }

        return $tableRoom;
    }

    public function update(Request $request, int $tableId): TableRoom
    {
        $tableRoom = $this->model->findOrFail($tableId);

        if (! $tableRoom) {
            throw new Exception('Table/Room not found.');
        }

        $tableRoom->update([
            'branch_id'              => session('active_branch')['id'],
            'name'                   => $request->input('name'),
            'chairs'                 => $request->input('chairs'),
            'table_room_location_id' => $request->input('table_room_location_id'),
            'table_width'            => $request->input('table_width'),
            'table_height'           => $request->input('table_height'),
            'table_x'                => $request->input('table_x'),
            'table_y'                => $request->input('table_y'),
            'merge_to'               => $request->input('merge_to'),
        ]);

        if (! $tableRoom) {
            throw new Exception('Failed to update table room.');
        }

        if (! empty($request->file('featured_image'))) {
            $tableRoom->clearMediaCollection('featured_image');
            $tableRoom->addMediaFromRequest('featured_image')
                ->toMediaCollection('featured_image');
        }

        return $tableRoom;
    }

    public function mergeTable(int $tableId, int $mergeToId): bool
    {
        $tableRoom = $this->model->findOrFail($tableId);

        if (! $tableRoom) {
            throw new Exception('Table/Room not found.');
        }

        // Validate that the merge target exists and is occupied
        $mergeTarget = $this->model->findOrFail($mergeToId);
        if ($mergeTarget->status !== 'occupied') {
            throw new Exception('Can only merge into occupied tables.');
        }

        // Validate that the source table is vacant
        if ($tableRoom->status !== 'vacant') {
            throw new Exception('Can only merge vacant tables.');
        }

        return $tableRoom->update([
            'merge_to' => $mergeToId,
            'status'   => TableRoomStatusType::OCCUPIED->value,
        ]);
    }

    public function reserveTable(Request $request)
    {
        $tableRoom = TableRoom::findOrFail($request->table_room_id);

        if (! $tableRoom) {
            throw new Exception('Table/Room not found.');
        }

        $reservationFrom = Carbon::parse($request->reservation_from);
        $now             = Carbon::now();

        // If the reservation time is same to the current time, update the status
        if ($now->format('Y-m-d H:i') == $reservationFrom->format('Y-m-d H:i')) {
            $tableRoom->update([
                'status' => TableRoomStatusType::RESERVED->value,
            ]);
        }

        $tableReservation = $tableRoom->tableReservations()
            ->create([
                'table_room_id'    => $tableRoom->id,
                'user_id'          => Auth::id(),
                'name'             => $request->name,
                'reservation_from' => $request->reservation_from,
                'reservation_to'   => $request->reservation_to,
                'pax'              => $request->pax,
                'contact_phone'    => $request->contact_number,
                'contact_email'    => $request->contact_email,
                'notes'            => $request->notes,
            ]);

        if (! $tableReservation) {
            throw new Exception('Error in creating reservation.');
        }

        return $tableReservation;
    }



    public function list(Int $branchId)
    {

        $tableRoomLocations = TableRoomLocation::with(['tableRooms' => function ($query) use ($branchId) {
            $query->where('branch_id', $branchId)
                    ->whereNull('merge_to')
                    ->orderBy('name')
                    ->with(['mergedTables', 'cart']);
        }])
        ->orderBy('name')
        ->get();

        return $tableRoomLocations;
    }

    public function mergedTable(Int $mainTableId, Int $mergingTableId): ?TableRoom
    {
        return $this->model->where('id', $mergingTableId)
            ->where('merge_to', $mainTableId)
            ->first();
    }


    public function unmergeTable(int $tableId): bool
    {
        $tableRoom = $this->model->findOrFail($tableId);

        if (! $tableRoom) {
            throw new Exception('Table/Room not found.');
        }

        return $tableRoom->update([
            'merge_to' => null,
            'status'   => TableRoomStatusType::VACANT->value,
        ]);
    }




    function removeMergedTables(Int $tableId): void
    {
        $this->model->where('merge_to', $tableId)
            ->update(['merge_to' => null, 'status' => TableRoomStatusType::VACANT->value]);
    }
}
