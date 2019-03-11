<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use CRUDBooster;

class ApiHomeController extends \crocodicstudio\crudbooster\controllers\ApiController
{

    function __construct()
    {
        $this->table = "anggaran";
        $this->permalink = "home";
        $this->method_type = "get";
    }


    public function hook_before(&$postdata)
    {
        //This method will be execute before run the main process
        $validator['id'] = 'required';
        CRUDBooster::Validator($validator);

        $id = Request::input('id');
        $month = strtotime(date('Y-m-d') . ' - 1 Year');
        $end = strtotime(date('Y-m-d'));
        $date = [];
        $date[] = date('Y-m-d', $end);
        while ($end > $month) {
            $end = strtotime("-1 month", $end);
            $date[] = date('Y-m-d', $end);
        }

        /**
         * Total Pengeluaran & Sisa Kas
         */
        $anggaran = DB::table('anggaran')
            ->where('year', date('Y'))
            ->where('month', number_format(date('m'), 0, '', ''))
            ->whereNull('deleted_at')
            ->first();
        $sisa_kas = $anggaran->nominal - $anggaran->reimbursement;

        $pengajuan = DB::table('pengajuan')
            ->whereNull('deleted_at')
            ->where('status', 'Disetujui')
            ->where('id_users', $id)
            ->where('year', date('Y'))
            ->where('month', number_format(date('m'), 0, '', ''))
            ->sum('total_nominal');

        /**
         * Claim bulanan
         * data 1 tahun terakhir
         */
        $claim_bulanan = [];
        $no = 1;
        foreach ($date as $key => $value) {
            $tahun = date('Y', strtotime($value));
            $bulan = number_format(date('m', strtotime($value)), 0, '', '');

            $pengajuan_bulanan = DB::table('pengajuan')
                ->whereNull('deleted_at')
                ->where('status', 'Diterima')
                ->where('id_users', $id)
                ->where('year', $tahun)
                ->where('month', $bulan)
                ->sum('total_nominal');

            if ($pengajuan_bulanan == 0) continue;

            $rest_bulanan['id'] = $no++;
            $rest_bulanan['date_start'] = date('Y-m-', strtotime($value)) . '01';
            $rest_bulanan['date_end'] = date("Y-m-t", strtotime($value));;
            $rest_bulanan['nominal'] = $pengajuan_bulanan;

            $claim_bulanan[] = $rest_bulanan;
        }

        /**
         * Pengajuan Terakhir
         * diambil 50 Data Terakhir
         */
        $pengajuan_terakhir = DB::table('pengajuan')
            ->select('id', 'total_nominal as nominal', 'name', 'description', 'status', 'created_at')
            ->whereNull('deleted_at')
            ->where('id_users', $id)
            ->whereIn('status',['Diproses','Disetujui','Ditolak'])
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->get();
        foreach ($pengajuan_terakhir as $row) {
            $nota = DB::table('pengajuan_detail')
                ->select('pengajuan_detail.id', 'pengajuan_detail.image', 'pengajuan_detail.date', 'pengajuan_detail.nominal',
                    'pengajuan_detail.description', 'pengajuan_detail.id_kategori', 'kategori.name as kategori')
                ->join('kategori', 'kategori.id', '=', 'pengajuan_detail.id_kategori')
                ->whereNull('pengajuan_detail.deleted_at')
                ->where('pengajuan_detail.id_pengajuan', $row->id)
                ->get();
            foreach ($nota as $xrow) {
                $xrow->image = ($xrow->image != '' ? url($xrow->image) : '');
            }

            $row->nota = $nota;
        }

        $result['api_status'] = 1;
        $result['api_code'] = 200;
        $result['api_message'] = 'Success';
        $result['sisa_kas'] = $sisa_kas;
        $result['pengeluaran_anda'] = $pengajuan;
        $result['claim_bulanan'] = $claim_bulanan;
        $result['pengajuan_terakhir'] = $pengajuan_terakhir;
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