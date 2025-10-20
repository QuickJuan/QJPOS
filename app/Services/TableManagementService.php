<?php
namespace App\Services;

use App\Models\TableRoom;
use App\Models\TableRoomLocation;
use Exception;
use Illuminate\Http\Request;

class TableManagementService
{
    public function __construct(public TableRoom $tableRoom, public TableRoomLocation $tableRoomLocation)
    {
        $this->tableRoom         = $tableRoom;
        $this->tableRoomLocation = $tableRoomLocation;
    }

    public function storeLocation(Request $request): TableRoomLocation
    {
        $location = TableRoomLocation::create([
            'name' => $request->name,
        ]);

        if (! $location) {
            throw new Exception('Failed to create location.');
        }

        return $location;
    }

    public function updateLocation(Request $request, TableRoomLocation $location): TableRoomLocation
    {
        $location->update([
            'name' => $request->name,
        ]);

        if (! $location) {
            throw new Exception('Failed to update location.');
        }

        return $location;
    }
}
