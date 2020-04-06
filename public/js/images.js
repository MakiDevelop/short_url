$(function () {
    $('#image_block').click(function(){
        // $('#image_file').click();
        $('#image_file').trigger('click'); 
    });
    $('#image_file').change(function(){
        App.readImage(this.files[0], $('#pre_image'));
        $('#text_block').hide();
        $('#remove_image_file').removeClass('d-none');
    });

    $('#input-file-now').change(function(){
        console.log($(this).val());
    }); 
    $('#target').click(function(){
        $('#input-file-now').click();
    });

    $('#remove_image_file').click(function(){
        $('#image_file').val('');
        $(this).addClass('d-none');
        $('#pre_image').attr('src', $('#image').val());
        $('#text_block').show();
        return false;
    });
});
function dragstart_handler(ev) {
    console.log("dragStart");
    console.log(ev);
    // Add the target element's id to the data transfer object
    // ev.dataTransfer.setData("text/plain", ev.target.id);
    return false;
}
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

// function drop_handler(ev) {
//     ev.preventDefault();
//     console.log(ev);
//     if (ev.dataTransfer.items) {
//         console.log(ev.dataTransfer.items.length);
//         var name = ev.dataTransfer.items[0].getAsFile().name;
//         var file = ev.dataTransfer.items[0].getAsFile();
//         console.log(name);
//         // $('#input-file-now').val(file);

//         // const dT = new DataTransfer();
//         // dT.items.add(file);
//         // fileInput.files = dT.files;
//         // $('#input-file-now').files = dT.files;
//         $('#input-file-now').files = file;

//         $('#target').append('<img>');
//         var reader = new FileReader();
		  
// 		reader.onload = function(e) {
// 			$('#target img').attr('src', e.target.result);
// 		}
//         reader.readAsDataURL(file);

//         for (var i = 0; i < ev.dataTransfer.items.length; i++) {
//             if (ev.dataTransfer.items[i].kind === 'file') {
//                 var file = ev.dataTransfer.items[i].getAsFile();
//                 console.log('... file[' + i + '].name = ' + file.name);
//                 console.log('... file[' + i + '].size = ' + file.size);
//                 console.log('... file[' + i + '].type = ' + file.type);
//             }
//         }
//     } else {
//         // Use DataTransfer interface to access the file(s)
//         for (var i = 0; i < ev.dataTransfer.files.length; i++) {
//             console.log('... file[' + i + '].name = ' + ev.dataTransfer.files[i].name);
//         }
//     }
//     // Get the id of the target and add the moved element to the target's DOM
//     // var data = ev.dataTransfer.getData("text");
//     // ev.target.appendChild(document.getElementById(data));
// }