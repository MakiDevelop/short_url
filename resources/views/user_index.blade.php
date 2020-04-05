@extends('layouts.master')

@section('content')
<div class="jumbotron"> 
    <div class="text-right">
        <button type="button" id="short" class="btn btn-primary" data-toggle="modal" data-target="#fullModal">新增短網址</button>
    </div>
    @if (isset($lists))
        @foreach ($lists as $index => $item)
            <div class="card">
                <div class="card-body">
                    <div class="">
                        {{ $item->created_at->format('Y-m-d') }}
                    </div>
                    <div id="title{{ $index }}" class="">{{ $item->og_title }}</div>
                    <div class="">{{ $item->original_url }}</div>
                    <div class="">
                        <p id="url_text{{ $index }}" class="d-inline">{{ url($item->short_url) }}</p>
                        <div class="d-inline p-2">
                            <button type="button" name="copy{{ $index }}" data-index="{{ $index }}" class="btn btn-primary">複製</button>
                            <button type="button" name="qrcode{{ $index }}" data-index="{{ $index }}" class="btn btn-primary" data-toggle="collapse" data-target="#collapseQRCode{{ $index }}" aria-expanded="false" aria-controls="collapseQRCode{{ $index }}">QRCode</button>
                            <button type="button" name="edit{{ $index }}" data-index="{{ $index }}" data-code="{{ $item->short_url }}" class="btn btn-primary">Edit</button>
                            <button type="button" name="delete{{ $index }}" data-index="{{ $index }}" data-code="{{ $item->short_url }}" class="btn btn-danger">Delete</button>
                        </div>
                    </div>
                    <div class="">
                        <p>Tag : </p>
                        @foreach ($item->tags as $tag)
                            <span class="badge badge-secondary">{{ $tag->tag_name }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="col-sm-12 text-center collapse" id="collapseQRCode{{ $index }}">
                    
                </div>
            </div>
        @endforeach
        
        @if ($lists->hasPages())
            <div class="justify-content-center">
                {{ $lists->appends($querys)->links('page') }}
            </div>
        @endif
    @endif
</div>


@include('modal_url')
@include('modal_url_delete')

@endsection

@section('css_link')
    
@endsection

@section('js_script')
    <script type="text/javascript" src="{{ asset('/js/user_index.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/images.js') }}"></script>
@endsection