// onClick event for more button (showmorecomments.js)
HashOver.prototype.showMoreComments = function (element, finishedCallback)
{
	finishedCallback = finishedCallback || null;

	// Reference to this object
	var hashover = this;

	// Do nothing if already showing all comments
	if (this.instance['showing-more'] === true) {
		// Execute callback function
		if (finishedCallback !== null) {
			finishedCallback ();
		}

		return false;
	}

	// Check if AJAX is enabled
	if (this.setup['uses-ajax'] !== false) {
		// If so, set request path
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
			// Store start time
			var execStart = Date.now ();

			// Display the comments
			hashover.appendComments (json.primary);

			// Remove loading class from element
			hashover.classes.remove (element, 'hashover-loading');

			// Hide the more hyperlink and display the comments
			hashover.hideMoreLink (finishedCallback);

			// Store execution time
			var execTime = Date.now () - execStart;

			// Log execution time and memory usage in JavaScript console
			if (window.console) {
				console.log (hashover.strings.sprintf ('HashOver: front-end %d ms, backend %d ms, %s', [
					execTime, json.statistics['execution-time'], json.statistics['script-memory']
				]));
			}
		}, true);

		// And set class to indicate loading to element
		this.classes.add (element, 'hashover-loading');
	} else {
		// If not, hide the more hyperlink and display the comments
		this.hideMoreLink (finishedCallback);
	}

	// Set all comments as shown
	this.instance['showing-more'] = true;

	return false;
};
