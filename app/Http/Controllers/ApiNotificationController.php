<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use crocodicstudio\crudbooster\helpers\CRUDBooster;

class ApiNotificationController extends \crocodicstudio\crudbooster\controllers\ApiController
{

    function __construct()
    {
        $this->table = "users_notification";
        $this->permalink = "notification";
        $this->method_type = "get";
    }


    public function hook_before(&$postdata)
    {
        //This method will be execute before run the main process
        $validator['id'] = 'required';
        CRUDBooster::Validator($validator);
        $id = Request::input('id');

        $users = DB::table('users')
            ->where('id', $id)
            ->first();
        if (empty($users)) {
            $result['api_status'] = 0;
            $result['api_code'] = 401;
            $result['api_message'] = 'Akun anda tidak ditemukan';
        } elseif ($users->deleted_at != '') {
            $result['api_status'] = 0;
            $result['api_code'] = 440;
            $result['api_message'] = 'Akun anda tidak dapat digunakan, silahkan login ulang';
        } else {
            $notification = DB::table('users_notification')
                ->select('users_notification.*')
                ->join('pengajuan', 'pengajuan.id', '=', 'users_notification.id_pengajuan')
                ->whereNull('users_notification.deleted_at')
                ->whereNull('pengajuan.deleted_at')
                ->where('pengajuan.id_users', $id)
                ->orderBy('users_notification.id','DESC')
                ->paginate(20);

            $item = [];
            foreach ($notification as $row) {
                $rest['id'] = $row->id;
                $rest['id_pengajuan'] = $row->id_pengajuan;
                $rest['date'] = $row->date;
                $rest['title'] = $row->title;
                $rest['content'] = $row->content;
                $rest['type'] = $row->type;

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