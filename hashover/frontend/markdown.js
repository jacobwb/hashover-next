// Parses a string as markdown (markdown.js)
HashOverConstructor.prototype.markdown = {
	blockCodeRegex: /```([\s\S]+?)```/g,
	inlineCodeRegex: /(^|[^a-z0-9`])`([^`]+?[\s\S]+?)`([^a-z0-9`]|$)/ig,
	blockCodeMarker: /CODE_BLOCK\[([0-9]+)\]/g,
	inlineCodeMarker: /CODE_INLINE\[([0-9]+)\]/g,

	// Array for inline code and code block markers
	codeMarkers: {
		block: { marks: [], count: 0 },
		inline: { marks: [], count: 0 }
	},

	// Markdown patterns to search for
	markdownSearch: [
		/\*\*([^ *])([\s\S]+?)([^ *])\*\*/g,
		/\*([^ *])([\s\S]+?)([^ *])\*/g,
		/(^|\W)_([^_]+?[\s\S]+?)_(\W|$)/g,
		/__([^ _])([\s\S]+?)([^ _])__/g,
		/~~([^ ~])([\s\S]+?)([^ ~])~~/g
	],

	// HTML replacements for markdown patterns
	markdownReplace: [
		'<strong>$1$2$3</strong>',
		'<em>$1$2$3</em>',
		'$1<u>$2</u>$3',
		'<u>$1$2$3</u>',
		'<s>$1$2$3</s>'
	],

	// Replaces markdown for inline code with a marker
	codeReplace: function (fullTag, first, second, third, display)
	{
		var markName = 'CODE_' + display.toUpperCase ();
		var markCount = this.codeMarkers[display].count++;

		if (display !== 'block') {
			var codeMarker = first + markName + '[' + markCount + ']' + third;
			this.codeMarkers[display].marks[markCount] = this.parent.EOLTrim (second);
		} else {
			var codeMarker = markName + '[' + markCount + ']';
			this.codeMarkers[display].marks[markCount] = this.parent.EOLTrim (first);
		}

		return codeMarker;
	},

	parse: function (string)
	{
		// Reference to this object
		var markdown = this;

		// Reset marker arrays
		this.codeMarkers = {
			block: { marks: [], count: 0 },
			inline: { marks: [], count: 0 }
		};

		// Replace code blocks with markers
		string = string.replace (this.blockCodeRegex, function (fullTag, first, second, third) {
			return markdown.codeReplace (fullTag, first, second, third, 'block');
		});

		// Break string into paragraphs
		var paragraphs = string.split (this.parent.regex.paragraphs);

		// Run through each paragraph replacing markdown patterns
		for (var i = 0, il = paragraphs.length; i < il; i++) {
			// Replace code tags with marker text
			paragraphs[i] = paragraphs[i].replace (this.inlineCodeRegex, function (fullTag, first, second, third) {
				return markdown.codeReplace (fullTag, first, second, third, 'inline');
			});

			// Perform each markdown regular expression on the current paragraph
			for (var r = 0, rl = this.markdownSearch.length; r < rl; r++) {
				// Replace markdown patterns
				paragraphs[i] = paragraphs[i].replace (this.markdownSearch[r], this.markdownReplace[r]);
			}

			// Return the original markdown code with HTML replacement
			paragraphs[i] = paragraphs[i].replace (this.inlineCodeMarker, function (marker, number) {
				return '<code class="hashover-inline">' + markdown.codeMarkers.inline.marks[number] + '</code>';
			});
		}

		// Join paragraphs
		string = paragraphs.join (this.parent.setup['server-eol'] + this.parent.setup['server-eol']);

		// Replace code block markers with original markdown code
		string = string.replace (this.blockCodeMarker, function (marker, number) {
			return '<code>' + markdown.codeMarkers.block.marks[number] + '</code>';
		});

		return string;
	}
};
