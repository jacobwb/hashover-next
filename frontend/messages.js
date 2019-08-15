// Initial timeouts (messages.js)
HashOver.prototype.messageTimeouts = {};

// Gets a computed element style by property (messages.js)
HashOver.prototype.computeStyle = function (element, property, type)
{
	// Check for modern browser support (Mozilla Firefox, Google Chrome)
	if (window.getComputedStyle !== undefined) {
		// If found, get the computed styles for the element
		var computedStyle = window.getComputedStyle (element, null);

		// And get the specific property
		computedStyle = computedStyle.getPropertyValue (property);
	} else {
		// Otherwise, assume we're in IE
		var computedStyle = element.currentStyle[property];
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
};

// Gets the client height of a message element (messages.js)
HashOver.prototype.getHeight = function (element, setChild)
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
};

// Open a message element (messages.js)
HashOver.prototype.openMessage = function (element)
{
	// Reference to this object
	var hashover = this;

	// Add classes to indicate message element is open
	this.classes.remove (element, 'hashover-message-animated');
	this.classes.add (element, 'hashover-message-open');

	// Get height of element
	var maxHeight = this.getHeight (element);

	// Get first child of message element
	var firstChild = element.children[0];

	// Remove class indicating message element is open
	this.classes.remove (element, 'hashover-message-open');

	setTimeout (function () {
		// Add class to indicate message element is open
		hashover.classes.add (element, 'hashover-message-open');
		hashover.classes.add (element, 'hashover-message-animated');

		// Set max-height styles
		element.style.maxHeight = maxHeight + 'px';
		firstChild.style.maxHeight = maxHeight + 'px';

		// Set max-height style to initial after transition
		setTimeout (function () {
			element.style.maxHeight = 'initial';
			firstChild.style.maxHeight = 'initial';
		}, 150);
	}, 150);
};

// Close a message element (messages.js)
HashOver.prototype.closeMessage = function (element)
{
	// Reference to this object
	var hashover = this;

	// Set max-height style to specific height before transition
	element.style.maxHeight = this.getHeight (element, true) + 'px';

	setTimeout (function () {
		// Remove max-height style from message elements
		element.children[0].style.maxHeight = '';
		element.style.maxHeight = '';

		// Remove classes indicating message element is open
		hashover.classes.remove (element, 'hashover-message-open');
		hashover.classes.remove (element, 'hashover-message-error');
	}, 150);
};

// Handle message element(s) (messages.js)
HashOver.prototype.showMessage = function (messageText, type, permalink, error)
{
	// Reference to this object
	var hashover = this;

	// Check if message is in an edit form
	if (type === 'edit') {
		// If so, get message from edit form by permalink
		var container = this.getElement ('edit-message-container-' + permalink);
		var message = this.getElement ('edit-message-' + permalink);
	} else {
		// If not, check if message is anything other than a reply
		if (type !== 'reply') {
			// If so, get primary message element
			var container = this.getElement ('message-container');
			var message = this.getElement ('message');
		} else {
			// If not, get message from reply form by permalink
			var container = this.getElement ('reply-message-container-' + permalink);
			var message = this.getElement ('reply-message-' + permalink);
		}
	}

	// Check if the message isn't empty
	if (messageText !== undefined && messageText !== '') {
		// Add message text to element
		message.textContent = messageText;

		// Add class to indicate message is an error if set
		if (error === true) {
			this.classes.add (container, 'hashover-message-error');
		}
	}

	// Add class to indicate message element is open
	this.openMessage (container);

	// Instantiated permalink as timeout key
	var key = this.prefix (permalink);

	// Add the comment to message counts
	if (this.messageTimeouts[key] === undefined) {
		this.messageTimeouts[key] = {};
	}

	// Clear necessary timeout
	if (this.messageTimeouts[key][type] !== undefined) {
		clearTimeout (this.messageTimeouts[key][type]);
	}

	// Add timeout to close message element after 10 seconds
	this.messageTimeouts[key][type] = setTimeout (function () {
		hashover.closeMessage (container);
	}, 10000);
};
