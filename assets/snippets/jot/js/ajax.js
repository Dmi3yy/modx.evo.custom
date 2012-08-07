(function($) {
	setData = function(data,id) {
		var array = data.split("|!|~|!|");
		$('#form-'+id).html(array[0]);
		$('#comments-'+id).html(array[1]);
		$('#moderate-'+id).html(array[2]);
		$('.navigation-'+id).html(array[3]);
		$('#subscribe-'+id).html(array[4]);
	}
	jotAjax = function(id) {
		var hist;
		
		$(document).delegate('.jot-nav a','click',function(event) {
			hist = $(this).attr('href');
			$.get($(this).attr('href'), function(data) {setData(data,id);});
			return false;
		});
		$(document).delegate('.jot-list a,.jot-mod a','click',function(event) {
			$.get($(this).attr('href'), function(data) {setData(data,id);});
			return false;
		});
		$(document).delegate('.jot-form','submit',function(event) {
			event.preventDefault();
			$.post($(this).attr('action'), $(this).serialize(), function(data) {setData(data,id);});
		});
		$(document).delegate('.jot-form .jot-btn-cancel','click',function(event) {
			event.preventDefault();
			$.get(hist, function(data) {setData(data,id);});
		});
	}
})(jQuery);