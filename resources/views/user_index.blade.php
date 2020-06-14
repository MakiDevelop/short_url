@extends('layouts.master')

@section('content')
<div class="jumbotron"> 
    <form action="" method="get" class="" id="search_form">
        <div class="form-group row">
            <div class="col-sm-10">
                <input required type="text" class="form-control form-control-lg" id="keyword" name="keyword" placeholder="標題、標籤關鍵字查詢">
            </div>
            <div class="col-sm-2">
                <button type="submit" id="search" class="btn btn-primary mb-2 form-control-lg">查詢</button>
            </div>
        </div>
    </form>
</div>
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
	                        <div class="dropdown">
		                        <button class="btn btn-success dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		                        	分享至 
		                        </button>
		                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
									<a class="dropdown-item" href="https://www.facebook.com/sharer/sharer.php?u={{ url($item->short_url) }}" target="_blank">Facebook</a>
									<a class="dropdown-item" href="http://www.facebook.com/dialog/send?app_id=867277569999349&amp;link={{ url($item->short_url) }}&amp;redirect_uri={{ url($item->short_url) }}" target="_blank">Messenger</a>
									<a class="dropdown-item" href="https://twitter.com/intent/tweet?text={{ url($item->short_url) }}" target="_blank">Twitter</a>
									<a class="dropdown-item" href="http://www.plurk.com/?qualifier=shares&amp;status={{ url($item->short_url) }}" target="_blank">Plurk</a>
									<a class="dropdown-item" href="https://social-plugins.line.me/lineit/share?url={{ url($item->short_url) }}" target="_blank">LINE</a>
						  		</div>
							</div> 
<!--
	                        <button type="button" name="sharefb{{ $index }}" data-index="{{ $index }}" class="btn btn-primary" onclick="window.open('https://www.facebook.com/sharer/sharer.php?u={{ url($item->short_url) }}');">分享至FB</button>
	                        <button type="button" name="sharefb{{ $index }}" data-index="{{ $index }}" class="btn btn-dark" onclick="window.open('https://twitter.com/intent/tweet?text={{ url($item->short_url) }}');">分享至推特</button>
	                        <button type="button" name="sharefb{{ $index }}" data-index="{{ $index }}" class="btn btn-warning" onclick="window.open('http://www.plurk.com/?qualifier=shares&amp;status={{ url($item->short_url) }}');">分享至噗浪</button>
	                        <button type="button" name="sharefb{{ $index }}" data-index="{{ $index }}" class="btn btn-success" onclick="window.open('https://social-plugins.line.me/lineit/share?url={{ url($item->short_url) }}');">分享至LINE</button>
-->
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