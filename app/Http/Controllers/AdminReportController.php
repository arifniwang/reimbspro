<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use crocodicstudio\crudbooster\helpers\CRUDBooster;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeWriting;


class AdminReportController extends \crocodicstudio\crudbooster\controllers\CBController
{
    public function getIndex()
    {
        $pegawai = DB::table('users')
            ->whereNull('deleted_at')
            ->orderby('name', 'ASC')
            ->get();

        $pengajuan = DB::table('pengajuan')
            ->whereNull('deleted_at')
            ->orderBy('strtotime', 'DESC')
            ->get();


        $response['pegawai'] = $pegawai;
        return view('report', $response);
    }

    public function postDownload()
    {
        /**
         * No
         * Nama Pegawai
         * Nama Pengajuan
         * Total
         * Status
         * Deskripsi
         * Tanggal Pengajuan
         */

        $start = date('Y-m-d', strtotime(Request::input('start')));
        $end = date('Y-m-d', strtotime(Request::input('end')));
        $pegawai = Request::input('pegawai');

        $pegawai = DB::table('users')
            ->whereNull('deleted_at')
            ->where(function ($query) use ($pegawai) {
                if ($pegawai != '') {
                    $query->where('id', $pegawai);
                }
            })
            ->orderby('name', 'ASC')
            ->get();

//        return view('reportdownload',$response);

        $filename = 'Report Pengajuan ' . $start . ' - ' . $end;
        try {
            Excel::create($filename, function ($excel) use ($pegawai, $filename, $start, $end) {
                $excel->setTitle($filename)
                    ->setCreator("TEAM B CROCODIC COMPETITION")
                    ->setCompany(CRUDBooster::getSetting('appname'));

                foreach ($pegawai as $row) {
                    $time_start = strtotime($start);
                    $time_end = strtotime($end);

                    $pengajuan = DB::table('pengajuan')
                        ->where('id_users', $row->id)
                        ->where('strtotime', '>=', $time_start)
                        ->where('strtotime', '<=', $time_end)
                        ->where('status', '!=', 'Draft')
                        ->whereNull('deleted_at')
                        ->get();

                    if (count($pengajuan) > 0) {
                        $result['name'] = $row->name;
                        $result['phone'] = $row->phone;
                        $result['pengajuan'] = $pengajuan;
                        $excel->sheet($row->name, function ($sheet) use ($result) {
                            $sheet->setOrientation('landscape');
                            $sheet->loadview('reportdownload', $result);
                        });
                    }

                }

            })->export('xls');
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
}