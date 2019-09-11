// Loads all comments and executes a callback to handle them (showmorecomments.js)
HashOver.prototype.loadAllComments = function (element, callback)
{
	// Reference to this object
	var hashover = this;

	// Just execute callback  if all comments are already loaded
	if (this.instance['comments-loaded'] === true) {
		return callback ();
	}

	// Otherwise, set request path
	var requestPath = this.setup['http-backend'] + '/load-comments.php';

	// Set URL queries
	var queries = this.queries.concat ([
		// Add current client time
		'time=' + HashOver.getClientTime (),

		// Add AJAX indicator
		'ajax=yes'
	]);

	// Set class on element to indicate loading
	this.classes.add (element, 'hashover-loading');

	// Handle AJAX request return data
	this.ajax ('POST', requestPath, queries, function (json) {
		// Remove loading class from element
		hashover.classes.remove (element, 'hashover-loading');

		// Replace initial comments
		hashover.instance.comments.primary = json.primary;

		// And log backend execution time and memory usage in console
		console.log (hashover.strings.sprintf (
			'HashOver: backend %d ms, %s', [
				json.statistics['execution-time'],
				json.statistics['script-memory']
			]
		));

		// Execute callback
		callback ();
	}, true);

	// Set all comments as loaded
	this.instance['comments-loaded'] = true;
};

// Click event handler for show more comments button (showmorecomments.js)
HashOver.prototype.showMoreComments = function (element, callback)
{
	// Reference to this object
	var hashover = this;

	// Check if all comments are already shown
	if (this.instance['showing-more'] === true) {
		// If so, execute callback function
		if (typeof (callback) === 'function') {
			callback ();
		}

		// And prevent default event
		return false;
	}

	// Check if AJAX is enabled
	if (this.setup['uses-ajax'] === false) {
		// If so, hide the more hyperlink; displaying the comments
		this.hideMoreLink (callback);

		// Set all comments as shown
		this.instance['showing-more'] = true;

		// And return false to prevent default event
		return false;
	}

	// Otherwise, load all comments
	this.loadAllComments (element, function () {
		// Afterwards, hide show more comments hyperlink
		hashover.hideMoreLink (function () {
			// Afterwards, store start time
			var execStart = Date.now ();

			// Get primary comments
			var primary = hashover.instance.comments.primary;

			// Attempt to get sort method drop down menu
			var sortSelect = hashover.getElement ('sort-select');

			// Check if sort method drop down menu exists
			if (sortSelect !== null) {
				// If so, sort primary comments using select method
				var sorted = hashover.sortComments (primary, sortSelect.value);
			} else {
				// If not, sort primary comment using default method
				var sorted = hashover.sortComments (primary);
			}

			// Append sorted comments
			var htmlTime = hashover.appendComments (sorted);

			// Execute callback function
			if (typeof (callback) === 'function') {
				callback ();
			}

			// Store execution time
			var execTime = Math.abs (Date.now () - execStart - htmlTime);

			// And log execution time in console
			console.log (hashover.strings.sprintf (
				'HashOver: front-end %d ms, HTML %d ms', [ execTime, htmlTime ]
			));
		});
	});

	// Set all comments as shown
	this.instance['showing-more'] = true;

	// And prevent default event
	return false;
};
