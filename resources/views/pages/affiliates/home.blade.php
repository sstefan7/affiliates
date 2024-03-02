@extends('partials.layout')

@section('title')
Affiliates
@endsection

@section('content')
<h2>So, let's have them invited.</h2>
<p>Simply upload the guest list, and we'll take care of the rest. Bear in mind that we're only accepting text files with JSON strings.</p>
<form action="/invite" method="POST" enctype="multipart/form-data">
    {{ csrf_field() }}
    <input type="file" id="affiliates" name="affiliates">
    <br/>
    <br/>
    <input class="btn submit" type="submit" value="Submit">
</form>
@include('pages.affiliates._invited')
@endsection