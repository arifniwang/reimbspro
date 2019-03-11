<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use crocodicstudio\crudbooster\helpers\CRUDBooster;

class ApiDraftController extends \crocodicstudio\crudbooster\controllers\ApiController
{

    function __construct()
    {
        $this->table = "pengajuan";
        $this->permalink = "draft";
        $this->method_type = "get";
    }


    public function hook_before(&$postdata)
    {
        //This method will be execute before run the main process

    }

    public function hook_query(&$query)
    {
        //This method is to customize the sql query

    }

    public function hook_after($postdata, &$result)
    {
        //This method will be execute after run the main process

    }

}