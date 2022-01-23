jQuery(function($){
	function setCookie(cname, cvalue, exdays) {
	  const d = new Date();
	  d.setTime(d.getTime() + (exdays*24*60*60*1000));
	  let expires = "expires="+ d.toUTCString();
	  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
	}

	$('#order_type').on('change',function(){
		var type = $(this).val();
		setCookie( 'order_type', type, 3 );
		$( 'body' ).trigger( 'update_checkout' );
	}).change();

	$('#billing_email').on('change',function(){
		var email = $(this).val();
		setCookie( 'order_email', email, 3 );
		$( 'body' ).trigger( 'update_checkout' );
	}).change();


});