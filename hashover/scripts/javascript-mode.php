<?php

// Copyright (C) 2010-2015 Jacob Barkdull
// This file is part of HashOver.
//
// HashOver is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// HashOver is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


// Display source code
if (basename ($_SERVER['PHP_SELF']) === basename (__FILE__)) {
	if (isset ($_GET['source'])) {
		header ('Content-type: text/plain; charset=UTF-8');
		exit (file_get_contents (basename (__FILE__)));
	} else {
		exit ('<b>HashOver</b>: This isn\'t a standalone file.');
	}
}

// Text for "Show X Other Comment(s)" link
if ($hashover->setup->collapsesComments !== false) {
	if ($hashover->setup->collapseLimit >= 1) {
		$other_comment_count = ($hashover->readComments->totalCount - 1) - $hashover->setup->collapseLimit;
		$collapse_link_plural = ($other_comment_count !== 1) ? 1 : 0;
		$collapse_link_text = $hashover->locales->locale['show-other-comments'][$collapse_link_plural];
		$collapse_link_text = sprintf ($collapse_link_text, $other_comment_count);
	} else {
		$collapse_link_plural = ($hashover->readComments->totalCount !== 1) ? 1 : 0;
		$collapse_link_text = $hashover->locales->locale['show-number-comments'][$collapse_link_plural];
		$collapse_link_text = sprintf ($collapse_link_text, $hashover->readComments->totalCount - 1);
	}
}

// Some locale plural arrays
$like_locale = $hashover->locales->locale ('like', true);
$dislike_locale = $hashover->locales->locale ('dislike', true);

?>
// Copyright (C) 2010-2015 Jacob Barkdull
// This file is part of HashOver.
//
// HashOver is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// HashOver is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


(function () {
	"use strict";

	var execStart		= Date.now ();
	var serverEOL		= '<?php echo str_replace (array ("\r", "\n"), array ('\r', '\n'), PHP_EOL); ?>';
	var httpRoot		= '<?php echo $hashover->setup->httpRoot; ?>';
	var elementsById	= {};
	var collapseLimit	= <?php echo $hashover->setup->collapseLimit; ?>;
	var collapsedCount	= 0;
	var streamMode		= <?php echo ($hashover->setup->replyMode === 'stream') ? 'true' : 'false'; ?>;
	var streamDepth		= <?php echo $hashover->setup->streamDepth; ?>;
	var allowsDislikes	= <?php echo $hashover->setup->allowsDislikes ? 'true' : 'false'; ?>;
	var head		= document.head || document.getElementsByTagName ('head')[0];
	var imagePlaceholder	= '<?php echo $hashover->setup->httpImages; ?>/place-holder.<?php echo $hashover->setup->imageFormat; ?>';
	var imageExtensions	= ['<?php echo implode ('\', \'', $hashover->setup->imageTypes); ?>'];
	var URLRegex		= '((ftp|http|https):\/\/[a-z0-9-@:%_\+.~#?&\/=]+)';
	var linkRegex		= new RegExp (URLRegex + '( {0,1})', 'ig');
	var imageRegex		= new RegExp ('\\[img\\]<a.*?>' + URLRegex + '</a>\\[/img\\]', 'ig');
	var codeOpenRegex	= /<code>/i;
	var codeTagRegex	= /(<code>)([\s\S]*?)(<\/code>)/ig;
	var codeTagMarkerRegex	= /CODE_TAG\[([0-9]+)\]/g;
	var preOpenRegex	= /<pre>/i;
	var preTagRegex		= /(<pre>)([\s\S]*?)(<\/pre>)/ig;
	var preTagMarkerRegex	= /PRE_TAG\[([0-9]+)\]/g;
	var lineRegex		= new RegExp (serverEOL, 'g');
	var messageCounts	= {};
	var fieldOptions	= <?php echo json_encode ($hashover->setup->fieldOptions); ?>;
	var userIsLoggedIn	= <?php echo $hashover->login->userIsLoggedIn ? 'true' : 'false'; ?>;
	var themeCSS		= httpRoot + '/themes/<?php echo $hashover->setup->theme; ?>/style.css';
	var appendCSS		= true;
	var totalCount		= <?php echo $hashover->readComments->totalCount - 1; ?>;
	var primaryCount	= <?php echo $hashover->readComments->primaryCount - 1; ?>;
	var HashOverDiv		= document.getElementById ('hashover');
	var hashoverScript	= <?php echo !empty ($_GET['hashover-script']) ? $hashover->misc->makeXSSsafe ($_GET['hashover-script']) : 'false'; ?>;
	var deviceType		= '<?php echo $hashover->setup->isMobile ? 'mobile' : 'desktop'; ?>';
	var pageURL		= '<?php echo $hashover->setup->pageURL; ?>';
	var threadRegex		= /^(c[0-9r]+)r[0-9\-pop]+$/;
	var sortDiv		= null;
	var HashOverForm	= null;
	var AJAXPost		= null;
	var AJAXEdit		= null;
	var httpScripts		= '<?php echo $hashover->setup->httpScripts; ?>';
	var URLParts		= window.location.href.split ('#');
	var URLHref		= URLParts[0];
	var URLHash		= URLParts[1] || '';
	var moreDiv		= null;
	var moreLink		= null;
	var showingMore		= false;

	// Tags that will have their innerHTML trimmed
	var trimTagRegexes = {
		'blockquote': {
			'test': /<blockquote>/,
			'replace': /(<blockquote>)([\s\S]*?)(<\/blockquote>)/ig
		},
		'ul': {
			'test': /<ul>/,
			'replace': /(<ul>)([\s\S]*?)(<\/ul>)/ig
		},
		'ol': {
			'test': /<ol>/,
			'replace': /(<ol>)([\s\S]*?)(<\/ol>)/ig
		}
	};

	var locale = {
		'cancel':		'<?php echo $hashover->locales->locale['cancel']; ?>',
		'like':			['<?php echo implode ("', '", $like_locale); ?>'],
		'liked':		'<?php echo $hashover->locales->locale ('liked', true); ?>',
		'unlike':		'<?php echo $hashover->locales->locale ('unlike', true); ?>',
		'likeComment':		'<?php echo $hashover->locales->locale ('like-comment', true); ?>',
		'likedComment':		'<?php echo $hashover->locales->locale ('liked-comment', true); ?>',
		'dislike':		['<?php echo implode ("', '", $dislike_locale); ?>'],
		'disliked':		'<?php echo $hashover->locales->locale ('disliked', true); ?>',
		'dislikeComment':	'<?php echo $hashover->locales->locale ('dislike-comment', true); ?>',
		'dislikedComment':	'<?php echo $hashover->locales->locale ('disliked-comment', true); ?>',
		'name':			'<?php echo $hashover->locales->locale ('name', true); ?>',
		'password':		'<?php echo $hashover->locales->locale ('password', true); ?>',
		'email':		'<?php echo $hashover->locales->locale ('email', true); ?>',
		'website':		'<?php echo $hashover->locales->locale ('website', true); ?>'
	};

	// Shorthand for Document.getElementById ()
	function getElement (id, force)
	{
		if (force) {
			return document.getElementById (id);
		}

		if (!elementsById[id]) {
			elementsById[id] = document.getElementById (id);
		}

		return elementsById[id];
	}

	// Execute callback function if element isn't false
	function ifElement (element, callback)
	{
		if (element = getElement (element, true)) {
			return callback (element);
		}

		return false;
	}

	// Trims whitespace from an HTML tag's inner HTML
	function tagTrimmer (fullTag, openTag, innerHTML, closeTag)
	{
		return openTag + innerHTML.trim (serverEOL) + closeTag;
	}

	// Find parent comment via permalink
	function findParent (permalink)
	{
		var levels;
		var currentLevel = PHPContent.comments;
		var keys;
		var currentKey;

		// Split permalink into keys
		keys = permalink.slice (1);
		keys = keys.split ('r');

		// Remove last key
		keys.pop ();

		// Limit depth in stream mode
		if (streamMode === true) {
			levels = Math.min (streamDepth, keys.length) - 1;
		} else {
			levels = keys.length - 1;
		}

		// Return the comment if it's primary
		if (levels <= 0) {
			return currentLevel[(keys[0] - 1)];
		}

		// Get replies from each comment
		for (currentKey = 0; currentKey < levels; currentKey++) {
			currentLevel = currentLevel[(keys[currentKey] - 1)].replies;
		}

		return currentLevel[(keys[currentKey] - 1)];
	}

	// Add comment content to HTML template
	function parseComment (comment, parent, collapse, sort, method, popular)
	{
		var parent = parent || null;
		var collapse = collapse || false;
		var sort = sort || false;
		var method = method || 'ascending';
		var popular = popular || false;

		var permalink = comment.permalink;
		var nameClass = 'hashover-name-plain';
		var template = {permalink: permalink};
		var isReply = (permalink.indexOf ('r') > -1);
		var codeTagCount = 0;
		var codeTags = [];
		var preTagCount = 0;
		var preTags = [];
		var commentClass = '';
		var replies = '';

		// Text for avatar image alt attribute
		var permatext = permalink.slice (1);
		    permatext = permatext.split ('r');
		    permatext = permatext.pop ();

		// Get parent comment via permalink
		if (isReply && parent === null) {
			parent = findParent (comment.permalink);
		}

		// Check if this comment is a popular comment
		if (popular) {
			// Remove "-pop" from text for avatar
			permatext = permatext.replace ('-pop', '');
		} else {
			// Check if comment is a reply
			if (isReply) {
				// Check that comments are being sorted
				if (!sort || method === 'ascending') {
					// Append class to indicate comment is a reply
					commentClass = ' hashover-reply';
				}
			}
<?php if ($hashover->setup->collapsesComments !== false) { ?>

			if (collapse === true && collapsedCount >= collapseLimit) {
				commentClass += ' hashover-hidden';
			} else {
				collapsedCount++;
			}
<?php } ?>
		}

		// Add avatar image to template
		template.avatar = '<?php echo $hashover->html->userAvatar ('permatext', 'permalink', 'comment.avatar'); ?>';

		if (!comment.notice) {
			var name = comment.name || '<?php echo $hashover->setup->defaultName; ?>';
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
					if (!website) {
						website = 'http://twitter.com/' + name;
					}
				}
			}

			// Check whether user gave a website
			if (website) {
				if (isTwitter === false) {
					nameClass = 'hashover-name-website';
				}

				// If so, display name as a hyperlink
				var nameLink = '<?php echo $hashover->html->nameElement ('a', 'name', 'permalink', 'website'); ?>';
			} else {
				// If not, display name as plain text
				var nameLink = '<?php echo $hashover->html->nameElement ('span', 'name', 'permalink'); ?>';
			}

			// Construct thread hyperlink
			if (isReply) {
				var parentThread = parent.permalink;
				var parentName = parent.name || '<?php echo $hashover->setup->defaultName; ?>';

				// Add thread parent hyperlink to template
				template['thread-link'] = '<?php echo $hashover->html->threadLink ('permalink', 'parentThread', 'parentName'); ?>';
			}

			if (comment['user-owned']) {
				// Define "Reply" link with original poster title
				var replyTitle = '<?php echo $hashover->locales->locale ('commenter-tip', true); ?>';
				var replyClass = 'hashover-no-email';

				// Add "Reply" hyperlink to template
				template['edit-link'] = '<?php echo $hashover->html->editLink ('permalink'); ?>';
			} else {
				// Check if commenter is subscribed
				if (comment.subscribed === true) {
					// If so, set subscribed title
					var replyTitle = comment.name + ' <?php echo $hashover->locales->locale ('subscribed-tip', true); ?>';
					var replyClass = 'hashover-has-email';
				} else{
					// If not, set unsubscribed title
					var replyTitle = comment.name + ' <?php echo $hashover->locales->locale ('unsubscribed-tip', true); ?>';
					var replyClass = 'hashover-no-email';
				}

				// Check whether this comment was liked by the visitor
				if (comment.liked) {
					// If so, set various attributes to indicate comment was liked
					var likeClass = 'hashover-liked';
					var likeTitle = locale.likedComment;
					var likeText = locale.liked;
				} else {
					// If not, set various attributes to indicate comment can be liked
					var likeClass = 'hashover-like';
					var likeTitle = locale.likeComment;
					var likeText = locale.like[0];
				}

				// Append class to indicate dislikes are enabled
				if (allowsDislikes) {
					likeClass += ' hashover-dislikes-enabled';
				}

				// Add like link to HTML template
				template['like-link'] = '<?php echo $hashover->html->likeLink ('permalink', 'likeClass', 'likeTitle', 'likeText'); ?>';

<?php if ($hashover->setup->allowsDislikes === true) { ?>
				// Check whether this comment was disliked by the visitor
				if (comment.disliked) {
					// If so, set various attributes to indicate comment was disliked
					var dislikeClass = 'hashover-disliked';
					var dislikeTitle = locale.dislikedComment;
					var dislikeText = locale.disliked;
				} else {
					// If not, set various attributes to indicate comment can be disliked
					var dislikeClass = 'hashover-dislike';
					var dislikeTitle = locale.dislikeComment;
					var dislikeText = locale.dislike[0];
				}

				// Add dislike link to HTML template
				template['dislike-link'] = '<?php echo $hashover->html->dislikeLink ('permalink', 'dislikeClass', 'dislikeTitle', 'dislikeText'); ?>';
<?php } ?>
			}

			// Get number of likes, append "Like(s)" locale
			if (comment.likes) {
				var likeCount = comment.likes + ' ' + locale.like[(comment.likes === 1 ? 0 : 1)];
			}

			// Add like count to HTML template
			template['like-count'] = '<?php echo $hashover->html->likeCount ('permalink', '(likeCount || \'\')'); ?>';

<?php if ($hashover->setup->allowsDislikes === true) { ?>
			// Get number of dislikes, append "Dislike(s)" locale
			if (comment.dislikes) {
				var dislikeCount = comment.dislikes + ' ' + locale.dislike[(comment.dislikes === 1 ? 0 : 1)];
			}

			// Add dislike count to HTML template
			template['dislike-count'] = '<?php echo $hashover->html->dislikeCount ('permalink', '(dislikeCount || \'\')'); ?>';

<?php } ?>
			// Add name HTML to template
			template.name = '<?php echo $hashover->html->nameWrapper ('nameLink', 'nameClass'); ?>';

			// Add date permalink hyperlink to template
			template.date = '<?php echo $hashover->html->dateLink ('permalink', 'comment.date'); ?>';

			// Add "Reply" hyperlink to template
			template['reply-link'] = '<?php echo $hashover->html->replyLink ('permalink', 'replyClass', 'replyTitle'); ?>';

			// Add reply count to template
			if (comment.replies) {
				template['reply-count'] = comment.replies.length;

				if (template['reply-count'] > 0) {
					if (template['reply-count'] !== 1) {
						template['reply-count'] += ' <?php echo $hashover->locales->locale ('replies', true); ?>';
					} else {
						template['reply-count'] += ' <?php echo $hashover->locales->locale ('reply', true); ?>';
					}
				}
			}

			// Add HTML anchor tag to URLs
			var body = comment.body.replace (linkRegex, '<a href="$1" target="_blank">$1</a>');

			// Replace [img] tags with external image placeholder if enabled
			body = body.replace (imageRegex, function (fullURL, url) {
<?php if ($hashover->setup->allowsImages === true) { ?>
				// Get image extension from URL
				var urlExtension = url.split ('#')[0];
				    urlExtension = urlExtension.split ('?')[0];
				    urlExtension = urlExtension.split ('.');
				    urlExtension = urlExtension.pop ();

				// Check if the image URL is of an allowed type
				for (var ext = 0, length = imageExtensions.length; ext < length; ext++) {
					if (imageExtensions[ext] !== urlExtension) {
						continue;
					}

					var imgtag = document.createElement ('img');
					    imgtag.className = 'hashover-imgtag';
					    imgtag.src = imagePlaceholder;
					    imgtag.title = 'Click to view external image';
					    imgtag.dataset.placeholder = imagePlaceholder;
					    imgtag.dataset.url = url;
					    imgtag.alt = 'External Image';

					return imgtag.outerHTML;
				}

<?php } ?>
				return '<a href="' + url + '" target="_blank">' + url + '</a>';
			});

			// Check for code tags
			if (codeOpenRegex.test (body)) {
				// Replace code tags with placeholder text
				body = body.replace (codeTagRegex, function (fullTag, openTag, innerHTML, closeTag) {
					var codePlaceholder = openTag + 'CODE_TAG[' + codeTagCount + ']' + closeTag;

					codeTags[codeTagCount] = innerHTML.trim (serverEOL);
					codeTagCount++;

					return codePlaceholder;
				});
			}

			// Check for pre tags
			if (preOpenRegex.test (body)) {
				// Replace pre tags with placeholder text
				body = body.replace (preTagRegex, function (fullTag, openTag, innerHTML, closeTag) {
					var prePlaceholder = openTag + 'PRE_TAG[' + preTagCount + ']' + closeTag;

					preTags[preTagCount] = innerHTML.trim (serverEOL);
					preTagCount++;

					return prePlaceholder;
				});
			}

			// Check for various multi-line tags
			for (var trimTag in trimTagRegexes) {
				if (trimTagRegexes.hasOwnProperty (trimTag)
				    && trimTagRegexes[trimTag]['test'].test (body))
				{
					// Trim whitespace
					body = body.replace (trimTagRegexes[trimTag]['replace'], tagTrimmer);
				}
			}

			// Break comment into paragraphs
			var paragraphs = body.split (serverEOL + serverEOL);
			var pdComment = '';

			// Wrap comment in paragraph tag
			// Replace single line breaks with break tags
			for (var i = 0, il = paragraphs.length; i < il; i++) {
				pdComment += '<p>' + paragraphs[i].replace (lineRegex, '<br>') + '</p>';
			}

			// Replace code tag placeholders with original code tag HTML
			if (codeTagCount > 0) {
				pdComment = pdComment.replace (codeTagMarkerRegex, function (placeholder, number) {
					return codeTags[number];
				});
			}

			// Replace pre tag placeholders with original pre tag HTML
			if (preTagCount > 0) {
				pdComment = pdComment.replace (preTagMarkerRegex, function (placeholder, number) {
					return preTags[number];
				});
			}

			// Add comment data to template
			template.comment = pdComment;
		} else {
			// Append notice class
			commentClass += ' hashover-notice ' + comment['notice-class'];

			// Add notice to template
			template.comment = comment.notice;

			// Add name HTML to template
			template.name = '<?php echo $hashover->html->nameWrapper ('comment.title', 'nameClass'); ?>';
		}

		// Comment HTML template
<?php

		echo $hashover->html->asJSVar ($hashover->templater->parseTemplate (), 'html', "\t\t");

?>

		// Recursively parse replies
		if (comment.replies) {
			for (var reply = 0, total = comment.replies.length; reply < total; reply++) {
				replies += parseComment (comment.replies[reply], comment, collapse);
			}
		}

		return '<?php echo $hashover->html->commentWrapper ('permalink', 'commentClass', 'html + replies'); ?>';
	}

	// Generate file from permalink
	function reversePermalink (permalink)
	{
		var file = permalink.slice (1);
		    file = file.replace (/r/g, '-');
		    file = file.replace ('-pop', '');

		return file;
	}

	// Change and hyperlink, like "Edit" or "Reply", into a "Cancel" hyperlink
	function cancelSwitcher (form, link, wrapper, permalink)
	{
		// Initial state properties of hyperlink
		var reset = {
			'textContent': link.textContent,
			'title': link.title,
			'onclick': link.onclick
		};

		// Change hyperlink to "Cancel" hyperlink
		link.textContent = locale.cancel;
		link.title = locale.cancel;

		// This resets the "Cancel" hyperlink to initial state onClick
		link.onclick = function () {
			// Remove fields from form wrapper
			wrapper.textContent = '';

			// Reset button
			link.textContent = reset.textContent;
			link.title = reset.title;
			link.onclick = reset.onclick;

			return false;
		};
<?php if ($hashover->setup->usesCancelButtons === true) { ?>

		// Attach event listeners to "Cancel" button
		getElement ('hashover-' + form + '-cancel-' + permalink, true).onclick = function () {
			// Remove fields from form wrapper
			wrapper.textContent = '';
			link.onclick ();

			return false;
		};
<?php } ?>
	}

	// Returns false if key event is the enter key
	function enterCheck (event)
	{
		return (event.keyCode === 13) ? false : true;
	}

	// Prevents enter key on inputs from submitting form
	function preventSubmit (form)
	{
		// Get login info inputs
		var infoInputs = form.getElementsByClassName ('hashover-input-info');

		// Set enter key press to return false
		for (var i = 0, il = infoInputs.length; i < il; i++) {
			infoInputs[i].onkeypress = enterCheck;
		}
	}

	// Check whether browser has classList support
	if (document.documentElement.classList) {
		// If so, wrap relevant functions
		// classList.contains () method
		var containsClass = function (element, className) {
			return element.classList.contains (className);
		};

		// classList.add () method
		var addClass = function (element, className) {
			element.classList.add (className);
		};

		// classList.remove () method
		var removeClass = function (element, className) {
			element.classList.remove (className);
		};
	} else {
		// If not, define fallback functions
		// classList.contains () method
		var containsClass = function (element, className) {
			if (!element || !element.className) {
				return false;
			}

			var regex = new RegExp ('(^|\\s)' + className + '(\\s|$)');
			return element.className.match (regex);
		};

		// classList.add () method
		var addClass = function (element, className) {
			if (!element) {
				return false;
			}

			if (!containsClass (element, className)) {
				element.className += (element.className ? ' ' : '') + className;
			}
		};

		// classList.remove () method
		var removeClass = function (element, className) {
			if (!element || !element.className) {
				return false;
			}

			var regex = new RegExp ('(^|\\s)' + className + '(\\s|$)', 'g');
			element.className = element.className.replace (regex, '$2');
		};
	}

	// Handle message element(s)
	function showMessage (message, permalink, error, isReply, isEdit)
	{
		var permalink = permalink || '';
		var error = error || true;
		var isReply = isReply || false;
		var isEdit = isEdit || false;
		var element;

		// Decide which message element to use
		if (isEdit === true) {
			element = getElement ('hashover-edit-message-' + permalink, true);
		} else {
			if (isReply !== true) {
				element = getElement ('hashover-message', true);
			} else {
				element = getElement ('hashover-reply-message-' + permalink, true);
			}
		}

		if (message) {
			// Add message text to element
			element.textContent = message;

			// Add class to indicate message is an error if set
			if (error) {
				addClass (element, 'hashover-message-error');
			}
		}

		// Add class to indicate message element is open
		addClass (element, 'hashover-message-open');

		// Add the comment to message counts
		if (messageCounts[permalink] === undefined) {
			messageCounts[permalink] = 0;
		}

		// Add timeout to close message element after 10 seconds
		setTimeout (function () {
			if (messageCounts[permalink] <= 1) {
				removeClass (element, 'hashover-message-open');
				removeClass (element, 'hashover-message-error');
			}

			// Decrease count of open message timeouts
			messageCounts[permalink]--;
		}, 10000);

		// Increase count of open message timeouts
		messageCounts[permalink]++;
	}

	// Handles display of various warnings when user attempts to post or login
	function emailValidator (form, subscribe, permalink, isReply, isEdit)
	{
		// Return true if user was uncheck the subscribe checkbox
		if (getElement (subscribe, true).checked === false) {
			form.email.value = '';
			return true;
		}

		// Whether the e-mail form is empty
		if (form.email.value === '') {
			// If so, warn the user that they won't receive reply notifications
			if (!confirm ('<?php echo $hashover->locales->locale ('no-email-warning', true); ?>')) {
				form.email.focus ();
				return false;
			}
		} else {
			var message;

			// If not, check if the e-mail is valid
			if (!form.email.value.match (/\S+@\S+/)) {
				message = '<?php echo $hashover->locales->locale ('invalid-email', true); ?>';
				showMessage (message, permalink, true, isReply, isEdit);
				form.email.focus ();

				return false;
			}
		}

		return true;
	}

	// Validate a comment form e-mail field
	function validateEmail (permalink, form, isReply, isEdit)
	{
		var permalink = permalink || null;
		var isReply = isReply || false;
		var isEdit = isEdit || false;

		var form;
		var subscribe;

		// Check whether comment is an edit
		if (isEdit === true) {
			// If it is, validate edit form e-mail
			subscribe = 'hashover-subscribe-' + permalink;
		} else {
			// If it is not, validate as primary or reply
			if (isReply !== true) {
				// Validate primary form e-mail
				subscribe = 'hashover-subscribe';
			} else {
				// Validate reply form e-mail
				subscribe = 'hashover-subscribe-' + permalink;
			}
		}

		// Validate form fields
		return emailValidator (form, subscribe, permalink, isReply, isEdit);
	}

	// Simplistic JavaScript port of sprintf function in C
	function sprintf (string, args)
	{
		var string = string || '';
		var args = args || [];
		var count = 0;

		return string.replace (/%([cdfs])/g, function (match, type) {
			if (args[count] === undefined) {
				return match;
			}

			switch (type) {
				case 'c': {
					return args[count++][0];
				}

				case 'd': {
					return parseInt (args[count++]);
				}

				case 'f': {
					return parseFloat (args[count++]);
				}

				case 's': {
					return args[count++];
				}
			}
		});
	}

	// Validate a comment form
	function commentValidator (form, skipComment)
	{
		var skipComment = skipComment || false;
		var fieldNeeded = '<?php echo $hashover->locales->locale ('field-needed', true); ?>';

		// Check each input field for if they are required
		for (var field in fieldOptions) {
			// Skip other people's prototypes
			if (fieldOptions.hasOwnProperty (field) !== true) {
				continue;
			}

			// Check if the field is required, and that the input exists
			if (fieldOptions[field] === 'required' && form[field] !== undefined) {
				// Check if it has a value
				if (form[field].value === '') {
					// If not, add a class indicating a failed post
					addClass (form[field], 'hashover-emphasized-input');

					// Error message to display to the user
					fieldNeeded = sprintf (fieldNeeded, [ locale[field].toLowerCase () ]);

					// Focus the input
					form[field].focus ();

					// Return message in proper case
					return fieldNeeded[0].toUpperCase () + fieldNeeded.slice (1);
				}

				// Remove class indicating a failed post
				removeClass (form[field], 'hashover-emphasized-input');
			}
		}

		// Check if a comment was given
		if (skipComment !== true && form.comment.value === '') {
			// If not, add a class indicating a failed post
			addClass (form.comment, 'hashover-emphasized-input');

			// Focus the comment textarea
			form.comment.focus ();

			// Return a error message to display to the user
			return '<?php echo $hashover->locales->locale ('comment-needed', true); ?>';
		}

		return true;
	}

	// Validate required comment credentials
	function validateComment (skipComment, form, permalink, isReply, isEdit)
	{
		var skipComment = skipComment || false;
		var permalink = permalink || null;
		var isReply = isReply || false;
		var isEdit = isEdit || false;

		// Validate comment form
		var message = commentValidator (form, skipComment);

		// Display the validator's message
		if (message !== true) {
			showMessage (message, permalink, true, isReply, isEdit);
			return false;
		}

		// Validate e-mail if user isn't logged in or is editing
		if (userIsLoggedIn === false || isEdit === true) {
			// Return false on any failure
			if (validateEmail (permalink, form, isReply, isEdit) === false) {
				return false;
			}
		}

		return true;
	}

	// For posting comments, both traditionally and via AJAX
	function postComment (destination, form, button, callback, permalink, close, isReply, isEdit)
	{
		var permalink = permalink || '';
		var close = close || null;
		var isReply = isReply || false;
		var isEdit = isEdit || false;

		// Return false if comment is invalid
		if (validateComment (false, form, permalink, isReply, isEdit) === false) {
			return false;
		}

		// Disable button
		setTimeout (function () {
			button.disabled = true;
		}, 500);

<?php if ($hashover->setup->usesAJAX !== false) { ?>
		var httpRequest = new XMLHttpRequest ();
		var formElements = form.elements;
		var elementsLength = formElements.length;
		var queries = [];

		// Get all form input names and values
		for (var i = 0; i < elementsLength; i++) {
			// Skip login/logout input
			if (formElements[i].name === 'login' || formElements[i].name === 'logout') {
				continue;
			}

			// Skip unchecked checkboxes
			if (formElements[i].type === 'checkbox' && formElements[i].checked !== true) {
				continue;
			}

			// Skip delete input
			if (formElements[i].name === 'delete') {
				continue;
			}

			// Add query to queries array
			queries.push (formElements[i].name + '=' + encodeURIComponent (formElements[i].value));
		}

		// Add AJAX query to queries array
		queries.push ('ajax=yes');

		// Handle AJAX request return data
		httpRequest.onload = function () {
			// Do nothing if request wasn't successful in a meaningful way
			if (this.readyState !== 4 || this.status !== 200) {
				return;
			}

			// Parse AJAX response as JSON
			var json = JSON.parse (this.responseText);
			var scrollToElement;

			// Check if JSON includes a comment
			if (json.comment) {
				// If so, execute callback function
				callback (json, permalink, destination);

				// Execute callback function if one was provided
				if (close !== null) {
					close ();
				}

				// Scroll comment into view
				scrollToElement = getElement (json.comment.permalink, true);
				scrollToElement.scrollIntoView ({'behavior': 'smooth'});

				// Clear form
				form.comment.value = '';
			} else {
				// If not, display the message return instead
				showMessage (json.message, permalink, (json.type === 'error'), isReply, isEdit);
				return false;
			}

			// Re-enable button on success
			setTimeout (function () {
				button.disabled = false;
			}, 1000);
		};

		// Send request
		httpRequest.open ('POST', form.action, true);
		httpRequest.setRequestHeader ('Content-type', 'application/x-www-form-urlencoded');
		httpRequest.send (queries.join ('&'));

		// Re-enable button after 20 seconds
		setTimeout (function () {
			// Abort unfinish request
			httpRequest.abort ();

			// Re-enable button
			button.disabled = false;
		}, 20000);

		return false;
<?php } else { ?>
		// Re-enable button after 20 seconds
		setTimeout (function () {
			button.disabled = false;
		}, 20000);

		return true;
<?php } ?>
	}

	// Find a comment by its permalink
	function findByPermalink (permalink, comments)
	{
		// Default return value is false
		var comment = false;

		// Loop through all comments
		for (var i = 0, il = comments.length; i < il; i++) {
			// Return comment if its permalink matches
			if (comments[i].permalink === permalink) {
				return comments[i];
			}

			// Recursively check replies when present
			if (comments[i].replies) {
				comment = findByPermalink (permalink, comments[i].replies);
			}
		}

		// Return comment or false
		return comment;
	}

<?php if ($hashover->setup->usesAJAX !== false) { ?>
	// Converts an HTML string to DOM NodeList
	function HTMLToNodeList (html)
	{
		var div = document.createElement ('div');
		    div.innerHTML = html;

		return div.childNodes;
	}

	// Increase comment counts
	function incrementCounts (isReply)
	{
		// Count top level comments
		if (isReply === false) {
			primaryCount++;
		}

		// Increase all count
		totalCount++;
	}

	// For adding new comments to comments array
	function addComments (comment, isReply, index)
	{
		var isReply = isReply || false;
		var index = index || null;

		// Check that comment is not a reply
		if (isReply !== true) {
			// If so, add to primary comments
			if (index !== null) {
				PHPContent.comments.splice (index, 0, comment);
				return;
			}

			PHPContent.comments.push (comment);
			return;
		}

		// If not, fetch parent comment
		parent = findParent (comment.permalink);

		// Check if comment has replies
		if (parent.replies) {
			// If so, add comment to reply array
			if (index !== null) {
				parent.replies.splice (index, 0, comment);
				return;
			}

			parent.replies.push (comment);
			return;
		}

		// If not, create reply array
		parent.replies = [comment];
	}

	// For posting comments
	AJAXPost = function (json, permalink, destination)
	{
		var parent = null;
		var isReply = (json.comment.permalink.indexOf ('r') > -1);
		var commentNode;

		// If there aren't any comments, replace first comment message
		if (totalCount === 0) {
			PHPContent.comments[0] = json.comment;
			destination.innerHTML = parseComment (json.comment);
		} else {
			// Add comment to comments array
			addComments (json.comment, isReply);

			// Create div element for comment
			commentNode = HTMLToNodeList (parseComment (json.comment, parent));

			// Append comment to parent element
			if (streamMode === true && permalink.split('r').length > streamDepth) {
				destination.parentNode.insertBefore (commentNode[0], destination.nextSibling);
			} else {
				destination.appendChild (commentNode[0]);
			}
		}

		// Add controls to the new comment
		addControls (json.comment);

		// Update comment count
		getElement ('hashover-count').textContent = json.count;
		incrementCounts (isReply);
	}

	// For editing comments
	AJAXEdit = function (json, permalink, destination)
	{
		// Get old comment element nodes
		var comment = getElement (permalink, true);
		var oldNodes = comment.childNodes;
		var oldComment = findByPermalink (permalink, PHPContent.comments);

		// Get new comment element nodes
		var newNodes = HTMLToNodeList (parseComment (json.comment));
		    newNodes = newNodes[0].childNodes;

		// Replace old comment with edited comment
		for (var i = 0, il = newNodes.length; i < il; i++) {
			if (typeof (oldNodes[i]) === 'object'
			    && typeof (newNodes[i]) === 'object')
			{
				comment.replaceChild (newNodes[i], oldNodes[i]);
			}
		}

		// Add controls back to the comment
		addControls (json.comment);

		// Update old in array comment with edited comment
		for (var attribute in json.comment) {
			oldComment[attribute] = json.comment[attribute];
		}
	}

<?php } ?>
	// Displays reply form
	function hashoverReply (permalink)
	{
		// Get reply link element
		var link = getElement ('hashover-reply-link-' + permalink, true);

		// Get file
		var file = reversePermalink (permalink);

		// Create reply form element
		var form = document.createElement ('form');
		    form.id = 'hashover-reply-' + permalink;
		    form.className = 'hashover-reply-form';
		    form.method = 'post';
		    form.action = httpScripts + '/postcomments.php';

<?php

		echo $hashover->html->asJSVar ($hashover->html->replyForm ('permalink', 'file'), 'formHTML', "\t\t");

?>

		// Place reply fields into form
		form.innerHTML = formHTML;

		// Prevent input submission
		preventSubmit (form);

		// Add form to page
		var replyForm = getElement ('hashover-placeholder-reply-form-' + permalink, true);
		    replyForm.appendChild (form);

		// Change "Reply" link to "Cancel" link
		cancelSwitcher ('reply', link, replyForm, permalink);

		// Attach event listeners to "Post Reply" button
		var postReply = getElement ('hashover-reply-post-' + permalink, true);

		// Get the element of comment being replied to
		var destination = getElement (permalink, true);

		// Onclick
		postReply.onclick = function () {
			return postComment (destination, form, this, AJAXPost, permalink, link.onclick, true, false);
		};

		// Onsubmit
		postReply.onsubmit = function () {
			return postComment (destination, form, this, AJAXPost, permalink, link.onclick, true, false);
		};

		// Focus comment field
		form.comment.focus ();

		return true;
	}

	// Displays edit form
	function hashoverEdit (comment)
	{
		if (comment['user-owned'] !== true) {
			return false;
		}

		// Get permalink from comment JSON object
		var permalink = comment.permalink;

		// Get edit link element
		var link = getElement ('hashover-edit-link-' + permalink, true);

		// Get file
		var file = reversePermalink (permalink);

		// Get name and website
		var name = comment.name || '';
		var website = comment.website || '';

		// Get and clean comment body
		var body = comment.body;
		    body = body.replace (linkRegex, '$1');

		// Create edit form element
		var form = document.createElement ('form');
		    form.id = 'hashover-edit-' + permalink;
		    form.className = 'hashover-edit-form';
		    form.method = 'post';
		    form.action = httpScripts + '/postcomments.php';

<?php

		echo $hashover->html->asJSVar ($hashover->html->editForm ('permalink', 'file', 'name', 'website', 'body'), 'formHTML', "\t\t");

?>

		// Place edit form fields into form
		form.innerHTML = formHTML;

		// Prevent input submission
		preventSubmit (form);

		// Add edit form to page
		var editForm = getElement ('hashover-placeholder-edit-form-' + permalink, true);
		    editForm.appendChild (form);

		// Uncheck subscribe checkbox if user isn't subscribed
		if (comment.subscribed !== true) {
			getElement ('hashover-subscribe-' + permalink, true).checked = null;
		}

		// Displays onClick confirmation dialog for comment deletion
		getElement ('hashover-edit-delete-' + permalink, true).onclick = function () {
			return confirm ('<?php echo $hashover->locales->locale ('delete-comment', true); ?>');
		};

		// Change "Edit" link to "Cancel" link
		cancelSwitcher ('edit', link, editForm, permalink);

		// Attach event listeners to "Save Edit" button
		var saveEdit = getElement ('hashover-edit-post-' + permalink, true);

		// Get the element of comment being replied to
		var destination = getElement (permalink, true);

		// Onclick
		saveEdit.onclick = function () {
			return postComment (destination, form, this, AJAXEdit, permalink, link.onclick, false, true);
		};

		// Onsubmit
		saveEdit.onsubmit = function () {
			return postComment (destination, form, this, AJAXEdit, permalink, link.onclick, false, true);
		};

		return false;
	}

	// Run all comments in array data through parseComment function
	function parseAll (comments, element, collapse, popular, sort, method)
	{
		var popular = popular || false;
		var sort = sort || false;
		var method = method || 'ascending';
		var commentHTML = '';

		// Parse every comment
		for (var comment = 0, total = comments.length; comment < total; comment++) {
			commentHTML += parseComment (comments[comment], null, collapse, sort, method, popular);
		}

		// Add comments to element's innerHTML
		if ('insertAdjacentHTML' in element) {
			element.insertAdjacentHTML ('beforeend', commentHTML);
		} else {
			element.innerHTML = commentHTML;
		}

		// Add control events
		for (var comment = 0, total = comments.length; comment < total; comment++) {
			addControls (comments[comment]);
		}
	}
<?php if ($hashover->setup->collapsesComments !== false) { ?>

	// For showing more comments, via AJAX or removing a class
	function hideMoreLink (finishedCallback)
	{
		var finishedCallback = finishedCallback || null;

		// Add class to hide the more hyperlink
		moreLink.className = 'hashover-hide-morelink';

		setTimeout (function () {
			// Remove the more hyperlink from page
			if (sortDiv.contains (moreLink)) {
				sortDiv.removeChild (moreLink);
			}

			// Show comment count and sort options
			getElement ('hashover-sort').style.display = '';
			getElement ('hashover-count').style.display = '';

			// Get each hidden comment element
			var collapsed = sortDiv.getElementsByClassName ('hashover-hidden');

			// Remove hidden comment class from each comment
			for (var i = collapsed.length - 1; i >= 0; i--) {
				removeClass (collapsed[i], 'hashover-hidden');
			}

			// Execute callback function
			if (finishedCallback !== null) {
				finishedCallback ();
			}

			showingMore = true;
		}, 350);
	}
<?php if ($hashover->setup->usesAJAX !== false) { ?>

	// Returns the permalink of a comment's parent
	function getParentPermalink (permalink)
	{
		var parent = permalink.split ('r');
		var length = parent.length - 1;

		// Limit depth if in stream mode
		if (streamMode === true) {
			length = Math.min (streamDepth, length);
		}

		// Remove child from permalink
		parent = parent.slice (0, length);

		// Return parent permalink as string
		return parent.join ('r');
	}

	// For appending new comments to the thread on page
	function appendComments (comments)
	{
		var comment;
		var isReply;
		var element;
		var parent;

		for (var i = 0, il = comments.length; i < il; i++) {
			// Skip existing comments
			if (findByPermalink (comments[i].permalink, PHPContent.comments) !== false) {
				// Check comment's replies
				if (comments[i].replies) {
					appendComments (comments[i].replies);
				}

				continue;
			}

			// Parse comment, convert HTML to DOM node
			comment = HTMLToNodeList (parseComment (comments[i], null, true));
			isReply = (comments[i].permalink.indexOf ('r') > -1);

			// Add comment to comments array
			addComments (comments[i], isReply, i);

			// Check that comment is not a reply
			if (isReply !== true) {
				// If so, append to primary comments
				element = moreDiv;
			} else {
				// If not, append to its parent's element
				parent = getParentPermalink (comments[i].permalink);
				element = getElement (parent, true);
			}

			// Otherwise append it to the primary element
			element.appendChild (comment[0]);

			// Add controls to the comment
			addControls (comments[i]);
		}
	}
<?php } ?>

	// onClick event for more button
	function showMoreComments (element, finishedCallback)
	{
		var finishedCallback = finishedCallback || null;

		// Do nothing if already showing all comments
		if (showingMore === true) {
			// Execute callback function
			if (finishedCallback !== null) {
				finishedCallback ();
			}

			return;
		}

<?php if ($hashover->setup->usesAJAX !== false) { ?>
		var httpRequest = new XMLHttpRequest ();
		var queries = ['url=' + encodeURIComponent (pageURL), 'start=' + collapseLimit, 'ajax=yes'];

		// Handle AJAX request return data
		httpRequest.onload = function () {
			// Do nothing if request wasn't successful in a meaningful way
			if (this.readyState !== 4 || this.status !== 200) {
				return;
			}

			// Parse AJAX response as JSON
			var json = JSON.parse (this.responseText);

			// Display the comments
			appendComments (json.comments);

			// Display most popular comments
			ifElement ('hashover-top-comments', function (topComments) {
				if (json.popularComments[0] !== undefined) {
					parseAll (json.popularComments, topComments, false, true);
				}
			});

			// Hide the more hyperlink and display the comments
			hideMoreLink (finishedCallback);
		}

		httpRequest.open ('POST', httpRoot + '/api/json.php', true);
		httpRequest.setRequestHeader ('Content-type', 'application/x-www-form-urlencoded');
		httpRequest.send (queries.join ('&'));
<?php } else { ?>
		// Hide the more hyperlink and display the comments
		hideMoreLink (finishedCallback);
<?php } ?>

		return false;
	}
<?php } ?>

	// Add various events to various elements in each comment
	function addControls (json, popular)
	{
		if (json.notice) {
			return false;
		}

		// Get permalink from JSON object
		var permalink = json.permalink;

		// Get embedded image elements
		var embeddedImgs = document.getElementsByClassName ('hashover-imgtag');

		// Set onclick functions for external images
		for (var i = 0, il = embeddedImgs.length; i < il; i++) {
			embeddedImgs[i].onclick = embeddedImageCallback;
		}

<?php if ($hashover->setup->collapsesComments !== false) { ?>
		// Get thread link of comment
		ifElement ('hashover-thread-link-' + permalink, function (threadLink) {
			// Add onClick event to thread hyperlink
			threadLink.onclick = function () {
				showMoreComments (moreLink, function () {
					var parentThread = permalink.replace (threadRegex, '$1');
					var scrollToElement = getElement (parentThread);

					// Scroll to the comment
					scrollToElement.scrollIntoView ({'behavior': 'smooth'});
				});

				return false;
			};
		});

<?php } ?>
		// Get reply link of comment
		ifElement ('hashover-reply-link-' + permalink, function (replyLink) {
			// Add onClick event to "Reply" hyperlink
			replyLink.onclick = function () {
				hashoverReply (permalink);
				return false;
			};
		});

		// Check if logged in user owns the comment
		if (json['user-owned'] === true) {
			ifElement ('hashover-edit-link-' + permalink, function (editLink) {
				// Add onClick event to "Edit" hyperlinks
				editLink.onclick = function () {
					hashoverEdit (json);
					return false;
				};
			});
		} else {
			ifElement ('hashover-like-' + permalink, function (likeLink) {
				// Add onClick event to "Like" hyperlinks
				likeLink.onclick = function () {
					likeComment ('like', permalink);
					return false;
				};

				if (containsClass (likeLink, 'hashover-liked')) {
					mouseOverChanger (likeLink, locale.unlike, locale.liked);
				}
			});
<?php if ($hashover->setup->allowsDislikes === true) { ?>

			ifElement ('hashover-dislike-' + permalink, function (dislikeLink) {
				// Add onClick event to "Dislike" hyperlinks
				dislikeLink.onclick = function () {
					likeComment ('dislike', permalink);
					return false;
				};
			});
<?php } ?>
		}

		// Recursively execute this function on replies
		if (json.replies) {
			for (var reply = 0, total = json.replies.length; reply < total; reply++) {
				addControls (json.replies[reply]);
			}
		}
	}

	// Changes Element.textContent onmouseover and reverts onmouseout
	function mouseOverChanger (element, over, out)
	{
		if (over === null || out === null) {
			element.onmouseover = null;
			element.onmouseout = null;

			return false;
		}

		element.onmouseover = function () {
			this.textContent = over;
		};

		element.onmouseout = function () {
			this.textContent = out;
		};
	}

	// Onclick callback function for embedded images
	function embeddedImageCallback ()
	{
		if (this.src === this.dataset.url) {
			this.src = this.dataset.placeholder;
			this.title = 'Click to view external image';

			return false;
		}

		this.src = this.dataset.url;
		this.title = 'Loading...';

		this.onload = function () {
			this.title = 'Click to close';
			this.onload = null;
		};
	}

	// "Flatten" the comments object
	function getAllComments (comments)
	{
		var commentsCopy = JSON.parse (JSON.stringify (comments));
		var output = [];

		function descend (comment)
		{
			output.push (comment);

			if (comment.replies) {
				for (var reply = 0, total = comment.replies.length; reply < total; reply++) {
					descend (comment.replies[reply]);
				}

				delete comment.replies;
			}
		}

		for (var comment = 0, total = commentsCopy.length; comment < total; comment++) {
			descend (commentsCopy[comment]);
		}

		return output;
	}

	// For liking comments
	function likeComment (action, permalink)
	{
		// Get file
		var file = reversePermalink (permalink);

		var actionLink = getElement ('hashover-' + action + '-' + permalink, true);
		var likesElement = getElement ('hashover-' + action + 's-' + permalink, true);
		var dislikesClass = (action === 'like') ? '<?php if ($hashover->setup->allowsDislikes === true) echo ' hashover-dislikes-enabled'; ?>' : '';

		// Load "like.php"
		var like = new XMLHttpRequest ();
		var queries;

		// When loaded update like count
		like.onload = function () {
			var likes = 0;

			// Get number of likes
			if (likesElement.textContent !== '') {
				var likes = parseInt (likesElement.textContent.split (' ')[0]);
			}

			// Change "Like" button title and class
			if (actionLink.className === 'hashover-' + action + dislikesClass) {
				// Change class to indicate the comment has been liked/disliked
				actionLink.className = 'hashover-' + action + 'd' + dislikesClass;
				actionLink.title = (action === 'like') ? locale.likedComment : locale.dislikedComment;
				actionLink.textContent = (action === 'like') ? locale.liked : locale.disliked;

				if (action === 'like') {
					mouseOverChanger (actionLink, locale.unlike, locale.liked);
				}

				// Increase likes
				likes++;
			} else {
				// Change class to indicate the comment is unliked
				actionLink.className = 'hashover-' + action + dislikesClass;
				actionLink.title = (action === 'like') ? locale.likeComment : locale.dislikeComment;
				actionLink.textContent = (action === 'like') ? locale.like[0] : locale.dislike[0];

				if (action === 'like') {
					mouseOverChanger (actionLink, null, null);
				}

				// Decrease likes
				likes--;
			}

			if (action === 'like') {
				var likeCount = likes + ' ' + locale.like[(likes !== 1 ? 1 : 0)];
			} else {
				var likeCount = likes + ' ' + locale.dislike[(likes !== 1 ? 1 : 0)];
			}

			// Change number of likes
			likesElement.style.fontWeight = 'bold';
			likesElement.textContent = (likes > 0) ? likeCount : '';
		};

		// Set request queries
		queries  = 'url=' + encodeURIComponent (pageURL);
		queries += '&thread=<?php echo $hashover->setup->threadDirectory; ?>';
		queries += '&like=' + file;
		queries += '&action=' + action;

		// Send request
		like.open ('POST', httpScripts + '/like.php', true);
		like.setRequestHeader ('Content-type', 'application/x-www-form-urlencoded');
		like.send (queries);
	}

	// Sort methods
	var sortMethods = {
		ascending: function () {
			parseAll (PHPContent.comments, sortDiv, false, false, true, 'ascending');
		},

		descending: function () {
			var tmpArray = getAllComments (PHPContent.comments);

			var tmpSortArray = Object.keys (tmpArray).map (function (key) {
				return tmpArray[key];
			});

			parseAll (tmpSortArray.reverse (), sortDiv, false, false, true, 'descending');
		},

		byName: function () {
			var tmpArray = getAllComments (PHPContent.comments);

			var tmpSortArray = Object.keys (tmpArray).map (function (key) {
				return tmpArray[key];
			});

			tmpSortArray = tmpSortArray.sort (function (a, b) {
				return (a.name > b.name);
			});

			parseAll (tmpSortArray, sortDiv, false, false, true, 'byName');
		},

		byDate: function () {
			var tmpSortArray = getAllComments (PHPContent.comments).sort (function (a, b) {
				if (a['sort-date'] === b['sort-date']) {
					return 1;
				}

				return b['sort-date'] - a['sort-date'];
			});

			parseAll (tmpSortArray, sortDiv, false, false, true, 'byDate');
		},

		byLikes: function () {
			var tmpSortArray = getAllComments (PHPContent.comments).sort (function (a, b) {
				a.likes = a.likes || 0;
				b.likes = b.likes || 0;
				a.dislikes = a.dislikes || 0;
				b.dislikes = b.dislikes || 0;

				return (b.likes - b.dislikes) - (a.likes - a.dislikes);
			});

			parseAll (tmpSortArray, sortDiv, false, false, true, 'byLikes');
		},

		threadedDescending: function () {
			var tmpSortArray = Object.keys (PHPContent.comments).map (function (key) {
				return PHPContent.comments[key];
			});

			parseAll (tmpSortArray.reverse (), sortDiv, false, false, true, 'threadedDescending');
		},

		threadedByName: function () {
			var tmpSortArray = Object.keys (PHPContent.comments).map (function (key) {
				return PHPContent.comments[key];
			});

			tmpSortArray = tmpSortArray.sort (function (a, b) {
				return (a.name > b.name);
			});

			parseAll (tmpSortArray, sortDiv, false, false, true, 'threadedByName');
		},

		threadedByDate: function () {
			var tmpSortArray = Object.keys (PHPContent.comments).map (function (key) {
				return PHPContent.comments[key];
			});

			tmpSortArray = tmpSortArray.sort (function (a, b) {
				if (a['sort-date'] === b['sort-date']) {
					return 1;
				}

				return b['sort-date'] - a['sort-date'];
			});

			parseAll (tmpSortArray, sortDiv, false, false, true, 'threadedByDate');
		},

		threadedByLikes: function () {
			var tmpSortArray = Object.keys (PHPContent.comments).map (function (key) {
				return PHPContent.comments[key];
			});

			tmpSortArray = tmpSortArray.sort (function (a, b) {
				a.likes = a.likes || 0;
				b.likes = b.likes || 0;
				a.dislikes = a.dislikes || 0;
				b.dislikes = b.dislikes || 0;

				return (b.likes - b.dislikes) - (a.likes - a.dislikes);
			});

			parseAll (tmpSortArray, sortDiv, false, false, true, 'threadedByLikes');
		}
	};

<?php if ($hashover->setup->appendsCSS === true) { ?>
	// Check if comment theme stylesheet is already in page head
	if (typeof (document.querySelector) === 'function') {
		appendCSS = !document.querySelector ('link[href="' + themeCSS + '"]');
	} else {
		// Fallback for old web browsers without querySelector
		var links = head.getElementsByTagName ('link');

		for (var i = 0, il = links.length; i < il; i++) {
			if (links[i].getAttribute ('href') === themeCSS) {
				appendCSS = false;
				break;
			}
		}
	}

	// Create link element for comment stylesheet
	if (appendCSS) {
		var css = document.createElement ('link');
		    css.rel = 'stylesheet';
		    css.href = themeCSS;
		    css.type = 'text/css';

		// Append comment stylesheet link element to page head
		head.appendChild (css);
	}

<?php } ?>
	// Put number of comments into "hashover-comment-count" identified HTML element
	if (totalCount !== 0) {
		if (getElement ('hashover-comment-count')) {
			getElement ('hashover-comment-count').textContent = totalCount;
		}
<?php if ($hashover->setup->APIStatus ('rss') !== 'disabled') { ?>

		// Create link element for comment RSS feed
		var rss = document.createElement ('link');
		    rss.rel = 'alternate';
		    rss.href = httpRoot + '/api/rss.php?url=' + encodeURIComponent (URLHref);
		    rss.type = 'application/rss+xml';
		    rss.title = 'Comments';

		// Append comment RSS feed link element to page head
		head.appendChild (rss);
<?php } ?>
	}

	// Initial HTML
<?php

	echo $hashover->html->asJSVar ($hashover->html->initialHTML (false), 'initialHTML');

?>

	// Create div tag for HashOver comments to appear in
	if (HashOverDiv === null) {
		HashOverDiv = document.createElement ('div');
		HashOverDiv.id = 'hashover';

		// Place HashOver element on page
		if (hashoverScript !== false) {
			var thisScript = getElement ('hashover-script-' + hashoverScript);
			    thisScript.parentNode.insertBefore (HashOverDiv, thisScript);
		} else {
			document.body.appendChild (HashOverDiv);
		}
	}

	// Add class for differentiating desktop and mobile styling
	HashOverDiv.className = 'hashover-' + deviceType;

	// Add class to indicate user login status
	if (userIsLoggedIn) {
		addClass (HashOverDiv, 'hashover-logged-in');
	} else {
		addClass (HashOverDiv, 'hashover-logged-out');
	}

	// Add initial HTML to page
	if ('insertAdjacentHTML' in HashOverDiv) {
		HashOverDiv.insertAdjacentHTML ('beforeend', initialHTML);
	} else {
		HashOverDiv.innerHTML = initialHTML;
	}

	// Content passed from PHP
	var PHPContent = HASHOVER_PHP_CONTENT;

	// Display most popular comments
	ifElement ('hashover-top-comments', function (topComments) {
		if (PHPContent.popularComments[0] !== undefined) {
			parseAll (PHPContent.popularComments, topComments, false, true);
		}
	});

	// Get sort div element
	sortDiv = getElement ('hashover-sort-div');

	// Get primary form element
	HashOverForm = getElement ('hashover-form');

	// Add initial event handlers
	parseAll (PHPContent.comments, sortDiv);

<?php if ($hashover->setup->collapsesComments !== false) { ?>
	// Check whether there are more than the collapse limit
	if (totalCount > collapseLimit) {
		// Create element for the comments
		moreDiv = document.createElement ('div');
		moreDiv.id = 'hashover-more-section';

		// If so, create "More Comments" hyperlink
		moreLink = document.createElement ('a');
		moreLink.href = '#';
		moreLink.id = 'hashover-more-link';
		moreLink.textContent = '<?php echo $collapse_link_text; ?>';

		// Add onClick event to more button
		moreLink.onclick = function () {
			return showMoreComments (this);
		};

		// Add more button link to sort div
		sortDiv.appendChild (moreDiv);

		// Add more button link to sort div
		sortDiv.appendChild (moreLink);
	} else {
		// If not, consider all comment shown
		showingMore = true;
	}

<?php } ?>
	// Attach event listeners to "Post Comment" button
	var postButton = getElement ('hashover-post-button');

	// Onclick
	postButton.onclick = function () {
		return postComment (sortDiv, HashOverForm, postButton, AJAXPost);
	};

	// Onsubmit
	postButton.onsubmit = function () {
		return postComment (sortDiv, HashOverForm, postButton, AJAXPost);
	};

<?php if ($hashover->setup->allowsLogin === true) { ?>
	// Attach event listeners to "Login" button
	if (!userIsLoggedIn) {
		var loginButton = getElement ('hashover-login-button');

		// Onclick
		loginButton.onclick = function () {
			return validateComment (true, HashOverForm);
		};

		// Onsubmit
		loginButton.onsubmit = function () {
			return validateComment (true, HashOverForm);
		};
	}

<?php } ?>
	// Five method sort
	ifElement ('hashover-sort-select', function (sortSelect) {
		sortSelect.onchange = function () {
<?php if ($hashover->setup->collapsesComments !== false) { ?>
			showMoreComments (sortSelect, function () {
				sortDiv.textContent = '';
				sortMethods[sortSelect.value] ();
			});
<?php } else { ?>
			sortDiv.textContent = '';
			sortMethods[sortSelect.value] ();
<?php } ?>
		};
	});

	// Display reply or edit form when the proper URL queries are set
	if (URLHref.match (/hashover-(reply|edit)=/)) {
		var permalink = URLHref.replace (/.*?hashover-(edit|reply)=(c[0-9r\-pop]+).*?/, '$2');

		if (!URLHref.match ('hashover-edit=')) {
			// Reply form
<?php if ($hashover->setup->collapsesComments !== false) { ?>
			showMoreComments (moreLink, function () {
				hashoverReply (permalink);
			});
<?php } else { ?>
			hashoverReply (permalink);
<?php } ?>
		} else {
			var isPop = permalink.match ('-pop');
			var comments = (isPop) ? PHPContent.popularComments : PHPContent.comments;
<?php if ($hashover->setup->collapsesComments !== false) { ?>

			showMoreComments (moreLink, function () {
				var comment = findByPermalink (permalink, comments);

				// Edit form
				hashoverEdit (comment);
			});
<?php } else { ?>
			var comment = findByPermalink (permalink, comments);

			// Edit form
			hashoverEdit (comment);
<?php } ?>
		}
	}

	// Log execution time in JavaScript console
	if (window.console) {
		console.log ('HashOver executed in ' + (Date.now () - execStart) + ' ms.');
	}

	// Workaround for stupid Chrome bug
	var scroller = function () {
		setTimeout (function () {
			if (URLHash.match (/comments|hashover/)) {
				ifElement (URLHash, function (comments) {
					comments.scrollIntoView ({'behavior': 'smooth'});
				});
			}

			// Jump to linked comment
			if (URLHash.match (/c[0-9]+r*/)) {
<?php if ($hashover->setup->collapsesComments !== false) { ?>
				var existingComment = getElement (URLHash);

				// Check if comment exists on the page and is visable
				if (existingComment !== null
				    && containsClass (existingComment, 'hashover-hidden') === false)
				{
					// If so, scroll the comment into view
					existingComment.scrollIntoView ({'behavior': 'smooth'});
				} else {
					// If not, show more comments
					showMoreComments (moreLink, function () {
						ifElement (URLHash, function (comment) {
							comment.scrollIntoView ({'behavior': 'smooth'});
						});
					});
				}
<?php } else { ?>
				ifElement (URLHash, function (comment) {
					comment.scrollIntoView ({'behavior': 'smooth'});
				});
<?php } ?>
			}
		}, 500);
	};

	// Compatibility wrapper
	if (window.addEventListener) {
		// Rest of the world
		window.addEventListener ('load', scroller, false);
	} else {
		// IE ~8
		window.attachEvent ('onload', scroller);
	}

	// Open the message element if there's a message
	if (getElement ('hashover-message').textContent !== '') {
		showMessage ();
	}
}) ();
