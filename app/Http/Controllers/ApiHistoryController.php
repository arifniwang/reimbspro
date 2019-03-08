<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use CRUDBooster;

class ApiHistoryController extends \crocodicstudio\crudbooster\controllers\ApiController
{

    function __construct()
    {
        $this->table = "pengajuan";
        $this->permalink = "history";
        $this->method_type = "get";
    }


    public function hook_before(&$postdata)
    {
        //This method will be execute before run the main process

        $anggaran = DB::table('anggaran')
            ->where('date',date('Y-m-d'))
            ->whereNull('deleted_at')
            ->first();
        $sisa_kas = $anggaran-$nominal - $anggaran->reimbursement;

        $result['api_status'] = 1;
        $result['api_code'] = 200;
        $result['api_message'] = 'Success';
        $result['sisa_kas'] = $sisa_kas;
        $result['pengeluaran_anda'] = 0;
        $res = response()->json($result);
        $res->send();
        exit;
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