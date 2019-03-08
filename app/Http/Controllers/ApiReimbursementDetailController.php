<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use CRUDBooster;

class ApiReimbursementDetailController extends \crocodicstudio\crudbooster\controllers\ApiController
{

    function __construct()
    {
        $this->table = "pengajuan";
        $this->permalink = "reimbursement_detail";
        $this->method_type = "get";
    }

    public function hook_before(&$postdata)
    {
        //This method will be execute before run the main process

        $validator['id'] = 'required';
        $validator['id_pengajuan'] = 'required';
        CRUDBooster::Validator($validator);
        $id = Request::input('id');
        $id_pengajuan = Request::input('id_pengajuan');

        $users = DB::table('users')
            ->where('id', $id)
            ->first();
        $pengajuan = DB::table('pengajuan')
            ->where('id', $id_pengajuan)
            ->first();
        if (empty($users)) {
            $result['api_status'] = 0;
            $result['api_code'] = 401;
            $result['api_message'] = 'Akun anda tidak ditemukan';
        } elseif ($users->deleted_at != '') {
            $result['api_status'] = 0;
            $result['api_code'] = 440;
            $result['api_message'] = 'Akun anda tidak dapat digunakan, silahkan login ulang';
        } elseif (empty($pengajuan) || $pengajuan->id_users != $id) {
            $result['api_status'] = 0;
            $result['api_code'] = 401;
            $result['api_message'] = 'Data pengajuan tidak ditemukan';
        } elseif ($pengajuan->deleted_at != '') {
            $result['api_status'] = 0;
            $result['api_code'] = 401;
            $result['api_message'] = 'Pengajuan ini sudah dihapus oleh admin';
        } else {
            $nota = DB::table('pengajuan_detail')
                ->select('pengajuan_detail.id', 'pengajuan_detail.image', 'pengajuan_detail.date', 'pengajuan_detail.nominal',
                    'pengajuan_detail.description', 'pengajuan_detail.id_kategori', 'kategori.name as kategori')
                ->join('kategori', 'kategori.id', '=', 'pengajuan_detail.id_kategori')
                ->whereNull('pengajuan_detail.deleted_at')
                ->where('pengajuan_detail.id_pengajuan', $pengajuan->id)
                ->get();
            foreach ($nota as $row) {
                $row->image = ($row->image != '' ? url($row->image) : '');
                $xrow->id_reimbusement = $row->id;
            }

            $result['api_status'] = 1;
            $result['api_code'] = 200;
            $result['api_message'] = 'Success';
            $result['id'] = $pengajuan->id;
            $result['nominal'] = $pengajuan->total_nominal;
            $result['name'] = $pengajuan->name;
            $result['description'] = $pengajuan->description;
            $result['status'] = $pengajuan->status;
            $result['created_at'] = $pengajuan->created_at;
            $result['nota'] = $nota;

            $res = response()->json($result);
            $res->send();
            exit;
        }
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