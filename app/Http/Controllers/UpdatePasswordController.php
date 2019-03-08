<?php

namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use crocodicstudio\crudbooster\helpers\CRUDBooster;

class UpdatePasswordController extends Controller
{
    public function Index($key)
    {
        $rest['key'] = $key;
        return view('updatepassword',$rest);
    }

    public function UpdatePassword($key)
    {
        $password = Request::input('password');
        $confirm_password = Request::input('confirm_password');
        $key = explode('|',Crypt::decrypt($key));
        $email = $key[0];

        if ($password == $confirm_password){
            $password = Hash::make($password);

            $save['updated_at'] = date('Y-m-d H:i:s');
            $save['password'] = $password;
            DB::table('cms_users')->where('email',$email)->update($save);

            return redirect()->route('getLogin')->with(['message' => 'Password berhasil diubah',
                'message_type' => 'success']);
        }else{
            return redirect()->back()->with(['message' => 'Konfirmasi password tidak sesuai',
                'message_type' => 'danger']);
        }
    }
}