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
			// Return the original specifier if there isn't an item for it
			if (args[count] === undefined) {
				return match;
			}

			// Switch through each specific type
			switch (type) {
				// Single characters
				case 'c': {
					// Use only the first character
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

	templatify: function (text)
	{
		var template = text.split (this.curlyBraces);
		var indexes = {};

		for (var i = 0, il = template.length, curly, name; i < il; i++) {
			curly = template[i].match (this.curlyNames);

			if (curly && curly.length > 0) {
				name = curly[1];
				template[i] = '';

				if (indexes[name] !== undefined) {
					indexes[name].push (i);
				} else {
					indexes[name] = [ i ];
				}
			}
		}

		return {
			text: template,
			indexes: indexes
		}
	},

	// Parses an HTML template
	parseTemplate: function (template, data)
	{
		if (!template || !template.indexes || !template.text) {
			return;
		}

		var textClone = template.text.slice ();

		for (var name in data) {
			if (template.indexes[name] === undefined) {
				continue;
			}

			for (var i = 0, il = template.indexes[name].length, index; i < il; i++) {
				index = template.indexes[name][i];
				textClone[index] = data[name];
			}
		}

		return textClone.join ('');
	}
};
