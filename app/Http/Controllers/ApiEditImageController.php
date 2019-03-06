<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use crocodicstudio\crudbooster\helpers\CRUDBooster;

class ApiEditImageController extends \crocodicstudio\crudbooster\controllers\ApiController
{

    function __construct()
    {
        $this->table = "users";
        $this->permalink = "edit_image";
        $this->method_type = "post";
    }


    public function hook_before(&$postdata)
    {
        //This method will be execute before run the main process
        $validator['id'] = 'required';
        $validator['image'] = 'required|mimes:jpg,png,jpeg';
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
            $image = CRUDBooster::uploadFile('image', true);

            $save['image'] = $image;
            $save['updated_at'] = date('Y-m-d H:i:s');
            DB::table('users')->where('id', $id)->update($save);

            $result['api_status'] = 1;
            $result['api_code'] = 200;
            $result['api_message'] = 'Foto berhasil diubah';
            $result['image'] = url($image);
        }

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