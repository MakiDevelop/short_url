$(function () {
    $('#image_block>div').click(function(e){
        $('#image_file').trigger('click'); 
    });
    $('#image_file').change(function(e){
        App.readImage(this.files[0], $('#pre_image'));
        $('#text_block').hide();
        $('#remove_image_file').removeClass('d-none');
        return false;
    });

    $('#remove_image_file').click(function(){
        $('#image_file').val('');
        $(this).addClass('d-none');
        $('#pre_image').attr('src', $('#image').val());
        $('#text_block').show();
        return false;
    });
});

function dragover_handler(ev) {
    ev.preventDefault();
    // Set the dropEffect to move
    ev.dataTransfer.dropEffect = "move"
}
document.getElementById('image_block').ondrop = function(ev) {
    document.getElementById('image_file').files = ev.dataTransfer.files;
    App.readImage(ev.dataTransfer.files[0], $('#pre_image'));
    $('#text_block').hide();
    $('#remove_image_file').removeClass('d-none');
    ev.preventDefault();
};