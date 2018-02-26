// Collection of element class related functions (classes.js)
HashOverConstructor.prototype.classes = new (function () {
	// Check whether browser has classList support
	if (document.documentElement.classList) {
		// If so, wrap relevant functions
		// classList.contains () method
		this.contains = function (element, className)
		{
			return element.classList.contains (className);
		};

		// classList.add () method
		this.add = function (element, className)
		{
			element.classList.add (className);
		};

		// classList.remove () method
		this.remove = function (element, className)
		{
			element.classList.remove (className);
		};
	} else {
		// If not, define fallback functions
		// classList.contains () method
		this.contains = function (element, className)
		{
			if (!element || !element.className) {
				return false;
			}

			var regex = new RegExp ('(^|\\s)' + className + '(\\s|$)');
			return regex.test (element.className);
		};

		// classList.add () method
		this.add = function (element, className)
		{
			if (!element) {
				return false;
			}

			if (!this.contains (element, className)) {
				element.className += (element.className ? ' ' : '') + className;
			}
		};

		// classList.remove () method
		this.remove = function (element, className)
		{
			if (!element || !element.className) {
				return false;
			}

			var regex = new RegExp ('(^|\\s)' + className + '(\\s|$)', 'g');
			element.className = element.className.replace (regex, '$2');
		};
	}
}) ();
