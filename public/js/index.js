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
                if (response.success) {
                    $('#short_url').removeClass('d-none');
                    $('#url_text').text(response.short_url);

                    var img_url = 'https://chart.googleapis.com/chart?cht=qr&chs=200x200&choe=UTF-8&chl=' + encodeURIComponent(response.short_url);
                    $('#collapseQRCode').prepend('<img src="'+ img_url +'" />');
                }
            };
        App.ajax(url, method, data, callbackSuccess);
    });

    $('#copy').click(function(){
        copyToClipboard('url_text')
    });
});
function isUrl(s) {
    var regexp = /(http|https|line):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
    return regexp.test(s);
}

function copyToClipboard(id) {
    var text = document.getElementById(id);
        //做下相容
    if (document.body.createTextRange) {  //如果支援
        var range = document.body.createTextRange(); //獲取range
        range.moveToElementText(text); //游標移上去
        range.select();  //選擇
        document.execCommand('copy');
    } else if (window.getSelection) {
        var selection = window.getSelection(); //獲取selection
        var range = document.createRange(); //建立range
        range.selectNodeContents(text);  //選擇節點內容
        selection.removeAllRanges(); //移除所有range
        selection.addRange(range);  //新增range
        document.execCommand('copy');
    }
}