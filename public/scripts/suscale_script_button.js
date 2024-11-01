jQuery(document).ready(function($){
	$("#sus_btn").click(function(e){
		e.preventDefault();
		localStorage.setItem("SESSION_SYSTEM_ID", $('#session_sys_id').val());
		localStorage.setItem("SESSION_USER_ID", $('#session_usr_id').val());
		localStorage.setItem("SESSION_CAME_FROM", window.location.href);
		// window.location.replace( $('#session_sus_page').val());
		$.ajax({
            url: wp_ajax.ajaxurl,
            type: "post",
            data: {
            	action: "suscale_redirect",
            	_nonce : wp_ajax.nonce},
            dataType: "json"
		}).done(function(data){
			// console.log("DONE");
			window.location.replace( data['redirect_url']);
		}).fail(function(jqXHR, textStatus){

		});
	})
})