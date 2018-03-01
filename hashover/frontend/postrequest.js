// Posts comments via AJAX (postrequest.js)
HashOver.prototype.postRequest = function (destination, form, button, callback, type, permalink, close, isReply, isEdit)
{
	close = close || null;

	var formElements = form.elements;
	var elementsLength = formElements.length;
	var queries = [];

	// Reference to this object
	var hashover = this;

	// AJAX response handler
	function commentHandler (json)
	{
		// Check if JSON includes a comment
		if (json.comment !== undefined) {
			// If so, execute callback function
			callback.apply (hashover, [ json, permalink, destination, isReply ]);

			// Execute callback function if one was provided
			if (close !== null) {
				close ();
			}

			// Get the comment element by its permalink
			var scrollToElement = hashover.elements.get (json.comment.permalink, true);

			// Scroll comment into view
			scrollToElement.scrollIntoView ({ behavior: 'smooth' });

			// And clear the comment form
			form.comment.value = '';
		} else {
			// If not, display the message return instead
			hashover.messages.show (json.message, type, permalink, (json.type === 'error'), isReply, isEdit);
			return false;
		}

		// Re-enable button on success
		setTimeout (function () {
			button.disabled = false;
		}, 1000);
	}

	// Sends a request to post a comment
	function sendRequest ()
	{
		hashover.ajax ('POST', form.action, queries, commentHandler, true);
	}

	// Get all form input names and values
	for (var i = 0; i < elementsLength; i++) {
		// Skip login/logout input
		if (formElements[i].name === 'login' || formElements[i].name === 'logout') {
			continue;
		}

		// Skip unchecked checkboxes
		if (formElements[i].type === 'checkbox' && formElements[i].checked !== true) {
			continue;
		}

		// Skip delete input
		if (formElements[i].name === 'delete') {
			continue;
		}

		// Add query to queries array
		queries.push (formElements[i].name + '=' + encodeURIComponent (formElements[i].value));
	}

	// Add AJAX query to queries array
	queries.push ('ajax=yes');

	// Check if autologin is enabled and user isn't admin
	if (this.setup['user-is-admin'] !== true
	    && this.setup['uses-auto-login'] !== false)
	{
		// If so, check if the user is logged in
		if (this.setup['user-is-logged-in'] !== true || isEdit === true) {
			// If not, send a login request
			var loginQueries = queries.concat (['login=Login']);

			// Send post comment request after login
			this.ajax ('POST', form.action, loginQueries, sendRequest, true);
		} else {
			// If so, send post comment request normally
			sendRequest ();
		}
	} else {
		// If not, send post comment request
		sendRequest ();
	}

	// Re-enable button after 20 seconds
	setTimeout (function () {
		button.disabled = false;
	}, 20000);

	return false;
};
