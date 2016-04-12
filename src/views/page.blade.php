@extends('frame')

@section('main_custom_css')

{!! $page_css !!}

@stop

@section('main_content')

    {!! $page_html !!}

@stop

@section('main_custom_javascripts')

{!! $page_js !!}

@stop