<?php
namespace App\Services;

use App\Models\Modifier;

class ModifierService
{

    public function __construct(public Modifier $modifier)
    {
        $this->model = $modifier;
    }


    public function getAvailableModifiers()
    {
        $query = $this->model->select('id', 'name', 'list');
        $list =  $query->get()->toArray();
        return $list;
    }
}
