<?php
namespace App\DataTables;

use App\Models\AccessControl;
use Yajra\DataTables\Services\DataTable;

class AccessControlTable extends DataTable {
    public function html()
    {
        return $this->builder()
                    ->columns($this->getColumns())
                    ->parameters([
                        'buttons' => ['excel'],
                    ]);
    }
    protected function getColumns()
    {
        return [
            'id',
            'name',
            'email',
            'created_at',
            'updated_at',
        ];
    }       
}
