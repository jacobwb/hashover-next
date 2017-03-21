<?php

// Copyright (C) 2010-2017 Jacob Barkdull
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
	// Check if at least 1 comment is to be shown
	if ($hashover->setup->collapseLimit >= 1) {
		// If so, use the "Show X Other Comments" locale
		$other_comment_count = ($hashover->readComments->totalCount - 1) - $hashover->setup->collapseLimit;
		$more_link_plural = ($other_comment_count !== 1) ? 1 : 0;
		$more_link_locale = $hashover->locale->get ('show-other-comments');
		$more_link_text = $more_link_locale[$more_link_plural];
		$more_link_text = sprintf ($more_link_text, $other_comment_count);
	} else {
		// If not, show count according to `$showsReplyCount` setting
		$more_link_text = $hashover->getCommentCount ('show-number-comments');
	}
}

// Some short variables (FIXME: this is cosmetic)
$allowsLikes = !!$hashover->setup->allowsLikes;
$allowsDislikes = !!$hashover->setup->allowsDislikes;
$likesOrDislikes = ($allowsLikes or $allowsDislikes);

// Return a boolean as a string
function string_boolean ($boolean, $value = true)
{
	return ($boolean === $value) ? 'true' : 'false';
}

// Return a boolean as a string, preferring true
function string_true ($boolean)
{
	return ($boolean !== false) ? 'true' : 'false';
}

// Encodes JSON, returns output that conforms to coding standard
function js_json ($string, $pretty_print = true, $tabs = 1)
{
	$search = array ('\\', "'", '"', "','", '    ', PHP_EOL);
	$replace = array ('', "\'", "'", "', '", "\t", PHP_EOL . str_repeat ("\t", $tabs));

	// Encode string as JSON with pretty where possible
	if ($pretty_print !== false and defined ('JSON_PRETTY_PRINT')) {
		$json = json_encode ($string, JSON_PRETTY_PRINT);
	} else {
		$json = json_encode ($string);
	}

	// Conform JSON to coding standard
	$json = str_replace ($search, $replace, $json);

	return $json;
}

// Returns a regular expression in JavaScript syntax
function js_regex ($regex, $strings)
{
	$regex = preg_replace ('/\\\\([0-9]+)/', '$\\1', $regex);

	if ($strings !== true) {
		$regex .= 'g';
	}

	return $regex;
}

// Returns an array of regular expressions in JavaScript syntax
function js_regex_array ($regexes, $strings, $tabs = "\t")
{
	// Convert capturing groups to JavaScript syntax
	for ($i = 0, $il = count ($regexes); $i < $il; $i++) {
		$regexes[$i] = js_regex ($regexes[$i], $strings);
	}

	// Return array as strings in JavaScript syntax
	if ($strings === true) {
		return js_json ($regexes);
	}

	// Join array items as regular expressions
	$js_array = implode (',' . PHP_EOL . $tabs . $tabs, $regexes);

	// Return array in JavaScript syntax
	return '[' . PHP_EOL . $tabs . $tabs .  $js_array . PHP_EOL . $tabs . ']';
}

?>
// Copyright (C) 2010-2017 Jacob Barkdull
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


// Initial HashOver object
var HashOver = {};

// FIXME: This will be split into multiple functions in separate files
HashOver.init = function ()
{
	"use strict";

	var execStart		= Date.now ();
	var httpRoot		= '<?php echo $hashover->setup->httpRoot; ?>';
	var URLRegex		= '((http|https|ftp):\/\/[a-z0-9-@:;%_\+.~#?&\/=]+)';
	var URLParts		= window.location.href.split ('#');
	var elementsById	= {};
	var trimRegex		= /^[\r\n]+|[\r\n]+$/g;
	var streamMode		= <?php echo string_boolean ($hashover->setup->replyMode, 'stream'); ?>;
	var streamDepth		= <?php echo $hashover->setup->streamDepth; ?>;
	var blockCodeRegex	= <?php echo js_regex ($hashover->markdown->blockCodeRegex, false); ?>;
	var inlineCodeRegex	= <?php echo js_regex ($hashover->markdown->inlineCodeRegex, false); ?>;
	var blockCodeMarker	= /CODE_BLOCK\[([0-9]+)\]/g;
	var inlineCodeMarker	= /CODE_INLINE\[([0-9]+)\]/g;
	var collapsedCount	= 0;
	var collapseLimit	= <?php echo $hashover->setup->collapseLimit; ?>;
	var defaultName		= '<?php echo $hashover->setup->defaultName; ?>';
	var allowsDislikes	= <?php echo string_true ($allowsDislikes); ?>;
	var allowsLikes		= <?php echo string_true ($allowsLikes); ?>;
	var linkRegex		= new RegExp (URLRegex + '( {0,1})', 'ig');
	var imageRegex		= new RegExp ('\\[img\\]<a.*?>' + URLRegex + '</a>\\[/img\\]', 'ig');
	var imageExtensions	= <?php echo js_json ($hashover->setup->imageTypes, false); ?>;
	var imagePlaceholder	= '<?php echo $hashover->setup->httpImages; ?>/place-holder.<?php echo $hashover->setup->imageFormat; ?>';
	var codeOpenRegex	= /<code>/i;
	var codeTagRegex	= /(<code>)([\s\S]*?)(<\/code>)/ig;
	var preOpenRegex	= /<pre>/i;
	var preTagRegex		= /(<pre>)([\s\S]*?)(<\/pre>)/ig;
	var lineRegex		= /(?:\r\n|\r|\n)/g;
	var paragraphRegex	= /(?:\r\n|\r|\n){2}/g;
	var serverEOL		= '<?php echo str_replace (array ("\r", "\n"), array ('\r', '\n'), PHP_EOL); ?>';
	var doubleEOL		= serverEOL + serverEOL;
	var codeTagMarkerRegex	= /CODE_TAG\[([0-9]+)\]/g;
	var preTagMarkerRegex	= /PRE_TAG\[([0-9]+)\]/g;
	var messageCounts	= {};
	var userIsLoggedIn	= <?php echo string_boolean ($hashover->login->userIsLoggedIn); ?>;
	var primaryCount	= <?php echo $hashover->readComments->primaryCount - 1; ?>;
	var totalCount		= <?php echo $hashover->readComments->totalCount - 1; ?>;
	var AJAXPost		= null;
	var AJAXEdit		= null;
	var httpScripts		= '<?php echo $hashover->setup->httpScripts; ?>';
	var commentStatuses	= ['approved', 'pending', 'deleted'];
	var moreLink		= null;
	var sortDiv		= null;
	var moreDiv		= null;
	var showingMore		= false;
	var pageURL		= '<?php echo $hashover->setup->pageURL; ?>';
	var threadRegex		= /^(c[0-9r]+)r[0-9\-pop]+$/;
	var appendCSS		= true;
	var themeCSS		= httpRoot + '/themes/<?php echo $hashover->setup->theme; ?>/style.css';
	var head		= document.head || document.getElementsByTagName ('head')[0];
	var URLHref		= URLParts[0];
	var HashOverDiv		= document.getElementById ('hashover');
	var hashoverScript	= <?php echo js_json ($hashover->setup->executingScript, false); ?>;
	var deviceType		= '<?php echo $hashover->setup->isMobile === true ? 'mobile' : 'desktop'; ?>';
	var HashOverForm	= null;
	var collapseComments	= <?php echo string_true ($hashover->setup->collapsesComments); ?>;
	var URLHash		= URLParts[1] || '';

	// Array for inline code and code block markers
	var codeMarkers = {
		block: { marks: [], count: 0 },
		inline: { marks: [], count: 0 }
	};

	// Some locales, stored in JavaScript to avoid using a lot of PHP tags
	var locale = {
		cancel:			'<?php echo $hashover->locale->get ('cancel'); ?>',
		externalImageTip:	'<?php echo $hashover->locale->get ('external-image-tip'); ?>',
		like:			<?php echo js_json ($hashover->locale->get ('like', false), false); ?>,
		liked:			'<?php echo $hashover->locale->get ('liked'); ?>',
		unlike:			'<?php echo $hashover->locale->get ('unlike'); ?>',
		likeComment:		'<?php echo $hashover->locale->get ('like-comment'); ?>',
		likedComment:		'<?php echo $hashover->locale->get ('liked-comment'); ?>',
		dislike:		<?php echo js_json ($hashover->locale->get ('dislike', false), false); ?>,
		disliked:		'<?php echo $hashover->locale->get ('disliked'); ?>',
		dislikeComment:		'<?php echo $hashover->locale->get ('dislike-comment'); ?>',
		dislikedComment:	'<?php echo $hashover->locale->get ('disliked-comment'); ?>',
		name:			'<?php echo $hashover->locale->get ('name'); ?>',
		password:		'<?php echo $hashover->locale->get ('password'); ?>',
		email:			'<?php echo $hashover->locale->get ('email'); ?>',
		website:		'<?php echo $hashover->locale->get ('website'); ?>'
	};

	// Markdown patterns to search for
	var markdownSearch = <?php echo js_regex_array ($hashover->markdown->search, false); ?>;

	// HTML replacements for markdown patterns
	var markdownReplace = <?php echo js_regex_array ($hashover->markdown->replace, true); ?>;

	// Tags that will have their innerHTML trimmed
	var trimTagRegexes = {
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
	};

	// Field options
	var fieldOptions = <?php echo js_json ($hashover->setup->fieldOptions); ?>;

	// Shorthand for Document.getElementById ()
	function getElement (id, force)
	{
		if (force === true) {
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

	// Trims leading and trailing newlines from a string
	function EOLTrim (string)
	{
		return string.replace (trimRegex, '');
	}

	// Trims whitespace from an HTML tag's inner HTML
	function tagTrimmer (fullTag, openTag, innerHTML, closeTag)
	{
		return openTag + EOLTrim (innerHTML) + closeTag;
	}

	// Find a comment by its permalink
	function findByPermalink (permalink, comments)
	{
		var comment;

		// Loop through all comments
		for (var i = 0, il = comments.length; i < il; i++) {
			// Return comment if its permalink matches
			if (comments[i].permalink === permalink) {
				return comments[i];
			}

			// Recursively check replies when present
			if (comments[i].replies !== undefined) {
				comment = findByPermalink (permalink, comments[i].replies);

				if (comment !== null) {
					return comment;
				}
			}
		}

		// Otherwise return null
		return null;
	}

	// Returns the permalink of a comment's parent
	function getParentPermalink (permalink, flatten)
	{
		flatten = flatten || false;

		var parent = permalink.split ('r');
		var length = parent.length - 1;

		// Limit depth if in stream mode
		if (streamMode === true && flatten === true) {
			length = Math.min (streamDepth, length);
		}

		// Check if there is a parent after flatten
		if (length > 0) {
			// If so, remove child from permalink
			parent = parent.slice (0, length);

			// Return parent permalink as string
			return parent.join ('r');
		}

		return null;
	}

	// Replaces markdown for inline code with a marker
	function codeReplace (fullTag, first, second, third, display)
	{
		var markName = 'CODE_' + display.toUpperCase ();
		var markCount = codeMarkers[display].count++;
		var codeMarker;

		if (display !== 'block') {
			codeMarker = first + markName + '[' + markCount + ']' + third;
			codeMarkers[display].marks[markCount] = EOLTrim (second);
		} else {
			codeMarker = markName + '[' + markCount + ']';
			codeMarkers[display].marks[markCount] = EOLTrim (first);
		}

		return codeMarker;
	}

	// Parses a string as markdown
	function parseMarkdown (string)
	{
		// Reset marker arrays
		codeMarkers = {
			block: { marks: [], count: 0 },
			inline: { marks: [], count: 0 }
		};

		// Replace code blocks with markers
		string = string.replace (blockCodeRegex, function (fullTag, first, second, third) {
			return codeReplace (fullTag, first, second, third, 'block');
		});

		// Break string into paragraphs
		var paragraphs = string.split (paragraphRegex);

		// Run through each paragraph replacing markdown patterns
		for (var i = 0, il = paragraphs.length; i < il; i++) {
			// Replace code tags with marker text
			paragraphs[i] = paragraphs[i].replace (inlineCodeRegex, function (fullTag, first, second, third) {
				return codeReplace (fullTag, first, second, third, 'inline');
			});

			// Perform each markdown regular expression on the current paragraph
			for (var r = 0, rl = markdownSearch.length; r < rl; r++) {
				// Replace markdown patterns
				paragraphs[i] = paragraphs[i].replace (markdownSearch[r], markdownReplace[r]);
			}

			// Return the original markdown code with HTML replacement
			paragraphs[i] = paragraphs[i].replace (inlineCodeMarker, function (marker, number) {
				return '<code class="hashover-inline">' + codeMarkers.inline.marks[number] + '</code>';
			});
		}

		// Join paragraphs
		string = paragraphs.join (doubleEOL);

		// Replace code block markers with original markdown code
		string = string.replace (blockCodeMarker, function (marker, number) {
			return '<code>' + codeMarkers.block.marks[number] + '</code>';
		});

		return string;
	}

	// Adds properties to an element
	function addProperties (element, properties)
	{
		element = element || document.createElement ('span');
		properties = properties || {};

		// Add each property to element
		for (var property in properties) {
			if (properties.hasOwnProperty (property) === false) {
				continue;
			}

			// If the property is an object add each item to existing property
			if (!!properties[property] && properties[property].constructor === Object) {
				addProperties (element[property], properties[property]);
				continue;
			}

			element[property] = properties[property];
		}

		return element;
	}

	// Create an element with attributes
	function createElement (tagName, attributes)
	{
		tagName = tagName || 'span';
		attributes = attributes || {};

		// Create element
		var element = document.createElement (tagName);

		// Add properties to element
		element = addProperties (element, attributes);

		return element;
	}

	// Add comment content to HTML template
	function parseComment (comment, parent, collapse, sort, method, popular)
	{
		parent = parent || null;
		collapse = collapse || false;
		sort = sort || false;
		method = method || 'ascending';
		popular = popular || false;

		var permalink = comment.permalink;
		var nameClass = 'hashover-name-plain';
		var template = { permalink: permalink };
		var isReply = (parent !== null);
		var parentPermalink;
		var codeTagCount = 0;
		var codeTags = [];
		var preTagCount = 0;
		var preTags = [];
		var classes = '';
		var replies = '';

		// Text for avatar image alt attribute
		var permatext = permalink.slice (1);
		    permatext = permatext.split ('r');
		    permatext = permatext.pop ();

		// Get parent comment via permalink
		if (isReply === false && permalink.indexOf ('r') > -1) {
			parentPermalink = getParentPermalink (permalink);
			parent = findByPermalink (parentPermalink, PHPContent.comments);
			isReply = (parent !== null);
		}

		// Check if this comment is a popular comment
		if (popular === true) {
			// Remove "-pop" from text for avatar
			permatext = permatext.replace ('-pop', '');
		} else {
			// Check if comment is a reply
			if (isReply === true) {
				// Check that comments are being sorted
				if (!sort || method === 'ascending') {
					// Append class to indicate comment is a reply
					classes += ' hashover-reply';
				}
			}
<?php if ($hashover->setup->collapsesComments !== false): ?>

			// Append class to indicate collapsed comment
			if (collapse === true && collapsedCount >= collapseLimit) {
				classes += ' hashover-hidden';
			} else {
				collapsedCount++;
			}
<?php endif; ?>
		}

		// Add avatar image to template
		template.avatar = '<?php echo $hashover->html->userAvatar ('permatext', 'permalink', 'comment.avatar'); ?>';

		if (comment.notice === undefined) {
			var name = comment.name || defaultName;
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
				var nameLink = '<?php echo $hashover->html->nameElement ('a', 'name', 'permalink', 'website'); ?>';
			} else {
				// If not, display name as plain text
				var nameLink = '<?php echo $hashover->html->nameElement ('span', 'name', 'permalink'); ?>';
			}

			// Construct thread hyperlink
			if (isReply === true) {
				var parentThread = parent.permalink;
				var parentName = parent.name || defaultName;

				// Add thread parent hyperlink to template
				template['thread-link'] = '<?php echo $hashover->html->threadLink ('permalink', 'parentThread', 'parentName'); ?>';
			}

			if (comment['user-owned'] !== undefined) {
				// Append class to indicate comment is from logged in user
				classes += ' hashover-user-owned';

				// Define "Reply" link with original poster title
				var replyTitle = '<?php echo $hashover->locale->get ('commenter-tip'); ?>';
				var replyClass = 'hashover-no-email';

				// Add "Edit" hyperlink to template
				template['edit-link'] = '<?php echo $hashover->html->formLink ('edit', 'permalink'); ?>';
			} else {
				// Check if commenter is subscribed
				if (comment.subscribed === true) {
					// If so, set subscribed title
					var replyTitle = name + ' <?php echo $hashover->locale->get ('subscribed-tip'); ?>';
					var replyClass = 'hashover-has-email';
				} else{
					// If not, set unsubscribed title
					var replyTitle = name + ' <?php echo $hashover->locale->get ('unsubscribed-tip'); ?>';
					var replyClass = 'hashover-no-email';
				}
<?php if ($allowsLikes !== false): ?>

				// Check whether this comment was liked by the visitor
				if (comment.liked !== undefined) {
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
				if (allowsDislikes === true) {
					likeClass += ' hashover-dislikes-enabled';
				}

				// Add like link to HTML template
				template['like-link'] = '<?php echo $hashover->html->likeLink ('like', 'permalink', 'likeClass', 'likeTitle', 'likeText'); ?>';
<?php endif; ?>
<?php if ($allowsDislikes === true): ?>

				// Check whether this comment was disliked by the visitor
				if (comment.disliked !== undefined) {
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

				// Append class to indicate likes are enabled
				if (allowsLikes === true) {
					dislikeClass += ' hashover-likes-enabled';
				}

				// Add dislike link to HTML template
				template['dislike-link'] = '<?php echo $hashover->html->likeLink ('dislike', 'permalink', 'dislikeClass', 'dislikeTitle', 'dislikeText'); ?>';
<?php endif; ?>
			}

<?php if ($allowsLikes !== false): ?>
			// Get number of likes, append "Like(s)" locale
			if (comment.likes !== undefined) {
				var likeCount = comment.likes + ' ' + locale.like[(comment.likes === 1 ? 0 : 1)];
			}

			// Add like count to HTML template
			template['like-count'] = '<?php echo $hashover->html->likeCount ('likes', 'permalink', '(likeCount || \'\')'); ?>';

<?php endif; ?>
<?php if ($allowsDislikes === true): ?>
			// Get number of dislikes, append "Dislike(s)" locale
			if (comment.dislikes !== undefined) {
				var dislikeCount = comment.dislikes + ' ' + locale.dislike[(comment.dislikes === 1 ? 0 : 1)];
			}

			// Add dislike count to HTML template
			template['dislike-count'] = '<?php echo $hashover->html->likeCount ('dislikes', 'permalink', '(dislikeCount || \'\')'); ?>';

<?php endif; ?>
			// Add name HTML to template
			template.name = '<?php echo $hashover->html->nameWrapper ('nameLink', 'nameClass'); ?>';

			// Add date permalink hyperlink to template
			template.date = '<?php echo $hashover->html->dateLink ('permalink', 'comment.date'); ?>';

			// Add "Reply" hyperlink to template
			template['reply-link'] = '<?php echo $hashover->html->formLink ('reply', 'permalink', 'replyClass', 'replyTitle'); ?>';

			// Add reply count to template
			if (comment.replies !== undefined) {
				template['reply-count'] = comment.replies.length;

				if (template['reply-count'] > 0) {
					if (template['reply-count'] !== 1) {
						template['reply-count'] += ' <?php echo $hashover->locale->get ('replies'); ?>';
					} else {
						template['reply-count'] += ' <?php echo $hashover->locale->get ('reply'); ?>';
					}
				}
			}

			// Add HTML anchor tag to URLs
			var body = comment.body.replace (linkRegex, '<a href="$1" rel="noopener noreferrer" target="_blank">$1</a>');

			// Replace [img] tags with external image placeholder if enabled
			body = body.replace (imageRegex, function (fullURL, url) {
<?php if ($hashover->setup->allowsImages !== false): ?>
				// Get image extension from URL
				var urlExtension = url.split ('#')[0];
				    urlExtension = urlExtension.split ('?')[0];
				    urlExtension = urlExtension.split ('.');
				    urlExtension = urlExtension.pop ();

				// Check if the image extension is an allowed type
				if (imageExtensions.indexOf (urlExtension) > -1) {
					// If so, create a wrapper element for the embedded image
					var embeddedImage = createElement ('span', {
						className: 'hashover-embedded-image-wrapper'
					});

					// Append an image tag to the embedded image wrapper
					embeddedImage.appendChild (createElement ('img', {
						className: 'hashover-embedded-image',
						src: imagePlaceholder,
						title: locale.externalImageTip,
						alt: 'External Image',

						dataset: {
							placeholder: imagePlaceholder,
							url: url
						}
					}));

					// And return the embedded image HTML
					return embeddedImage.outerHTML;
				}

<?php endif; ?>
				// Convert image URL into an anchor tag
				return '<a href="' + url + '" rel="noopener noreferrer" target="_blank">' + url + '</a>';
			});

			// Parse markdown in comment
			body = parseMarkdown (body);

			// Check for code tags
			if (codeOpenRegex.test (body) === true) {
				// Replace code tags with marker text
				body = body.replace (codeTagRegex, function (fullTag, openTag, innerHTML, closeTag) {
					var codeMarker = openTag + 'CODE_TAG[' + codeTagCount + ']' + closeTag;

					codeTags[codeTagCount] = EOLTrim (innerHTML);
					codeTagCount++;

					return codeMarker;
				});
			}

			// Check for pre tags
			if (preOpenRegex.test (body) === true) {
				// Replace pre tags with marker text
				body = body.replace (preTagRegex, function (fullTag, openTag, innerHTML, closeTag) {
					var preMarker = openTag + 'PRE_TAG[' + preTagCount + ']' + closeTag;

					preTags[preTagCount] = EOLTrim (innerHTML);
					preTagCount++;

					return preMarker;
				});
			}

			// Check for various multi-line tags
			for (var trimTag in trimTagRegexes) {
				if (trimTagRegexes.hasOwnProperty (trimTag) === true
				    && trimTagRegexes[trimTag]['test'].test (body) === true)
				{
					// Trim whitespace
					body = body.replace (trimTagRegexes[trimTag]['replace'], tagTrimmer);
				}
			}

			// Break comment into paragraphs
			var paragraphs = body.split (paragraphRegex);
			var pdComment = '';

			// Wrap comment in paragraph tag
			// Replace single line breaks with break tags
			for (var i = 0, il = paragraphs.length; i < il; i++) {
				pdComment += '<p>' + paragraphs[i].replace (lineRegex, '<br>') + '</p>' + serverEOL;
			}

			// Replace code tag markers with original code tag HTML
			if (codeTagCount > 0) {
				pdComment = pdComment.replace (codeTagMarkerRegex, function (marker, number) {
					return codeTags[number];
				});
			}

			// Replace pre tag markers with original pre tag HTML
			if (preTagCount > 0) {
				pdComment = pdComment.replace (preTagMarkerRegex, function (marker, number) {
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
			template.name = '<?php echo $hashover->html->nameWrapper ('comment.title', 'nameClass'); ?>';
		}

		// Comment HTML template
<?php

		echo $hashover->html->asJSVar ($hashover->templater->parseTemplate (), 'html', "\t\t");

?>

		// Recursively parse replies
		if (comment.replies !== undefined) {
			for (var reply = 0, total = comment.replies.length; reply < total; reply++) {
				replies += parseComment (comment.replies[reply], comment, collapse);
			}
		}

		return '<?php echo $hashover->html->commentWrapper ('permalink', 'classes', 'html + replies'); ?>';
	}

	// Generate file from permalink
	function fileFromPermalink (permalink)
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
			textContent: link.textContent,
			title: link.title,
			onclick: link.onclick
		};

		function linkOnClick ()
		{
			// Remove fields from form wrapper
			wrapper.textContent = '';

			// Reset button
			link.textContent = reset.textContent;
			link.title = reset.title;
			link.onclick = reset.onclick;

			return false;
		}

		// Change hyperlink to "Cancel" hyperlink
		link.textContent = locale.cancel;
		link.title = locale.cancel;

		// This resets the "Cancel" hyperlink to initial state onClick
		link.onclick = linkOnClick;
<?php if ($hashover->setup->usesCancelButtons !== false): ?>

		// Get "Cancel" button
		var cancelButtonId = 'hashover-' + form + '-cancel-' + permalink;
		var cancelButton = getElement (cancelButtonId, true);

		// Attach event listeners to "Cancel" button
		cancelButton.onclick = linkOnClick;
<?php endif; ?>
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
		var containsClass = function (element, className)
		{
			return element.classList.contains (className);
		};

		// classList.add () method
		var addClass = function (element, className)
		{
			element.classList.add (className);
		};

		// classList.remove () method
		var removeClass = function (element, className)
		{
			element.classList.remove (className);
		};
	} else {
		// If not, define fallback functions
		// classList.contains () method
		var containsClass = function (element, className)
		{
			if (!element || !element.className) {
				return false;
			}

			var regex = new RegExp ('(^|\\s)' + className + '(\\s|$)');
			return regex.test (element.className);
		};

		// classList.add () method
		var addClass = function (element, className)
		{
			if (!element) {
				return false;
			}

			if (!containsClass (element, className)) {
				element.className += (element.className ? ' ' : '') + className;
			}
		};

		// classList.remove () method
		var removeClass = function (element, className)
		{
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
		permalink = permalink || '';
		error = error || true;
		isReply = isReply || false;
		isEdit = isEdit || false;

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

		if (message !== undefined && message !== '') {
			// Add message text to element
			element.textContent = message;

			// Add class to indicate message is an error if set
			if (error === true) {
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
		if (form.email === undefined) {
			return true;
		}

		// Whether the e-mail form is empty
		if (form.email.value === '') {
			// Return true if user unchecked the subscribe checkbox
			if (getElement (subscribe, true).checked === false) {
				return true;
			}

			// If so, warn the user that they won't receive reply notifications
			if (confirm ('<?php echo $hashover->locale->get ('no-email-warning'); ?>') === false) {
				form.email.focus ();
				return false;
			}
		} else {
			var message;
			var emailRegex = /\S+@\S+/;

			// If not, check if the e-mail is valid
			if (emailRegex.test (form.email.value) === false) {
				// Return true if user unchecked the subscribe checkbox
				if (getElement (subscribe, true).checked === false) {
					form.email.value = '';
					return true;
				}

				message = '<?php echo $hashover->locale->get ('invalid-email'); ?>';
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
		permalink = permalink || null;
		isReply = isReply || false;
		isEdit = isEdit || false;

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

	// Validate a comment form
	function commentValidator (form, skipComment)
	{
		skipComment = skipComment || false;

		var fieldNeeded = '<?php echo $hashover->locale->get ('field-needed'); ?>';

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

					// Focus the input
					form[field].focus ();

					// Return error message to display to the user
					return fieldNeeded.replace ('%s', locale[field]);
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
			return '<?php echo $hashover->locale->get ('comment-needed'); ?>';
		}

		return true;
	}

	// Validate required comment credentials
	function validateComment (skipComment, form, permalink, isReply, isEdit)
	{
		skipComment = skipComment || false;
		permalink = permalink || null;
		isReply = isReply || false;
		isEdit = isEdit || false;

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
		permalink = permalink || '';
		close = close || null;
		isReply = isReply || false;
		isEdit = isEdit || false;

		// Return false if comment is invalid
		if (validateComment (false, form, permalink, isReply, isEdit) === false) {
			return false;
		}

		// Disable button
		setTimeout (function () {
			button.disabled = true;
		}, 500);

<?php if ($hashover->setup->usesAJAX !== false): ?>
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
		httpRequest.onreadystatechange = function ()
		{
			// Do nothing if request wasn't successful in a meaningful way
			if (this.readyState !== 4 || this.status !== 200) {
				return;
			}

			// Parse AJAX response as JSON
			var json = JSON.parse (this.responseText);
			var scrollToElement;

			// Check if JSON includes a comment
			if (json.comment !== undefined) {
				// If so, execute callback function
				callback (json, permalink, destination, isReply);

				// Execute callback function if one was provided
				if (close !== null) {
					close ();
				}

				// Scroll comment into view
				scrollToElement = getElement (json.comment.permalink, true);
				scrollToElement.scrollIntoView ({ behavior: 'smooth' });

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
<?php else: ?>
		// Re-enable button after 20 seconds
		setTimeout (function () {
			button.disabled = false;
		}, 20000);

		return true;
<?php endif; ?>
	}

<?php if ($hashover->setup->usesAJAX !== false): ?>
	// Converts an HTML string to DOM NodeList
	function HTMLToNodeList (html)
	{
		return createElement ('div', { innerHTML: html }).childNodes;
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
		isReply = isReply || false;
		index = index || null;

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
		var parentPermalink = getParentPermalink (comment.permalink);
		var parent = findByPermalink (parentPermalink, PHPContent.comments);

		// Check if comment has replies
		if (parent !== null && parent.replies !== undefined) {
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
	AJAXPost = function (json, permalink, destination, isReply)
	{
		// If there aren't any comments, replace first comment message
		if (totalCount === 0) {
			PHPContent.comments[0] = json.comment;
			destination.innerHTML = parseComment (json.comment);
		} else {
			// Add comment to comments array
			addComments (json.comment, isReply);

			// Create div element for comment
			var commentNode = HTMLToNodeList (parseComment (json.comment));

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
	};

	// For editing comments
	AJAXEdit = function (json, permalink, destination, isReply)
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
	};

<?php endif; ?>
	// Displays reply form
	function hashoverReply (permalink)
	{
		// Get reply link element
		var link = getElement ('hashover-reply-link-' + permalink, true);

		// Get file
		var file = fileFromPermalink (permalink);

		// Create reply form element
		var form = createElement ('form', {
			id: 'hashover-reply-' + permalink,
			className: 'hashover-reply-form',
			method: 'post',
			action: httpScripts + '/postcomments.php'
		});

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
		postReply.onclick = function ()
		{
			return postComment (destination, form, this, AJAXPost, permalink, link.onclick, true, false);
		};

		// Onsubmit
		postReply.onsubmit = function ()
		{
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
		var file = fileFromPermalink (permalink);

		// Get name and website
		var name = comment.name || '';
		var website = comment.website || '';

		// Get and clean comment body
		var body = comment.body.replace (linkRegex, '$1');

		// Create edit form element
		var form = createElement ('form', {
			id: 'hashover-edit-' + permalink,
			className: 'hashover-edit-form',
			method: 'post',
			action: httpScripts + '/postcomments.php'
		});

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

		// Set status dropdown menu option to comment status
		ifElement ('hashover-edit-status-' + permalink, function (status) {
			if (comment.status !== undefined) {
				status.selectedIndex = commentStatuses.indexOf (comment.status);
			}
		});

		// Blank out password field
		setTimeout (function () {
			if (form.password !== undefined) {
				form.password.value = '';
			}
		}, 100);

		// Uncheck subscribe checkbox if user isn't subscribed
		if (comment.subscribed !== true) {
			getElement ('hashover-subscribe-' + permalink, true).checked = null;
		}

		// Displays onClick confirmation dialog for comment deletion
		getElement ('hashover-edit-delete-' + permalink, true).onclick = function ()
		{
			return confirm ('<?php echo $hashover->locale->get ('delete-comment'); ?>');
		};

		// Change "Edit" link to "Cancel" link
		cancelSwitcher ('edit', link, editForm, permalink);

		// Attach event listeners to "Save Edit" button
		var saveEdit = getElement ('hashover-edit-post-' + permalink, true);

		// Get the element of comment being replied to
		var destination = getElement (permalink, true);

		// Onclick
		saveEdit.onclick = function ()
		{
			return postComment (destination, form, this, AJAXEdit, permalink, link.onclick, false, true);
		};

		// Onsubmit
		saveEdit.onsubmit = function ()
		{
			return postComment (destination, form, this, AJAXEdit, permalink, link.onclick, false, true);
		};

		return false;
	}
<?php if ($hashover->setup->collapsesComments !== false): ?>

	// For showing more comments, via AJAX or removing a class
	function hideMoreLink (finishedCallback)
	{
		finishedCallback = finishedCallback || null;

		// Add class to hide the more hyperlink
		moreLink.className = 'hashover-hide-morelink';

		setTimeout (function () {
			// Remove the more hyperlink from page
			if (sortDiv.contains (moreLink) === true) {
				sortDiv.removeChild (moreLink);
			}

			// Show hidden form elements
			getElement ('hashover-form-section').style.display = '';
			getElement ('hashover-count-wrapper').style.display = '';
			getElement ('hashover-count').style.display = '';
			getElement ('hashover-sort').style.display = '';
			getElement ('hashover-end-links').style.display = '';

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
		}, 350);
	}
<?php if ($hashover->setup->usesAJAX !== false): ?>

	// For appending new comments to the thread on page
	function appendComments (comments)
	{
		var comment;
		var isReply;
		var element;
		var parent;

		for (var i = 0, il = comments.length; i < il; i++) {
			// Skip existing comments
			if (findByPermalink (comments[i].permalink, PHPContent.comments) !== null) {
				// Check comment's replies
				if (comments[i].replies !== undefined) {
					appendComments (comments[i].replies);
				}

				continue;
			}

			// Check if comment is a reply
			isReply = (comments[i].permalink.indexOf ('r') > -1);

			// Add comment to comments array
			addComments (comments[i], isReply, i);

			// Parse comment, convert HTML to DOM node
			comment = HTMLToNodeList (parseComment (comments[i], null, true));

			// Check that comment is not a reply
			if (isReply !== true) {
				// If so, append to primary comments
				element = moreDiv;
			} else {
				// If not, append to its parent's element
				parent = getParentPermalink (comments[i].permalink, true);
				element = getElement (parent, true);
			}

			// Otherwise append it to the primary element
			element.appendChild (comment[0]);

			// Add controls to the comment
			addControls (comments[i]);
		}
	}
<?php endif; ?>

	// onClick event for more button
	function showMoreComments (element, finishedCallback)
	{
		finishedCallback = finishedCallback || null;

		// Do nothing if already showing all comments
		if (showingMore === true) {
			// Execute callback function
			if (finishedCallback !== null) {
				finishedCallback ();
			}

			return false;
		}

<?php if ($hashover->setup->usesAJAX !== false): ?>
		var httpRequest = new XMLHttpRequest ();
		var queries = ['url=' + encodeURIComponent (pageURL), 'start=' + collapseLimit, 'ajax=yes'];

		// Handle AJAX request return data
		httpRequest.onreadystatechange = function ()
		{
			// Do nothing if request wasn't successful in a meaningful way
			if (this.readyState !== 4 || this.status !== 200) {
				return;
			}

			// Parse AJAX response as JSON
			var json = JSON.parse (this.responseText);

			// Display the comments
			appendComments (json.comments);

			// Remove loading class from element
			removeClass (element, 'hashover-loading');

			// Hide the more hyperlink and display the comments
			hideMoreLink (finishedCallback);
		};

		// Open and send request
		httpRequest.open ('POST', httpRoot + '/api/json.php', true);
		httpRequest.setRequestHeader ('Content-type', 'application/x-www-form-urlencoded');
		httpRequest.send (queries.join ('&'));

		// Set class to indicate loading to element
		addClass (element, 'hashover-loading');
<?php else: ?>
		// Hide the more hyperlink and display the comments
		hideMoreLink (finishedCallback);
<?php endif; ?>

		// Set all comments as shown
		showingMore = true;

		return false;
	}
<?php endif; ?>

	// Callback to close the embedded image
	function closeEmbeddedImage (image) {
		// Reset source
		image.src = image.dataset.placeholder;

		// Reset title
		image.title = locale.externalImageTip;

		// Remove loading class from wrapper
		removeClass (image.parentNode, 'hashover-loading');
	}

	// Onclick callback function for embedded images
	function embeddedImageCallback ()
	{
		// If embedded image is open, close it and return false
		if (this.src === this.dataset.url) {
			closeEmbeddedImage (this);
			return false;
		}

		// Set title
		this.title = '<?php echo $hashover->locale->get ('loading'); ?>';

		// Add loading class to wrapper
		addClass (this.parentNode, 'hashover-loading');

		// Change title and remove load event handler once image is loaded
		this.onload = function ()
		{
			this.title = '<?php echo $hashover->locale->get ('click-to-close'); ?>';
			this.onload = null;

			// Remove loading class from wrapper
			removeClass (this.parentNode, 'hashover-loading');
		};

		// Close embedded image if any error occurs
		this.onerror = function ()
		{
			closeEmbeddedImage (this);
		};

		// Set placeholder image to embedded source
		this.src = this.dataset.url;
	}

	// Changes Element.textContent onmouseover and reverts onmouseout
	function mouseOverChanger (element, over, out)
	{
		if (over === null || out === null) {
			element.onmouseover = null;
			element.onmouseout = null;

			return false;
		}

		element.onmouseover = function ()
		{
			this.textContent = over;
		};

		element.onmouseout = function ()
		{
			this.textContent = out;
		};
	}
<?php if ($likesOrDislikes !== false): ?>

	// For liking comments
	function likeComment (action, permalink)
	{
		// Get file
		var file = fileFromPermalink (permalink);

		var actionLink = getElement ('hashover-' + action + '-' + permalink, true);
		var likesElement = getElement ('hashover-' + action + 's-' + permalink, true);
		var dislikesClass = (action === 'like') ? '<?php if ($allowsDislikes === true) echo ' hashover-dislikes-enabled'; ?>' : '';

		// Load "like.php"
		var like = new XMLHttpRequest ();
		var queries;

		// When loaded update like count
		like.onreadystatechange = function ()
		{
			var likeResponse;
			var likesKey, likes = 0;

			// Do nothing if request wasn't successful in a meaningful way
			if (this.readyState !== 4 || this.status !== 200) {
				return;
			}

			// Get JSON response
			likeResponse = JSON.parse (this.responseText);

			// If a message is returned display it to the user
			if (likeResponse.message !== undefined) {
				alert (likeResponse.message);
				return;
			}

			// If an error is returned display a stand error to the user
			if (likeResponse.error !== undefined) {
				alert ('Error! Something went wrong!');
				return;
			}

			// Get number of likes
			likesKey = (action !== 'dislikes') ? 'likes' : 'dislikes';
			likes = likeResponse[likesKey] || 0;

			// Change "Like" button title and class
			if (actionLink.className === 'hashover-' + action + dislikesClass) {
				// Change class to indicate the comment has been liked/disliked
				actionLink.className = 'hashover-' + action + 'd' + dislikesClass;
				actionLink.title = (action === 'like') ? locale.likedComment : locale.dislikedComment;
				actionLink.textContent = (action === 'like') ? locale.liked : locale.disliked;

				// Add listener to change link text to "Unlike" on mouse over
				if (action === 'like') {
					mouseOverChanger (actionLink, locale.unlike, locale.liked);
				}
			} else {
				// Change class to indicate the comment is unliked
				actionLink.className = 'hashover-' + action + dislikesClass;
				actionLink.title = (action === 'like') ? locale.likeComment : locale.dislikeComment;
				actionLink.textContent = (action === 'like') ? locale.like[0] : locale.dislike[0];

				// Add listener to change link text to "Unlike" on mouse over
				if (action === 'like') {
					mouseOverChanger (actionLink, null, null);
				}
			}

			if (likes > 0) {
				// Locale plural key
				var plural = (likes !== 1) ? 1 : 0;

				if (action === 'like') {
					var likeCount = likes + ' ' + locale.like[plural];
				} else {
					var likeCount = likes + ' ' + locale.dislike[plural];
				}

				// Change number of likes; set font weight bold
				likesElement.textContent = likeCount;
				likesElement.style.fontWeight = 'bold';
			} else {
				// Remove like count; set font weight normal
				likesElement.textContent = '';
				likesElement.style.fontWeight = '';
			}
		};

		// Set request queries
		queries  = 'url=' + encodeURIComponent (pageURL);
		queries += '&thread=<?php echo $hashover->setup->threadDirectory; ?>';
		queries += '&comment=' + file;
		queries += '&action=' + action;

		// Send request
		like.open ('POST', httpScripts + '/like.php', true);
		like.setRequestHeader ('Content-type', 'application/x-www-form-urlencoded');
		like.send (queries);
	}
<?php endif; ?>

	// Add various events to various elements in each comment
	function addControls (json, popular)
	{
		function stepIntoReplies ()
		{
			if (json.replies !== undefined) {
				for (var reply = 0, total = json.replies.length; reply < total; reply++) {
					addControls (json.replies[reply]);
				}
			}
		}

		if (json.notice !== undefined) {
			stepIntoReplies ();
			return false;
		}

		// Get permalink from JSON object
		var permalink = json.permalink;

		// Get embedded image elements
		var embeddedImgs = document.getElementsByClassName ('hashover-embedded-image');

		// Set onclick functions for external images
		for (var i = 0, il = embeddedImgs.length; i < il; i++) {
			embeddedImgs[i].onclick = embeddedImageCallback;
		}

<?php if ($hashover->setup->collapsesComments !== false): ?>
		// Get thread link of comment
		ifElement ('hashover-thread-link-' + permalink, function (threadLink) {
			// Add onClick event to thread hyperlink
			threadLink.onclick = function ()
			{
				showMoreComments (threadLink, function () {
					var parentThread = permalink.replace (threadRegex, '$1');
					var scrollToElement = getElement (parentThread, true);

					// Scroll to the comment
					scrollToElement.scrollIntoView ({ behavior: 'smooth' });
				});

				return false;
			};
		});

<?php endif; ?>
		// Get reply link of comment
		ifElement ('hashover-reply-link-' + permalink, function (replyLink) {
			// Add onClick event to "Reply" hyperlink
			replyLink.onclick = function ()
			{
				hashoverReply (permalink);
				return false;
			};
		});

		// Check if logged in user owns the comment
		if (json['user-owned'] === true) {
			ifElement ('hashover-edit-link-' + permalink, function (editLink) {
				// Add onClick event to "Edit" hyperlinks
				editLink.onclick = function ()
				{
					hashoverEdit (json);
					return false;
				};
			});
<?php if ($likesOrDislikes): ?>
		} else {
<?php if ($allowsLikes !== false): ?>
			ifElement ('hashover-like-' + permalink, function (likeLink) {
				// Add onClick event to "Like" hyperlinks
				likeLink.onclick = function ()
				{
					likeComment ('like', permalink);
					return false;
				};

				if (containsClass (likeLink, 'hashover-liked') === true) {
					mouseOverChanger (likeLink, locale.unlike, locale.liked);
				}
			});
<?php endif; ?>
<?php if ($allowsDislikes === true): ?>

			ifElement ('hashover-dislike-' + permalink, function (dislikeLink) {
				// Add onClick event to "Dislike" hyperlinks
				dislikeLink.onclick = function ()
				{
					likeComment ('dislike', permalink);
					return false;
				};
			});
<?php endif; ?>
<?php endif; ?>
		}

		// Recursively execute this function on replies
		stepIntoReplies ();
	}

	// Returns a clone of an object
	function cloneObject (object)
	{
		return JSON.parse (JSON.stringify (object));
	}

	// "Flatten" the comments object
	function getAllComments (comments)
	{
		var commentsCopy = cloneObject (comments);
		var output = [];

		function descend (comment)
		{
			output.push (comment);

			if (comment.replies !== undefined) {
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

	// Run all comments in array data through parseComment function
	function parseAll (comments, element, collapse, popular, sort, method)
	{
		popular = popular || false;
		sort = sort || false;
		method = method || 'ascending';

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

	// Comment sorting
	function sortComments (method)
	{
		var tmpArray;
		var sortArray;

		function replyPropertySum (comment, callback)
		{
			var sum = 0;

			if (comment.replies !== undefined) {
				for (var i = 0, il = comment.replies.length; i < il; i++) {
					sum += replyPropertySum (comment.replies[i], callback);
				}
			}

			sum += callback (comment);

			return sum;
		}

		function replyCounter (comment)
		{
			return (comment.replies) ? comment.replies.length : 0;
		}

		function netLikes (comment)
		{
			var likes = comment.likes || 0;
			var dislikes = comment.dislikes || 0;

			return likes - dislikes;
		}

		// Sort methods
		switch (method) {
			case 'descending': {
				tmpArray = getAllComments (PHPContent.comments);
				sortArray = tmpArray.reverse ();
				break;
			}

			case 'by-date': {
				sortArray = getAllComments (PHPContent.comments).sort (function (a, b) {
					if (a['sort-date'] === b['sort-date']) {
						return 1;
					}

					return b['sort-date'] - a['sort-date'];
				});

				break;
			}

			case 'by-likes': {
				sortArray = getAllComments (PHPContent.comments).sort (function (a, b) {
					a.likes = a.likes || 0;
					b.likes = b.likes || 0;
					a.dislikes = a.dislikes || 0;
					b.dislikes = b.dislikes || 0;

					return (b.likes - b.dislikes) - (a.likes - a.dislikes);
				});

				break;
			}

			case 'by-replies': {
				tmpArray = cloneObject (PHPContent.comments);

				sortArray = tmpArray.sort (function (a, b) {
					var ac = (!!a.replies) ? a.replies.length : 0;
					var bc = (!!b.replies) ? b.replies.length : 0;

					return bc - ac;
				});

				break;
			}

			case 'by-discussion': {
				tmpArray = cloneObject (PHPContent.comments);

				sortArray = tmpArray.sort (function (a, b) {
					var replyCountA = replyPropertySum (a, replyCounter);
					var replyCountB = replyPropertySum (b, replyCounter);

					return replyCountB - replyCountA;
				});

				break;
			}

			case 'by-popularity': {
				tmpArray = cloneObject (PHPContent.comments);

				sortArray = tmpArray.sort (function (a, b) {
					var likeCountA = replyPropertySum (a, netLikes);
					var likeCountB = replyPropertySum (b, netLikes);

					return likeCountB - likeCountA;
				});

				break;
			}

			case 'by-name': {
				tmpArray = getAllComments (PHPContent.comments);

				sortArray = tmpArray.sort (function (a, b) {
					var nameA = (a.name || defaultName).toLowerCase ();
					var nameB = (b.name || defaultName).toLowerCase ();

					nameA = (nameA.charAt (0) === '@') ? nameA.slice (1) : nameA;
					nameB = (nameB.charAt (0) === '@') ? nameB.slice (1) : nameB;

					if (nameA > nameB) {
						return 1;
					}

					if (nameA < nameB) {
						return -1;
					}

					return 0;
				});

				break;
			}

			case 'threaded-descending': {
				tmpArray = cloneObject (PHPContent.comments);
				sortArray = tmpArray.reverse ();
				break;
			}

			case 'threaded-by-date': {
				tmpArray = cloneObject (PHPContent.comments);

				sortArray = tmpArray.sort (function (a, b) {
					if (a['sort-date'] === b['sort-date']) {
						return 1;
					}

					return b['sort-date'] - a['sort-date'];
				});

				break;
			}

			case 'threaded-by-likes': {
				tmpArray = cloneObject (PHPContent.comments);

				sortArray = tmpArray.sort (function (a, b) {
					a.likes = a.likes || 0;
					b.likes = b.likes || 0;
					a.dislikes = a.dislikes || 0;
					b.dislikes = b.dislikes || 0;

					return (b.likes - b.dislikes) - (a.likes - a.dislikes);
				});

				break;
			}

			case 'threaded-by-name': {
				tmpArray = cloneObject (PHPContent.comments);

				sortArray = tmpArray.sort (function (a, b) {
					var nameA = (a.name || defaultName).toLowerCase ();
					var nameB = (b.name || defaultName).toLowerCase ();

					nameA = (nameA.charAt (0) === '@') ? nameA.slice (1) : nameA;
					nameB = (nameB.charAt (0) === '@') ? nameB.slice (1) : nameB;

					if (nameA > nameB) {
						return 1;
					}

					if (nameA < nameB) {
						return -1;
					}

					return 0;
				});

				break;
			}

			default: {
				sortArray = PHPContent.comments;
				break;
			}
		}

		parseAll (sortArray, sortDiv, false, false, true, method);
	}

<?php if ($hashover->setup->appendsCSS !== false): ?>
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
	if (appendCSS === true) {
		var css = createElement ('link', {
			rel: 'stylesheet',
			href: themeCSS,
			type: 'text/css',
		});

		// Append comment stylesheet link element to page head
		head.appendChild (css);
	}

<?php endif; ?>
	// Put number of comments into "hashover-comment-count" identified HTML element
	if (totalCount !== 0) {
		ifElement ('hashover-comment-count', function (countElement) {
			countElement.textContent = totalCount;
		});
<?php if ($hashover->setup->APIStatus ('rss') !== 'disabled'): ?>

		// Create link element for comment RSS feed
		var rss = createElement ('link', {
			rel: 'alternate',
			href: httpRoot + '/api/rss.php?url=' + encodeURIComponent (URLHref),
			type: 'application/rss+xml',
			title: 'Comments'
		});

		// Append comment RSS feed link element to page head
		head.appendChild (rss);
<?php endif; ?>
	}

	// Initial HTML
<?php

	$initialHTML = $hashover->html->initialHTML ($hashover->commentParser->popularList, false);
	echo $hashover->html->asJSVar ($initialHTML, 'initialHTML');

?>

	// Create div tag for HashOver comments to appear in
	if (HashOverDiv === null) {
		HashOverDiv = createElement ('div', { id: 'hashover' });

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
	if (userIsLoggedIn === true) {
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

	// Get sort div element
	sortDiv = getElement ('hashover-sort-div');

	// Get primary form element
	HashOverForm = getElement ('hashover-form');

	// Display most popular comments
	ifElement ('hashover-top-comments', function (topComments) {
		if (PHPContent.popularComments[0] !== undefined) {
			parseAll (PHPContent.popularComments, topComments, false, true);
		}
	});

	// Add initial event handlers
	parseAll (PHPContent.comments, sortDiv, collapseComments);

<?php if ($hashover->setup->collapsesComments !== false): ?>
	// Check whether there are more than the collapse limit
	if (totalCount > collapseLimit) {
		// Create element for the comments
		moreDiv = createElement ('div', { id: 'hashover-more-section' });

		// If so, create "More Comments" hyperlink
		moreLink = createElement ('a', {
			href: '#',
			id: 'hashover-more-link',
			textContent: '<?php echo $more_link_text; ?>',

			onclick: function () {
				return showMoreComments (this);
			}
		});

		// Add more button link to sort div
		sortDiv.appendChild (moreDiv);

		// Add more button link to sort div
		sortDiv.appendChild (moreLink);
	} else {
		// If not, consider all comments shown
		showingMore = true;
	}

<?php endif; ?>
	// Attach event listeners to "Post Comment" button
	var postButton = getElement ('hashover-post-button');

	// Onclick
	postButton.onclick = function ()
	{
		return postComment (sortDiv, HashOverForm, postButton, AJAXPost);
	};

	// Onsubmit
	postButton.onsubmit = function ()
	{
		return postComment (sortDiv, HashOverForm, postButton, AJAXPost);
	};

<?php if ($hashover->setup->allowsLogin !== false): ?>
	// Attach event listeners to "Login" button
	if (userIsLoggedIn !== true) {
		var loginButton = getElement ('hashover-login-button');

		// Onclick
		loginButton.onclick = function ()
		{
			return validateComment (true, HashOverForm);
		};

		// Onsubmit
		loginButton.onsubmit = function ()
		{
			return validateComment (true, HashOverForm);
		};
	}

<?php endif; ?>
	// Five method sort
	ifElement ('hashover-sort-select', function (sortSelect) {
		sortSelect.onchange = function ()
		{
<?php if ($hashover->setup->collapsesComments !== false): ?>
			var sortSelectDiv = getElement ('hashover-sort');

			showMoreComments (sortSelectDiv, function () {
				sortDiv.textContent = '';
				sortComments (sortSelect.value);
			});
<?php else: ?>
			sortDiv.textContent = '';
			sortComments (sortSelect.value);
<?php endif; ?>
		};
	});

	// Display reply or edit form when the proper URL queries are set
	if (URLHref.match (/hashover-(reply|edit)=/)) {
		var permalink = URLHref.replace (/.*?hashover-(edit|reply)=(c[0-9r\-pop]+).*?/, '$2');

		if (!URLHref.match ('hashover-edit=')) {
<?php if ($hashover->setup->collapsesComments !== false): ?>
			// Show more comments
			showMoreComments (moreLink, function () {
				// Then display reply form
				hashoverReply (permalink);
			});
<?php else: ?>
			// Display reply form
			hashoverReply (permalink);
<?php endif; ?>
		} else {
			var isPop = permalink.match ('-pop');
			var comments = (isPop) ? PHPContent.popularComments : PHPContent.comments;
<?php if ($hashover->setup->collapsesComments !== false): ?>

			// Show more comments
			showMoreComments (moreLink, function () {
				// Then display edit form
				hashoverEdit (findByPermalink (permalink, comments));
			});
<?php else: ?>
			// Display edit form
			hashoverEdit (findByPermalink (permalink, comments));
<?php endif; ?>
		}
	}

	// Log execution time in JavaScript console
	if (window.console) {
		console.log ('HashOver executed in ' + (Date.now () - execStart) + ' ms.');
	}

	// Callback for scrolling a comment into view on page load
	var scroller = function ()
	{
		setTimeout (function () {
			// Workaround for stupid Chrome bug
			if (URLHash.match (/comments|hashover/)) {
				ifElement (URLHash, function (comments) {
					comments.scrollIntoView ({ behavior: 'smooth' });
				});
			}

			// Jump to linked comment
			if (URLHash.match (/c[0-9]+r*/)) {
<?php if ($hashover->setup->collapsesComments !== false): ?>
				var existingComment = getElement (URLHash);

				// Check if comment exists on the page and is visable
				if (existingComment !== null
				    && containsClass (existingComment, 'hashover-hidden') === false)
				{
					// If so, scroll the comment into view
					existingComment.scrollIntoView ({ behavior: 'smooth' });
				} else {
					// If not, show more comments
					showMoreComments (moreLink, function () {
						ifElement (URLHash, function (comment) {
							comment.scrollIntoView ({ behavior: 'smooth' });
						});
					});
				}
<?php else: ?>
				ifElement (URLHash, function (comment) {
					comment.scrollIntoView ({ behavior: 'smooth' });
				});
<?php endif; ?>
			}
		}, 500);
	};

	// Page onload compatibility wrapper
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
};

// Initiate HashOver
HashOver.init ();
