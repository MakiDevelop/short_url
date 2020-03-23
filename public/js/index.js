$(function () {
    $('#url').change(function(){
        console.log(isUrl($(this).val()));
        let url = new URL($(this).val());
        console.log(url.hostname);
    });
    $('#send').click(function(){
        
    });
});
function isUrl(s) {
    var regexp = /(http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
    return regexp.test(s);
}