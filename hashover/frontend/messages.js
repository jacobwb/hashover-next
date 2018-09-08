// Collection of HashOver message element related functions (messages.js)
HashOver.prototype.messages = {
	timeouts: {},

	// Gets a computed element style by property
	computeStyle: function (element, proterty, type)
	{
		// Check for modern browser support (Mozilla Firefox, Google Chrome)
		if (window.getComputedStyle !== undefined) {
			// If found, get the computed styles for the element
			var computedStyle = window.getComputedStyle (element, null);

			// And get the specific property
			computedStyle = computedStyle.getPropertyValue (proterty);
		} else {
			// Otherwise, assume we're in IE
			var computedStyle = element.currentStyle[proterty];
		}

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
		// Get first child of message element
		var firstChild = element.children[0];

		// Set max-height style to initial
		firstChild.style.maxHeight = 'initial';

		// Get various computed styles
		var borderTop = this.computeStyle (firstChild, 'border-top-width', 'int');
		var borderBottom = this.computeStyle (firstChild, 'border-bottom-width', 'int');
		var marginBottom = this.computeStyle (firstChild, 'margin-bottom', 'int');
		var border = borderTop + borderBottom;

		// Calculate its client height
		var maxHeight = firstChild.clientHeight + border + marginBottom;

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
		// Reference to the parent object
		var parent = this.parent;

		// Add classes to indicate message element is open
		this.parent.classes.remove (element, 'hashover-message-animated');
		this.parent.classes.add (element, 'hashover-message-open');

		// Get height of element
		var maxHeight = this.getHeight (element);

		// Get first child of message element
		var firstChild = element.children[0];

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
		// Reference to the parent object
		var parent = this.parent;

		// Set max-height style to specific height before transition
		element.style.maxHeight = this.getHeight (element, true) + 'px';

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

		// Reference to this object
		var messages = this;

		// Check if message is in an edit form
		if (isEdit === true) {
			// If so, get message from edit form by permalink
			var container = this.parent.elements.get ('edit-message-container-' + permalink);
			var message = this.parent.elements.get ('edit-message-' + permalink);
		} else {
			// If not, check if message is anything other than a reply
			if (isReply !== true) {
				// If so, get primary message element
				var container = this.parent.elements.get ('message-container');
				var message = this.parent.elements.get ('message');
			} else {
				// If not, get message from reply form by permalink
				var container = this.parent.elements.get ('reply-message-container-' + permalink);
				var message = this.parent.elements.get ('reply-message-' + permalink);
			}
		}

		// Check if the message isn't empty
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

		// Instantiated permalink as timeout key
		var key = permalink;

		// Add the comment to message counts
		if (this.timeouts[key] === undefined) {
			this.timeouts[key] = {};
		}

		// Clear necessary timeout
		if (this.timeouts[key][type] !== undefined) {
			clearTimeout (this.timeouts[key][type]);
		}

		// Add timeout to close message element after 10 seconds
		this.timeouts[key][type] = setTimeout (function () {
			messages.close (container);
		}, 10000);
	}
};
