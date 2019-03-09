<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use CRUDBooster;

class AdminIndexController extends \crocodicstudio\crudbooster\controllers\CBController
{
    public function getIndex()
    {
        return view('index');
    }
}