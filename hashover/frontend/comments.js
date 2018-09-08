// Collection of comment parsing functions (comments.js)
HashOverConstructor.prototype.comments = {
	// URL replacement for automatic hyperlinks
	linksReplace: '<a href="$1" rel="noopener noreferrer" target="_blank">$1</a>',

	// Various regular expressions
	rx: {
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
	},

	// Add comment content to HTML template
	parse: function (comment, parent, collapse, popular)
	{
		// Parameter defaults
		parent = parent || null;

		// Reference to the parent object
		var hashover = this.parent;

		var commentKey = comment.permalink;
		var permalink = 'hashover-' + commentKey;
		var nameClass = 'hashover-name-plain';
		var template = { permalink: commentKey };
		var commentDate = comment.date;
		var codeTagCount = 0;
		var codeTags = [];
		var preTagCount = 0;
		var preTags = [];
		var classes = '';
		var replies = '';

		// Text for avatar image alt attribute
		var permatext = commentKey.slice(1).split('r').pop();

		// Check if this comment is a popular comment
		if (popular === true) {
			// Attempt to get parent comment permalink
			parent = hashover.permalinkParent (commentKey);

			// Get parent comment by its permalink if it exists
			if (parent !== null) {
				parent = hashover.permalinksComment (parent, hashover.instance.comments.primary);
			}

			// And remove "-pop" from text for avatar
			permatext = permatext.replace ('-pop', '');
		} else {
			// Append class to indicate comment is a reply when appropriate
			if (parent !== null) {
				classes += ' hashover-reply';
			}

			// Check if we have comments to collapse
			if (collapse === true && hashover.instance['total-count'] > 0) {
				// If so, check if we've reached the collapse limit
				if (this.collapseLimit >= hashover.setup['collapse-limit']) {
					// If so, append class to indicate collapsed comment
					classes += ' hashover-hidden';
				} else {
					// If not, increase collapse limit
					this.collapseLimit++;
				}
			}
		}

		// Add avatar image to template
		template.avatar = hashover.strings.parseTemplate (
			hashover.ui['user-avatar'], {
				src: comment.avatar,
				href: permalink,
				text: permatext
			}
		);

		// Check if comment is not a notice
		if (comment.notice === undefined) {
			// If so, define commenter name
			var name = comment.name || hashover.setup['default-name'];

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
				var nameElement = hashover.strings.parseTemplate (
					hashover.ui['name-link'], {
						href: website,
						permalink: commentKey,
						name: name
					}
				);
			} else {
				// If not, set name as plain text
				var nameElement = hashover.strings.parseTemplate (
					hashover.ui['name-span'], {
						permalink: commentKey,
						name: name
					}
				);
			}

			// Construct thread link
			if ((comment.url && comment.title) !== undefined) {
				template['thread-link'] = hashover.strings.parseTemplate (
					hashover.ui['thread-link'], {
						url: comment.url,
						title: comment.title
					}
				);
			}

			// Check if comment has a parent
			if (parent !== null) {
				// If so, create the parent thread permalink
				var parentThread = 'hashover-' + parent.permalink;

				// Get the parent's name
				var parentName = parent.name || hashover.setup['default-name'];

				// Add thread parent hyperlink to template
				template['parent-link'] = hashover.strings.parseTemplate (
					hashover.ui['parent-link'], {
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
				var replyTitle = hashover.locale['commenter-tip'];
				var replyClass = 'hashover-no-email';
			} else {
				// Check if commenter is subscribed
				if (comment.subscribed === true) {
					// If so, set subscribed title
					var replyTitle = name + ' ' + hashover.locale['subscribed-tip'];
					var replyClass = 'hashover-has-email';
				} else{
					// If not, set unsubscribed title
					var replyTitle = name + ' ' + hashover.locale['unsubscribed-tip'];
					var replyClass = 'hashover-no-email';
				}
			}

			// Check if the comment is editable for the user
			if (comment['editable'] !== undefined) {
				// If so, add "Edit" hyperlink to template
				template['edit-link'] = hashover.strings.parseTemplate (
					hashover.ui['edit-link'], {
						href: comment.url || hashover.instance['file-path'],
						permalink: commentKey
					}
				);
			}

			// Add like link and count to template if likes are enabled
			if (hashover.setup['allows-likes'] !== false) {
				hashover.optionalMethod ('addRatings', [
					comment, template, 'like', commentKey
				], 'comments');
			}

			// Add dislike link and count to template if dislikes are enabled
			if (hashover.setup['allows-dislikes'] !== false) {
				hashover.optionalMethod ('addRatings', [
					comment, template, 'dislike', commentKey
				], 'comments');
			}

			// Add name HTML to template
			template.name = hashover.strings.parseTemplate (
				hashover.ui['name-wrapper'], {
					class: nameClass,
					link: nameElement
				}
			);

			// Check if user timezones is enabled
			if (hashover.setup['uses-user-timezone'] !== false) {
				// If so, get local comment post date
				var postDate = new Date (comment['sort-date'] * 1000);

				// Check if short date format is enabled
				if (hashover.setup['uses-short-dates'] !== false) {
					// If so, get local date
					var localDate = new Date ();

					// Local comment post date to remove time from
					var postDateCopy = new Date (postDate.getTime ());

					// And format local time if the comment was posted today
					if (postDateCopy.setHours (0, 0, 0, 0) === localDate.setHours (0, 0, 0, 0)) {
						commentDate = hashover.strings.sprintf (hashover.locale['today'], [
							hashover.getDateTime (hashover.setup['time-format'], postDate)
						]);
					}
				} else {
					// If not, format a long local date/time
					commentDate = hashover.getDateTime (hashover.locale['date-time'], postDate);
				}
			}

			// Append status text to date
			if (comment['status-text'] !== undefined) {
				commentDate += ' (' + comment['status-text'] + ')';
			}

			// Add date from comment as permalink hyperlink to template
			template.date = hashover.strings.parseTemplate (
				hashover.ui['date-link'], {
					href: comment.url || hashover.instance['file-path'],
					permalink: permalink,
					date: commentDate
				}
			);

			// Add "Reply" hyperlink to template
			template['reply-link'] = hashover.strings.parseTemplate (
				hashover.ui['reply-link'], {
					href: comment.url || hashover.instance['file-path'],
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
						template['reply-count'] += ' ' + hashover.locale['replies'];
					} else {
						template['reply-count'] += ' ' + hashover.locale['reply'];
					}
				}
			}

			// Add HTML anchor tag to URLs
			var body = comment.body.replace (hashover.regex.links, this.linksReplace);

			// Replace [img] tags with external image placeholder if enabled
			body = body.replace (hashover.regex.imageTags, function (fullURL, url) {
				// Check if embedded images are enabled
				if (hashover.setup['allows-images'] !== false) {
					return hashover.optionalMethod ('embedImage', [ url ], 'comments');
				}

				// Convert image URL into an anchor tag
				return '<a href="' + url + '" rel="noopener noreferrer" target="_blank">' + url + '</a>';
			});

			// Parse markdown in comment if enabled
			if (hashover.parseMarkdown !== undefined) {
				body = hashover.parseMarkdown (body);
			}

			// Check if there are code tags in the comment
			if (this.rx.code.open.test (body) === true) {
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
				body = body.replace (this.rx.code.replace, codeReplacer);
			}

			// Check if there are pre tags in the comment
			if (this.rx.pre.open.test (body) === true) {
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

				// Replace pre tags with marker text
				body = body.replace (this.rx.pre.replace, preReplacer);
			}

			// Check if comment has whitespace to be trimmed
			if (this.rx.trimTags.open.test (body) === true) {
				// If so, define a regular expression callback
				var tagTrimmer = function (fullTag, open, name, html, close) {
					return open + hashover.EOLTrim (html) + close;
				};

				// And trim whitespace from comment
				body = body.replace (this.rx.trimTags.replace, tagTrimmer);
			}

			// Break comment into paragraphs
			var paragraphs = body.split (hashover.regex.paragraphs);

			// Initial paragraph'd comment
			var pdComment = '';

			// Run through paragraphs
			for (var i = 0, il = paragraphs.length; i < il; i++) {
				// Replace single line breaks with break tags
				var lines = paragraphs[i].replace (this.rx.lines, '<br>');

				// Wrap comment in paragraph tags
				pdComment += '<p>' + lines + '</p>' + hashover.setup['server-eol'];
			}

			// Replace code tag markers with original code tag HTML
			if (codeTagCount > 0) {
				pdComment = pdComment.replace (this.rx.code.marker, function (m, i) {
					return codeTags[i];
				});
			}

			// Replace pre tag markers with original pre tag HTML
			if (preTagCount > 0) {
				pdComment = pdComment.replace (this.rx.pre.marker, function (m, i) {
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
			template.name = hashover.strings.parseTemplate (
				hashover.ui['name-wrapper'], {
					class: nameClass,
					link: comment.title
				}
			);
		}

		// Comment HTML template
		var html = hashover.strings.parseTemplate (hashover.ui['theme'], template);

		// Recursively parse replies
		if (comment.replies !== undefined) {
			for (var i = 0, il = comment.replies.length; i < il; i++) {
				replies += this.parse (comment.replies[i], comment, collapse);
			}
		}

		// Wrap comment HTML
		var wrapper = hashover.strings.parseTemplate (
			hashover.ui['comment-wrapper'], {
				permalink: permalink,
				class: classes,
				html: html + replies
			}
		);

		return wrapper;
	}
};
