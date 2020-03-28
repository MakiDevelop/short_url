@extends('layouts.master')

@section('content')
<div class="jumbotron"> 
    <form action="" method="post" class="">
        @csrf
        <div class="form-group row">
            <div class="col-sm-10">
                <input type="url" class="form-control form-control-lg" id="url" name="url" placeholder="網址">
            </div>
            <div class="col-sm-2">
                <button type="button" id="send" class="btn btn-primary mb-2 form-control-lg">提交</button>
            </div>
        </div>
    </form>
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
@endsection

@section('js_script')
    <script type="text/javascript" src="{{ asset('/js/index.js') }}"></script>
@endsection