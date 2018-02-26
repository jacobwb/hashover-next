// HashOver UI initialization process (init.js)
HashOver.prototype.init = function ()
{
	// Store start time
	this.execStart = Date.now ();

	var hashover		= this;
	var URLParts		= window.location.href.split ('#');
	var URLHref		= URLParts[0];
	var URLHash		= URLParts[1] || '';
	var formEvents		= ['onclick', 'onsubmit'];

	// Append theme CSS if enabled
	this.optionalMethod ('appendCSS');

	// Put number of comments into "hashover-comment-count" identified HTML element
	if (this.instance['total-count'] !== 0) {
		this.elements.exists ('comment-count', function (countElement) {
			countElement.textContent = hashover.instance['total-count'];
		});

		// Append RSS feed if enabled
		this.optionalMethod ('appendRSS');
	}

	// Get the main HashOver element
	var mainElement = this.getMainElement ();

	// Add initial HTML to page
	if ('insertAdjacentHTML' in mainElement) {
		mainElement.textContent = '';
		mainElement.insertAdjacentHTML ('beforeend', this.instance['initial-html']);
	} else {
		mainElement.innerHTML = this.instance['initial-html'];
	}

	// Add main HashOver element to this HashOver instance
	this.instance['main-element'] = mainElement;

	// Templatify UI HTML strings
	for (var element in this.ui) {
		this.ui[element] = this.strings.templatify (this.ui[element]);
	}

	// Get sort div element
	this.instance['sort-section'] = this.elements.get ('sort-section');

	// Get primary form element
	var formElement = this.elements.get ('form');

	// Display most popular comments
	this.elements.exists ('top-comments', function (topComments) {
		if (hashover.instance.comments.popular[0] !== undefined) {
			hashover.parseAll (hashover.instance.comments.popular, topComments, false, true);
		}
	});

	// Add initial event handlers
	this.parseAll (this.instance.comments.primary, this.instance['sort-section'], this.setup['collapses-comments']);

	// Create uncollapse UI hyperlink if enabled
	this.optionalMethod ('uncollapseInterfaceLink');

	// Create uncollapse comments hyperlink if enabled
	this.optionalMethod ('uncollapseCommentsLink');

	// Attach click event to formatting revealer hyperlink
	this.formattingOnclick ('main');

	// Attach event listeners to "Post Comment" button
	var postButton = this.elements.get ('post-button');

	// Set onclick and onsubmit event handlers
	this.elements.duplicateProperties (postButton, formEvents, function () {
		return hashover.postComment (hashover.instance['sort-section'], formElement, postButton, hashover.AJAXPost);
	});

	// Check if login is enabled
	if (this.setup['allows-login'] !== false) {
		// Attach event listeners to "Login" button
		if (this.setup['user-is-logged-in'] !== true) {
			var loginButton = this.elements.get ('login-button');

			// Set onclick and onsubmit event handlers
			this.elements.duplicateProperties (loginButton, formEvents, function () {
				return hashover.validateComment (true, formElement);
			});
		}
	}

	// Five method sort
	this.elements.exists ('sort-select', function (sortSelect) {
		sortSelect.onchange = function ()
		{
			// Check if the comments are collapsed
			if (hashover.setup['collapses-comments'] !== false) {
				// If so, get the select div
				var sortSelectDiv = hashover.elements.get ('sort');

				// And uncollapse the comments before sorting
				hashover.showMoreComments (sortSelectDiv, function () {
					hashover.instance['sort-section'].textContent = '';
					hashover.sortComments (sortSelect.value);
				});
			} else {
				// If not, sort the comments normally
				hashover.instance['sort-section'].textContent = '';
				hashover.sortComments (sortSelect.value);
			}
		};
	});

	// Scrolls to a specified element
	var scrollToElement = function (id)
	{
		hashover.elements.exists (id, function (element) {
			element.scrollIntoView ({ behavior: 'smooth' });
		}, false);
	};

	// Display reply or edit form when the proper URL queries are set
	if (URLHref.match (/hashover-(reply|edit)=/)) {
		var permalink = URLHref.replace (/.*?hashover-(edit|reply)=(c[0-9r\-pop]+).*?/, '$2');

		if (!URLHref.match ('hashover-edit=')) {
			// Check if the comments are collapsed
			if (hashover.setup['collapses-comments'] !== false) {
				// If so, show more comments
				this.showMoreComments (this.instance['more-link'], function () {
					// Then display and scroll to reply form
					hashover.replyToComment (permalink);
					scrollToElement (URLHash);
				});
			} else {
				// If not, display and scroll to reply form
				this.replyToComment (permalink);
				scrollToElement (URLHash);
			}
		} else {
			var isPop = permalink.match ('-pop');
			var comments = (isPop) ? this.instance.comments.popular : this.instance.comments.primary;

			// Check if the comments are collapsed
			if (hashover.setup['collapses-comments'] !== false) {
				// If so, show more comments
				this.showMoreComments (this.instance['more-link'], function () {
					// Then display and scroll to edit form
					hashover.editComment (hashover.permalinks.getComment (permalink, comments));
					scrollToElement (URLHash);
				});
			} else {
				// If not, display and scroll to edit form
				this.editComment (this.permalinks.getComment (permalink, comments));
				scrollToElement (URLHash);
			}
		}
	}

	// Store end time
	this.execEnd = Date.now ();

	// Store execution time
	this.execTime = this.execEnd - this.execStart;

	// Log execution time and memory usage in JavaScript console
	if (window.console) {
		console.log (this.strings.sprintf ('HashOver: front-end %d ms, backend %d ms, %s', [
			this.execTime, this.statistics['execution-time'], this.statistics['script-memory']
		]));
	}

	// Callback for scrolling a comment into view on page load
	var scroller = function ()
	{
		setTimeout (function () {
			// Workaround for stupid Chrome bug
			if (URLHash.match (/comments|hashover/)) {
				scrollToElement (URLHash);
			}

			// Jump to linked comment
			if (URLHash.match (/hashover-c[0-9]+r*/)) {
				// Check if the comments are collapsed
				if (hashover.setup['collapses-comments'] !== false) {
					// Check if comment exists on the page
					var linkedHidden = hashover.elements.exists (URLHash, function (comment) {
						// Check if the comment is visable
						if (hashover.classes.contains (comment, 'hashover-hidden') === false) {
							// If so, scroll to the comment
							scrollToElement (URLHash);
							return true;
						}

						return false;
					}, false);

					// Check if the linked comment is hidden
					if (linkedHidden === false) {
						// If not, show more comments
						hashover.showMoreComments (hashover.instance['more-link'], function () {
							// Then scroll to comment
							scrollToElement (URLHash);
						});
					}
				} else {
					// If not, scroll to comment normally
					scrollToElement (URLHash);
				}
			}

			// Open the message element if there's a message
			if (hashover.elements.get ('message').textContent !== '') {
				hashover.messages.show ();
			}
		}, 500);
	};

	// Page onload compatibility wrapper
	if (window.addEventListener) {
		// Rest of the world
		window.addEventListener ('load', scroller, false);
	} else {
		// IE ~8
		window.attachEvent ('onload', scroller);
	}

	// Execute scroller manually
	scroller ();
};
