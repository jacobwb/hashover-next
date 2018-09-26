// Collection of convenient string related functions (strings.js)
HashOverConstructor.prototype.strings = {
	// sprintf specifiers regular expression
	specifiers: /%([cdfs])/g,

	// Curly-brace variable regular expression
	curlyBraces: /(\{.+?\})/g,

	// Curly-brace variable name regular expression
	curlyNames: /\{(.+?)\}/,

	// Simplistic JavaScript port of sprintf function in C
	sprintf: function (string, args)
	{
		var string = string || '';
		var args = args || [];
		var count = 0;

		// Replace specifiers with array items
		return string.replace (this.specifiers, function (match, type)
		{
			// Return original specifier if there isn't an item for it
			if (args[count] === undefined) {
				return match;
			}

			// Switch through each specific type
			switch (type) {
				// Single characters
				case 'c': {
					// Use only first character
					return args[count++][0];
				}

				// Integer numbers
				case 'd': {
					// Parse item as integer
					return parseInt (args[count++]);
				}

				// Floating point numbers
				case 'f': {
					// Parse item as float
					return parseFloat (args[count++]);
				}

				// Strings
				case 's': {
					// Use string as-is
					return args[count++];
				}
			}
		});
	},

	// Converts a string containing {curly} variables into an array
	templatifier: function (text)
	{
		// Split string by curly variables
		var template = text.split (this.curlyBraces);

		// Initial variable indexes
		var indexes = {};

		// Run through template
		for (var i = 0, il = template.length; i < il; i++) {
			// Get curly variable names
			var curly = template[i].match (this.curlyNames);

			// Check if any curly variables exist
			if (curly !== null && curly[1] !== undefined) {
				// If so, store the name
				var name = curly[1];

				// Check if variable was previously encountered
				if (indexes[name] !== undefined) {
					// If so, add index to existing indexes
					indexes[name].push (i);
				} else {
					// If not, create indexes
					indexes[name] = [ i ];
				}

				// And remove curly variable from template
				template[i] = '';
			}
		}

		// Return template and indexes
		return {
			template: template,
			indexes: indexes
		}
	},

	// Templatify UI HTML from backend
	templatify: function (ui)
	{
		// Initial template
		var template = {};

		// Templatify each UI HTML string
		for (var name in ui) {
			if (ui.hasOwnProperty (name) === true) {
				template[name] = this.templatifier (ui[name]);
			}
		}

		return template;
	},

	// Parses an HTML template
	parseTemplate: function (template, data)
	{
		// Clone template
		var textClone = template.template.slice ();

		// Run through template data
		for (var name in data) {
			// Store indexes
			var indexes = template.indexes[name];

			// Do nothing if no indexes exist for data
			if (indexes === undefined) {
				continue;
			}

			// Otherwise, add data at each index of template
			for (var i = 0, il = indexes.length; i < il; i++) {
				textClone[(indexes[i])] = data[name];
			}
		}

		// Merge template clone to string
		var text = textClone.join ('');

		return text;
	}
};
