$(function () {
    $('#url').change(function(){
        console.log(isUrl($(this).val()));
        let url = new URL($(this).val());
        console.log(url.hostname);
        console.log(url.search);
        console.log(url.pathname);
        console.log(url.searchParams);

        let params = url.searchParams;
        // var new_params = {};
        // for (let pair of params.entries()) {
        //     console.log(`key: ${pair[0]}, value: ${pair[1]}`)
        //     if (pair[0] != 'fbclid') {

        //     }
        // }
        params.delete('fbclid');
        console.log(params.toString());
        url.search = params;
        console.log(url.href);
        $(this).val(url.href);
    });
    $('#send').click(function(){
        
    });
});
function isUrl(s) {
    var regexp = /(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
    return regexp.test(s);
}