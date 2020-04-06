$(function () {
    $('#url').change(function(){
        if (App.isUrl($(this).val())) {
            let url = new URL($(this).val());
            let params = url.searchParams;
            if (params.has('fbclid')) {
                params.delete('fbclid');
                url.search = params;
                $(this).val(url.href);
            }
            if ($(this).data('ourl') != $(this).val()) {
                var postUrl = '/index/website',
                method = 'POST',
                data = $('form').serialize(),
                callbackSuccess = function (response) {
                    $('#send').prop('disabled', false);
                    if (response.success && response.data) {
                        $('#title').val(response.data.og_title);
                        $('#description').val(response.data.og_description);
                        if (typeof(response.data.og_image) != 'undefined') {
                            $('#pre_image').prop('src', response.data.og_image);
                            $('#image').val(response.data.og_image);
                        }
                    }
                };
                App.ajax(postUrl, method, data, callbackSuccess);
            }
            $(this).data('ourl', $(this).val());
        }
    });
    $('#send').click(function(){
        var url = '/index/short_url',
            method = 'POST',
            data = new FormData($('form')[0]),
            callbackSuccess = function (response) {
                console.log(response);
                if ($('#error_alert').is(':visible')) {
                    $('#error_alert').attr('hidden', 'hidden');
                }
                
                if ($('#send').length > 0) {
					$('#send').prop('disabled', false);
                }
                if (response.success) {
                    location.reload();
                }
            };
        App.ajaxUpload(url, method, data, callbackSuccess);
    });
    $('[name^=copy]').click(function(){
        var target_id = 'url_text' + $(this).data('index');
        App.copyToClipboard(target_id);
    });
    $('[name^=qrcode]').click(function(){
        var num = $(this).data('index'),
            target_id = '#url_text' + num;

        if ($('#collapseQRCode' + num).find('img').length == 0) {
            var img_url = 'https://chart.googleapis.com/chart?cht=qr&chs=200x200&choe=UTF-8&chl=' + encodeURIComponent($(target_id).text());
            $('#collapseQRCode' + num).prepend('<img src="'+ img_url +'" />');
        }
    });

    $('#fullModal').on('hide.bs.modal', function (e) {
        $('#code').val('')
        $('#url_form')[0].reset();
        $('#pre_image').attr('src', '');
    });

    // edit
    $('[name^=edit]').click(function(){
        var url = '/index/url',
            method = 'GET',
            code = $(this).data('code'),
            data = {},
            callbackSuccess = function (response) {
                console.log(response);
                if ($('#error_alert').is(':visible')) {
                    $('#error_alert').attr('hidden', 'hidden');
                }
                
                if (response.success) {
                    $('#code').val(response.data.code);
                    $('#url').val(response.data.url);
                    $('#title').val(response.data.title);
                    $('#description').val(response.data.description);
                    $('#image').val(response.data.image);
                    $('#pre_image').attr('src', response.data.image);
                    $('#ga_id').val(response.data.ga_id);
                    $('#pixel_id').val(response.data.pixel_id);
                    $('#hash_tag').val(response.data.hashtag);
                    $('#fullModal').modal('show');
                }
            };
            
        App.ajaxUpload(url + '?code=' + code, method, data, callbackSuccess);
    });

    // delete
    $('[name^=delete]').click(function(){
        var num = $(this).data('index');
        $('#delete_code').val($(this).data('code'));
        $('#del_short_text').text($('#url_text' + num).text());
        $('#deleteModal').modal('show');
    });
    $('#deleteModal').on('hide.bs.modal', function (e) {
        $('#delete_code').val('');
    });
    $('#delete_btn').click(function(){
        var url = '/index/url_delete',
            method = 'POST',
            data = $('#delete_form').serialize(),
            callbackSuccess = function (response) {
                if ($('#error_alert').is(':visible')) {
                    $('#error_alert').attr('hidden', 'hidden');
                }
                
                if ($('#delete_btn').length > 0) {
					$('#delete_btn').prop('disabled', false);
                }
                if (response.success) {
                    location.reload();
                }
            };
        App.ajax(url, method, data, callbackSuccess);
    });
});