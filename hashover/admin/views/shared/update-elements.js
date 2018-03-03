// Remove "Saved" from page title
setTimeout (function () {
	var title = document.getElementById ('title');
	var regex = / - Saved!/i;

	if (regex.test (title.textContent) === true) {
		title.textContent = title.textContent.replace (regex, '');
	}

	if (regex.test (document.title) === true) {
		document.title = document.title.replace (regex, '');
	}
}, 1000 * 5);
