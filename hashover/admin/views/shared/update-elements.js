// Wait for the page HTML to be parsed
document.addEventListener ('DOMContentLoaded', function () {
	// Get message element
	var message = document.getElementById ('message');

	// Hide message after 5 seconds
	if (message !== null) {
		setTimeout (function () {
			message.className = message.className.replace ('success', 'hide');
		}, 1000 * 5);
	}
}, false);
