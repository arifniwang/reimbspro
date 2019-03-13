@extends('crudbooster::admin_template')
@section('content')
Lorem ipsum dolor sit amet, consectetur adipisicing elit. Amet asperiores, aspernatur assumenda atque beatae corporis culpa dignissimos dolorem doloremque doloribus earum enim fugiat id laboriosam possimus sint sunt, suscipit veritatis.
@endsection

@push('bottom')
    <script>
        $('section.content-header').find('small').html('Dashboard')
    </script>
@endpush