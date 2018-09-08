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
		hashover.elementExists (id, function (element) {
			element.scrollIntoView ({ behavior: 'smooth' });
		}, true);
	}

	// Callback for scrolling a comment into view on page load
	function scrollCommentIntoView ()
	{
		// Check if the comments are collapsed
		if (hashover.setup['collapses-comments'] !== false) {
			// Check if comment exists on the page
			var linkedHidden = hashover.elementExists (pageHash, function (comment) {
				// Check if the comment is visible
				if (hashover.classes.contains (comment, 'hashover-hidden') === false) {
					// If so, scroll to the comment
					scrollToElement (pageHash);
					return true;
				}

				return false;
			}, true);

			// Check if the linked comment is hidden
			if (linkedHidden === false) {
				// If not, scroll to comment after showing more comments
				hashover.showMoreComments (hashover.instance['more-link'], function () {
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
		if (hashover.getElement('message').textContent !== '') {
			hashover.showMessage ();
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
		this.elementExists ('comment-count', function (countElement) {
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
		if (this.ui.hasOwnProperty (element) === true) {
			this.ui[element] = this.strings.templatify (this.ui[element]);
		}
	}

	// Get the sort section
	var sortSection = this.getElement ('sort-section');

	// Get sort div element
	this.instance['sort-section'] = sortSection;

	// Display most popular comments
	this.elementExists ('top-comments', function (topComments) {
		if (hashover.instance.comments.popular[0] !== undefined) {
			hashover.parseAll (hashover.instance.comments.popular, topComments, false, true);
		}
	});

	// Initial comments
	var comments = this.instance.comments.primary;

	// Sort the initial comments if they weren't sorted on the backend
	if (this.setup['collapses-comments'] === false || this.setup['uses-ajax'] === false) {
		comments = this.sortComments (comments);
	}

	// Parse all of the initial comments
	this.parseAll (comments, sortSection, this.setup['collapses-comments']);

	// Create uncollapse UI hyperlink if enabled
	this.optionalMethod ('uncollapseInterfaceLink');

	// Create uncollapse comments hyperlink if enabled
	this.optionalMethod ('uncollapseCommentsLink');

	// Attach click event to formatting revealer hyperlink
	this.formattingOnclick ('main');

	// Get some various form elements
	var postButton = this.getElement ('post-button');
	var formElement = this.getElement ('form');

	// Set onclick and onsubmit event handlers
	this.duplicateProperties (postButton, formEvents, function () {
		return hashover.postComment (sortSection, formElement, postButton, hashover.AJAXPost);
	});

	// Check if login is enabled
	if (this.setup['allows-login'] !== false) {
		// If so, check if user is logged in
		if (this.setup['user-is-logged-in'] !== true) {
			// If so, get the login button
			var loginButton = this.getElement ('login-button');

			// Set onclick and onsubmit event handlers
			this.duplicateProperties (loginButton, formEvents, function () {
				return hashover.validateComment (true, formElement);
			});
		}
	}

	// Check if sort method drop down menu exists
	this.elementExists ('sort-select', function (sortSelect) {
		// If so, add change event handler
		sortSelect.onchange = function ()
		{
			// Check if the comments are collapsed
			if (hashover.setup['collapses-comments'] !== false) {
				// If so, get the select div
				var sortSelectDiv = hashover.getElement ('sort');

				// And uncollapse the comments before sorting
				hashover.showMoreComments (sortSelectDiv, function () {
					hashover.sortPrimary (sortSelect.value);
				}, false);
			} else {
				// If not, sort comments immediately
				hashover.sortPrimary (sortSelect.value);
			}
		};
	});

	// Check if reply or edit form request URL queries are set
	if (pageURL.match (/hashover-(reply|edit)=/)) {
		// If so, get the permalink from form request URL query
		var permalink = pageURL.replace (/.*?hashover-(edit|reply)=(c[0-9r\-pop]+).*?/, '$2');

		// Check if the reply form is requested
		if (pageURL.match ('hashover-reply=')) {
			// If so, define a callback to execute after showing comments
			var callback = function ()
			{
				// Open the reply form
				hashover.replyToComment (permalink);

				// Then scroll to reply form
				scrollToElement (pageHash);
			};

			// Check if the comments are collapsed
			if (hashover.setup['collapses-comments'] !== false) {
				// If so, show more comments before executing callback
				this.showMoreComments (this.instance['more-link'], callback);
			} else {
				// If not, execute callback directly
				callback ();
			}
		} else {
			// If not, indicate if the comment is popular
			var isPop = permalink.match ('-pop');

			// Define a callback to execute after showing comments
			var callback = function ()
			{
				// Decide appropriate array to get comment from
				var comments = hashover.instance.comments[isPop ? 'popular' : 'primary'];

				// Get the comment being edited
				var edit = hashover.permalinkComment (permalink, comments);

				// Open comment edit form
				hashover.editComment (edit);

				// Then scroll to edit form
				scrollToElement (pageHash);
			};

			// Check if the comments are collapsed
			if (hashover.setup['collapses-comments'] !== false) {
				// If so, show more comments before executing callback
				this.showMoreComments (this.instance['more-link'], callback);
			} else {
				// If not, execute callback directly
				callback ();
			}
		}
	}

	// Store end time
	this.execEnd = Date.now ();

	// Store execution time
	this.execTime = this.execEnd - this.execStart;

	// Log execution time and memory usage in JavaScript console
	if (window.console) {
		console.log (this.strings.sprintf (
			'HashOver: front-end %d ms, backend %d ms, %s', [
				this.execTime,
				this.statistics['execution-time'],
				this.statistics['script-memory']
			]
		));
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
