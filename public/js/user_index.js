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
                    
                }
            };
        App.ajaxUpload(url, method, data, callbackSuccess);
    });
});