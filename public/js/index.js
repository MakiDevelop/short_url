$(function () {
    $('#url').change(function(){
        if (isUrl($(this).val())) {
            let url = new URL($(this).val());
            let params = url.searchParams;
            if (params.has('fbclid')) {
                params.delete('fbclid');
                url.search = params;
                $(this).val(url.href);
            }
        }
    });
    $('#send').click(function(){
        var url = '/index/short_url',
            method = 'POST',
            data = $('form').serialize(),
            callbackSuccess = function (response) {
                console.log(response);
                if ($('#error_alert').is(':visible')) {
                    $('#error_alert').attr('hidden', 'hidden');
                }
                
                if ($('#send').length > 0) {
					$('#send').prop('disabled', false);
				}
            };
        App.ajax(url, method, data, callbackSuccess);
    });
});
function isUrl(s) {
    var regexp = /(http|https|line):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
    return regexp.test(s);
}