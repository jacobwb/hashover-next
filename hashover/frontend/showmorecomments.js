// onClick event for more button (showmorecomments.js)
HashOver.prototype.showMoreComments = function (element, callback, append)
{
	// Reference to this object
	var hashover = this;

	// Do nothing if already showing all comments
	if (this.instance['showing-more'] === true) {
		// Execute callback function
		if (typeof (callback) === 'function') {
			callback ();
		}

		return false;
	}

	// Check if AJAX is enabled
	if (this.setup['uses-ajax'] === false) {
		// If so, hide the more hyperlink; displaying the comments
		this.hideMoreLink (callback);

		// And set all comments as shown
		this.instance['showing-more'] = true;

		return false;
	}

	// Otherwise, set request path
	var requestPath = this.setup['http-backend'] + '/load-comments.php';

	// Set URL queries
	var queries = [
		'url=' + encodeURIComponent (this.instance['page-url']),
		'title=' + encodeURIComponent (this.instance['page-title']),
		'thread=' + encodeURIComponent (this.instance['thread-name']),
		'start=' + encodeURIComponent (this.setup['collapse-limit']),
		'ajax=yes'
	];

	// Handle AJAX request return data
	this.ajax ('POST', requestPath, queries, function (json) {
		// Remove loading class from element
		hashover.classes.remove (element, 'hashover-loading');

		// Hide the more hyperlink
		hashover.hideMoreLink (function () {
			// Afterwards, store start time
			var execStart = Date.now ();

			// Replace initial comments
			hashover.instance.comments.primary = json.primary;

			// Check if we are appending the comments
			if (append !== false) {
				// If so, sort the comments
				var sorted = hashover.sortComments (json.primary);

				// And append the sorted comments
				hashover.appendComments (sorted);
			}

			// Execute callback function
			if (typeof (callback) === 'function') {
				callback ();
			}

			// Store execution time
			var execTime = Date.now () - execStart;

			// And log execution time and memory usage in JavaScript console
			if (window.console) {
				console.log (hashover.strings.sprintf (
					'HashOver: front-end %d ms, backend %d ms, %s', [
						execTime,
						json.statistics['execution-time'],
						json.statistics['script-memory']
					]
				));
			}
		});
	}, true);

	// Set class to indicate loading to element
	this.classes.add (element, 'hashover-loading');

	// And set all comments as shown
	this.instance['showing-more'] = true;

	return false;
};
