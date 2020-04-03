@extends('layouts.master')

@section('content')
<div class="jumbotron"> 
    <button type="button" id="short" class="btn btn-primary" data-toggle="modal" data-target="#fullModal">新增短網址</button>
    
    <div class="card d-none" id="short_url">
        <div class="card-body">
            <div class="col-sm-12 text-right">
                <div id="url_text" class="d-inline p-8"></div>
                <div class="d-inline p-2">
                    <button type="button" id="copy" class="btn btn-primary">複製</button>
                    <button type="button" id="qrcode" class="btn btn-primary" data-toggle="collapse" data-target="#collapseQRCode" aria-expanded="false" aria-controls="collapseQRCode">QRCode</button>
                </div>
            </div>
            <div class="col-sm-12 text-center collapse" id="collapseQRCode">

            </div>
        </div>
    </div>
</div>
@include('modal_url')

@endsection

@section('css_link')
    
@endsection

@section('js_script')
    <script type="text/javascript" src="{{ asset('/js/user_index.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/images.js') }}"></script>
@endsection