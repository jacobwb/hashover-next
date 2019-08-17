// HashOver UI initialization process (init.js)
HashOver.prototype.init = function (id)
{
	// Reference to this object
	var hashover = this;

	// Execution start time
	var execStart = Date.now ();

	// Get the main HashOver element
	var mainElement = this.getMainElement (id);

	// Form events that get the same listeners
	var formEvents = [ 'onclick', 'onsubmit' ];

	// Current page URL without the hash
	var pageURL = window.location.href.split ('#')[0];

	// Current page URL hash
	var pageHash = window.location.hash.substring (1);

	// Scrolls to a specified element
	function scrollToElement (id)
	{
		hashover.elementExists (id, function (element) {
			element.scrollIntoView ({
				behavior: 'smooth',
				block: 'start',
				inline: 'start'
			});
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
				hashover.showInterface (scrollCommentIntoView);
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
	this.optionalMethod ('appendCSS', [ id ]);

	// Put number of comments into "hashover-comment-count" identified HTML element
	if (this.instance['total-count'] !== 0) {
		this.elementExists ('comment-count', function (countElement) {
			countElement.textContent = hashover.instance['total-count'];
		});

		// Append RSS feed if enabled
		this.optionalMethod ('appendRSS');
	}

	// Check if we can insert HTML adjacently
	if ('insertAdjacentHTML' in mainElement) {
		// If so, clear main element's contents
		mainElement.textContent = '';

		// And insert initial HTML adjacently
		mainElement.insertAdjacentHTML ('beforeend', this.instance['initial-html']);
	} else {
		// If not, replace main element's inner HTML with initial HTML
		mainElement.innerHTML = this.instance['initial-html'];
	}

	// Add main HashOver element to this HashOver instance
	this.instance['main-element'] = mainElement;

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
	this.htmlTime = this.parseAll (comments, sortSection, this.setup['collapses-comments']);

	// Create show interface hyperlink if enabled
	this.optionalMethod ('showInterfaceLink');

	// Create show more comments hyperlink if enabled
	this.optionalMethod ('showMoreLink');

	// Attach click event to formatting revealer hyperlink
	this.formattingOnclick ('main');

	// Get some various form elements
	var postButton = this.getElement ('post-button');
	var formElement = this.getElement ('form');

	// Set onclick and onsubmit event handlers
	this.duplicateProperties (postButton, formEvents, function () {
		return hashover.postComment (formElement, postButton, 'main');
	});

	// Check if login is enabled
	if (this.setup['allows-login'] !== false) {
		// If so, check if user is logged in
		if (this.setup['user-is-logged-in'] !== true) {
			// If so, get the login button
			var loginButton = this.getElement ('login-button');

			// Set onclick and onsubmit event handlers
			this.duplicateProperties (loginButton, formEvents, function () {
				return hashover.validateComment (formElement, 'main', null, true);
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

				// And show comments before sorting
				hashover.loadAllComments (sortSelectDiv, function () {
					// Collapse comment indicator based on current collapse state
					var collapse = !hashover.instance['showing-more'];

					// Sort primary comments using selected sort method
					hashover.sortPrimary (sortSelect.value, collapse);

					// And reappend show more comments hyperlink
					hashover.reappendMoreLink ();
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

		// Callback to execute after showing comments
		var callback = function ()
		{
			// Check if reply form is requested
			if (pageURL.match ('hashover-reply=')) {
				// If so, open reply form
				hashover.replyToComment (permalink);

				// And scroll to reply form
				scrollToElement (pageHash);
			} else {
				// If not, indicate if comment is popular
				var isPop = permalink.match ('-pop');

				// Decide appropriate array to get comment from
				var comments = hashover.instance.comments[isPop ? 'popular' : 'primary'];

				// Get comment being edited
				var edit = hashover.permalinkComment (permalink, comments);

				// Open and scroll to comment edit form
				hashover.editComment (edit, function () {
					scrollToElement (pageHash);
				});
			}
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

	// Execution end time
	this.execTime = Math.abs (Date.now () - execStart - this.htmlTime);

	// Log execution time in console
	console.log (this.strings.sprintf (
		'HashOver: front-end %d ms, HTML %d ms', [ this.execTime, this.htmlTime ]
	));

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
