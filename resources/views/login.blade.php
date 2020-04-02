@extends('layouts.master')

@section('content')
<div class="card-body"> 
    

    <a class="btn btn-block btn-google" href="{{ url('/login/oauth/google') }}">
        <img src="https://developers.google.com/identity/images/g-logo.png">
        <span class="ml-2">Sign in with Google</span>
    </a>
    {{-- <button class="btn btn-primary btn-block btn-facebook">
        <i class="fab fa-facebook-square"></i>
        <span class="ml-2">Sign in with Facebook</span>
    </button> --}}


</div>
@endsection