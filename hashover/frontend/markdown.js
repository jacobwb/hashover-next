// Add markdown regular expressions (markdown.js)
HashOverConstructor.prototype.rx.md = {
	// Matches a markdown code block
	blockCode: /```([\s\S]+?)```/g,

	// Matches markdown inline code
	inlineCode: /(^|[^a-z0-9`])`((?!`)[\s\S]+?)`([^a-z0-9`]|$)/ig,

	// Matches temporary code block placeholder
	blockMarker: /CODE_BLOCK\[([0-9]+)\]/g,

	// Matches temporary inline code placeholder
	inlineMarker: /CODE_INLINE\[([0-9]+)\]/g,

	// Markdown patterns to search for
	search: [
		// Matches **bold** text
		/\*\*([^ *])([\s\S]+?)([^ *])\*\*/g,

		// Matches *italic* text
		/\*([^ *])([\s\S]+?)([^ *])\*/g,

		// Matches _underlined_ text
		/(^|\W)_((?!_)[\s\S]+?)_(\W|$)/g,

		// Matches forced __underlined__ text
		/__([^ _])([\s\S]+?)([^ _])__/g,

		// Matches ~~strikethrough~~ text
		/~~([^ ~])([\s\S]+?)([^ ~])~~/g
	],

	// HTML replacements for markdown patterns
	replace: [
		'<strong>$1$2$3</strong>',
		'<em>$1$2$3</em>',
		'$1<u>$2</u>$3',
		'<u>$1$2$3</u>',
		'<s>$1$2$3</s>'
	]
};

// Parses markdown code (markdown.js)
HashOverConstructor.prototype.parseMarkdown = function (string)
{
	// Reference to this object
	var hashover = this;

	// Initial marker arrays
	var block = { marks: [], count: 0 };
	var inline = { marks: [], count: 0 };

	// Replaces inline code with markers
	var inlineReplacer = function (m, first, code, third)
	{
		// Increase inline code count
		var markCount = inline.count++;

		// Inline code marker
		var marker = 'CODE_INLINE[' + markCount + ']';

		// Add inline code to marker array
		inline.marks[markCount] = hashover.EOLTrim (code);

		// And return first match, inline marker, and third match
		return first + marker + third;
	};

	// Replace code blocks with markers
	string = string.replace (this.rx.md.blockCode, function (m, code) {
		// Increase block code count
		var markCount = block.count++;

		// Add block code to marker array
		block.marks[markCount] = hashover.EOLTrim (code);

		// And return block marker
		return 'CODE_BLOCK[' + markCount + ']';
	});

	// Break string into paragraphs
	var ps = string.split (this.rx.paragraphs);

	// Run through each paragraph replacing markdown patterns
	for (var i = 0, il = ps.length; i < il; i++) {
		// Replace code tags with marker text
		ps[i] = ps[i].replace (this.rx.md.inlineCode, inlineReplacer);

		// Perform each markdown regular expression on the current paragraph
		for (var r = 0, rl = this.rx.md.search.length; r < rl; r++) {
			ps[i] = ps[i].replace (this.rx.md.search[r], this.rx.md.replace[r]);
		}

		// Return the original markdown code with HTML replacement
		ps[i] = ps[i].replace (this.rx.md.inlineMarker, function (marker, number) {
			return '<code class="hashover-inline">' + inline.marks[number] + '</code>';
		});
	}

	// Join paragraphs
	string = ps.join (this.setup['server-eol'] + this.setup['server-eol']);

	// Replace code block markers with original markdown code
	string = string.replace (this.rx.md.blockMarker, function (marker, number) {
		return '<code>' + block.marks[number] + '</code>';
	});

	return string;
};
