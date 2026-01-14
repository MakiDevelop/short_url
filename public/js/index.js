$(function() {
    $('#url').change(function() {
        if (App.isUrl($(this).val())) {
            let url = new URL($(this).val());
            let params = url.searchParams;
            if (params.has('fbclid')) {
                params.delete('fbclid');
                url.search = params;
                $(this).val(url.href);
            }
        }
    });
    $('#send').click(function() {
        var url = '/index/short_url',
            method = 'POST',
            data = $('form').serialize(),
            callbackSuccess = function(response) {
                // console.log(response);
                if ($('#error_alert').is(':visible')) {
                    $('#error_alert').attr('hidden', 'hidden');
                }

                if ($('#send').length > 0) {
                    $('#send').prop('disabled', false);
                }
                if (response.success) {
                    if ($('#collapseQRCode').find('img').length > 0) {
                        $('#collapseQRCode').find('img').remove();
                    }
                    $('#short_url').removeClass('d-none');
                    $('#url_text').text(response.short_url);

                    var img_url = 'https://chart.googleapis.com/chart?cht=qr&chs=200x200&choe=UTF-8&chl=' + encodeURIComponent(response.short_url);
                    $('#collapseQRCode').prepend('<img src="' + img_url + '" />');
                } else {
                    if (response.err) {
                        alert(response.msg);
                    }
                }
            };
        if ($('#short_url').is(':visible')) {
            $('#short_url').addClass('d-none');
        }
        App.ajax(url, method, data, callbackSuccess);
    });

    $('#copy').click(function() {
        App.copyToClipboard('url_text')
    });

    //停用form本身的enter即submit start
    $('#short_url_form').on('keyup keypress', function(e) {
        var keyCode = e.keyCode || e.which;
        if (keyCode === 13) {
            e.preventDefault();
            return false;
        }
    });
});