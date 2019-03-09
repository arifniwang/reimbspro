<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use crocodicstudio\crudbooster\helpers\CRUDBooster;

class AdminReportController extends \crocodicstudio\crudbooster\controllers\CBController
{
    public function getIndex()
    {
        return view('report');
    }
}