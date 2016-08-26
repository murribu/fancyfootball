@extends('template')

@section('content')
@include('nav', ['activelink' => 'about'])
<div class="container" style="padding-top:75px;">
    <div class="jumbotron">
        <h2>About</h2>
        <p>This is an attempt to assign values to NFL players for a fantasy football league.</p>
    </div>
</div>
@endsection