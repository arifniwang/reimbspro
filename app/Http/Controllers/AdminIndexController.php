<?php namespace App\Http\Controllers;

use Session;
use Request;
use DB;
use crocodicstudio\crudbooster\helpers\CRUDBooster;

class AdminIndexController extends \crocodicstudio\crudbooster\controllers\CBController
{
	public function getGenerateSaldo()
	{
		$testing_user = DB::table('users')
			->whereNull('deleted_at')
			->where('testing', 1)
			->pluck('id')
			->toArray();
		$anggaran = DB::table('anggaran')
			->whereNull('deleted_at')
			->get();
		foreach ($anggaran as $row) {
			$pengajuan = DB::table('pengajuan')
				->whereNotIn('id_users', $testing_user)
				->whereNull('deleted_at')
				->where('year', $row->year)
				->where('month', $row->month)
				->where('status', 'Disetujui')
				->sum('total_nominal');
			
			$update['updated_at'] = date('Y-m-d H:i:s');
			$update['reimbursement'] = $pengajuan;
			DB::table('anggaran')->where('id', $row->id)->update($update);
		}
		
		CRUDBooster::redirectBack('Dashbaord Has been refresh', 'success');
	}
	
	public function getIndex()
	{
		$now = date('Y-m-d H:i:s');
		$year = date('Y');
		$month = number_format(date('m'), 0, '', '');
		$bulan = array(
			1 => 'Januari',
			'Februari',
			'Maret',
			'April',
			'Mei',
			'Juni',
			'Juli',
			'Agustus',
			'September',
			'Oktober',
			'November',
			'Desember'
		);
		
		/**
		 * KUOTA
		 */
		$master = DB::table('anggaran')
			->where('year', $year)
			->where('month', $month)
			->whereNull('deleted_at')
			->first();
		if (empty($master)) {
			$anggaran = 0;
			$sisa = 0;
			$over = 0;
		} else {
			$anggaran = $master->nominal;
			
			$hitung_sisa = $master->nominal - $master->reimbursement;
			$sisa = ($hitung_sisa < 0 ? 0 : $hitung_sisa);
			
			$over = abs(($hitung_sisa < 0 ? $hitung_sisa : 0));
		}
		
		/**
		 * PERSENTASE
		 */
		$persentase = [];
		$diproses = DB::table('pengajuan')
			->whereYear('created_at', $year)
			->whereMonth('created_at', $month)
			->whereNull('deleted_at')
			->where('status', 'Diproses')
			->count();
		$push_persentase['name'] = 'Diproses';
		$push_persentase['y'] = $diproses;
		$persentase[] = $push_persentase;
		
		$disetujui = DB::table('pengajuan')
			->whereYear('created_at', $year)
			->whereMonth('created_at', $month)
			->whereNull('deleted_at')
			->where('status', 'Disetujui')
			->count();
		$push_persentase['name'] = 'Disetujui';
		$push_persentase['y'] = $disetujui;
		$persentase[] = $push_persentase;
		
		$ditolak = DB::table('pengajuan')
			->whereYear('created_at', $year)
			->whereMonth('created_at', $month)
			->whereNull('deleted_at')
			->where('status', 'Ditolak')
			->count();
		$push_persentase['name'] = 'Ditolak';
		$push_persentase['y'] = $ditolak;
		$persentase[] = $push_persentase;
		
		$persentase = json_encode($persentase);
		
		/**
		 * GRAFIK
		 */
		$grafik = [];
		$grafik_overbudget = [];
		$grafik_pemakaian = [];
		$grafik_anggaran = [];
		$start = strtotime('2019-01-01');
		$end = strtotime($now);
		while ($start < $end) {
			$year = date('Y', $start);
			$month = number_format(date('m', $start), 0, '', '');
			$check_anggaran = DB::table('anggaran')
				->whereNull('deleted_at')
				->where('year', $year)
				->where('month', $month)
				->first();
			
			if (!empty($check_anggaran)) {
				$check_pemakaian = $check_anggaran->nominal - $check_anggaran->reimbursement;
				$pemakaian = ($check_pemakaian < 0 ? $check_anggaran->nominal : $check_anggaran->reimbursement);
				$over = ($check_pemakaian < 0 ? abs($check_pemakaian) : 0);
				
				$grafik_anggaran[] = $check_anggaran->nominal;
				$grafik_pemakaian[] = $pemakaian;
				$grafik_overbudget[] = $over;
			} else {
				$grafik_anggaran[] = 0;
				$grafik_pemakaian[] = 0;
				$grafik_overbudget[] = 0;
			}
			
			$start = strtotime("+1 month", $start);
		}
		$push_grafik['type'] = 'column';
		$push_grafik['name'] = 'Over Budget';
		$push_grafik['data'] = $grafik_overbudget;
		$grafik[] = $push_grafik;
		
		$push_grafik['type'] = 'column';
		$push_grafik['name'] = 'Pemakaian';
		$push_grafik['data'] = $grafik_pemakaian;
		$grafik[] = $push_grafik;
		
		$push_grafik['type'] = 'spline';
		$push_grafik['name'] = 'Anggaran';
		$push_grafik['data'] = $grafik_anggaran;
		$grafik[] = $push_grafik;
		
		$grafik = json_encode($grafik);
		
		/**
		 * TABLE
		 */
		$disetujui = DB::table('pengajuan')
			->join('users', 'users.id', '=', 'pengajuan.id_users')
			->whereNull('pengajuan.deleted_at')
			->where('pengajuan.status', 'Disetujui')
			->count();
		$ditolak = DB::table('pengajuan')
			->join('users', 'users.id', '=', 'pengajuan.id_users')
			->whereNull('pengajuan.deleted_at')
			->where('pengajuan.status', 'Ditolak')
			->count();
		$last_disetujui = DB::table('pengajuan')
			->join('users', 'users.id', '=', 'pengajuan.id_users')
			->select('pengajuan.*', 'users.name as nama_user')
			->whereNull('pengajuan.deleted_at')
			->where('pengajuan.status', 'Disetujui')
			->orderBy('pengajuan.id', 'DESC')
			->limit(10)
			->get();
		$last_ditolak = DB::table('pengajuan')
			->join('users', 'users.id', '=', 'pengajuan.id_users')
			->select('pengajuan.*', 'users.name as nama_user')
			->whereNull('pengajuan.deleted_at')
			->where('pengajuan.status', 'Ditolak')
			->orderBy('pengajuan.id', 'DESC')
			->limit(10)
			->get();
		
		$response['anggaran'] = $anggaran;
		$response['sisa'] = $sisa;
		$response['over'] = $over;
		$response['persentase'] = $persentase;
		$response['grafik'] = $grafik;
		$response['disetujui'] = $disetujui;
		$response['ditolak'] = $ditolak;
		$response['last_disetujui'] = $last_disetujui;
		$response['last_ditolak'] = $last_ditolak;
		return view('index', $response);
	}
}