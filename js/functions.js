$(document).ready(function () {

	$('a.confirm').on('click', function () {
		return confirm('Are you sure?')
	});

	$('.scroll_to').click(function () {
		var scroll_to = $(this).data('scroll_to');

		scroll_to(scroll_to);
	});


	if ($(window).width() < 992) {
		scroll_to('.today');
	}

});

function scroll_to(scroll_to) {
	if ($(scroll_to).length) {
		$('html, body').animate({
			scrollTop: $(scroll_to).offset().top - 55
		});
	}

	return false;
}