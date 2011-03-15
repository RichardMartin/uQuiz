$(function() {
	$('.submit').click(function() {
		$(this).closest('form').submit();
	});

	$('.button').bind('touchstart', function() {
		$(this).addClass('pressed');
	});
	$('.button').bind('touchend', function() {
		$(this).removeClass('pressed');
	});
	$('.button').click(function() {
		$(this).removeClass('pressed');
	});
});
