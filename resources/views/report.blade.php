@extends('crudbooster::admin_template')
@section('content')
    <link rel="stylesheet"
          href="{{asset('vendor/crudbooster/assets/adminlte/plugins/daterangepicker/daterangepicker-bs3.css')}}">
    <style>
        .daterangepicker td.active, .daterangepicker td.active:hover {
            background-color: #053459 !important;
            border-color: #053459 !important;
        }

        .daterangepicker td.in-range {
            background: #D8D8D8 !important;
            color: #053459;
        }

        .box-filter {
            padding-top: 20px;
        }

        #btnPrint {
            width: 100%;
        }
    </style>

    <div class="box shadow">
        <form class="box-body" method="POST" target="_blank" action="{{CRUDBooster::mainpath('download')}}">
            <div class="row no-margin box-filter">
                <div class="col-sm-6">
                    <div class="row">
                        <div class="col-sm-6">
                            <label for="start">Tanggal Mulai</label>
                            <input type="text" readonly="" required="" class="form-control notfocus input_date"
                                   name="start"
                                   id="start" placeholder="Tanggal Mulai" value="{{date('m')}}/01/{{date('Y')}}">
                        </div>
                        <div class="col-sm-6">
                            <label for="start">Tanggal Selesai</label>
                            <input type="text" readonly="" required="" class="form-control notfocus input_date"
                                   name="end"
                                   id="end" placeholder="Tanggal Selesai" value="{{date('m/d/Y')}}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row no-margin box-filter">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label for="pegawai">Pegawai</label>
                        <select name="pegawai" id="pegawai" class="form-control">
                            <option value="">Silahkan pilih pegawai</option>
                            @foreach($pegawai as $row)
                                <option value="{{$row->id}}">{{$row->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>


            <div class="row no-margin box-filter">
                <div class="col-sm-6">
                    <div class="form-group">
                        <button id="btnPrint" class="btn btn-default">Unduh <i class="fa fa-download"></i></button>
                    </div>
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                </div>
            </div>
        </form>
    </div>
@endsection

@push('bottom')
    <!--BOOTSTRAP DATERANGEPICKER-->
    <script src="{{asset('vendor/crudbooster/assets/adminlte/plugins/daterangepicker/moment.min.js')}}"></script>
    <script src="{{asset('vendor/crudbooster/assets/adminlte/plugins/daterangepicker/daterangepicker.js')}}"></script>
    <script>
        $('#start').daterangepicker({
            "singleDatePicker": true,
            "maxDate": "{{date('m/d/Y')}}"
        });
        $('#end').daterangepicker({
            "singleDatePicker": true,
            "maxDate": "{{date('m/d/Y')}}"
        });
    </script>
@endpush