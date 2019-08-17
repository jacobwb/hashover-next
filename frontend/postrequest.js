// Posts comments via AJAX (postrequest.js)
HashOver.prototype.postRequest = function (form, button, type, permalink, callback)
{
	// Reference to this object
	var hashover = this;

	// Form inputs
	var inputs = form.elements;

	// Initial request queries
	var queries = [];

	// AJAX response handler
	function commentHandler (json)
	{
		// Check if JSON includes a comment
		if (json.comment !== undefined) {
			// If so, check if comment is anything other than an edit
			if (type !== 'edit') {
				// If so, execute primary comment post function
				hashover.AJAXPost.apply (hashover, [ json, permalink, type ]);
			} else {
				// If so, execute comment edit function
				hashover.AJAXEdit.apply (hashover, [ json, permalink ]);
			}

			// Execute callback function if one was provided
			if (typeof (callback) === 'function') {
				callback ();
			}

			// Get the comment element by its permalink
			var scrollToElement = hashover.getElement (json.comment.permalink);

			// Scroll comment into view
			scrollToElement.scrollIntoView ({
				behavior: 'smooth',
				block: 'start',
				inline: 'start'
			});

			// And clear the comment form
			form.comment.value = '';

			// Re-enable button on success
			setTimeout (function () {
				button.disabled = false;
			}, 1000);
		} else {
			// If not, display message returned instead
			hashover.showMessage (json.message, type, permalink, true);

			// And return false
			return false;
		}
	}

	// Sends a request to post a comment
	function sendRequest ()
	{
		// Create post comment request queries
		var postQueries = queries.concat ([
			button.name + '=' + encodeURIComponent (button.value)
		]);

		// Send request to post a comment
		hashover.ajax ('POST', form.action, postQueries, commentHandler, true);
	}

	// Get all form input names and values
	for (var i = 0, il = inputs.length; i < il; i++) {
		// Skip submit inputs
		if (inputs[i].type === 'submit') {
			continue;
		}

		// Skip unchecked checkboxes
		if (inputs[i].type === 'checkbox' && inputs[i].checked !== true) {
			continue;
		}

		// Otherwise, get encoded input value
		var value = encodeURIComponent (inputs[i].value);

		// Add query to queries array
		queries.push (inputs[i].name + '=' + value);
	}

	// Add final queries
	queries = queries.concat ([
		// Add current client time
		'time=' + HashOver.getClientTime (),

		// Add AJAX indicator
		'ajax=yes'
	]);

	// Check if autologin is enabled and user isn't admin
	if (this.setup['user-is-admin'] !== true
	    && this.setup['uses-auto-login'] !== false)
	{
		// If so, check if the user is logged in
		if (this.setup['user-is-logged-in'] !== true) {
			// If not, create login request queries
			var loginQueries = queries.concat ([ 'login=Login' ]);

			// Send post comment request after login request
			this.ajax ('POST', form.action, loginQueries, sendRequest, true);
		} else {
			// If so, send post comment request normally
			sendRequest ();
		}
	} else {
		// If not, send post comment request
		sendRequest ();
	}

	// Re-enable button after 10 seconds
	setTimeout (function () {
		button.disabled = false;
	}, 10000);

	// And return false
	return false;
};
