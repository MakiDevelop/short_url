@extends('layouts.master')

@section('content')
<div class="jumbotron"> 
    <div class="text-right">
        <button type="button" id="short" class="btn btn-primary" data-toggle="modal" data-target="#fullModal">建立短網址</button>
    </div>
    @if (isset($lists))
        @foreach ($lists as $index => $item)
            <div class="card">
                <div class="card-body">
                    <div class="">
                        {{ $item->created_at->format('Y-m-d') }}
                    </div>
                    <div id="title{{ $index }}" class="">原網址：
                        <a href="{{ $item->original_url }}" target="_blank">{{ ($item->og_title != '')? $item->og_title : $item->original_url }}</a>
                    </div>
                    {{-- <div class="">
                        {{ $item->original_url }}
                    </div> --}}
                    <div class="">
                        短網址：<p id="url_text{{ $index }}" class="d-inline"><a href="{{ url($item->short_url) }}" target="_blank">{{ url($item->short_url) }}</a></p>
                        <div class="d-inline p-2">
                            <button type="button" name="copy{{ $index }}" data-index="{{ $index }}" class="btn btn-primary">複製</button>
                            <button type="button" name="qrcode{{ $index }}" data-index="{{ $index }}" class="btn btn-primary" data-toggle="collapse" data-target="#collapseQRCode{{ $index }}" aria-expanded="false" aria-controls="collapseQRCode{{ $index }}">QRCode</button>
                            <button type="button" name="analytics{{ $index }}" class="btn btn-primary" data-toggle="collapse" data-target="#collapseAnalytics{{ $index }}" aria-expanded="false" aria-controls="collapseAnalytics{{ $index }}">分析</button>
                            <button type="button" name="edit{{ $index }}" data-index="{{ $index }}" data-code="{{ $item->short_url }}" class="btn btn-primary">編輯</button>
                            <button type="button" name="delete{{ $index }}" data-index="{{ $index }}" data-code="{{ $item->short_url }}" class="btn btn-danger">刪除</button>
                        </div>
                    </div>
                    <div class="">
                        <p>累積點擊：{{ $item->clicks ?? 0 }}</p>
                    </div>
                    <div class="">
                        <span>標籤：</span>
                        @foreach ($item->tags as $tag)
                            <span class="badge badge-secondary">{{ $tag->tag_name }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="col-sm-12 text-center collapse" id="collapseQRCode{{ $index }}">
                    
                </div>
                <div class="col-sm-6 text-center collapse" id="collapseAnalytics{{ $index }}" data-index="{{ $index }}" data-code="{{ $item->short_url }}">
                    <canvas id="referralChart{{ $index }}" style="height:160vh; width:200vw"></canvas>
                    <canvas id="osChart{{ $index }}" style="height:160vh; width:200vw"></canvas>
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
    <script type="text/javascript" src="{{ asset('/js/moment.js') }}"></script>
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js" integrity="sha256-R4pqcOYV8lt7snxMQO/HSbVCFRPMdrhAFMH+vr9giYI=" crossorigin="anonymous"></script> -->
    <script type="text/javascript" src="{{ asset('/js/chart.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/user_index.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/images.js') }}"></script>
    <script type="text/javascript" src="{{ asset('/js/analytics_chart.js') }}"></script>
@endsection