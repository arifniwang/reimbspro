@extends('crudbooster::admin_template')
@section('content')
	<style>
		section.content-header small {
			display: none;
		}
		
		#refreshSaldo {
			position: absolute;
			left: 475px;
			top: 70px;
		}
		
		.box-header {
			border-bottom: 1px solid #CCC;
			font-size: 16px;
			font-weight: bold;
			padding-left: 0;
			padding-right: 0;
		}
		
		.info-box {
			border-radius: 5px;
			padding: 10px 20px;
		}
		
		.info-box .separator-badge {
			width: 50px;
			border-radius: 10px;
			height: 10px;
			margin-bottom: 25px;
		}
		
		.info-box .separator-badge.bg-success {
			background-color: #2DC399;
		}
		
		.info-box .separator-badge.bg-warning {
			background-color: #FFD31F;
		}
		
		.info-box .separator-badge.bg-danger {
			background-color: #E75651;
		}
		
		.info-box .info-title {
			color: #444 !important;
			font-size: 16px;
			font-weight: bold;
		}
		
		.info-box .info-nominal {
			color: #444 !important;
			font-size: 28px;
			font-weight: 400;
		}
		
		.table-box {
			border-radius: 5px;
			background-color: #FFFFFF;
		}
		
		.table-title-box-success {
			background-color: #2DC399;
			color: #FFFFFF;
			border-top-left-radius: 5px;
			border-top-right-radius: 5px;
			padding: 20px 20px 10px;
		}
		
		.table-title-box-danger {
			background-color: #E75651;
			color: #FFFFFF;
			border-top-left-radius: 5px;
			border-top-right-radius: 5px;
			padding: 20px 20px 10px;
		}
		
		.table-content-box {
			border-bottom-left-radius: 5px;
			border-bottom-right-radius: 5px;
			padding: 10px 0px 20px;
		}
		
		.table-box-header {
			font-size: 20px;
		}
		
		.table-box-content {
			font-size: 28px;
			font-family: bold;
		}
		
		.table-box-icon {
			font-size: 50px;
			opacity: 0.5;
		}
	</style>
	
	<a id="refreshSaldo" href="{{\crocodicstudio\crudbooster\helpers\CRUDBooster::mainpath('generate-saldo')}}"
	   class="btn btn-xs btn-success">
		<i class="fa fa-refresh"></i>
	</a>
	
	<div class="row" style="margin-top: 10px;">
		<div class="col-md-4 col-sm-4 col-xs-12">
			<div class="info-box shadow">
				<p class="info-title">Total Anggaran Bulan Ini</p>
				<div class="separator-badge bg-success"></div>
				<p class="info-nominal">Rp.{{number_format($anggaran,0,'.','.')}},-</p>
			</div>
		</div>
		<div class="col-md-4 col-sm-4 col-xs-12">
			<div class="info-box shadow">
				<p class="info-title">Sisa Anggaran Bulan Ini</p>
				<div class="separator-badge bg-warning"></div>
				<p class="info-nominal">Rp.{{number_format($sisa,0,'.','.')}},-</p>
			</div>
		</div>
		<div class="col-md-4 col-sm-4 col-xs-12">
			<div class="info-box shadow">
				<p class="info-title">Over Budget Bulan Ini</p>
				<div class="separator-badge bg-danger"></div>
				<p class="info-nominal">Rp.{{number_format($over,0,'.','.')}},-</p>
			</div>
		</div>
	</div>
	
	<div class="row" style="margin-top: 20px;">
		<div class="col-md-5 col-sm-5 col-xs-12">
			<div class="info-box shadow">
				<div class="box-header">
					Persentase Pengajuan
				</div>
				<div class="box-body">
					<div id="persentasePengajuan" style="min-width: 100%; height: 300px;"></div>
				</div>
			</div>
		</div>
		<div class="col-md-7 col-sm-7 col-xs-12">
			<div class="info-box shadow">
				<div class="box-header">
					Grafik Pengajuan Tahunan
				</div>
				<div class="box-body">
					<div id="pengajuanTahunan" style="min-width: 100%; height: 300px;"></div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="row" style="margin-top: 20px;">
		<div class="col-md-6 col-sm-6 col-xs-12">
			<div class="table-box shadow">
				<div class="table-title-box-success">
					<span class="pull-right table-box-icon"><i class="fa fa-user-plus"></i></span>
					<p class="table-box-header">Total Pengajuan yang Diterima</p>
					<p class="table-box-content">{{$disetujui}}</p>
				</div>
				<div class="table table-content-box">
					<table class="table table-striped">
						<thead>
						<tr>
							<th>Nama</th>
							<th>Tanggal Pengajuan</th>
							<th>Nominal</th>
						</tr>
						</thead>
						<tbody>
						@foreach($last_disetujui as $row)
							<tr>
								<td style="font-size: 13px;">{{$row->nama_user}}</td>
								<td style="font-size: 13px;">{{ date('d M Y',strtotime($row->created_at)) }}</td>
								<td style="font-size: 13px;">Rp.{{number_format($row->total_nominal,0,'.','.')}},-</td>
							</tr>
						@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-6 col-sm-6 col-xs-12">
			<div class="table-box shadow">
				<div class="table-title-box-danger">
					<span class="pull-right table-box-icon"><i class="fa fa-user-times"></i></span>
					<p class="table-box-header">Total Pengajuan yang Ditolak</p>
					<p class="table-box-content">{{$ditolak}}</p>
				</div>
				<div class="table table-content-box">
					<table class="table table-striped">
						<thead>
						<tr>
							<th>Nama</th>
							<th>Tanggal Pengajuan</th>
							<th>Nominal</th>
						</tr>
						</thead>
						<tbody>
						@foreach($last_ditolak as $row)
							<tr>
								<td style="font-size: 13px;">{{$row->nama_user}}</td>
								<td style="font-size: 13px;">{{ date('d M Y',strtotime($row->created_at)) }}</td>
								<td style="font-size: 13px;">Rp.{{number_format($row->total_nominal,0,'.','.')}},-</td>
							</tr>
						@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
@endsection

@push('bottom')
	<script>
		$('section.content-header').find('small').html('Halaman Awal')
		$('section.content-header').find('small').show()
	</script>
	
	{{--HIGHCHART PLUGIN--}}
	<script src="https://code.highcharts.com/highcharts.js"></script>
	{{--<script src="https://code.highcharts.com/modules/exporting.js"></script>--}}
	{{--<script src="https://code.highcharts.com/modules/export-data.js"></script>--}}
	
	<script>
		/**
		 * PERSENTASE PENGAJUAN
		 */
		Highcharts.chart('persentasePengajuan', {
			chart: {
				plotBackgroundColor: null,
				plotBorderWidth: null,
				plotShadow: false,
				type: 'pie'
			},
			title: {
				text: ''
			},
			credits: {
				enabled: false
			},
			tooltip: {
				pointFormat: '{series.name}: <b>{point.y} ({point.percentage:.0f}%)</b>'
			},
			style: {
				color: 'black',
				fontFamily: 'Helvetica',
				border: 0
			},
			colors: ['#FFD31F', '#2DC399', '#E75651'],
			plotOptions: {
				pie: {
					dataLabels: {
						enabled: true,
						distance: -50,
						format: '<b>{point.name}</b>:<br> {point.y} ({point.percentage:.0f}%)',
						style: {
							color: 'black',
							fontFamily: 'Helvetica',
							border: false
						}
					},
					cursor: 'pointer',
					showInLegend: true,
					size: '100%'
				}
			},
			series: [{
				name: 'Total',
				data: {!! $persentase !!}
			}]
		});
		
		
		/**
		 * GRAFIK TAHUNAN
		 */
		Highcharts.chart('pengajuanTahunan', {
			title: {
				text: ''
			},
			xAxis: {
				categories: ['Januari', 'Febuari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']
			},
			credits: {
				enabled: false
			},
			yAxis: {
				allowDecimals: false,
				min: 0,
				title: {
					text: ''
				},
				stackLabels: {
					enabled: true,
					style: {
						fontWeight: 'bold'
					}
				}
			},
			plotOptions: {
				column: {
					stacking: 'normal'
				},
				dataLabels: {
					enabled: true
				},
				showInLegend: true,
			},
			colors: ['#E75651', '#2DC399', '#FFD31F'],
			series: {!! $grafik !!}
			// series: [{
			//     type: 'column',
			//     name: 'Over Budget',
			//     data: [1000000, 0, 500000, 0, 15000]
			// }, {
			//     type: 'column',
			//     name: 'Pemakaian',
			//     data: [5000000, 0, 3500000, 3000000, 1000000]
			// }, {
			//     type: 'spline',
			//     name: 'Anggaran',
			//     data: [5000000, 3000000, 3500000, 4000000, 1000000],
			//     marker: {
			//         lineWidth: 2,
			//         lineColor: Highcharts.getOptions().colors[3],
			//         fillColor: 'white'
			//     }
			// }]
		});
	</script>

@endpush