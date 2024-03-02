@extends('master')

@section('layout')
@include('partials.header')
<section>
    <div class="row">
        <div class="main">
            @yield('content')
        </div>
    </div>
</section>
@include('partials.footer')
@endsection