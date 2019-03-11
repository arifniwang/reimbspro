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
        $validator['id'] = 'required';
        CRUDBooster::Validator($validator);

        $id = Request::input('id');
        $item = [];

        $pengajuan = DB::table('pengajuan')
            ->select('id', 'total_nominal as nominal', 'name', 'description', 'status', 'created_at')
            ->whereNull('deleted_at')
            ->where('id_users', $id)
            ->where('status','Draft')
            ->orderBy('strtotime', 'DESC');
        if (Request::input('date_start') != '' && Request::input('date_end') != ''){
            $date_start = date('Y-m-d H:i:s', strtotime(Request::input('date_start')));
            $date_end = date('Y-m-d H:i:s', strtotime(Request::input('date_end') . ' +1 day'));
            $start = strtotime($date_start);
            $end = strtotime($date_end);

            $pengajuan = $pengajuan->where('strtotime', '>=', $start)
                ->where('strtotime', '<=', $end);
        }
        $pengajuan = $pengajuan->paginate(20);

        $list_date = [];
        $i = 0;
        foreach ($pengajuan as $row) {
            $nota = DB::table('pengajuan_detail')
                ->select('pengajuan_detail.id', 'pengajuan_detail.image', 'pengajuan_detail.date', 'pengajuan_detail.nominal',
                    'pengajuan_detail.description', 'pengajuan_detail.id_kategori', 'kategori.name as kategori')
                ->join('kategori', 'kategori.id', '=', 'pengajuan_detail.id_kategori')
                ->whereNull('pengajuan_detail.deleted_at')
                ->where('pengajuan_detail.id_pengajuan', $row->id)
                ->get();
            foreach ($nota as $xrow) {
                $xrow->id_reimbusement = $row->id;
                $xrow->image = ($xrow->image != '' ? url($xrow->image) : '');
            }
            $row->nota = $nota;

            $date = date('Y-m-d', strtotime($row->created_at));
            if (!in_array($date,$list_date)){
                if ($i != 0){
                    $rest['date'] = $exist_date;
                    $rest['list'] = $list;
                    $item[] = $rest;

                    $list = [];
                    $list[] = $row;
                }else{
                    $list = [];
                    $list[] = $row;
                }

                array_push($list_date,$date);
                $exist_date = $date;
            }else{
                $list[] = $row;
            }

            $i++;
        }
        if(!empty($pengajuan)){
            $rest['date'] = $date;
            $rest['list'] = $list;
            $item[] = $rest;
        }

        $result['api_status'] = 1;
        $result['api_code'] = 200;
        $result['api_message'] = 'Success';
        $result['item'] = $item;
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