<?php
namespace App\Services;

use App\Models\TableRoom;
use Exception;
use Illuminate\Http\Request;

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
}
