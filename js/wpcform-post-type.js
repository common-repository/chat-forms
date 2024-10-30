/* vim: set expandtab tabstop=4 shiftwidth=4: */
jQuery('.repeatable-add').click(function() {
	field = jQuery(this).closest('td').find('.custom_repeatable li:last').clone(true);
	fieldLocation = jQuery(this).closest('td').find('.custom_repeatable li:last');
	jQuery('input', field).val('').attr('name', function(index, name) {
		return name.replace(/(\d+)/, function(fullMatch, n) {
			return Number(n) + 1;
		});
	})
	jQuery('select', field).val('').attr('name', function(index, name) {
		return name.replace(/(\d+)/, function(fullMatch, n) {
			return Number(n) + 1;
		});
	})
	field.insertAfter(fieldLocation, jQuery(this).closest('td'))
	return false;
});

jQuery('.repeatable-remove').click(function(){
	jQuery(this).parent().remove();
	return false;
});
	
jQuery('.custom_repeatable').sortable({
	opacity: 0.6,
	revert: true,
	cursor: 'move',
	handle: '.sort'
});


if(typeof getUrlParameter !== 'function'){
	var getUrlParameter = function getUrlParameter(sParam) {
	    var sPageURL = window.location.search.substring(1),
	        sURLVariables = sPageURL.split('&'),
	        sParameterName,
	        i;
	
	    for (i = 0; i < sURLVariables.length; i++) {
	        sParameterName = sURLVariables[i].split('=');
	
	        if (sParameterName[0] === sParam) {
	            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
	        }
	    }
	};
}

//For addition code from Tai
jQuery(document).ready(function($){
	var post_type = $('#post_type').val();
	var chat_form_api = 'https://chat-forms.com/api';
	var wpcform_language = $('#wpcform_language');
	var email_notifi = $('#wpcform_notification_email').val();
	var title = $('#title').val();
	var data_changed = false;

	autosize($('#wpcform_form'));

	//Validate email function
	var wpcform_validateEmail = function(email) {
	    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	    return re.test(String(email).toLowerCase());
	}

	var save_chat_form_id = function(pid, id){
		$.ajax({
			url: ajaxurl,
			method: 'POST',
			data: {
				action: 'wpcform_save_chat_form_id',
				chat_form_id: id,
				pid: pid
			},
			dataType: 'json',
			
			success: function(res){
				console.log(res);
			}
		})
	}

	if(post_type === 'wpcform'){
		var publish_button = $('#publish');
		var lang = $('#wpcform_language').val();
		var gen_button = $('#conversational-forms-generator');

		if(publish_button.val() === 'Publish' || lang === 'none'){
			publish_button.attr('disabled', 'disabled');
			gen_button.attr('disabled', 'disabled');
		}
	}


	$('#title').on('input', function(){
		var new_title = $('#title').val();

		if(title !== new_title && title !== ''){
			publish_button.removeAttr('disabled');
		}else{
			publish_button.attr('disabled', 'disabled');
		}
	});

	$('#wpcform_notification_email').on('input', function(){
		var new_val = $(this).val();

		if(email_notifi !== new_val && title !== ''){
			publish_button.removeAttr('disabled');
		}else{
			publish_button.attr('disabled', 'disabled');
		}
	});

	var report_email = $('#wpcform_reporting_email').val();
	$('#wpcform_reporting_email').on('input', function(){
		var new_val = $(this).val();

		if(report_email !== new_val && title !== ''){
			publish_button.removeAttr('disabled');
		}else{
			publish_button.attr('disabled', 'disabled');
		}
	});

	var noti_status = $('input[name="wpcform_enable_notifi"]').val();
	$('input[name="wpcform_enable_notifi"]').on('change', function(){
		var new_val = $(this).val();

		if(noti_status !== new_val && title !== ''){
			publish_button.removeAttr('disabled');
		}else{
			publish_button.attr('disabled', 'disabled');
		}
	});

	var report_status = $('input[name="wpcform_enable_reporting"]').val();
	$('input[name="wpcform_enable_reporting"]').on('change', function(){
		var new_val = $(this).val();

		if(report_status !== new_val && title !== ''){
			publish_button.removeAttr('disabled');
		}else{
			publish_button.attr('disabled', 'disabled');
		}
	});

	//Select Langue action
	$('#wpcform_language').on('change', function(){
		var lang_val = $(this).val();
		var pid = getUrlParameter('post');

		if(lang_val === 'none'){
			publish_button.attr('disabled', 'disabled');
			gen_button.attr('disabled', 'disabled');
		}else{
			if(pid !== undefined){
				publish_button.removeAttr('disabled');
			}
			
			gen_button.removeAttr('disabled');
		}
	});


	$(document).on('click', '#select-language', function(event){
		event.preventDefault();

		var wpcform_form = $('#wpcform_form').val();
		var list_lang = [];
		var button = $(this);
		var error_show = $(this).next('.lang-error-show').html('');

		$("#wpcform_language option").each(function(){
		    if($(this).val() !== 'none'){
		    	list_lang.push($(this).val());
		    }
		});

		var request_support_lang = function(){
			$.ajax({
				url: chat_form_api+'/cache/google-form',
				type: 'POST',
				dataType: 'json',
				data: {
					preferredLn: 'en',
					url: wpcform_form
				},
				success: function(response){
					if(response.status === 'success'){
						var pid = getUrlParameter('post');

						if(pid !== undefined){
							publish_button.removeAttr('disabled');
						}
						
						var pre_lang = 'en';

						$.each(response.output.supported, function (i, item) {
							if(list_lang.indexOf(item.code) === -1){
								list_lang.push(item.code);

								$('#wpcform_language').append($('<option>', { 
							        value: item.code,
							        text : item.name 
							    }));

							    if(item.isPreferred){
							    	pre_lang = item.code;
							    }
							}
						});

						wpcform_language.val(pre_lang).trigger('change');
					}else{
						error_show.html(`<span class="${response.status}">${response.error}</span>`);
					}

					button.html('Get languages supported');
				}
			});
		}

		button.html('Loading...');
		request_support_lang();
	});

	//Button action
	$(document).on('click', '#conversational-forms-generator', function(event) {
		event.preventDefault();
		/* Act on the event */

		var wpcform_form = $('#wpcform_form').val();
		var loading = $(this).next('.loading');
		var error_show = $(this).parent().next('.gen-error-show');
		var button = $(this);
		var noti_status = $('input[name="wpcform_enable_notifi"]').val();

		if(!wpcform_validateEmail(email_notifi) || noti_status !== 'on'){
			email_notifi = '';
		}

		var lang = $('#wpcform_language').val();

		if(lang === ''){
			alert('Select language');
			return;
		}

		var cform_grunt_dir = function(){
			button.hide();
			loading.html('Processing...').show();
			error_show.html('');

			$.ajax({
				url: chat_form_api+'/exec/grunt-dir',
				type: 'POST',
				dataType: 'json',
				data: {
					preferredLn: lang,
					url: wpcform_form
				},

				success: function(response){
					if(response.status === 'success'){
						var boot_id = response.output;
						
						cform_deploy({
							name: boot_id,
							email: email_notifi,
							url: wpcform_form
						})
					}else{
						error_show.html(`<span class="${response.status}">${response.error}. Please check form url.</span>`);
						loading.hide();
						button.show();
					}
				}
			});
		}

		var cform_deploy = function(data_dp){
			$.ajax({
				url: chat_form_api+'/exec/deploy-form',
				type: 'POST',
				dataType: 'json',
				data: {
					name: data_dp.name,
					email: data_dp.email,
					url: data_dp.url
				},
				success: function(response){
					var url_chat = `https://chat-forms.com/forms/${response.output}/index.html`;

					if(response.status === 'success'){
						var pid = getUrlParameter('post');

						if(pid === undefined){
							pid = $('#post_ID').val();
						}

						error_show.html(`<span class="success">Generate chat from successfully! <a href="${url_chat}" target="_blank">View</a></span>`);
						publish_button.removeAttr('disabled');
						save_chat_form_id(pid, response.output);
					}else{
						error_show.html(`<span class="${response.status}">${response.error}</span>`);
					}

					loading.hide();
					button.show();
				}
			})
		}

		cform_grunt_dir();
	});
});