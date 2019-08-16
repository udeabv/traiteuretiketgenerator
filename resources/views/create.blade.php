@extends('layouts.app')
@section('title', 'generator')
@section('content')
    <div class="container section">
        <h1>Generator</h1>
        @if(!Session::has('success'))
        {!! Form::open(array('url'=>'apply/upload','method'=>'POST', 'files'=>true)) !!}
        <div class="form-group">
            <label for="products">1: Selecteer producten (.xlsx)</label>
            {!! Form::file('products') !!}
            <small id="emailHelp" class="form-text text-muted">{!!$errors->first('products')!!}</small>
            @if(Session::has('error'))
                <small id="emailHelp" class="form-text text-muted bg-danger">{!! Session::get('error') !!}</small>
            @endif
        </div>
        <div class="form-group">
            <label for="template">2: Selecteer template (.label)</label>
            {!! Form::file('template') !!}
            <small id="emailHelp" class="form-text text-muted">{!!$errors->first('template')!!}</small>
            @if(Session::has('error'))
                <small id="emailHelp" class="form-text text-muted bg-danger">{!! Session::get('error') !!}</small>
            @endif
        </div>
        {!! Form::submit('Start the magic', array('class'=>'send-btn btn btn-success')) !!}
        {!! Form::close() !!}
        @else
            <div id="success" class="bg-success">{!! Session::get('success') !!} </div>
        @endif
    </div>
@endsection