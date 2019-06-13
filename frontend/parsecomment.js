// Add comment parsing regular expressions (parsecomment.js)
HashOverConstructor.prototype.rx.html = {
	// URL replacement for automatic hyperlinks
	linksReplace: '<a href="$1" rel="noopener noreferrer" target="_blank">$1</a>',

	// Matches various line ending styles
	lines: /(?:\r\n|\r|\n)/g,

	// For <code> tags
	code: {
		// Matches <code> opening
		open: /<code>/i,

		// Replacement for code tag processing
		replace: /(<code>)([\s\S]*?)(<\/code>)/ig,

		// Matches code tag markers
		marker: /CODE_TAG\[([0-9]+)\]/g
	},

	// For <pre> tags
	pre: {
		// Matches <pre> opening
		open: /<pre>/i,

		// Replacement for pre tag processing
		replace: /(<pre>)([\s\S]*?)(<\/pre>)/ig,

		// Matches pre tag markers
		marker: /PRE_TAG\[([0-9]+)\]/g
	},

	// Tags that will have their inner HTML trimmed
	trimTags: {
		// Matches blockquote/ul/ol tags openings
		open: /<(blockquote|ul|ol)>/,

		// Replacement for blockquote/ul/ol trimming
		replace: /(<(blockquote|ul|ol)>)([\s\S]*?)(<\/\2>)/ig
	}
};

// Add comment content to HTML template (parsecomment.js)
HashOverConstructor.prototype.parseComment = function (comment, parent, collapse, popular)
{
	// Parameter defaults
	parent = parent || null;

	// Reference to this object
	var hashover = this;

	var commentKey = comment.permalink;
	var permalink = this.prefix (commentKey);
	var nameClass = 'hashover-name-plain';
	var commentDate = comment.date;
	var codeTagCount = 0;
	var codeTags = [];
	var preTagCount = 0;
	var preTags = [];
	var classes = '';
	var replies = '';

	// Get instantiated prefix
	var prefix = this.prefix ();

	// Initial template
	var template = {
		hashover: prefix,
		permalink: commentKey
	};

	// Text for avatar image alt attribute
	var permatext = commentKey.slice(1).split('r').pop();

	// Check if this comment is a popular comment
	if (popular === true) {
		// Attempt to get parent comment permalink
		parent = this.permalinkParent (commentKey);

		// Get parent comment by its permalink if it exists
		if (parent !== null) {
			parent = this.permalinkComment (parent, this.instance.comments.primary);
		}

		// And remove "-pop" from text for avatar
		permatext = permatext.replace ('-pop', '');
	} else {
		// Append class to indicate comment is a reply when appropriate
		if (parent !== null) {
			classes += ' hashover-reply';
		}

		// Check if we have comments to collapse
		if (collapse === true && this.instance['total-count'] > 0) {
			// If so, check if we've reached the collapse limit
			if (this.instance.collapseLimit >= this.setup['collapse-limit']) {
				// If so, append class to indicate collapsed comment
				classes += ' hashover-hidden';
			} else {
				// If not, increase collapse limit
				this.instance.collapseLimit++;
			}
		}
	}

	// Add avatar image to template
	template.avatar = this.strings.parseTemplate (
		this.ui['user-avatar'], {
			src: comment.avatar,
			href: permalink,
			text: permatext
		}
	);

	// Check if comment is not a notice
	if (comment.notice === undefined) {
		// If so, define commenter name
		var name = comment.name || this.setup['default-name'];

		// Initial website
		var website = comment.website;

		// Name is Twitter handle indicator
		var isTwitter = (name.charAt (0) === '@');

		// Check if user's name is a Twitter handle
		if (isTwitter === true) {
			// If so, remove the leading "@" character
			name = name.slice (1);

			// Set Twitter name class
			nameClass = 'hashover-name-twitter';

			// Get the name length
			var nameLength = name.length;

			// Check if Twitter handle is valid length
			if (nameLength > 1 && nameLength <= 30) {
				// Set website to Twitter profile if a specific website wasn't given
				if (website === undefined) {
					website = 'http://twitter.com/' + name;
				}
			}
		}

		// Check whether user gave a website
		if (website !== undefined) {
			// If so, set normal website class where appropriate
			if (isTwitter === false) {
				nameClass = 'hashover-name-website';
			}

			// And set name as a hyperlink
			var nameElement = this.strings.parseTemplate (
				this.ui['name-link'], {
					hashover: prefix,
					href: website,
					permalink: commentKey,
					name: name
				}
			);
		} else {
			// If not, set name as plain text
			var nameElement = this.strings.parseTemplate (
				this.ui['name-span'], {
					hashover: prefix,
					permalink: commentKey,
					name: name
				}
			);
		}

		// Construct thread link
		if ((comment.url && comment.title) !== undefined) {
			template['thread-link'] = this.strings.parseTemplate (
				this.ui['thread-link'], {
					url: comment.url,
					title: comment.title
				}
			);
		}

		// Check if comment has a parent
		if (parent !== null && this.ui['parent-link'] !== undefined) {
			// If so, create the parent thread permalink
			var parentThread = 'hashover-' + parent.permalink;

			// Get the parent's name
			var parentName = parent.name || this.setup['default-name'];

			// Add thread parent hyperlink to template
			template['parent-link'] = this.strings.parseTemplate (
				this.ui['parent-link'], {
					hashover: prefix,
					href: comment.url || this.instance['file-path'],
					parent: parentThread,
					permalink: commentKey,
					name: parentName
				}
			);
		}

		// Check if the logged in user owns the comment
		if (comment['user-owned'] !== undefined) {
			// If so, append class to indicate comment is from logged in user
			classes += ' hashover-user-owned';

			// Define "Reply" link with original poster title
			var replyTitle = this.locale['commenter-tip'];
			var replyClass = 'hashover-no-email';
		} else {
			// Check if commenter is subscribed
			if (comment.subscribed === true) {
				// If so, set subscribed title
				var replyTitle = name + ' ' + this.locale['subscribed-tip'];
				var replyClass = 'hashover-has-email';
			} else{
				// If not, set unsubscribed title
				var replyTitle = name + ' ' + this.locale['unsubscribed-tip'];
				var replyClass = 'hashover-no-email';
			}
		}

		// Check if the comment is editable for the user
		if ((comment['editable'] && this.ui['edit-link']) !== undefined) {
			// If so, add "Edit" hyperlink to template
			template['edit-link'] = this.strings.parseTemplate (
				this.ui['edit-link'], {
					hashover: prefix,
					href: comment.url || this.instance['file-path'],
					permalink: commentKey
				}
			);
		}

		// Add like link and count to template if likes are enabled
		if (this.setup['allows-likes'] !== false) {
			this.optionalMethod ('addRatings', [
				comment, template, 'like', commentKey
			]);
		}

		// Add dislike link and count to template if dislikes are enabled
		if (this.setup['allows-dislikes'] !== false) {
			this.optionalMethod ('addRatings', [
				comment, template, 'dislike', commentKey
			]);
		}

		// Add name HTML to template
		template.name = this.strings.parseTemplate (
			this.ui['name-wrapper'], {
				class: nameClass,
				link: nameElement
			}
		);

		// Append status text to date
		if (comment['status-text'] !== undefined) {
			commentDate += ' (' + comment['status-text'] + ')';
		}

		// Add date from comment as permalink hyperlink to template
		template.date = this.strings.parseTemplate (
			this.ui['date-link'], {
				hashover: prefix,
				href: comment.url || this.instance['file-path'],
				permalink: 'hashover-' + commentKey,
				title: comment['date-time'],
				date: commentDate
			}
		);

		// Add "Reply" hyperlink to template
		template['reply-link'] = this.strings.parseTemplate (
			this.ui['reply-link'], {
				hashover: prefix,
				href: comment.url || this.instance['file-path'],
				permalink: commentKey,
				class: replyClass,
				title: replyTitle
			}
		);

		// Add reply count to template
		if (comment.replies !== undefined) {
			template['reply-count'] = comment.replies.length;

			if (template['reply-count'] > 0) {
				if (template['reply-count'] !== 1) {
					template['reply-count'] += ' ' + this.locale['replies'];
				} else {
					template['reply-count'] += ' ' + this.locale['reply'];
				}
			}
		}

		// Add HTML anchor tag to URLs
		var body = comment.body.replace (this.rx.links, this.rx.html.linksReplace);

		// Replace [img] tags with placeholders if embedded images are enabled
		if (hashover.setup['allows-images'] !== false) {
			body = body.replace (this.rx.imageTags, function (m, link, url) {
				return hashover.optionalMethod ('embedImage', arguments);
			});
		}

		// Parse markdown in comment if enabled
		if (this.parseMarkdown !== undefined) {
			body = this.parseMarkdown (body);
		}

		// Check if there are code tags in the comment
		if (this.rx.html.code.open.test (body) === true) {
			// If so, define regular expression callback
			var codeReplacer = function (fullTag, open, html, close) {
				// Create code marker
				var codeMarker = open + 'CODE_TAG[' + codeTagCount + ']' + close;

				// Store original HTML for later re-injection
				codeTags[codeTagCount] = hashover.EOLTrim (html);

				// Increase code tag count
				codeTagCount++;

				// Return code tag marker
				return codeMarker;
			};

			// And replace code tags with marker text
			body = body.replace (this.rx.html.code.replace, codeReplacer);
		}

		// Check if there are pre tags in the comment
		if (this.rx.html.pre.open.test (body) === true) {
			// If so, define regular expression callback
			var preReplacer = function (fullTag, open, html, close) {
				// Create pre marker
				var preMarker = open + 'PRE_TAG[' + preTagCount + ']' + close;

				// Store original HTML for later re-injection
				preTags[preTagCount] = hashover.EOLTrim (html);

				// Increase pre tag count
				preTagCount++;

				// Return pre tag marker
				return preMarker;
			};

			// And replace pre tags with marker text
			body = body.replace (this.rx.html.pre.replace, preReplacer);
		}

		// Check if comment has whitespace to be trimmed
		if (this.rx.html.trimTags.open.test (body) === true) {
			// If so, define a regular expression callback
			var tagTrimmer = function (fullTag, open, name, html, close) {
				return open + hashover.EOLTrim (html) + close;
			};

			// And trim whitespace from comment
			body = body.replace (this.rx.html.trimTags.replace, tagTrimmer);
		}

		// Break comment into paragraphs
		var paragraphs = body.split (this.rx.paragraphs);

		// Initial paragraph'd comment
		var pdComment = '';

		// Run through paragraphs
		for (var i = 0, il = paragraphs.length; i < il; i++) {
			// Replace single line breaks with break tags
			var lines = paragraphs[i].replace (this.rx.html.lines, '<br>');

			// Wrap comment in paragraph tags
			pdComment += '<p>' + lines + '</p>' + this.setup['server-eol'];
		}

		// Replace code tag markers with original code tag HTML
		if (codeTagCount > 0) {
			pdComment = pdComment.replace (this.rx.html.code.marker, function (m, i) {
				return codeTags[i];
			});
		}

		// Replace pre tag markers with original pre tag HTML
		if (preTagCount > 0) {
			pdComment = pdComment.replace (this.rx.html.pre.marker, function (m, i) {
				return preTags[i];
			});
		}

		// Add comment data to template
		template.comment = pdComment;
	} else {
		// Append notice class
		classes += ' hashover-notice ' + comment['notice-class'];

		// Add notice to template
		template.comment = comment.notice;

		// Add name HTML to template
		template.name = this.strings.parseTemplate (
			this.ui['name-wrapper'], {
				class: nameClass,
				link: comment.title
			}
		);
	}

	// Comment HTML template
	var html = this.strings.parseTemplate (this.ui['theme'], template);

	// Check if comment has replies
	if (comment.replies !== undefined) {
		// If so, append class to indicate comment has replies
		classes += ' hashover-has-replies';

		// Recursively parse replies
		for (var i = 0, il = comment.replies.length; i < il; i++) {
			replies += this.parseComment (comment.replies[i], comment, collapse);
		}
	}

	// Wrap comment HTML
	var wrapper = this.strings.parseTemplate (
		this.ui['comment-wrapper'], {
			hashover: prefix,
			permalink: commentKey,
			class: classes,
			html: html + replies
		}
	);

	return wrapper;
};
