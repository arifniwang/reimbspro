<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use crocodicstudio\crudbooster\helpers\CRUDBooster;

class AdminReportController extends \crocodicstudio\crudbooster\controllers\CBController
{
    public function getIndex()
    {
        $pegawai = DB::table('users')
            ->whereNull('deleted_at')
            ->orderby('name','ASC')
            ->get();

        $pengajuan = DB::table('pengajuan')
            ->whereNull('deleted_at')
            ->orderBy('strtotime','DESC')
            ->get();
        foreach($pengajuan as $row){
//            echo $row->created_at.' => '.$row->strtotime.' => '.strtotime($row->created_at);
//            echo '<br>';
        }

        $data = DB::table('pengajuan')
            ->where('strtotime','LIKE','%0000%')
            ->get();

        foreach ($data as $row){
            echo $row->id.') '.$row->created_at.' => '.$row->strtotime.' => '.strtotime($row->created_at);
            echo '<br>';

            $save['updated_at'] = date('Y-m-d H:i:s');
            $save['strtotime'] = strtotime($row->created_at);
            DB::table('pengajuan')->where('id',$row->id)->update($save);
            exit();
        }


        exit();
        $response['pegawai'] = $pegawai;
        return view('report',$response);
    }
}