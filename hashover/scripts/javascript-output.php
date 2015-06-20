<?php

	// Copyright (C) 2010-2015 Jacob Barkdull
	//
	//	This file is part of HashOver.
	//
	//	HashOver is free software: you can redistribute it and/or modify
	//	it under the terms of the GNU Affero General Public License as
	//	published by the Free Software Foundation, either version 3 of the
	//	License, or (at your option) any later version.
	//
	//	HashOver is distributed in the hope that it will be useful,
	//	but WITHOUT ANY WARRANTY; without even the implied warranty of
	//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	//	GNU Affero General Public License for more details.
	//
	//	You should have received a copy of the GNU Affero General Public License
	//	along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


	// Display source code
	if (basename ($_SERVER['PHP_SELF']) === basename (__FILE__)) {
		if (isset ($_GET['source'])) {
			header ('Content-type: text/plain; charset=UTF-8');
			exit (file_get_contents (basename (__FILE__)));
		}
	}

	// Text for "Show X Other Comment(s)" link
	if ($hashover->settings->collapseLimit >= 1) {
		$collapse_num_cmts = ($hashover->readComments->totalCount - 1) - $hashover->settings->collapseLimit;
		$collapse_link_plural = ($collapse_num_cmts !== 1) ? 1 : 0;
		$collapse_link_text = $hashover->locales->locale['other_cmts'][$collapse_link_plural];
		$collapse_link_text = str_replace ('_NUM_', $collapse_num_cmts, $collapse_link_text);
	} else {
		$collapse_link_plural = ($hashover->readComments->totalCount !== 1) ? 1 : 0;
		$collapse_link_text = $hashover->locales->locale['show_num_cmts'][$collapse_link_plural];
		$collapse_link_text = str_replace ('_NUM_', $hashover->readComments->totalCount - 1, $collapse_link_text);
	}

	// Some locale plural arrays
	$like_locale = $hashover->locales->locale ('like', true);
	$dislike_locale = $hashover->locales->locale ('dislike', true);

?>
// Copyright (C) 2010-2015 Jacob Barkdull
//
//	This file is part of HashOver.
//
//	HashOver is free software: you can redistribute it and/or modify
//	it under the terms of the GNU Affero General Public License as
//	published by the Free Software Foundation, either version 3 of the
//	License, or (at your option) any later version.
//
//	HashOver is distributed in the hope that it will be useful,
//	but WITHOUT ANY WARRANTY; without even the implied warranty of
//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//	GNU Affero General Public License for more details.
//
//	You should have received a copy of the GNU Affero General Public License
//	along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


(function () {
	"use strict";

	var execStart		= Date.now ();
	var hashoverScript	= <?php echo !empty ($_GET['hashover-script']) ? $_GET['hashover-script'] : 'false'; ?>;
	var deviceType		= '<?php echo $hashover->settings->isMobile ? 'mobile' : 'desktop'; ?>';
	var userIsLoggedIn	= <?php echo $hashover->setup->userIsLoggedIn ? 'true' : 'false'; ?>;
	var totalCount		= <?php echo $hashover->readComments->totalCount - 1; ?>;
	var collapseComments	= <?php echo $hashover->settings->collapsesComments ? 'true' : 'false'; ?>;
	var collapseLimit	= <?php echo $hashover->settings->collapseLimit; ?>;
	var collapsedCount	= 0;
	var allowsDislikes	= <?php echo $hashover->settings->allowsDislikes ? 'true' : 'false'; ?>;
	var serverEOL		= '<?php echo str_replace (array ("\r", "\n"), array ('\r', '\n'), PHP_EOL); ?>';
	var URLParts		= window.location.href.split ('#');
	var URLHref		= URLParts[0];
	var URLHash		= URLParts[1] || '';
	var URLJumps		= URLHash.match (/hashover-(edit|reply)|c[0-9r]+/);
	var HashOverDiv		= document.getElementById ('hashover');
	var head		= document.head || document.getElementsByTagName ('head')[0];
	var elementsById	= {};
	var httpRoot		= '<?php echo $hashover->setup->httpDirectory; ?>';
	var imagePlaceholder	= httpRoot + '/images/<?php echo $hashover->settings->imageFormat, 's/place-holder.', $hashover->settings->imageFormat; ?>';
	var imageExtensions	= ['<?php echo implode ('\', \'', $hashover->settings->imageTypes); ?>'];
	var URLRegex		= '((ftp|http|https):\/\/[a-z0-9-@:%_\+.~#?&\/=]+)';
	var linkRegex		= new RegExp (URLRegex + '( {0,1})', 'ig');
	var imageRegex		= new RegExp ('\\[img\\]<a.*?>' + URLRegex + '</a>\\[/img\\]', 'ig');
	var codeOpenRegex	= /<code>/i;
	var codeTagRegex	= /(<code>)([\s\S]*?)(<\/code>)/ig;
	var codeTagMarkerRegex	= /CODE_TAG\[([0-9]+)\]/g;
	var preOpenRegex	= /<pre>/i;
	var preTagRegex		= /(<pre>)([\s\S]*?)(<\/pre>)/ig;
	var preTagMarkerRegex	= /PRE_TAG\[([0-9]+)\]/g;
	var threadRegex		= /^(c[0-9r]+)r[0-9]+$/;
	var lineRegex		= new RegExp (serverEOL, 'g');
	var themeCSS		= httpRoot + '/themes/<?php echo $hashover->settings->theme; ?>/style.css';
	var appendCSS		= true;
	var messages		= 0;

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
		'cancel':	'<?php echo $hashover->locales->locale['cancel']; ?>',
		'like':		['<?php echo $like_locale[0] ?>', '<?php echo $like_locale[1]; ?>'],
		'liked':	'<?php echo $hashover->locales->locale ('liked', true); ?>',
		'unlike':	'<?php echo $hashover->locales->locale ('unlike', true); ?>',
		'likeCmt':	'<?php echo $hashover->locales->locale ('like_cmt', true); ?>',
		'likedCmt':	'<?php echo $hashover->locales->locale ('liked_cmt', true); ?>',
		'dislike':	['<?php echo $dislike_locale[0]; ?>', '<?php echo $dislike_locale[1]; ?>'],
		'disliked':	'<?php echo $hashover->locales->locale ('disliked', true); ?>',
		'dislikeCmt':	'<?php echo $hashover->locales->locale ('dislike_cmt', true); ?>',
		'dislikedCmt':	'<?php echo $hashover->locales->locale ('disliked_cmt', true); ?>'
	};
<?php if ($hashover->settings->appendsCSS) { ?>

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

	// Put number of comments into "hashover-cmtcount" identified HTML element
	if (totalCount !== 0) {
		if ($('hashover-cmtcount')) {
			$('hashover-cmtcount').textContent = totalCount;
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

	function $ (element, force)
	{
		if (force) {
			return document.getElementById (element);
		}

		if (!elementsById[element]) {
			elementsById[element] = document.getElementById (element);
		}

		return elementsById[element];
	}

	function ifCallback (element, callback)
	{
		if (element) {
			return callback (element);
		}

		return false;
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
			var thisScript = $('hashover-script-' + hashoverScript);
			    thisScript.parentNode.insertBefore (HashOverDiv, thisScript);
		} else {
			document.body.appendChild (HashOverDiv);
		}
	}

	// Add class for differentiating desktop and mobile styling
	HashOverDiv.className = 'hashover-' + deviceType;

	// Add class to indicate user login status
	if (userIsLoggedIn) {
		HashOverDiv.classList.add ('hashover-logged-in');
	} else {
		HashOverDiv.classList.add ('hashover-logged-out');
	}

	// Add initial HTML to page
	if ('insertAdjacentHTML' in HashOverDiv) {
		HashOverDiv.insertAdjacentHTML ('beforeend', initialHTML);
	} else {
		HashOverDiv.innerHTML = initialHTML;
	}

	// Content passed from PHP
	var PHPContent = HASHOVER_PHP_CONTENT;

	// Trims whitespace from an HTML tag's inner HTML
	function tagTrimmer (fullTag, openTag, innerHTML, closeTag)
	{
		return openTag + innerHTML.trim (serverEOL) + closeTag;
	}

	// Add comment content to HTML template
	function parseComment (json, collapse, sort, method, forpop)
	{
		collapse = collapse || false;
		sort = sort || false;
		method = method || 'ascending';
		forpop = forpop || false;

		var permalink = json.permalink;
		var nameClass = 'hashover-name-plain';
		var template = {permalink: permalink};
		var is_reply = (permalink.indexOf ('r') > -1);
		var codeTagCount = 0;
		var codeTags = [];
		var preTagCount = 0;
		var preTags = [];
		var cmtclass = '';
		var replies = '';

		// Text for avatar image alt attribute
		var permatext = permalink.slice (1);
		    permatext = permatext.split ('r');
		    permatext = permatext.pop ();

		// Check if this comment is a popular comment
		if (forpop) {
			// Remove "_pop" from text for avatar
			permatext = permatext.replace ('_pop', '');
		} else {
			// Check if comment is a reply
			if (is_reply) {
				// Check that comments are being sorted
				if (!sort || method === 'ascending') {
					// Append class to indicate comment is a reply
					cmtclass = ' hashover-reply';
				}
			}

			if (collapse) {
				if (collapsedCount >= collapseLimit) {
					cmtclass += ' hashover-hidden';
				} else {
					collapsedCount++;
				}
			}
		}

		// Add avatar image to template
		template.avatar = '<?php echo $hashover->html->userAvatar ('permatext', 'permalink', 'httpRoot + json.avatar'); ?>';

		if (!json.notice) {
			var name = json.name || '<?php echo $hashover->settings->defaultName; ?>';
			var website = json.website;
			var is_twitter = false;

			// Check if user's name is a Twitter handle
			if (name.charAt (0) === '@') {
				name = name.slice (1);
				nameClass = 'hashover-name-twitter';
				is_twitter = true;
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
				if (is_twitter === false) {
					nameClass = 'hashover-name-website';
				}

				// If so, display name as a hyperlink
				var nameLink = '<?php echo $hashover->html->nameElement ('a', 'name', 'permalink', 'website'); ?>';
			} else {
				// If not, display name as plain text
				var nameLink = '<?php echo $hashover->html->nameElement ('span', 'name', 'permalink'); ?>';
			}

			// Add "Top of Thread" hyperlink to template
			if (is_reply) {
				var threadParent = permalink.replace (threadRegex, '$1');
				template.thread_link = '<?php echo $hashover->html->threadLink ('threadParent'); ?>';
			}

			if (json.user_owned) {
				// Define "Reply" link with original poster title
				var replyTitle = '<?php echo $hashover->locales->locale ('op_cmt_note', true); ?>';
				var replyClass = 'hashover-no-email';

				// Add "Reply" hyperlink to template
				template.edit_link = '<?php echo $hashover->html->editLink ('permalink'); ?>';
			} else {
				// Check if commenter is subscribed
				if (json.subscribed) {
					// If so, set subscribed title
					var replyTitle = json.name + ' <?php echo $hashover->locales->locale ('subbed_note', true); ?>';
					var replyClass = 'hashover-has-email';
				} else{
					// If not, set unsubscribed title
					var replyTitle = json.name + ' <?php echo $hashover->locales->locale ('unsubbed_note', true); ?>';
					var replyClass = 'hashover-no-email';
				}

				// Check whether this comment was liked by the visitor
				if (json.liked) {
					// If so, set various attributes to indicate comment was liked
					var likeClass = 'hashover-liked';
					var likeTitle = locale.likedCmt;
					var likeText = locale.liked;
				} else {
					// If not, set various attributes to indicate comment can be liked
					var likeClass = 'hashover-like';
					var likeTitle = locale.likeCmt;
					var likeText = locale.like[0];
				}

				// Append class to indicate dislikes are enabled
				if (allowsDislikes) {
					likeClass += ' hashover-dislikes-enabled';
				}

				// Add like link to HTML template
				template.like_link = '<?php echo $hashover->html->likeLink ('permalink', 'likeClass', 'likeTitle', 'likeText'); ?>';

<?php if ($hashover->settings->allowsDislikes) { ?>
				// Check whether this comment was disliked by the visitor
				if (json.disliked) {
					// If so, set various attributes to indicate comment was disliked
					var dislikeClass = 'hashover-disliked';
					var dislikeTitle = locale.dislikedCmt;
					var dislikeText = locale.disliked;
				} else {
					// If not, set various attributes to indicate comment can be disliked
					var dislikeClass = 'hashover-dislike';
					var dislikeTitle = locale.dislikeCmt;
					var dislikeText = locale.dislike[0];
				}

				// Add dislike link to HTML template
				template.dislike_link = '<?php echo $hashover->html->dislikeLink ('permalink', 'dislikeClass', 'dislikeTitle', 'dislikeText'); ?>';
<?php } ?>
			}

			// Get number of likes, append "Like(s)" locale
			if (json.likes) {
				var likeCount = json.likes + ' ' + locale.like[(json.likes === 1 ? 0 : 1)];
			}

			// Add like count to HTML template
			template.like_count = '<?php echo $hashover->html->likeCount ('permalink', '(likeCount || \'\')'); ?>';

<?php if ($hashover->settings->allowsDislikes) { ?>
			// Get number of dislikes, append "Dislike(s)" locale
			if (json.dislikes) {
				var dislikeCount = json.dislikes + ' ' + locale.dislike[(json.dislikes === 1 ? 0 : 1)];
			}

			// Add dislike count to HTML template
			template.dislike_count = '<?php echo $hashover->html->dislikeCount ('permalink', '(dislikeCount || \'\')'); ?>';

<?php } ?>
			// Add name HTML to template
			template.name = '<?php echo $hashover->html->nameWrapper ('nameLink', 'nameClass'); ?>';

			// Add date permalink hyperlink to template
			template.date = '<?php echo $hashover->html->dateLink ('permalink', 'json.date'); ?>';

			// Add "Reply" hyperlink to template
			template.reply_link = '<?php echo $hashover->html->replyLink ('permalink', 'replyClass', 'replyTitle'); ?>';

			// Add reply count to template
			if (json.replies) {
				template.reply_count = json.replies.length;

				if (template.reply_count > 0) {
					if (template.reply_count !== 1) {
						template.reply_count += ' <?php echo $hashover->locales->locale ('replies', true); ?>';
					} else {
						template.reply_count += ' <?php echo $hashover->locales->locale ('reply', true); ?>';
					}
				}
			}

			// Add HTML anchor tag to URLs
			var body = json.body.replace (linkRegex, '<a href="$1" target="_blank">$1</a>');

			// Replace [img] tags with external image placeholder if enabled
			body = body.replace (imageRegex, function (fullURL, url) {
<?php if ($hashover->settings->allowsImages) { ?>
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
					    imgtag.id = 'hashover-embedded-image-' + permalink;
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
				if (trimTagRegexes[trimTag]['test'].test (body)) {
					// Trim whitespace
					body = body.replace (trimTagRegexes[trimTag]['replace'], tagTrimmer);
				}
			}

			// Break comment into paragraphs
			var paragraphs = body.split (serverEOL + serverEOL);
			var pd_comment = '';

			// Wrap comment in paragraph tag
			// Replace single line breaks with break tags
			for (var i = 0, il = paragraphs.length; i < il; i++) {
				pd_comment += '<p>' + paragraphs[i].replace (lineRegex, '<br>') + '</p>';
			}

			// Replace code tag placeholders with original code tag HTML
			if (codeTagCount > 0) {
				pd_comment = pd_comment.replace (codeTagMarkerRegex, function (placeholder, number) {
					return codeTags[number];
				});
			}

			// Replace pre tag placeholders with original pre tag HTML
			if (preTagCount > 0) {
				pd_comment = pd_comment.replace (preTagMarkerRegex, function (placeholder, number) {
					return preTags[number];
				});
			}

			// Add comment data to template
			template.comment = pd_comment;
		} else {
			// Append notice class
			cmtclass += ' hashover-notice ' + json.notice_class;

			// Add notice to template
			template.comment = json.notice;

			// Add name HTML to template
			template.name = '<?php echo $hashover->html->nameWrapper ('json.title', 'nameClass'); ?>';
		}

		// Comment HTML template
<?php

		echo $hashover->html->asJSVar ($hashover->templater->parseTemplate (), 'comment', "\t\t");

?>

		// Recursively parse replies
		if (json.replies) {
			for (var reply in json.replies) {
				replies += parseComment (json.replies[reply], collapse);
			}
		}

		return '<?php echo $hashover->html->commentWrapper ('permalink', 'cmtclass', 'comment + replies'); ?>';
	}

	// Generate file from permalink
	function reversePermalink (permalink)
	{
		var file = permalink.slice (1);
		    file = file.replace (/r/g, '-');
		    file = file.replace ('_pop', '');

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
<?php if ($hashover->settings->usesCancelButtons) { ?>

		// Attach event listeners to "Cancel" button
		$('hashover-' + form + '-cancel-' + permalink, true).onclick = function () {
			// Remove fields from form wrapper
			wrapper.textContent = '';
			link.onclick ();

			return false;
		};
<?php } ?>
	}

	// Prevents enter key on inputs from submitting form
	function preventSubmit (form)
	{
		// Get login info inputs
		var infoInputs = form.getElementsByClassName ('hashover-input-info');

		// Set enter key press to return false
		for (var i = 0, il = infoInputs.length; i < il; i++) {
			infoInputs[i].onkeypress = function (event) {
				return (event.keyCode === 13) ? false : true;
			};
		}
	}

	// Displays reply form
	function hashoverReply (permalink)
	{
		// Get reply link element
		var link = $('hashover-reply-link-' + permalink, true);

		// Get file
		var file = reversePermalink (permalink);

		// Create reply form element
		var form = document.createElement ('form');
		    form.id = 'hashover-reply-' + permalink;
		    form.className = 'hashover-reply-form';
		    form.method = 'post';
		    form.action = httpRoot + '/scripts/javascript-mode.php';

<?php

		echo $hashover->html->asJSVar ($hashover->html->replyForm ('permalink', 'file'), 'formHTML', "\t\t");

?>

		// Place reply fields into form
		form.innerHTML = formHTML;

		// Prevent input submission
		preventSubmit (form)

		// Add form to page
		var reply_form = $('hashover-placeholder-reply_form-' + permalink, true);
		    reply_form.appendChild (form);

		// Focus comment field
		form.comment.focus ();

		// Attach event listeners to "Post Reply" button
		var postReply = $('hashover-reply-post-' + permalink, true);

		// Onclick
		postReply.onclick = function () {
			return validateComment (permalink, this);
		};

		// Onsubmit
		postReply.onsubmit = function () {
			return validateComment (permalink, this);
		};

		// Change "Reply" link to "Cancel" link
		cancelSwitcher ('reply', link, reply_form, permalink);

		return true;
	}

	// Displays edit form
	function hashoverEdit (comment)
	{
		if (!comment.user_owned) {
			return false;
		}

		// Get permalink from comment JSON object
		var permalink = comment.permalink;

		// Get subscribed status from comment JSON object
		var subscribed = comment.subscribed;

		// Get edit link element
		var link = $('hashover-edit-link-' + permalink, true);

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
		    form.action = httpRoot + '/scripts/javascript-mode.php';

<?php

		echo $hashover->html->asJSVar ($hashover->html->editForm ('permalink', 'file', 'name', 'website', 'body'), 'formHTML', "\t\t");

?>

		// Place edit form fields into form
		form.innerHTML = formHTML;

		// Prevent input submission
		preventSubmit (form)

		// Add edit form to page
		var edit_form = $('hashover-placeholder-edit_form-' + permalink, true);
		    edit_form.appendChild (form);

		if (!subscribed) {
			$('hashover-subscribe-' + permalink, true).checked = null;
		}

		// Displays onClick confirmation dialog for comment deletion
		$('hashover-edit-delete-' + permalink, true).onclick = function () {
			return confirm ('<?php echo $hashover->locales->locale ('delete_cmt', true); ?>');
		};

		// Change "Edit" link to "Cancel" link
		cancelSwitcher ('edit', link, edit_form, permalink);

		return true;
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

	// Add various events to various elements in each comment
	function addControls (json)
	{
		if (json.notice) {
			return false;
		}

		// Get permalink from JSON object
		var permalink = json.permalink;

		// Set onclick functions for external images
		ifCallback ($('hashover-embedded-image-' + permalink, true), function (embeddedImg) {
			embeddedImg.onclick = function () {
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
			};
		});

		// Get reply link of comment
		ifCallback ($('hashover-reply-link-' + permalink, true), function (replyLink) {
			// Add onClick event to "Reply" hyperlink
			replyLink.onclick = function () {
				hashoverReply (permalink);

				return false;
			};
		});

		// Check if logged in user owns the comment
		if (json.user_owned) {
			ifCallback ($('hashover-edit-link-' + permalink, true), function (editLink) {
				// Add onClick event to "Edit" hyperlinks
				editLink.onclick = function () {
					hashoverEdit (json);

					return false;
				};
			});
		} else {
			ifCallback ($('hashover-like-' + permalink, true), function (likeLink) {
				// Add onClick event to "Like" hyperlinks
				likeLink.onclick = function () {
					likeComment ('like', permalink);

					return false;
				};

				if (likeLink.classList.contains ('hashover-liked')) {
					mouseOverChanger (likeLink, locale.unlike, locale.liked);
				}
			});
<?php if ($hashover->settings->allowsDislikes) { ?>

			ifCallback ($('hashover-dislike-' + permalink, true), function (dislikeLink) {
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
			for (var reply in json.replies) {
				addControls (json.replies[reply]);
			}
		}
	}

	// Run all comments in JSON data through parseComment function
	function parseAll (json, element, collapse, forPop, sort, method)
	{
		forPop = forPop || false;
		sort = sort || false;
		method = method || 'ascending';
		var comments = '';

		// Parse every comment
		for (var comment in json) {
			comments += parseComment (json[comment], collapse, sort, method, forPop);
		}

		// Add comments to element's innerHTML
		if ('insertAdjacentHTML' in element) {
			element.insertAdjacentHTML ('beforeend', comments);
		} else {
			element.innerHTML = comments;
		}

		// Add control events
		for (var comment in json) {
			addControls (json[comment]);
		}
	}

	// Display most popular comments
	if (PHPContent.pop_comments) {
		parseAll (PHPContent.pop_comments, $('hashover-top-comments'), false, true);
	}

	// Get sort div element
	var sortDiv = $('hashover-sort-div');

	// Get primary form element
	var hashoverForm = document.getElementById('hashover-form');

	// Add initial event handlers
	parseAll (PHPContent.comments, sortDiv, !URLJumps);

	// Create "More Comments" expand button link
	if (!URLJumps && totalCount > collapseLimit) {
		var moreLink = document.createElement ('a');
		    moreLink.href = '#';
		    moreLink.id = 'hashover-more-link';
		    moreLink.textContent = '<?php echo $collapse_link_text; ?>';

		// Add onClick event to expand button
		moreLink.onclick = function () {
			var collapsed = sortDiv.getElementsByClassName ('hashover-hidden');
			this.className = 'hashover-hide-morelink';

			setTimeout (function () {
				sortDiv.removeChild (moreLink);
				$('hashover-sort').style.display = '';
				$('hashover-count').style.display = '';

				for (var i = collapsed.length - 1; i >= 0; i--) {
					collapsed[i].classList.remove ('hashover-hidden');
				}
			}, 350);

			return false;
		};

		// Add expand button link to sort div
		sortDiv.appendChild (moreLink);
	}

	// Function to "open" message element and close after 10 seconds
	function showMessage (element, message, error)
	{
		error = error || true;

		if (message) {
			element.textContent = message;

			if (error) {
				element.classList.add ('hashover-message-error');
			}
		}

		setTimeout (function () {
			element.classList.add ('hashover-message-open');

			setTimeout (function () {
				if (messages <= 1) {
					element.classList.remove ('hashover-message-open');
					element.classList.remove ('hashover-message-error');
				}

				messages--;
			}, 10000);

			messages++;
		}, 500);
	}

	// Disable submit buttons on submissions
	function validateComment (permalink, button)
	{
		if (permalink === undefined) {
			if (hashoverForm.comment.value === '') {
				var primaryMessage = $('hashover-message', true);

				showMessage (primaryMessage, '<?php echo $hashover->locales->locale ('cmt_needed', true); ?>');
				hashoverForm.comment.focus ();

				return false;
			}
		} else {
			var replyForms = $('hashover-reply-' + permalink, true);

			if (replyForms.comment.value === '') {
				var replyMessage = $('hashover-message-' + permalink, true);

				showMessage (replyMessage, '<?php echo $hashover->locales->locale ('reply_needed', true); ?>');
				replyForms.comment.focus ();

				return false;
			}
		}

		if (!userIsLoggedIn) {
			if (validateEmail (permalink) === false) {
				return false;
			}
		}

		// Disable button
		setTimeout (function () {
			button.disabled = true;
		}, 1000);

		// Re-enable button after 20 seconds
		setTimeout (function () {
			button.disabled = false;
		}, 20000);

		return true;
	}

	// Handles display of various warnings when user attempts to post or login
	function validateEmail (permalink)
	{
		if (permalink === undefined) {
			var formEmail = hashoverForm.email;

			if ($('hashover-subscribe', true).checked === false) {
				formEmail.value = '';

				return true;
			}

			if (formEmail.value === '') {
				if (!confirm ('<?php echo $hashover->locales->locale ('no_email_warn', true); ?>')) {
					hashoverForm.email.focus ();

					return false;
				}
			} else {
				if (!formEmail.value.match (/\S+@\S+/)) {
					var primaryMessage = $('hashover-message', true);

					showMessage (primaryMessage, '<?php echo $hashover->locales->locale ('invalid_email', true); ?>');
					hashoverForm.email.focus ();

					return false;
				}
			}
		} else {
			var replyForms = $('hashover-reply-' + permalink, true);

			if ($('subscribe-' + permalink, true).checked === false) {
				replyForms.email.value = '';

				return true;
			}

			if (replyForms.email.value === '') {
				if (!confirm ('<?php echo $hashover->locales->locale ('no_email_warn', true); ?>')) {
					replyForms.email.focus ();

					return false;
				}
			} else {
				if (!replyForms.email.value.match (/\S+@\S+/)) {
					var replyMessage = $('hashover-message-' + permalink, true);

					showMessage (replyMessage, '<?php echo $hashover->locales->locale ('invalid_email', true); ?>');
					replyForms.email.focus ();

					return false;
				}
			}
		}

		return true;
	}

	// Attach event listeners to "Post Comment" button
	var postButton = $('hashover-post-button');

	// Onclick
	postButton.onclick = function () {
		return validateComment ();
	};

	// Onsubmit
	postButton.onsubmit = function () {
		return validateComment ();
	};

	// Attach event listeners to "Login" button
	if (!userIsLoggedIn) {
		var loginButton = $('hashover-login-button');

		// Onclick
		loginButton.onclick = function () {
			return validateEmail ();
		};

		// Onsubmit
		loginButton.onsubmit = function () {
			return validateEmail ();
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
				for (var reply in comment.replies) {
					descend (comment.replies[reply]);
				}

				delete comment.replies;
			}
		}

		for (var comment in commentsCopy) {
			descend (commentsCopy[comment]);
		}

		return output;
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
				if (a.sort_date === b.sort_date) {
					return 1;
				}

				return b.sort_date - a.sort_date;
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
				if (a.sort_date === b.sort_date) {
					return 1;
				}

				return b.sort_date - a.sort_date;
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

	// Five method sort
	var sortSelect = $('hashover-sort-select');

	if (sortSelect) {
		sortSelect.onchange = function () {
			sortDiv.textContent = '';
			sortMethods[this.value] ();
		};
	}

	// Function to like a comment
	function likeComment (action, permalink)
	{
		// Get file
		var file = reversePermalink (permalink);

		var actionLink = $('hashover-' + action + '-' + permalink);
		var likesElement = $('hashover-' + action + 's-' + permalink);
		var dislikesClass = (action === 'like') ? '<?php if ($hashover->settings->allowsDislikes) echo ' hashover-dislikes-enabled'; ?>' : '';

		// Load "like.php"
		var like = new XMLHttpRequest ();

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
				actionLink.title = (action === 'like') ? locale.likedCmt : locale.dislikedCmt;
				actionLink.textContent = (action === 'like') ? locale.liked : locale.disliked;

				if (action === 'like') {
					mouseOverChanger (actionLink, locale.unlike, locale.liked);
				}

				// Increase likes
				likes++;
			} else {
				// Change class to indicate the comment is unliked
				actionLink.className = 'hashover-' + action + dislikesClass;
				actionLink.title = (action === 'like') ? locale.likeCmt : locale.dislikeCmt;
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

		// Send request
		like.open ('POST', httpRoot + '/scripts/like.php', true);
		like.setRequestHeader ('Content-type', 'application/x-www-form-urlencoded');
		like.send ('thread=<?php echo $hashover->setup->threadDirectory; ?>&like=' + file + '&action=' + action);
	}

	// Display reply or edit form when the proper URL queries are set
	if (URLHref.match (/hashover_(reply|edit)=/)) {
		var permalink = URLHref.replace (/.*?hashover_(edit|reply)=(c[0-9r_pop]+).*?/, '$2');

		if (!URLHref.match (/hashover_edit=/)) {
			// Reply form
			hashoverReply (permalink);
		} else {
			// Get permalink from URL hash
			var commentKey = permalink.slice (1);
			    commentKey = commentKey.split ('r');

			// Get parent comment by key
			var comment = PHPContent['comments'][(commentKey[0] - 1)];

			// Remove parent key
			commentKey.shift ();

			// Descend into parent replies
			if (commentKey.length >= 1) {
				for (var reply in commentKey) {
					comment = comment.replies[(commentKey[reply]) - 1];
				}
			}

			// Edit form
			hashoverEdit (comment);
		}
	}

	// Log execution time in JavaScript console
	if (window.console) {
		console.log ('HashOver executed in ' + (Date.now () - execStart) + ' ms.');
	}

	// Workaround for stupid Chrome bug
	if (URLJumps || URLHash.match (/comments|hashover/)) {
		if ($(URLHash)) {
			var scroller = function () {
				$(URLHash).scrollIntoView (true);
			};

			// Compatibility wrapper
			if (window.addEventListener) {
				// Rest of the world
				window.addEventListener ('load', scroller, false);
			} else {
				// IE ~8
				window.attachEvent ('onload', scroller);
			}
		}
	}

	if ($('hashover-message').textContent !== '') {
		showMessage ($('hashover-message'));
	}
}) ();
