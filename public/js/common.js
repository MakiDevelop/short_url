var App = {
	debug: true,
	init: function init() {
		this.intGlobalEvents();
    },
	intGlobalEvents: function intGlobalEvents() {
		$('.toast').toast({'delay': 5000});
		$('.toast').on('shown.bs.toast', function() {
			$(this).css('z-index', '1350');
		});

		$(document).on('show.bs.modal', '.dialogWide', function () {
			$('.json_viewer').each(function () {
				$(this).jsonViewer($.parseJSON($(this).text()));
			});
		});
	},
	readImage: function readImage(file, img) {
		if (file) {
		  	var reader = new FileReader();
		  
		  	reader.onload = function(e) {
				$(img).attr('src', e.target.result);
		  	}
		  
		  	reader.readAsDataURL(file);
		}
	},
	isUrl: function isUrl(s) {
		var regexp = /(http|https|line):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/
		return regexp.test(s);
	},
	copyToClipboard: function copyToClipboard(id) {
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
	},
	ajax: function ajax(url, method, data, callbackSuccess) {
		$.ajax({
			url: url,
			cache: false,
			type: method,
			data: data,
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			success: callbackSuccess,
			beforeSend: function () {
				if ($('#send').length > 0) {
					$('#send').prop('disabled', true);
				}
			},
			error: function(response) {
				
				if (response.status == 422) {
					$('#error_alert').html(response.responseJSON.msg);
					$('#error_alert').removeAttr('hidden');
				}
				if (response.status == 401) {
					$('.toast').toast({'delay': 5000}).toast('show');
					$('.toast-header').addClass('bg-danger text-white');
					$('#toast_title').text('錯誤');
					$('#toast_body').text('請重新登入!');
					$('.toast').on('hidden.bs.toast', function () {
						location.href = '/admin';
					});
				}
				if ($('#send').length > 0) {
					$('#send').prop('disabled', false);
				}
			},
			done: function(response){
				console.log('in done');
				console.log(respnose);
				//check if response has errors object
				if(response.errors){
			
					// do what you want with errors, 
				}
			}
		});
	},
	ajaxUpload: function ajaxUpload(url, method, data, callbackSuccess) {
		$.ajax({
			url: url,
			cache: false,
			type: method,
			data: data,
			processData: false,
			contentType: false,
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			success: callbackSuccess,
			beforeSend: function () {
				if ($('#save').length > 0) {
					$('#save').prop('disabled', true);
				}
			},
			error: function(response) {
				// console.log('in error');
				// console.log(response);
				// console.log(response.responseJSON);
				if (response.status == 422) {
					$('#error_alert').html(response.responseJSON.msg);
					$('#error_alert').removeAttr('hidden');
				}
				if (response.status == 401) {
					$('.toast').toast({'delay': 5000}).toast('show');
					$('.toast-header').addClass('bg-danger text-white');
					$('#toast_title').text('錯誤');
					$('#toast_body').text('請重新登入!');
					$('.toast').on('hidden.bs.toast', function () {
						location.href = '/admin';
					});
				}
				if ($('#save').length > 0) {
					$('#save').prop('disabled', false);
				}
			},
			done: function(response){
				console.log('in done');
				console.log(respnose);
				//check if response has errors object
				if(response.errors){
			
					// do what you want with errors, 
				}
			}
		});
	},
	ajaxSubmit: function ajaxSubmit(action, data) {
		var form = $('<form/>', {
			action: action,
			method: 'post'
		});
		$.each(data, function (key, value) {
			form.append($('<input/>', {
				type: 'hidden',
				name: key,
				value: value
			}));
		});
		form.appendTo('body').submit();
	},
	getFormJsonData: function getFormJsonData($form) {
		var unindexed_array = $form.serializeArray();
		var indexed_array = {};

		$.map(unindexed_array, function (n, i) {
			indexed_array[n['name']] = n['value'];
		});

		return indexed_array;
	}
};

jQuery(document).ready(function () {
	App.init();
});