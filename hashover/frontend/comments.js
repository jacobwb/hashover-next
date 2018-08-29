// Collection of comment parsing functions (comments.js)
HashOverConstructor.prototype.comments = {
	collapseLimit: 0,
	codeOpenRegex: /<code>/i,
	codeTagRegex: /(<code>)([\s\S]*?)(<\/code>)/ig,
	preOpenRegex: /<pre>/i,
	preTagRegex: /(<pre>)([\s\S]*?)(<\/pre>)/ig,
	lineRegex: /(?:\r\n|\r|\n)/g,
	codeTagMarkerRegex: /CODE_TAG\[([0-9]+)\]/g,
	preTagMarkerRegex: /PRE_TAG\[([0-9]+)\]/g,

	// Tags that will have their innerHTML trimmed
	trimTagRegexes: {
		blockquote: {
			test: /<blockquote>/,
			replace: /(<blockquote>)([\s\S]*?)(<\/blockquote>)/ig
		},

		ul: {
			test: /<ul>/,
			replace: /(<ul>)([\s\S]*?)(<\/ul>)/ig
		},

		ol: {
			test: /<ol>/,
			replace: /(<ol>)([\s\S]*?)(<\/ol>)/ig
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
		var permatext = commentKey.slice (1);
		    permatext = permatext.split ('r');
		    permatext = permatext.pop ();

		// Trims whitespace from an HTML tag's inner HTML
		function tagTrimmer (fullTag, openTag, innerHTML, closeTag)
		{
			return openTag + hashover.EOLTrim (innerHTML) + closeTag;
		}

		// Check if this comment is a popular comment
		if (popular === true) {
			// Remove "-pop" from text for avatar
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
		template.avatar = hashover.strings.parseTemplate (hashover.ui['user-avatar'], {
			src: comment.avatar,
			href: permalink,
			text: permatext
		});

		if (comment.notice === undefined) {
			var name = comment.name || hashover.setup['default-name'];
			var website = comment.website;
			var isTwitter = false;

			// Check if user's name is a Twitter handle
			if (name.charAt (0) === '@') {
				name = name.slice (1);
				nameClass = 'hashover-name-twitter';
				isTwitter = true;
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
				if (isTwitter === false) {
					nameClass = 'hashover-name-website';
				}

				// If so, display name as a hyperlink
				var nameElement = hashover.strings.parseTemplate (hashover.ui['name-link'], {
					href: website,
					permalink: commentKey,
					name: name
				});
			} else {
				// If not, display name as plain text
				var nameElement = hashover.strings.parseTemplate (hashover.ui['name-span'], {
					permalink: commentKey,
					name: name
				});
			}

			// Construct thread link
			if ((comment.url && comment.title) !== undefined) {
				template['thread-link'] = hashover.strings.parseTemplate (hashover.ui['thread-link'], {
					url: comment.url,
					title: comment.title
				});
			}

			// Construct parent thread hyperlink
			if (parent !== null) {
				var parentThread = 'hashover-' + parent.permalink;
				var parentName = parent.name || hashover.setup['default-name'];

				// Add thread parent hyperlink to template
				template['parent-link'] = hashover.strings.parseTemplate (hashover.ui['parent-link'], {
					parent: parentThread,
					permalink: commentKey,
					name: parentName
				});
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
				template['edit-link'] = hashover.strings.parseTemplate (hashover.ui['edit-link'], {
					href: comment.url || hashover.instance['file-path'],
					permalink: commentKey
				});
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
			template.name = hashover.strings.parseTemplate (hashover.ui['name-wrapper'], {
				class: nameClass,
				link: nameElement
			});

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
							hashover.dateTime.format (hashover.setup['time-format'], postDate)
						]);
					}
				} else {
					// If not, format a long local date/time
					commentDate = hashover.dateTime.format (hashover.locale['date-time'], postDate);
				}
			}

			// Append status text to date
			if (comment['status-text'] !== undefined) {
				commentDate += ' (' + comment['status-text'] + ')';
			}

			// Add date from comment as permalink hyperlink to template
			template.date = hashover.strings.parseTemplate (hashover.ui['date-link'], {
				href: comment.url || hashover.instance['file-path'],
				permalink: permalink,
				date: commentDate
			});

			// Add "Reply" hyperlink to template
			template['reply-link'] = hashover.strings.parseTemplate (hashover.ui['reply-link'], {
				href: comment.url || hashover.instance['file-path'],
				permalink: commentKey,
				class: replyClass,
				title: replyTitle
			});

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
			var body = comment.body.replace (hashover.regex.links, '<a href="$1" rel="noopener noreferrer" target="_blank">$1</a>');

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
			if (hashover.markdown !== undefined) {
				body = hashover.markdown.parse (body);
			}

			// Check for code tags
			if (this.codeOpenRegex.test (body) === true) {
				// Replace code tags with marker text
				body = body.replace (this.codeTagRegex, function (fullTag, openTag, innerHTML, closeTag) {
					var codeMarker = openTag + 'CODE_TAG[' + codeTagCount + ']' + closeTag;

					codeTags[codeTagCount] = hashover.EOLTrim (innerHTML);
					codeTagCount++;

					return codeMarker;
				});
			}

			// Check for pre tags
			if (this.preOpenRegex.test (body) === true) {
				// Replace pre tags with marker text
				body = body.replace (this.preTagRegex, function (fullTag, openTag, innerHTML, closeTag) {
					var preMarker = openTag + 'PRE_TAG[' + preTagCount + ']' + closeTag;

					preTags[preTagCount] = hashover.EOLTrim (innerHTML);
					preTagCount++;

					return preMarker;
				});
			}

			// Check for various multi-line tags
			for (var trimTag in this.trimTagRegexes) {
				if (this.trimTagRegexes.hasOwnProperty (trimTag) === true
				    && this.trimTagRegexes[trimTag]['test'].test (body) === true)
				{
					// Trim whitespace
					body = body.replace (this.trimTagRegexes[trimTag]['replace'], tagTrimmer);
				}
			}

			// Break comment into paragraphs
			var paragraphs = body.split (hashover.regex.paragraphs);
			var pdComment = '';

			// Wrap comment in paragraph tag
			// Replace single line breaks with break tags
			for (var i = 0, il = paragraphs.length; i < il; i++) {
				pdComment += '<p>' + paragraphs[i].replace (this.lineRegex, '<br>') + '</p>' + hashover.setup['server-eol'];
			}

			// Replace code tag markers with original code tag HTML
			if (codeTagCount > 0) {
				pdComment = pdComment.replace (this.codeTagMarkerRegex, function (marker, number) {
					return codeTags[number];
				});
			}

			// Replace pre tag markers with original pre tag HTML
			if (preTagCount > 0) {
				pdComment = pdComment.replace (this.preTagMarkerRegex, function (marker, number) {
					return preTags[number];
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
			template.name = hashover.strings.parseTemplate (hashover.ui['name-wrapper'], {
				class: nameClass,
				link: comment.title
			});
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
		var wrapper = hashover.strings.parseTemplate (hashover.ui['comment-wrapper'], {
			permalink: permalink,
			class: classes,
			html: html + replies
		});

		return wrapper;
	}
};
