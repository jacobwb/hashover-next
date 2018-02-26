// Collection of HashOver message element related functions (messages.js)
HashOver.prototype.messages = {
	timeouts: {},

	// Gets a computed element style by property
	computeStyle: function (element, proterty, type)
	{
		var computedStyle;

		// IE, other
		if (window.getComputedStyle === undefined) {
			computedStyle = element.currentStyle[proterty];
		}

		// Mozilla Firefox, Google Chrome
		computedStyle = window.getComputedStyle (element, null);
		computedStyle = computedStyle.getPropertyValue (proterty);

		// Cast value to specified type
		switch (type) {
			case 'int': {
				computedStyle = computedStyle.replace (/px|em/, '');
				computedStyle = parseInt (computedStyle) || 0;
				break;
			}

			case 'float': {
				computedStyle = computedStyle.replace (/px|em/, '');
				computedStyle = parseFloat (computedStyle) || 0.0;
				break;
			}
		}

		return computedStyle;
	},

	// Gets the client height of a message element
	getHeight: function (element, setChild)
	{
		setChild = setChild || false;

		var firstChild = element.children[0];
		var maxHeight = 80;

		// If so, set max-height style to initial
		firstChild.style.maxHeight = 'initial';

		// Get various computed styles
		var borderTop = this.computeStyle (firstChild, 'border-top-width', 'int');
		var borderBottom = this.computeStyle (firstChild, 'border-bottom-width', 'int');
		var marginBottom = this.computeStyle (firstChild, 'margin-bottom', 'int');
		var border = borderTop + borderBottom;

		// Calculate its client height
		maxHeight = firstChild.clientHeight + border + marginBottom;

		// Set its max-height style as well if told to
		if (setChild === true) {
			firstChild.style.maxHeight = maxHeight + 'px';
		} else {
			firstChild.style.maxHeight = '';
		}

		return maxHeight;
	},

	// Open a message element
	open: function (element)
	{
		// Add classes to indicate message element is open
		this.parent.classes.remove (element, 'hashover-message-animated');
		this.parent.classes.add (element, 'hashover-message-open');

		var maxHeight = this.getHeight (element);
		var firstChild = element.children[0];

		// Reference to the parent object
		var parent = this.parent;

		// Remove class indicating message element is open
		this.parent.classes.remove (element, 'hashover-message-open');

		setTimeout (function () {
			// Add class to indicate message element is open
			parent.classes.add (element, 'hashover-message-open');
			parent.classes.add (element, 'hashover-message-animated');

			// Set max-height styles
			element.style.maxHeight = maxHeight + 'px';
			firstChild.style.maxHeight = maxHeight + 'px';

			// Set max-height style to initial after transition
			setTimeout (function () {
				element.style.maxHeight = 'initial';
				firstChild.style.maxHeight = 'initial';
			}, 150);
		}, 150);
	},

	// Close a message element
	close: function (element)
	{
		// Set max-height style to specific height before transition
		element.style.maxHeight = this.getHeight (element, true) + 'px';

		// Reference to the parent object
		var parent = this.parent;

		setTimeout (function () {
			// Remove max-height style from message elements
			element.children[0].style.maxHeight = '';
			element.style.maxHeight = '';

			// Remove classes indicating message element is open
			parent.classes.remove (element, 'hashover-message-open');
			parent.classes.remove (element, 'hashover-message-error');
		}, 150);
	},

	// Handle message element(s)
	show: function (messageText, type, permalink, error, isReply, isEdit)
	{
		type = type || 'main';
		permalink = permalink || '';
		error = error || false;
		isReply = isReply || false;
		isEdit = isEdit || false;

		var container;
		var message;

		// Reference to this object
		var messages = this;

		// Decide which message element to use
		if (isEdit === true) {
			// An edit form message
			container = this.parent.elements.get ('edit-message-container-' + permalink, true);
			message = this.parent.elements.get ('edit-message-' + permalink, true);
		} else {
			if (isReply !== true) {
				// The primary comment form message
				container = this.parent.elements.get ('message-container', true);
				message = this.parent.elements.get ('message', true);
			} else {
				// Of a reply form message
				container = this.parent.elements.get ('reply-message-container-' + permalink, true);
				message = this.parent.elements.get ('reply-message-' + permalink, true);
			}
		}

		if (messageText !== undefined && messageText !== '') {
			// Add message text to element
			message.textContent = messageText;

			// Add class to indicate message is an error if set
			if (error === true) {
				this.parent.classes.add (container, 'hashover-message-error');
			}
		}

		// Add class to indicate message element is open
		this.open (container);

		// Add the comment to message counts
		if (this.timeouts[permalink] === undefined) {
			this.timeouts[permalink] = {};
		}

		// Clear necessary timeout
		if (this.timeouts[permalink][type] !== undefined) {
			clearTimeout (this.timeouts[permalink][type]);
		}

		// Add timeout to close message element after 10 seconds
		this.timeouts[permalink][type] = setTimeout (function () {
			messages.close (container);
		}, 10000);
	}
};
