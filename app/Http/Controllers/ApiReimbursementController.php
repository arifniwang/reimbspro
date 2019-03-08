<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use crocodicstudio\crudbooster\helpers\CRUDBooster;

class ApiReimbursementController extends \crocodicstudio\crudbooster\controllers\ApiController
{

    function __construct()
    {
        $this->table = "pengajuan";
        $this->permalink = "reimbursement";
        $this->method_type = "post";
    }


    public function hook_before(&$postdata)
    {
//        $view

        //This method will be execute before run the main process
        $validator['id'] = 'required';
        $validator['name'] = 'required';
        $validator['description'] = 'required';
        $validator['nota'] = 'required|file';
        CRUDBooster::Validator($validator);

        $nota = Request::file('nota');
        $extension = $nota->getClientOriginalExtension();
        $nota = file_get_contents($nota);
        $valid_json = CRUDBooster::isJSON($nota);
        $now = date('Y-m-d H:i:s');
        $created_at = (Request::input('created_at') == '' ? '' : date('Y-m-d H:i:s',strtotime(Request::input('created_at'))));

        $users = DB::table('users')
            ->where('id', Request::input('id'))
            ->first();
        if (empty($users)) {
            $result['api_status'] = 0;
            $result['api_code'] = 401;
            $result['api_message'] = 'Akun anda tidak ditemukan';
        } elseif ($users->deleted_at != '') {
            $result['api_status'] = 0;
            $result['api_code'] = 440;
            $result['api_message'] = 'Akun anda tidak dapat digunakan, silahkan login ulang';
        } elseif ($extension != 'json') {
            $result['api_status'] = 0;
            $result['api_code'] = 401;
            $result['api_message'] = 'Nota harus berupa json';
        } elseif ($nota == '' || !$valid_json) {
            $result['api_status'] = 0;
            $result['api_code'] = 401;
            $result['api_message'] = 'Json tidak valid';
        } elseif (!is_array(json_decode($nota))) {
            $result['api_status'] = 0;
            $result['api_code'] = 401;
            $result['api_message'] = 'Json harus berupa array';
        } else {
            $json_message = '';
            $total_nominal = 0;
            $json = json_decode($nota);
            $save = [];

            /**
             * VALIDATE JSON FILE
             */
            foreach ($json as $row) {
                if ($row->image == '') {
                    $valid_json = false;
                    $json_message .= 'Image nota tidak boleh kosong';
                    break;
                } elseif ($row->date == '') {
                    $valid_json = false;
                    $json_message .= 'Tanggal nota tidak boleh kosong';
                    break;
                } elseif ($row->nominal == '') {
                    $valid_json = false;
                    $json_message .= 'Nominal nota tidak boleh kosong';
                    break;
                } elseif ($row->kategori == '') {
                    $valid_json = false;
                    $json_message .= 'Kategori nota tidak boleh kosong';
                    break;
                } else {
                    $rest['created_at'] = ($created_at != '' ? $created_at : $now);
                    $rest['image'] = $row->image;
                    $rest['date'] = date('Y-m-d', strtotime($row->date));
                    $rest['nominal'] = number_format($row->nominal, 0, '', '');
                    $rest['id_kategori'] = $row->kategori;
                    $rest['description'] = $row->description;
                    $total_nominal += (int)number_format($row->nominal, 0, '', '');

                    $save[] = $rest;
                }
            }

            if (!$valid_json) {
                $result['api_status'] = 0;
                $result['api_code'] = 401;
                $result['api_message'] = $json_message;
            } else {

                /**
                 * SAVE PENGAJUAN
                 */
                $save_pengajuan['created_at'] = ($created_at != '' ? $created_at : $now);
                $save_pengajuan['strtotime'] = strtotime($save_pengajuan['created_at']);
                $save_pengajuan['id_users'] = Request::input('id');
                $save_pengajuan['name'] = Request::input('name');
                $save_pengajuan['description'] = Request::input('description');
                $save_pengajuan['status'] = 'Diproses';
                $save_pengajuan['total_nominal'] = $total_nominal;
                $id_pengajuan = DB::table('pengajuan')->insertGetId($save_pengajuan);

                if (!$id_pengajuan) {
                    $result['api_status'] = 0;
                    $result['api_code'] = 401;
                    $result['api_message'] = 'Pengajuan gagal, silahkan di coba kembali';
                } else {
                    /**
                     * SAVE DETAIL PENGAJUAN
                     */
                    foreach ($save as $key => $value) {
                        $save[$key]['id_pengajuan'] = $id_pengajuan;
                        $save[$key]['image'] = CRUDBooster::uploadBase64($value['image']);
                    }
                    DB::table('pengajuan_detail')->insert($save);

                    /**
                     * SEND NOTIFIKASI TO BACKEND
                     */
                    $url = 'detail-pengajuan?parent_table=pengajuan&parent_columns=id_users,name,total_nominal,description&parent_columns_alias=Pegawai,Nama%20Pengajuan,Total,Dekripsi&parent_id=' . $id_pengajuan . '&return_url=' . CRUDBooster::adminPath('pengajuan') . '&foreign_key=id_pengajuan&label=';
                    $cms_users = DB::table('cms_users')->get();
                    $save_notif = [];
                    foreach ($cms_users as $row) {
                        $push_notif['id_cms_users'] = $row->id;
                        $push_notif['is_read'] = 0;
                        $push_notif['created_at'] = $now;
                        $push_notif['content'] = 'Pengajuan - ' . $users->name;
                        $push_notif['url'] = CRUDBooster::adminPath($url);
                        $push_notif['id_relation'] = $id_pengajuan;
                        $push_notif['type'] = 1; //pengajuan
                        $save_notif[] = $push_notif;
                    }
                    DB::table('cms_notifications')->insert($save_notif);

                    $result['api_status'] = 1;
                    $result['api_code'] = 200;
                    $result['api_message'] = 'Pengajuan berhasil ditambah';
                }
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