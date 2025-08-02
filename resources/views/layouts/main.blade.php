@extends('adminlte::page')

@section('title', config('app.name') . ' - Dashboard')

@section('content_header')
    <h1>@yield('page_title', 'Dashboard')</h1>
@stop

@section('content')
    @yield('page_content')
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    @yield('custom_css')
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Setup CSRF token for all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            // Toast notifications
            @if(session('success'))
                toastr.success('{{ session('success') }}');
            @endif
            
            @if(session('error'))
                toastr.error('{{ session('error') }}');
            @endif
            
            @if($errors->any())
                @foreach($errors->all() as $error)
                    toastr.error('{{ $error }}');
                @endforeach
            @endif
        });
    </script>
    @yield('custom_js')
@stop
