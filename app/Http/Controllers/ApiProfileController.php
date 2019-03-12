<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use crocodicstudio\crudbooster\helpers\CRUDBooster;

class ApiProfileController extends \crocodicstudio\crudbooster\controllers\ApiController
{

    function __construct()
    {
        $this->table = "users";
        $this->permalink = "profile";
        $this->method_type = "get";
    }


    public function hook_before(&$postdata)
    {
        //This method will be execute before run the main process
        $validator['id'] = 'required|numeric';
        CRUDBooster::Validator($validator);

        $cek = DB::table('users')
            ->whereNull('deleted_at')
            ->where('id', Request::input('id'))
            ->first();
        if (empty($cek)) {
            $result['api_status'] = 0;
            $result['api_code'] = 401;
            $result['api_message'] = 'Akun anda tidak ditemukan';
        } else {
            if ($cek->image == ''){
                $image = '';
                $base64 = '';
            }else{
                $path = storage_path('app/'.$cek->image);
                if (file_exists($path)){
                    $img = file_get_contents($path);

                    $image = url($cek->image);
                    $base64 = base64_encode($img);
                }else{
                    $image = '';
                    $base64 = '';
                }
            }

            $result['api_status'] = 1;
            $result['api_code'] = 200;
            $result['api_message'] = 'Login berhasil';
            $result['id'] = $cek->id;
            $result['image'] = $image;
            $result['image_base64'] = (Request::input('base64') == 'Yes' ? $base64 : '');
            $result['name'] = $cek->name;
            $result['phone'] = $cek->phone;
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