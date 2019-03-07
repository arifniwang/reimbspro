<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Session;
use Request;
use DB;
use crocodicstudio\crudbooster\helpers\CRUDBooster;

class ApiLoginController extends \crocodicstudio\crudbooster\controllers\ApiController
{

    function __construct()
    {
        $this->table = "users";
        $this->permalink = "login";
        $this->method_type = "post";
    }


    public function hook_before(&$postdata)
    {
        //This method will be execute before run the main process
        $validator['phone'] = 'required|numeric';
        $validator['password'] = 'required';
        CRUDBooster::Validator($validator);

        $phone = Request::input('phone');
        $password = Request::input('password');
        $regid = Request::input('regid');

        $cek = DB::table('users')
            ->whereNull('deleted_at')
            ->where('phone', $phone)
            ->first();
        if (empty($cek)) {
            $result['api_status'] = 0;
            $result['api_code'] = 401;
            $result['api_message'] = 'Akun anda tidak ditemukan';
        } elseif (!Hash::check($password, $cek->password)) {
            $result['api_status'] = 0;
            $result['api_code'] = 401;
            $result['api_message'] = 'Password yang anda masukan salah';
        } else {
            $result['api_status'] = 1;
            $result['api_code'] = 200;
            $result['api_message'] = 'Login berhasil';
            $result['id'] = $cek->id;
            $result['image'] = ($cek->image == '' ? '' : url($cek->image));
            $result['name'] = $cek->name;
            $result['phone'] = $cek->phone;

            $cek_regid = DB::table('users_regid')
                ->where('regid', $regid)
                ->first();
            if (empty($cek_regid)) {
                $save_regid['created_at'] = date('Y-m-d H:i:s');
                $save_regid['id_users'] = $cek->id;
                $save_regid['regid'] = $regid;
                DB::table('users_regid')->insert($save_regid);
            } elseif ($cek_regid->id_users != $cek->id) {
                $save_regid['updated_at'] = date('Y-m-d H:i:s');
                $save_regid['id_users'] = $cek->id;
                DB::table('users_regid')->where('id', $cek_regid->id)->update($save_regid);
            }
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