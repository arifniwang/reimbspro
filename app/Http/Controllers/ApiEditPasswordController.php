<?php namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Session;
use Request;
use DB;
use crocodicstudio\crudbooster\helpers\CRUDBooster;

class ApiEditPasswordController extends \crocodicstudio\crudbooster\controllers\ApiController
{

    function __construct()
    {
        $this->table = "users";
        $this->permalink = "edit_password";
        $this->method_type = "post";
    }


    public function hook_before(&$postdata)
    {
        //This method will be execute before run the main process
        $validator['old_password'] = 'required';
        $validator['new_password'] = 'required';
        CRUDBooster::Validator($validator);

        $id = Request::input('id');
        $old_password = Request::input('old_password');
        $new_password = Request::input('new_password');

        $users = DB::table('users')
            ->where('id',$id)
            ->first();
        if (empty($users)){
            $result['api_status'] = 0;
            $result['api_code'] = 401;
            $result['api_message'] = 'Akun anda tidak ditemukan';
        }elseif ($users->deleted_at != ''){
            $result['api_status'] = 0;
            $result['api_code'] = 440;
            $result['api_message'] = 'Akun anda tidak dapat digunakan, silahkan login ulang';
        } elseif (!Hash::check($old_password, $users->password)) {
            $result['api_status'] = 0;
            $result['api_code'] = 401;
            $result['api_message'] = 'Password lama tidak sesuai';
        }else{
            $save['password'] = Hash::make($new_password);
            $save['updated_at'] = date('Y-m-d H:i:s');
            DB::table('users')->where('id',$id)->update($save);

            $result['api_status'] = 1;
            $result['api_code'] = 200;
            $result['api_message'] = 'Password berhasil diubah';
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