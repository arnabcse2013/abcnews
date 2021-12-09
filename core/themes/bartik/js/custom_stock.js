var $ = jQuery;			 
$(document).ready(function () {
	//alert('ok');
});

(function estockdata() {
	var baseUrl = drupalSettings.path.baseUrl;
	var origin   = window.location.origin;
	var serverPath = origin+baseUrl+"dailystockupdate";
	$.ajax({
		url: serverPath,
		type: 'GET',
		dataType: 'json',
		async: false,  
		success: function(data) {
			var substr = data[0].split('||');
			$('#last_val').html(substr[0]);
			$('#time_val').html(substr[1]);
		},
		complete: function() {
			setTimeout(estockdata, 5000);
		}
	});
})();
