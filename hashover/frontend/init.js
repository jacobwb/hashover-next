// HashOver UI initialization process (init.js)
HashOver.prototype.init = function ()
{
	// Store start time
	this.execStart = Date.now ();

	// Reference to this object
	var hashover = this;

	// Get the main HashOver element
	var mainElement = this.getMainElement ();

	// Form events that get the same listeners
	var formEvents = [ 'onclick', 'onsubmit' ];

	// Current page URL without the hash
	var pageURL = window.location.href.split ('#')[0];

	// Current page URL hash
	var pageHash = window.location.hash.substr (1);

	// Scrolls to a specified element
	function scrollToElement (id)
	{
		hashover.elements.exists (id, function (element) {
			element.scrollIntoView ({ behavior: 'smooth' });
		}, false);
	}

	// Callback for scrolling a comment into view on page load
	function scrollCommentIntoView ()
	{
		// Check if the comments are collapsed
		if (hashover.setup['collapses-comments'] !== false) {
			// Check if comment exists on the page
			var linkedHidden = hashover.elements.exists (pageHash, function (comment) {
				// Check if the comment is visable
				if (hashover.classes.contains (comment, 'hashover-hidden') === false) {
					// If so, scroll to the comment
					scrollToElement (pageHash);
					return true;
				}

				return false;
			}, false);

			// Check if the linked comment is hidden
			if (linkedHidden === false) {
				// If not, show more comments
				hashover.showMoreComments (hashover.instance['more-link'], function () {
					// Then scroll to comment
					scrollToElement (pageHash);
				});
			}
		} else {
			// If not, scroll to comment normally
			scrollToElement (pageHash);
		}
	}

	// Callback for scrolling a comment into view on page load
	function prepareScroll ()
	{
		// Scroll the main HashOver element into view
		if (pageHash.match (/comments|hashover/)) {
			scrollToElement (pageHash);
		}

		// Check if we're scrolling to a comment
		if (pageHash.match (/hashover-c[0-9]+r*/)) {
			// If so, check if the user interface is collapsed
			if (hashover.setup['collapses-interface'] !== false) {
				// If so, scroll to it after uncollapsing the interface
				hashover.uncollapseInterface (scrollCommentIntoView);
			} else {
				// If not, scroll to the comment directly
				scrollCommentIntoView ();
			}
		}

		// Open the message element if there's a message
		if (hashover.elements.get ('message').textContent !== '') {
			hashover.messages.show ();
		}
	}

	// Page load event handler
	function onLoad ()
	{
		setTimeout (prepareScroll, 500);
	}

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

	// Get some various form elements
	var postButton = this.elements.get ('post-button');
	var formElement = this.elements.get ('form');

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

	// Display reply or edit form when the proper URL queries are set
	if (pageURL.match (/hashover-(reply|edit)=/)) {
		var permalink = pageURL.replace (/.*?hashover-(edit|reply)=(c[0-9r\-pop]+).*?/, '$2');

		if (!pageURL.match ('hashover-edit=')) {
			// Check if the comments are collapsed
			if (hashover.setup['collapses-comments'] !== false) {
				// If so, show more comments
				this.showMoreComments (this.instance['more-link'], function () {
					// Then display and scroll to reply form
					hashover.replyToComment (permalink);
					scrollToElement (pageHash);
				});
			} else {
				// If not, display and scroll to reply form
				this.replyToComment (permalink);
				scrollToElement (pageHash);
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
					scrollToElement (pageHash);
				});
			} else {
				// If not, display and scroll to edit form
				this.editComment (this.permalinks.getComment (permalink, comments));
				scrollToElement (pageHash);
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

	// Page onload compatibility wrapper
	if (window.addEventListener) {
		// Rest of the world
		window.addEventListener ('load', onLoad, false);
	} else {
		// IE ~8
		window.attachEvent ('onload', onLoad);
	}

	// Execute page load event handler manually
	onLoad ();
};
