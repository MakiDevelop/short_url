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
        
    });
});
function isUrl(s) {
    var regexp = /(http|https|line):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
    return regexp.test(s);
}