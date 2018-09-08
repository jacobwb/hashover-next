// Collection of element class related functions (classes.js)
HashOverConstructor.prototype.classes = new (function () {
	// If browser supports classList define wrapper functions
	if (document.documentElement.classList) {
		// classList.contains method
		this.contains = function (element, name) {
			return element.classList.contains (name);
		};

		// classList.add method
		this.add = function (element, name) {
			element.classList.add (name);
		};

		// classList.remove method
		this.remove = function (element, name) {
			element.classList.remove (name);
		};

		// And do nothing else
		return;
	}

	// Otherwise, define reasonable classList.contains fallback
	this.contains = function (element, name)
	{
		// Check if element exists with classes
		if (element && element.className) {
			// If so, compile regular expression for class
			var rx = new RegExp ('(^|\\s)' + name + '(\\s|$)');

			// Test class attribute for class name
			return rx.test (element.className);
		}

		// Otherwise, return false
		return false;
	};

	// Define reasonable classList.add fallback
	this.add = function (element, name)
	{
		// Append class if element doesn't already contain the class
		if (element && !this.contains (element, name)) {
			element.className += (element.className ? ' ' : '') + name;
		}
	};

	// Define reasonable classList.remove fallback
	this.remove = function (element, name)
	{
		// Check if element exists with classes
		if (element && element.className) {
			// If so, compile regular expression for class
			var rx = new RegExp ('(^|\\s)' + name + '(\\s|$)', 'g');

			// Remove class from class attribute
			element.className = element.className.replace (rx, '$2');
		}
	};
}) ();
