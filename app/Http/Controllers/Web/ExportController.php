<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\DataTables\ExportAccessControlDTableDataTable as EAC;

/**
 * Description of ExportController
 *
 * @author 62221522
 */ 
class ExportController {
    public function index(EAC $dataTable)
    {
        return $dataTable->render('report/exportir');
    }
}
