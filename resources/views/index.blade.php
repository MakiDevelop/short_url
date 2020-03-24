@extends('layouts.master')

@section('content')
<div class="jumbotron"> 
    <form action="" method="post" class="">
        @csrf
        
        {{-- <div class="form-group mb-2">
            <label for="staticEmail2" class="sr-only">Email</label>
            <input type="text" readonly class="form-control-plaintext" id="staticEmail2" value="email@example.com">
        </div> --}}
        {{-- <div class="form-group mx-sm-3 mb-2">
            <label for="inputPassword2" class="sr-only">網址</label>
            <input type="url" class="form-control" id="url" name="url" placeholder="網址">
        </div>
        <button type="button" id="send" class="btn btn-primary mb-2">Confirm identity</button> --}}

        <div class="form-group row">
            <div class="col-sm-10">
                <input type="url" class="form-control form-control-lg" id="url" name="url">
            </div>
            <div class="col-sm-2">
                <button type="button" id="send" class="btn btn-primary mb-2 form-control-lg">提交</button>
            </div>
        </div>
    </form>
    <div class="card" id="short_url">
        <div class="card-body">
          This is some text within a card body.
        </div>
    </div>
</div>
@endsection

@section('js_script')
    <script type="text/javascript" src="{{ asset('/js/index.js') }}"></script>
@endsection