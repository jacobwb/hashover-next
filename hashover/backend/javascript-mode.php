<?php namespace HashOver;

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

// Count according to `$showsReplyCount` setting
$show_number_comments = $hashover->getCommentCount ('show-number-comments');

// Text for "Show X Other Comment(s)" link
if ($hashover->setup->collapsesComments !== false) {
	// Check if at least 1 comment is to be shown
	if ($hashover->setup->collapseLimit >= 1) {
		// If so, use the "Show X Other Comments" locale
		$more_link_locale = $hashover->locale->get ('show-other-comments');

		// Shorter variables
		$total_count = $hashover->readComments->totalCount;
		$collapse_limit = $hashover->setup->collapseLimit;

		// Get number of comments after collapse limit
		$other_count = ($total_count - 1) - $collapse_limit;

		// Subtract deleted comment counts
		if ($hashover->setup->countIncludesDeleted === false) {
			$other_count -= $hashover->readComments->collapsedDeletedCount;
		}

		// Decide if count is pluralized
		$more_link_plural = ($other_count !== 1) ? 1 : 0;
		$more_link_text = $more_link_locale[$more_link_plural];

		// And inject the count into the locale string
		$more_link_text = sprintf ($more_link_text, $other_count);
	} else {
		// If not, show count according to `$showsReplyCount` setting
		$more_link_text = $show_number_comments;
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
	$json_options = 0;
	$search = array ('\\/', "'", '"', "','", '    ', PHP_EOL);
	$replace = array ('/', "\'", "'", "', '", "\t", PHP_EOL . str_repeat ("\t", $tabs));

	// Enable pretty print where possible
	if ($pretty_print !== false and defined ('JSON_PRETTY_PRINT')) {
		$json_options |= JSON_PRETTY_PRINT;
	}

	// Check if Unicode escaping can be disabled
	if (defined ('JSON_UNESCAPED_UNICODE')) {
		// If so, encode string as JSON without Unicode escaping
		$json = json_encode ($string, $json_options | JSON_UNESCAPED_UNICODE);
	} else {
		// If not, encode string as JSON normally
		$json = json_encode ($string, $json_options);

		// And decode Unicode escaped characters
		$json = preg_replace_callback ('/\\\u([0-9a-f]{3,4})/i', function ($groups) {
			return html_entity_decode ('&#x' . $groups[1] . ';');
		}, $json);
	}

	// Conform JSON to coding standard
	$json = str_replace ($search, $replace, $json);

	return $json;
}

// Convert carrige return and line feeds to JavaScript variant
function js_eol ($string)
{
	return str_replace (array ("\r", "\n", "\t"), array ('\r', '\n', '\t'), $string);
}

?>
// @licstart  The following is the entire license notice for the
//  JavaScript code in this page.
//
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
//
// @licend  The above is the entire license notice for the
//  JavaScript code in this page.

"use strict";

// Initial HashOver constructor
function HashOver ()
{
	// Check if HashOver is prepared
	if (HashOver.prepared !== true) {
		// Some locales, stored in JavaScript to avoid using a lot of PHP tags
		HashOver.prototype.locale = {
			'cancel':		'<?php echo $hashover->locale->get ('cancel'); ?>',
			'date-time':		'<?php echo $hashover->locale->get ('date-time'); ?>',
			'dislike-comment':	'<?php echo $hashover->locale->get ('dislike-comment'); ?>',
			'disliked-comment':	'<?php echo $hashover->locale->get ('disliked-comment'); ?>',
			'disliked':		'<?php echo $hashover->locale->get ('disliked'); ?>',
			'dislike':		<?php echo js_json ($hashover->locale->get ('dislike', false), false); ?>,
			'external-image-tip':	'<?php echo $hashover->locale->get ('external-image-tip'); ?>',
			'field-needed':		'<?php echo $hashover->locale->get ('field-needed'); ?>',
			'like-comment':		'<?php echo $hashover->locale->get ('like-comment'); ?>',
			'liked-comment':	'<?php echo $hashover->locale->get ('liked-comment'); ?>',
			'liked':		'<?php echo $hashover->locale->get ('liked'); ?>',
			'like':			<?php echo js_json ($hashover->locale->get ('like', false), false); ?>,
			'post-comment-on':	'<?php echo $hashover->ui->postCommentOn; ?>',
			'show-number-comments':	'<?php echo $show_number_comments; ?>', // if collapsesUI
			'more-link-text':	'<?php echo $more_link_text; ?>',
			'today':		'<?php echo $hashover->locale->get ('date-today'); ?>',
			'unlike':		'<?php echo $hashover->locale->get ('unlike'); ?>',
			'commenter-tip':	'<?php echo $hashover->locale->get ('commenter-tip'); ?>',
			'subscribed-tip':	'<?php echo $hashover->locale->get ('subscribed-tip'); ?>',
			'unsubscribed-tip':	'<?php echo $hashover->locale->get ('unsubscribed-tip'); ?>',
			'replies':		'<?php echo $hashover->locale->get ('replies'); ?>',
			'reply':		'<?php echo $hashover->locale->get ('reply'); ?>',
			'no-email-warning':	'<?php echo $hashover->locale->get ('no-email-warning'); ?>',
			'invalid-email':	'<?php echo $hashover->locale->get ('invalid-email'); ?>',
			'reply-needed':		'<?php echo $hashover->locale->get ('reply-needed'); ?>',
			'comment-needed':	'<?php echo $hashover->locale->get ('comment-needed'); ?>',
			'delete-comment':	'<?php echo $hashover->locale->get ('delete-comment'); ?>',
			'loading':		'<?php echo $hashover->locale->get ('loading'); ?>',
			'click-to-close':	'<?php echo $hashover->locale->get ('click-to-close'); ?>',
			'email':		'<?php echo $hashover->locale->get ('email'); ?>',
			'name':			'<?php echo $hashover->locale->get ('name'); ?>',
			'password':		'<?php echo $hashover->locale->get ('password'); ?>',
			'website':		'<?php echo $hashover->locale->get ('website'); ?>',

			'day-names': <?php echo js_json ($hashover->locale->get ('date-day-names'), true, 3); ?>,

			'month-names': <?php echo js_json ($hashover->locale->get ('date-month-names'), true, 3), PHP_EOL; ?>
		};

		// Setup information received from the HashOver back-end
		HashOver.prototype.setup = {
			'server-eol':		'<?php echo str_replace (array ("\r", "\n"), array ('\r', '\n'), PHP_EOL); ?>',
			'collapse-limit':	<?php echo $hashover->misc->jsEscape ($hashover->setup->collapseLimit); ?>,
			'default-name':		'<?php echo $hashover->misc->jsEscape ($hashover->setup->defaultName); ?>',
			'user-is-logged-in':	<?php echo string_boolean ($hashover->login->userIsLoggedIn); ?>,
			'http-root':		'<?php echo $hashover->misc->jsEscape ($hashover->setup->httpRoot); ?>',
			'allows-dislikes':	<?php echo string_true ($allowsDislikes); ?>,
			'allows-likes':		<?php echo string_true ($allowsLikes); ?>,
			'time-format':		<?php echo js_json ($hashover->setup->timeFormat, false); ?>,
			'image-extensions':	<?php echo js_json ($hashover->setup->imageTypes, false); ?>,
			'image-placeholder':	'<?php echo $hashover->misc->jsEscape ($hashover->setup->httpImages); ?>/place-holder.<?php echo $hashover->misc->jsEscape ($hashover->setup->imageFormat); ?>',
			'stream-mode':		<?php echo string_boolean ($hashover->setup->replyMode, 'stream'); ?>,
			'stream-depth':		<?php echo $hashover->misc->jsEscape ($hashover->setup->streamDepth); ?>,
			'http-scripts':		'<?php echo $hashover->misc->jsEscape ($hashover->setup->httpScripts); ?>',
			'theme-css':		'<?php echo $hashover->misc->jsEscape ($hashover->setup->httpRoot); ?>/themes/<?php echo $hashover->misc->jsEscape ($hashover->setup->theme); ?>/style.css',
			'device-type':		'<?php echo $hashover->setup->isMobile === true ? 'mobile' : 'desktop'; ?>',
			'collapse-comments':	<?php echo string_true ($hashover->setup->collapsesComments); ?>,

			// Form field options
			'field-options': <?php echo js_json ($hashover->setup->fieldOptions, true, 3), PHP_EOL; ?>
		};

		// UI HTML from server back-end
		HashOver.prototype.ui = {
			'user-avatar':		'<?php echo $hashover->ui->userAvatar (); ?>',
			'name-link':		'<?php echo $hashover->ui->nameElement ('a'); ?>',
			'name-span':		'<?php echo $hashover->ui->nameElement ('span'); ?>',
			'thread-link':		'<?php echo $hashover->ui->threadLink (); ?>',
			'edit-link':		'<?php echo $hashover->ui->formLink ('edit'); ?>',
			'reply-link':		'<?php echo $hashover->ui->formLink ('reply'); ?>',
			'like-link':		'<?php echo $hashover->ui->likeLink ('like'); ?>',
			'dislike-link':		'<?php echo $hashover->ui->likeLink ('dislike'); ?>',
			'like-count':		'<?php echo $hashover->ui->likeCount ('likes'); ?>',
			'dislike-count':	'<?php echo $hashover->ui->likeCount ('dislikes'); ?>',
			'name-wrapper':		'<?php echo $hashover->ui->nameWrapper (); ?>',
			'date-link':		'<?php echo $hashover->ui->dateLink (); ?>',
			'comment-wrapper':	'<?php echo $hashover->ui->commentWrapper (); ?>',
			'initial-html':		'<?php echo js_eol ($hashover->ui->initialHTML (false)); ?>',
			'theme':		'<?php echo js_eol ($hashover->templater->parseTemplate ()); ?>',
			'reply-form':		'<?php echo js_eol ($hashover->ui->replyForm ()) ?>',
			'edit-form':		'<?php echo js_eol ($hashover->ui->editForm ()) ?>',
		};

		// Set initial reference count
		HashOver.instanceCount = 0;

		// Mark HashOver is prepared
		HashOver.prepared = true;
	}

	// Information received from the HashOver back-end
	this.instance = {
		'primary-count':	<?php echo $hashover->misc->jsEscape ($hashover->readComments->primaryCount - 1); ?>,
		'total-count':		<?php echo $hashover->misc->jsEscape ($hashover->readComments->totalCount - 1); ?>,
		'page-url':		'<?php echo $hashover->misc->jsEscape ($hashover->setup->pageURL); ?>',
		'thread-directory':	'<?php echo $hashover->misc->jsEscape ($hashover->setup->threadDirectory); ?>',
		'executing-script':	<?php echo js_json ($hashover->setup->executingScript, false); ?>,

		// Comments
		comments: HASHOVER_PHP_CONTENT
	};

	// Increment HashOver instance count
	HashOver.instanceCount++;

	// Set instance number to current instance count
	this.instanceNumber = HashOver.instanceCount;

	// Add parent proterty to all prototype objects
	for (var name in this) {
		var value = this[name];

		if (value && value.constructor === Object) {
			value.parent = this;
		}
	}
};

// Pre-compiled regular expressions
HashOver.prototype.regex = new (function () {
	this.urls		= '((http|https|ftp):\/\/[a-z0-9-@:;%_\+.~#?&\/=]+)',
	this.links		= new RegExp (this.urls + '( {0,1})', 'ig'),
	this.thread		= /^(c[0-9r]+)r[0-9\-pop]+$/,
	this.imageTags		= new RegExp ('\\[img\\]<a.*?>' + this.urls + '</a>\\[/img\\]', 'ig'),
	this.EOLTrim		= /^[\r\n]+|[\r\n]+$/g,
	this.paragraphs		= /(?:\r\n|\r|\n){2}/g,
	this.email		= /\S+@\S+/
}) ();

// Collection of convenient element functions
HashOver.prototype.elements = {
	cache: {},

	// Shorthand for Document.getElementById ()
	get: function (id, force)
	{
		if (force === true || !this.cache[id]) {
			this.cache[id] = document.getElementById (id);
		}

		return this.cache[id];
	},

	// Execute callback function if element isn't false
	exists: function (element, callback)
	{
		if (element = this.get (element, true)) {
			return callback (element);
		}

		return false;
	},

	// Adds properties to an element
	addProperties: function (element, properties)
	{
		element = element || document.createElement ('span');
		properties = properties || {};

		var value;

		// Add each property to element
		for (var property in properties) {
			if (properties.hasOwnProperty (property) === false) {
				continue;
			}

			// Property value
			value = properties[property];

			// If the property is an object add each item to existing property
			if (!!value && value.constructor === Object) {
				this.addProperties (element[property], value);
				continue;
			}

			element[property] = value;
		}

		return element;
	},

	// Creates an element with attributes
	create: function (tagName, attributes)
	{
		tagName = tagName || 'span';
		attributes = attributes || {};

		// Create element
		var element = document.createElement (tagName);

		// Add properties to element
		element = this.addProperties (element, attributes);

		return element;
	}
};

// Trims leading and trailing newlines from a string
HashOver.prototype.EOLTrim = function (string)
{
	return string.replace (this.regex.EOLTrim, '');
};

// Returns the permalink of a comment's parent
HashOver.prototype.getParentPermalink = function (permalink, flatten)
{
	flatten = flatten || false;

	var parent = permalink.split ('r');
	var length = parent.length - 1;

	// Limit depth if in stream mode
	if (this.setup['stream-mode'] === true && flatten === true) {
		length = Math.min (this.setup['stream-depth'], length);
	}

	// Check if there is a parent after flatten
	if (length > 0) {
		// If so, remove child from permalink
		parent = parent.slice (0, length);

		// Return parent permalink as string
		return parent.join ('r');
	}

	return null;
};

// Find a comment by its permalink
HashOver.prototype.findByPermalink = function (permalink, comments)
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
			comment = this.findByPermalink (permalink, comments[i].replies);

			if (comment !== null) {
				return comment;
			}
		}
	}

	// Otherwise return null
	return null;
};

// Parses a string as markdown
HashOver.prototype.markdown = {
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
		var codeMarker;

		if (display !== 'block') {
			codeMarker = first + markName + '[' + markCount + ']' + third;
			this.codeMarkers[display].marks[markCount] = this.parent.EOLTrim (second);
		} else {
			codeMarker = markName + '[' + markCount + ']';
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
<?php if ($hashover->setup->usesUserTimezone !== false): ?>

// Collection of convenient date and time functions
HashOver.prototype.dateTime = {
	offsetRegex: /[0-9]{2}/g,
	dashesRegex: /-/g,

	// Simple PHP date function port
	format: function (format, date)
	{
		format = format || 'DATE_ISO8601';
		date = date || new Date ();

		var hours = date.getHours ();
		var ampm = (hours >= 12) ? 'pm' : 'am';
		var day = date.getDate ();
		var weekDay = date.getDay ();
		var dayName = this.parent.locale['day-names'][weekDay];
		var monthIndex = date.getMonth ();
		var monthName = this.parent.locale['month-names'][monthIndex];
		var hours12 = (hours % 12) ? hours % 12 : 12;
		var minutes = date.getMinutes ();
		var month = monthIndex + 1;
		var offsetHours = (date.getTimezoneOffset() / 60) * 100;
		var offset = ((offsetHours < 1000) ? '0' : '') + offsetHours;
		var offsetColon = offset.match (this.offsetRegex).join (':');
		var offsetPositivity = (offsetHours > 0) ? '-' : '+';
		var seconds = date.getSeconds ();
		var year = date.getFullYear ();
		var dateConstant;

		var characters = {
			a: ampm,
			A: ampm.toUpperCase (),
			d: (day < 10) ? '0' + day : day,
			D: dayName.substr (0, 3),
			F: monthName,
			g: hours12,
			G: hours,
			h: (hours12 < 10) ? '0' + hours12 : hours12,
			H: (hours < 10) ? '0' + hours : hours,
			i: (minutes < 10) ? '0' + minutes : minutes,
			j: day,
			l: dayName,
			m: (month < 10) ? '0' + month : month,
			M: monthName.substr (0, 3),
			n: month,
			N: weekDay + 1,
			O: offsetPositivity + offset,
			P: offsetPositivity + offsetColon,
			s: (seconds < 10) ? '0' + seconds : seconds,
			w: weekDay,
			y: ('' + year).substr (2),
			Y: year
		};

		dateConstant = format.replace (this.dashesRegex, '_');
		dateConstant = dateConstant.toUpperCase ();

		switch (dateConstant) {
			case 'DATE_ATOM':
			case 'DATE_RFC3339':
			case 'DATE_W3C': {
				format = 'Y-m-d\TH:i:sP';
				break;
			}

			case 'DATE_COOKIE': {
				format = 'l, d-M-Y H:i:s';
				break;
			}

			case 'DATE_ISO8601': {
				format = 'Y-m-d\TH:i:sO';
				break;
			}

			case 'DATE_RFC822':
			case 'DATE_RFC1036': {
				format = 'D, d M y H:i:s O';
				break;
			}

			case 'DATE_RFC850': {
				format = 'l, d-M-y H:i:s';
				break;
			}

			case 'DATE_RFC1123':
			case 'DATE_RFC2822':
			case 'DATE_RSS': {
				format = 'D, d M Y H:i:s O';
				break;
			}

			case 'GNOME_DATE': {
				format = 'D M d, g:i A';
				break;
			}

			case 'US_DATE': {
				format = 'm/d/Y';
				break;
			}

			case 'STANDARD_DATE': {
				format = 'Y-m-d';
				break;
			}

			case '12H_TIME': {
				format = 'g:ia';
				break;
			}

			case '24H_TIME': {
				format = 'H:i';
				break;
			}
		}

		var formatParts = format.split ('');

		for (var i = 0, c, il = formatParts.length; i < il; i++) {
			if (i > 0 && formatParts[i - 1] === '\\') {
				formatParts[i - 1] = '';
				continue;
			}

			c = formatParts[i];
			formatParts[i] = characters[c] || c;
		}

		return formatParts.join ('');
	}
};
<?php endif; ?>

// Collection of convenient string related functions
HashOver.prototype.strings = {
	// sprintf specifiers regular expression
	specifiers: /%([cdfs])/g,

	// Curly-brace variable regular expression
	curlyRegex: /\{\{([a-z_-]+)\}\}/ig,

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

	// Parses an HTML template
	parseTemplate: function (string, template)
	{
		return string.replace (this.curlyRegex, function (string, key) {
			return template[key] || '';
		});
	}
};

// Collection of comment parsing functions
HashOver.prototype.comments = {
	collapsedCount: 0,
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
	parse: function (comment, parent, collapse, sort, method, popular)
	{
		parent = parent || null;
		collapse = collapse || false;
		sort = sort || false;
		method = method || 'ascending';
		popular = popular || false;

		// Reference to the parent object
		var hashover = this.parent;

		var permalink = comment.permalink;
		var nameClass = 'hashover-name-plain';
		var template = { permalink: permalink };
		var isReply = (parent !== null);
		var parentPermalink;
		var commentDate = comment.date;
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

		// Trims whitespace from an HTML tag's inner HTML
		function tagTrimmer (fullTag, openTag, innerHTML, closeTag)
		{
			return openTag + hashover.EOLTrim (innerHTML) + closeTag;
		}

		// Get parent comment via permalink
		if (isReply === false && permalink.indexOf ('r') > -1) {
			parentPermalink = hashover.getParentPermalink (permalink);
			parent = hashover.findByPermalink (parentPermalink, hashover.instance.comments.primary);
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
			if (hashover.instance['total-count'] > 0) {
				if (collapse === true && this.collapsedCount >= hashover.setup['collapse-limit']) {
					classes += ' hashover-hidden';
				} else {
					this.collapsedCount++;
				}
			}
<?php endif; ?>
		}

		// Add avatar image to template
		template.avatar = hashover.strings.sprintf (hashover.ui['user-avatar'], [
			comment.avatar, permatext, permalink
		]);

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
				var nameElement = hashover.strings.sprintf (hashover.ui['name-link'], [
					website, permalink, name, name
				]);
			} else {
				// If not, display name as plain text
				var nameElement = hashover.strings.sprintf (hashover.ui['name-span'], [
					permalink, name
				]);
			}

			// Construct thread hyperlink
			if (isReply === true) {
				var parentThread = parent.permalink;
				var parentName = parent.name || hashover.setup['default-name'];

				// Add thread parent hyperlink to template
				template['thread-link'] = hashover.strings.sprintf (hashover.ui['thread-link'], [
					parentThread, permalink, parentName
				]);
			}

			if (comment['user-owned'] !== undefined) {
				// Append class to indicate comment is from logged in user
				classes += ' hashover-user-owned';

				// Define "Reply" link with original poster title
				var replyTitle = hashover.locale['commenter-tip'];
				var replyClass = 'hashover-no-email';

				// Add "Edit" hyperlink to template
				if (comment['editable'] !== undefined) {
					template['edit-link'] = hashover.strings.sprintf (hashover.ui['edit-link'], [
						permalink, permalink, permalink
					]);
				}
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
<?php if ($allowsLikes !== false): ?>

				// Check whether this comment was liked by the visitor
				if (comment.liked !== undefined) {
					// If so, set various attributes to indicate comment was liked
					var likeClass = 'hashover-liked';
					var likeTitle = hashover.locale['liked-comment'];
					var likeText = hashover.locale['liked'];
				} else {
					// If not, set various attributes to indicate comment can be liked
					var likeClass = 'hashover-like';
					var likeTitle = hashover.locale['like-comment'];
					var likeText = hashover.locale['like'][0];
				}

				// Append class to indicate dislikes are enabled
				if (hashover.setup['allows-dislikes'] === true) {
					likeClass += ' hashover-dislikes-enabled';
				}

				// Add like link to HTML template
				template['like-link'] = hashover.strings.sprintf (hashover.ui['like-link'], [
					permalink, likeClass, likeTitle, likeText
				]);
<?php endif; ?>
<?php if ($allowsDislikes === true): ?>

				// Check whether this comment was disliked by the visitor
				if (comment.disliked !== undefined) {
					// If so, set various attributes to indicate comment was disliked
					var dislikeClass = 'hashover-disliked';
					var dislikeTitle = hashover.locale['disliked-comment'];
					var dislikeText = hashover.locale['disliked'];
				} else {
					// If not, set various attributes to indicate comment can be disliked
					var dislikeClass = 'hashover-dislike';
					var dislikeTitle = hashover.locale['dislike-comment'];
					var dislikeText = hashover.locale['dislike'][0];
				}

				// Append class to indicate likes are enabled
				if (hashover.setup['allows-likes'] === true) {
					dislikeClass += ' hashover-likes-enabled';
				}

				// Add dislike link to HTML template
				template['dislike-link'] = hashover.strings.sprintf (hashover.ui['dislike-link'], [
					permalink, dislikeClass, dislikeTitle, dislikeText
				]);
<?php endif; ?>
			}

<?php if ($allowsLikes !== false): ?>
			// Check if the comment has been liked
			if (comment.likes !== undefined) {
				// Add likes to HTML template
				template['likes'] = comment.likes;

				// Get "X Like(s)" locale
				var likePlural = (comment.likes === 1 ? 0 : 1);
				var likeCount = comment.likes + ' ' + hashover.locale['like'][likePlural];

				// Add like count to HTML template
				template['like-count'] = hashover.strings.sprintf (hashover.ui['like-count'], [
					permalink, likeCount
				]);
			}

<?php endif; ?>
<?php if ($allowsDislikes === true): ?>
			// Check if the comment has been disliked
			if (comment.dislikes !== undefined) {
				// Add dislikes to HTML template
				template['dislikes'] = comment.likes;

				// Get "X Dislike(s)" locale
				var dislikePlural = (comment.dislikes === 1 ? 0 : 1);
				var dislikeCount = comment.dislikes + ' ' + hashover.locale['dislike'][dislikePlural];

				// Add dislike count to HTML template
				template['dislike-count'] = hashover.strings.sprintf (hashover.ui['dislike-count'], [
					permalink, dislikeCount
				]);
			}

<?php endif; ?>
			// Add name HTML to template
			template.name = hashover.strings.sprintf (hashover.ui['name-wrapper'], [
				nameClass, nameElement
			]);

<?php if ($hashover->setup->usesUserTimezone !== false): ?>
			// Local comment post date
			var postDate = new Date (comment['sort-date'] * 1000);

<?php if ($hashover->setup->usesShortDates !== false): ?>
			// Local comment post date to remove time from
			var postDateCopy = new Date (postDate.getTime ());

			// Local date
			var localDate = new Date ();

			// Format local time if the comment was posted today
			if (postDateCopy.setHours (0, 0, 0, 0) === localDate.setHours (0, 0, 0, 0)) {
				commentDate = hashover.strings.sprintf (hashover.locale['today'], [
					hashover.dateTime.format (hashover.setup['time-format'], postDate)
				]);
			}
<?php else: ?>
			// Format a long local date/time
			commentDate = hashover.dateTime.format (hashover.locale['date-time'], postDate);
<?php endif; ?>
<?php endif; ?>

			// Add date from comment as permalink hyperlink to template
			template.date = hashover.strings.sprintf (hashover.ui['date-link'], [
				permalink, commentDate
			]);

			// Add "Reply" hyperlink to template
			template['reply-link'] = hashover.strings.sprintf (hashover.ui['reply-link'], [
				permalink, permalink, permalink, replyClass, replyTitle
			]);

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
<?php if ($hashover->setup->allowsImages !== false): ?>
				// Get image extension from URL
				var urlExtension = url.split ('#')[0];
				    urlExtension = urlExtension.split ('?')[0];
				    urlExtension = urlExtension.split ('.');
				    urlExtension = urlExtension.pop ();

				// Check if the image extension is an allowed type
				if (hashover.setup['image-extensions'].indexOf (urlExtension) > -1) {
					// If so, create a wrapper element for the embedded image
					var embeddedImage = hashover.elements.create ('span', {
						className: 'hashover-embedded-image-wrapper'
					});

					// Append an image tag to the embedded image wrapper
					embeddedImage.appendChild (hashover.elements.create ('img', {
						className: 'hashover-embedded-image',
						src: hashover.setup['image-placeholder'],
						title: hashover.locale['external-image-tip'],
						alt: 'External Image',

						dataset: {
							placeholder: hashover.setup['image-placeholder'],
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
			body = hashover.markdown.parse (body);

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
			template.name = hashover.strings.sprintf (hashover.ui['name-wrapper'], [
				nameClass, comment.title
			]);
		}

		// Comment HTML template
		var html = hashover.strings.parseTemplate (hashover.ui.theme, template);

		// Recursively parse replies
		if (comment.replies !== undefined) {
			for (var reply = 0, total = comment.replies.length; reply < total; reply++) {
				replies += this.parse (comment.replies[reply], comment, collapse);
			}
		}

		return hashover.strings.sprintf (hashover.ui['comment-wrapper'], [
			permalink, classes, html + replies
		]);
	}
};

// Generate file from permalink
HashOver.prototype.fileFromPermalink = function (permalink)
{
	var file = permalink.slice (1);
	    file = file.replace (/r/g, '-');
	    file = file.replace ('-pop', '');

	return file;
};

// Change and hyperlink, like "Edit" or "Reply", into a "Cancel" hyperlink
HashOver.prototype.cancelSwitcher = function (form, link, wrapper, permalink)
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
	link.textContent = this.locale['cancel'];
	link.title = this.locale['cancel'];

	// This resets the "Cancel" hyperlink to initial state onClick
	link.onclick = linkOnClick;
<?php if ($hashover->setup->usesCancelButtons !== false): ?>

	// Get "Cancel" button
	var cancelButtonId = 'hashover-' + form + '-cancel-' + permalink;
	var cancelButton = this.elements.get (cancelButtonId, true);

	// Attach event listeners to "Cancel" button
	cancelButton.onclick = linkOnClick;
<?php endif; ?>
};

// Returns false if key event is the enter key
HashOver.prototype.enterCheck = function (event)
{
	return (event.keyCode === 13) ? false : true;
};

// Prevents enter key on inputs from submitting form
HashOver.prototype.preventSubmit = function (form)
{
	// Get login info inputs
	var infoInputs = form.getElementsByClassName ('hashover-input-info');

	// Set enter key press to return false
	for (var i = 0, il = infoInputs.length; i < il; i++) {
		infoInputs[i].onkeypress = this.enterCheck;
	}
};

// Collection of element class related functions
HashOver.prototype.classes = new (function () {
	// Check whether browser has classList support
	if (document.documentElement.classList) {
		// If so, wrap relevant functions
		// classList.contains () method
		this.contains = function (element, className)
		{
			return element.classList.contains (className);
		};

		// classList.add () method
		this.add = function (element, className)
		{
			element.classList.add (className);
		};

		// classList.remove () method
		this.remove = function (element, className)
		{
			element.classList.remove (className);
		};
	} else {
		// If not, define fallback functions
		// classList.contains () method
		this.contains = function (element, className)
		{
			if (!element || !element.className) {
				return false;
			}

			var regex = new RegExp ('(^|\\s)' + className + '(\\s|$)');
			return regex.test (element.className);
		};

		// classList.add () method
		this.add = function (element, className)
		{
			if (!element) {
				return false;
			}

			if (!this.contains (element, className)) {
				element.className += (element.className ? ' ' : '') + className;
			}
		};

		// classList.remove () method
		this.remove = function (element, className)
		{
			if (!element || !element.className) {
				return false;
			}

			var regex = new RegExp ('(^|\\s)' + className + '(\\s|$)', 'g');
			element.className = element.className.replace (regex, '$2');
		};
	}
}) ();

// Collection of HashOver message element related functions
HashOver.prototype.messages = {
	timeouts: {},

	// Gets a computed element style by property
	computeStyle: function (element, proterty, type)
	{
		var computedStyle;

		// IE, other
		if (window.getComputedStyle === undefined) {
			computedStyle = element.currentStyle[proterty];
		}

		// Mozilla Firefox, Google Chrome
		computedStyle = window.getComputedStyle (element, null);
		computedStyle = computedStyle.getPropertyValue (proterty);

		// Cast value to specified type
		switch (type) {
			case 'int': {
				computedStyle = computedStyle.replace (/px|em/, '');
				computedStyle = parseInt (computedStyle) || 0;
				break;
			}

			case 'float': {
				computedStyle = computedStyle.replace (/px|em/, '');
				computedStyle = parseFloat (computedStyle) || 0.0;
				break;
			}
		}

		return computedStyle;
	},

	// Gets the client height of a message element
	getHeight: function (element, setChild)
	{
		setChild = setChild || false;

		var firstChild = element.children[0];
		var maxHeight = 80;

		// If so, set max-height style to initial
		firstChild.style.maxHeight = 'initial';

		// Get various computed styles
		var borderTop = this.computeStyle (firstChild, 'border-top-width', 'int');
		var borderBottom = this.computeStyle (firstChild, 'border-bottom-width', 'int');
		var marginBottom = this.computeStyle (firstChild, 'margin-bottom', 'int');
		var border = borderTop + borderBottom;

		// Calculate its client height
		maxHeight = firstChild.clientHeight + border + marginBottom;

		// Set its max-height style as well if told to
		if (setChild === true) {
			firstChild.style.maxHeight = maxHeight + 'px';
		} else {
			firstChild.style.maxHeight = '';
		}

		return maxHeight;
	},

	// Open a message element
	open: function (element)
	{
		// Add classes to indicate message element is open
		this.parent.classes.remove (element, 'hashover-message-animated');
		this.parent.classes.add (element, 'hashover-message-open');

		var maxHeight = this.getHeight (element);
		var firstChild = element.children[0];

		// Reference to the parent object
		var parent = this.parent;

		// Remove class indicating message element is open
		this.parent.classes.remove (element, 'hashover-message-open');

		setTimeout (function () {
			// Add class to indicate message element is open
			parent.classes.add (element, 'hashover-message-open');
			parent.classes.add (element, 'hashover-message-animated');

			// Set max-height styles
			element.style.maxHeight = maxHeight + 'px';
			firstChild.style.maxHeight = maxHeight + 'px';

			// Set max-height style to initial after transition
			setTimeout (function () {
				element.style.maxHeight = 'initial';
				firstChild.style.maxHeight = 'initial';
			}, 150);
		}, 150);
	},

	// Close a message element
	close: function (element)
	{
		// Set max-height style to specific height before transition
		element.style.maxHeight = this.getHeight (element, true) + 'px';

		// Reference to the parent object
		var parent = this.parent;

		setTimeout (function () {
			// Remove max-height style from message elements
			element.children[0].style.maxHeight = '';
			element.style.maxHeight = '';

			// Remove classes indicating message element is open
			parent.classes.remove (element, 'hashover-message-open');
			parent.classes.remove (element, 'hashover-message-error');
		}, 150);
	},

	// Handle message element(s)
	show: function (messageText, type, permalink, error, isReply, isEdit)
	{
		type = type || 'main';
		permalink = permalink || '';
		error = error || true;
		isReply = isReply || false;
		isEdit = isEdit || false;

		var container;
		var message;

		// Reference to this object
		var messages = this;

		// Decide which message element to use
		if (isEdit === true) {
			// An edit form message
			container = this.parent.elements.get ('hashover-edit-message-container-' + permalink, true);
			message = this.parent.elements.get ('hashover-edit-message-' + permalink, true);
		} else {
			if (isReply !== true) {
				// The primary comment form message
				container = this.parent.elements.get ('hashover-message-container', true);
				message = this.parent.elements.get ('hashover-message', true);
			} else {
				// Of a reply form message
				container = this.parent.elements.get ('hashover-reply-message-container-' + permalink, true);
				message = this.parent.elements.get ('hashover-reply-message-' + permalink, true);
			}
		}

		if (messageText !== undefined && messageText !== '') {
			// Add message text to element
			message.textContent = messageText;

			// Add class to indicate message is an error if set
			if (error === true) {
				this.parent.classes.add (container, 'hashover-message-error');
			}
		}

		// Add class to indicate message element is open
		this.open (container);

		// Add the comment to message counts
		if (this.timeouts[permalink] === undefined) {
			this.timeouts[permalink] = {};
		}

		// Clear necessary timeout
		if (this.timeouts[permalink][type] !== undefined) {
			clearTimeout (this.timeouts[permalink][type]);
		}

		// Add timeout to close message element after 10 seconds
		this.timeouts[permalink][type] = setTimeout (function () {
			messages.close (container);
		}, 10000);
	}
};

// Handles display of various warnings when user attempts to post or login
HashOver.prototype.emailValidator = function (form, subscribe, type, permalink, isReply, isEdit)
{
	if (form.email === undefined) {
		return true;
	}

	// Whether the e-mail form is empty
	if (form.email.value === '') {
		// Return true if user unchecked the subscribe checkbox
		if (this.elements.get (subscribe, true).checked === false) {
			return true;
		}

		// If so, warn the user that they won't receive reply notifications
		if (confirm (this.locale['no-email-warning']) === false) {
			form.email.focus ();
			return false;
		}
	} else {
		var message;

		// If not, check if the e-mail is valid
		if (this.regex.email.test (form.email.value) === false) {
			// Return true if user unchecked the subscribe checkbox
			if (this.elements.get (subscribe, true).checked === false) {
				form.email.value = '';
				return true;
			}

			message = this.locale['invalid-email'];
			this.messages.show (message, type, permalink, true, isReply, isEdit);
			form.email.focus ();

			return false;
		}
	}

	return true;
};

// Validate a comment form e-mail field
HashOver.prototype.validateEmail = function (type, permalink, form, isReply, isEdit)
{
	type = type || 'main';
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
	return this.emailValidator (form, subscribe, type, permalink, isReply, isEdit);
};

// Validate a comment form
HashOver.prototype.commentValidator = function (form, skipComment, isReply)
{
	skipComment = skipComment || false;

	// Check each input field for if they are required
	for (var field in this.setup['field-options']) {
		// Skip other people's prototypes
		if (this.setup['field-options'].hasOwnProperty (field) !== true) {
			continue;
		}

		// Check if the field is required, and that the input exists
		if (this.setup['field-options'][field] === 'required' && form[field] !== undefined) {
			// Check if it has a value
			if (form[field].value === '') {
				// If not, add a class indicating a failed post
				this.classes.add (form[field], 'hashover-emphasized-input');

				// Focus the input
				form[field].focus ();

				// Return error message to display to the user
				return this.strings.sprintf (this.locale['field-needed'], [
					this.locale[field]
				]);
			}

			// Remove class indicating a failed post
			this.classes.remove (form[field], 'hashover-emphasized-input');
		}
	}

	// Check if a comment was given
	if (skipComment !== true && form.comment.value === '') {
		// If not, add a class indicating a failed post
		this.classes.add (form.comment, 'hashover-emphasized-input');

		// Focus the comment textarea
		form.comment.focus ();

		// Error message to display to the user
		var localeKey = (isReply === true) ? 'reply-needed' : 'comment-needed';
		var errorMessage = this.locale[localeKey];

		// Return a error message to display to the user
		return errorMessage;
	}

	return true;
};

// Validate required comment credentials
HashOver.prototype.validateComment = function (skipComment, form, type, permalink, isReply, isEdit)
{
	skipComment = skipComment || false;
	type = type || 'main';
	permalink = permalink || null;
	isReply = isReply || false;
	isEdit = isEdit || false;

	// Validate comment form
	var message = this.commentValidator (form, skipComment, isReply);

	// Display the validator's message
	if (message !== true) {
		this.messages.show (message, type, permalink, true, isReply, isEdit);
		return false;
	}

	// Validate e-mail if user isn't logged in or is editing
	if (this.setup['user-is-logged-in'] === false || isEdit === true) {
		// Return false on any failure
		if (this.validateEmail (type, permalink, form, isReply, isEdit) === false) {
			return false;
		}
	}

	return true;
};

// For posting comments, both traditionally and via AJAX
HashOver.prototype.postComment = function (destination, form, button, callback, type, permalink, close, isReply, isEdit)
{
	type = type || 'main';
	permalink = permalink || '';
	close = close || null;
	isReply = isReply || false;
	isEdit = isEdit || false;

	// Reference to this object
	var hashover = this;

	// Return false if comment is invalid
	if (this.validateComment (false, form, type, permalink, isReply, isEdit) === false) {
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

	// AJAX response handler
	function commentHandler ()
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
			callback.apply (hashover, [json, permalink, destination, isReply]);

			// Execute callback function if one was provided
			if (close !== null) {
				close ();
			}

			// Scroll comment into view
			scrollToElement = hashover.elements.get (json.comment.permalink, true);
			scrollToElement.scrollIntoView ({ behavior: 'smooth' });

			// Clear form
			form.comment.value = '';
		} else {
			// If not, display the message return instead
			hashover.messages.show (json.message, type, permalink, (json.type === 'error'), isReply, isEdit);
			return false;
		}

		// Re-enable button on success
		setTimeout (function () {
			button.disabled = false;
		}, 1000);
	}

	// Sends a request to post a comment
	function sendRequest ()
	{
		// Handle AJAX request return data
		httpRequest.onreadystatechange = commentHandler;

		// Send post comment request
		httpRequest.open ('POST', form.action, true);
		httpRequest.setRequestHeader ('Content-type', 'application/x-www-form-urlencoded');
		httpRequest.send (queries.join ('&'));
	}

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

<?php if ($hashover->setup->usesAutoLogin !== false): ?>
	// Check if the user is logged in
	if (this.setup['user-is-logged-in'] !== true || isEdit === true) {
		// If not, send a login request
		var loginRequest = new XMLHttpRequest ();
		var loginQueries = queries.concat (['login=Login']);

		// Handle AJAX request return data
		loginRequest.onreadystatechange = function ()
		{
			// Do nothing if request wasn't successful in a meaningful way
			if (this.readyState !== 4 || this.status !== 200) {
				return;
			}

			// Send post comment request after login
			hashover.setup['user-is-logged-in'] = true;
			sendRequest ();
		};

		// Send login request
		loginRequest.open ('POST', form.action, true);
		loginRequest.setRequestHeader ('Content-type', 'application/x-www-form-urlencoded');
		loginRequest.send (loginQueries.join ('&'));
	} else {
		// If so, send post comment request normally
		sendRequest ();
	}
<?php else: ?>
	// Send post comment request
	sendRequest ();
<?php endif; ?>

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
HashOver.prototype.HTMLToNodeList = function (html)
{
	return this.elements.create ('div', { innerHTML: html }).childNodes;
};

// Increase comment counts
HashOver.prototype.incrementCounts = function (isReply)
{
	// Count top level comments
	if (isReply === false) {
		this.instance['primary-count']++;
	}

	// Increase all count
	this.instance['total-count']++;
};

// For adding new comments to comments array
HashOver.prototype.addComments = function (comment, isReply, index)
{
	isReply = isReply || false;
	index = index || null;

	// Check that comment is not a reply
	if (isReply !== true) {
		// If so, add to primary comments
		if (index !== null) {
			this.instance.comments.primary.splice (index, 0, comment);
			return;
		}

		this.instance.comments.primary.push (comment);
		return;
	}

	// If not, fetch parent comment
	var parentPermalink = this.getParentPermalink (comment.permalink);
	var parent = this.findByPermalink (parentPermalink, this.instance.comments.primary);

	// Check if the parent comment exists
	if (parent !== null) {
		// If so, check if comment has replies
		if (parent.replies !== undefined) {
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

	// Otherwise, add to primary comments
	this.instance.comments.primary.push (comment);
};

// For posting comments
HashOver.prototype.AJAXPost = function (json, permalink, destination, isReply)
{
	// If there aren't any comments, replace first comment message
	if (this.instance['total-count'] === 0) {
		this.instance.comments.primary[0] = json.comment;
		destination.innerHTML = this.comments.parse (json.comment);
	} else {
		// Add comment to comments array
		this.addComments (json.comment, isReply);

		// Create div element for comment
		var commentNode = this.HTMLToNodeList (this.comments.parse (json.comment));

		// Append comment to parent element
		if (this.setup['stream-mode'] === true && permalink.split('r').length > this.setup['stream-depth']) {
			destination.parentNode.insertBefore (commentNode[0], destination.nextSibling);
		} else {
			destination.appendChild (commentNode[0]);
		}
	}

	// Add controls to the new comment
	this.addControls (json.comment);

	// Update comment count
	this.elements.get ('hashover-count').textContent = json.count;
	this.incrementCounts (isReply);
};

// For editing comments
HashOver.prototype.AJAXEdit = function (json, permalink, destination, isReply)
{
	// Get old comment element nodes
	var comment = this.elements.get (permalink, true);
	var oldNodes = comment.childNodes;
	var oldComment = this.findByPermalink (permalink, this.instance.comments.primary);

	// Get new comment element nodes
	var newNodes = this.HTMLToNodeList (this.comments.parse (json.comment));
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
	this.addControls (json.comment);

	// Update old in array comment with edited comment
	for (var attribute in json.comment) {
		oldComment[attribute] = json.comment[attribute];
	}
};
<?php endif; ?>

// Attach click event to formatting revealer hyperlinks
HashOver.prototype.formattingOnclick = function (type, permalink)
{
	permalink = (permalink !== undefined) ? '-' + permalink : '';

	// Reference to this object
	var hashover = this;

	// Get formatting message elements
	var formattingID = 'hashover-' + type + '-formatting';
	var formatting = this.elements.get (formattingID + permalink, true);
	var formattingMessage = this.elements.get (formattingID + '-message' + permalink, true);

	// Attach click event to formatting revealer hyperlink
	formatting.onclick = function ()
	{
		if (hashover.classes.contains (formattingMessage, 'hashover-message-open')) {
			hashover.messages.close (formattingMessage);
			return false;
		}

		hashover.messages.open (formattingMessage);
		return false;
	}
};

// Displays reply form
HashOver.prototype.replyToComment = function (permalink)
{
	// Reference to this object
	var hashover = this;

	// Get reply link element
	var link = this.elements.get ('hashover-reply-link-' + permalink, true);

	// Get file
	var file = this.fileFromPermalink (permalink);

	// Create reply form element
	var form = this.elements.create ('form', {
		id: 'hashover-reply-' + permalink,
		className: 'hashover-reply-form',
		method: 'post',
		action: this.setup['http-scripts'] + '/postcomments.php'
	});

	// Place reply fields into form
	form.innerHTML = hashover.strings.parseTemplate (hashover.ui['reply-form'], {
		permalink: permalink,
		file: file
	});

	// Prevent input submission
	this.preventSubmit (form);

	// Add form to page
	var replyForm = this.elements.get ('hashover-placeholder-reply-form-' + permalink, true);
	    replyForm.appendChild (form);

	// Change "Reply" link to "Cancel" link
	this.cancelSwitcher ('reply', link, replyForm, permalink);

	// Attach event listeners to "Post Reply" button
	var postReply = this.elements.get ('hashover-reply-post-' + permalink, true);

	// Get the element of comment being replied to
	var destination = this.elements.get (permalink, true);

	// Attach click event to formatting revealer hyperlink
	this.formattingOnclick ('reply', permalink);

	// Onclick
	postReply.onclick = function ()
	{
		return hashover.postComment (destination, form, this, hashover.AJAXPost, 'reply', permalink, link.onclick, true, false);
	};

	// Onsubmit
	postReply.onsubmit = function ()
	{
		return hashover.postComment (destination, form, this, hashover.AJAXPost, 'reply', permalink, link.onclick, true, false);
	};

	// Focus comment field
	form.comment.focus ();

	return true;
};

// Displays edit form
HashOver.prototype.editComment = function (comment)
{
	if (comment['user-owned'] !== true) {
		return false;
	}

	// Reference to this object
	var hashover = this;

	// Get permalink from comment JSON object
	var permalink = comment.permalink;

	// Get edit link element
	var link = this.elements.get ('hashover-edit-link-' + permalink, true);

	// Get file
	var file = this.fileFromPermalink (permalink);

	// Get name and website
	var name = comment.name || '';
	var website = comment.website || '';

	// Get and clean comment body
	var body = comment.body.replace (this.regex.links, '$1');

	// Create edit form element
	var form = this.elements.create ('form', {
		id: 'hashover-edit-' + permalink,
		className: 'hashover-edit-form',
		method: 'post',
		action: this.setup['http-scripts'] + '/postcomments.php'
	});

	// Place edit form fields into form
	form.innerHTML = hashover.strings.parseTemplate (hashover.ui['edit-form'], {
		permalink: permalink,
		file: file,
		name: name,
		website: website,
		body: body
	});

	// Prevent input submission
	this.preventSubmit (form);

	// Add edit form to page
	var editForm = this.elements.get ('hashover-placeholder-edit-form-' + permalink, true);
	    editForm.appendChild (form);

	// Set status dropdown menu option to comment status
	this.elements.exists ('hashover-edit-status-' + permalink, function (status) {
		var commentStatuses = ['approved', 'pending', 'deleted'];

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
		this.elements.get ('hashover-subscribe-' + permalink, true).checked = null;
	}

	// Displays onClick confirmation dialog for comment deletion
	this.elements.get ('hashover-edit-delete-' + permalink, true).onclick = function ()
	{
		return confirm (hashover.locale['delete-comment']);
	};

	// Change "Edit" link to "Cancel" link
	this.cancelSwitcher ('edit', link, editForm, permalink);

	// Attach event listeners to "Save Edit" button
	var saveEdit = this.elements.get ('hashover-edit-post-' + permalink, true);

	// Get the element of comment being replied to
	var destination = this.elements.get (permalink, true);

	// Attach click event to formatting revealer hyperlink
	this.formattingOnclick ('edit', permalink);

	// Onclick
	saveEdit.onclick = function ()
	{
		return hashover.postComment (destination, form, this, hashover.AJAXEdit, 'edit', permalink, link.onclick, false, true);
	};

	// Onsubmit
	saveEdit.onsubmit = function ()
	{
		return hashover.postComment (destination, form, this, hashover.AJAXEdit, 'edit', permalink, link.onclick, false, true);
	};

	return false;
};

<?php if ($hashover->setup->collapsesComments !== false): ?>

// For showing more comments, via AJAX or removing a class
HashOver.prototype.hideMoreLink = function (finishedCallback)
{
	finishedCallback = finishedCallback || null;

	// Reference to this object
	var hashover = this;

	// Add class to hide the more hyperlink
	this.classes.add (this.instance['more-link'], 'hashover-hide-more-link');

	setTimeout (function () {
		// Remove the more hyperlink from page
		if (hashover.instance['sort-section'].contains (hashover.instance['more-link']) === true) {
			hashover.instance['sort-section'].removeChild (hashover.instance['more-link']);
		}

		// Show comment count and sort options
		hashover.elements.get ('hashover-count-wrapper').style.display = '';

		// Show popular comments section
		hashover.elements.exists ('hashover-popular-section', function (popularSection) {
			popularSection.style.display = '';
		});

		// Get each hidden comment element
		var collapsed = hashover.instance['sort-section'].getElementsByClassName ('hashover-hidden');

		// Remove hidden comment class from each comment
		for (var i = collapsed.length - 1; i >= 0; i--) {
			hashover.classes.remove (collapsed[i], 'hashover-hidden');
		}

		// Execute callback function
		if (finishedCallback !== null) {
			finishedCallback ();
		}
	}, 350);
};
<?php if ($hashover->setup->usesAJAX !== false): ?>

// For appending new comments to the thread on page
HashOver.prototype.appendComments = function (comments)
{
	var comment;
	var isReply;
	var element;
	var parent;

	for (var i = 0, il = comments.length; i < il; i++) {
		// Skip existing comments
		if (this.findByPermalink (comments[i].permalink, this.instance.comments.primary) !== null) {
			// Check comment's replies
			if (comments[i].replies !== undefined) {
				this.appendComments (comments[i].replies);
			}

			continue;
		}

		// Check if comment is a reply
		isReply = (comments[i].permalink.indexOf ('r') > -1);

		// Add comment to comments array
		this.addComments (comments[i], isReply, i);

		// Parse comment, convert HTML to DOM node
		comment = this.HTMLToNodeList (this.comments.parse (comments[i], null, true));

		// Check that comment is not a reply
		if (isReply !== true) {
			// If so, append to primary comments
			element = this.instance['more-section'];
		} else {
			// If not, append to its parent's element
			parent = this.getParentPermalink (comments[i].permalink, true);
			element = this.elements.get (parent, true) || this.instance['more-section'];
		}

		// Otherwise append it to the primary element
		element.appendChild (comment[0]);

		// Add controls to the comment
		this.addControls (comments[i]);
	}
};
<?php endif; ?>

// onClick event for more button
HashOver.prototype.showMoreComments = function (element, finishedCallback)
{
	finishedCallback = finishedCallback || null;

	// Reference to this object
	var hashover = this;

	// Do nothing if already showing all comments
	if (this.instance['showing-more'] === true) {
		// Execute callback function
		if (finishedCallback !== null) {
			finishedCallback ();
		}

		return false;
	}

<?php if ($hashover->setup->usesAJAX !== false): ?>
	var httpRequest = new XMLHttpRequest ();
	var queries = ['url=' + encodeURIComponent (this.instance['page-url']), 'start=' + this.setup['collapse-limit'], 'ajax=yes'];

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
		hashover.appendComments (json.primary);

		// Remove loading class from element
		hashover.classes.remove (element, 'hashover-loading');

		// Hide the more hyperlink and display the comments
		hashover.hideMoreLink (finishedCallback);
	};

	// Open and send request
	httpRequest.open ('POST', this.setup['http-root'] + '/api/json.php', true);
	httpRequest.setRequestHeader ('Content-type', 'application/x-www-form-urlencoded');
	httpRequest.send (queries.join ('&'));

	// Set class to indicate loading to element
	this.classes.add (element, 'hashover-loading');
<?php else: ?>
	// Hide the more hyperlink and display the comments
	this.hideMoreLink (finishedCallback);
<?php endif; ?>

	// Set all comments as shown
	this.instance['showing-more'] = true;

	return false;
};
<?php endif; ?>

// Callback to close the embedded image
HashOver.prototype.closeEmbeddedImage = function (image)
{
	// Reset source
	image.src = image.dataset.placeholder;

	// Reset title
	image.title = this.locale['external-image-tip'];

	// Remove loading class from wrapper
	this.classes.remove (image.parentNode, 'hashover-loading');
};

// Onclick callback function for embedded images
HashOver.prototype.embeddedImageCallback = function (image)
{
	// Reference to this object
	var hashover = this;

	// If embedded image is open, close it and return false
	if (image.src === image.dataset.url) {
		this.closeEmbeddedImage (image);
		return false;
	}

	// Set title
	image.title = this.locale['loading'];

	// Add loading class to wrapper
	this.classes.add (image.parentNode, 'hashover-loading');

	// Change title and remove load event handler once image is loaded
	image.onload = function ()
	{
		image.title = hashover.locale['click-to-close'];
		image.onload = null;

		// Remove loading class from wrapper
		hashover.classes.remove (image.parentNode, 'hashover-loading');
	};

	// Close embedded image if any error occurs
	image.onerror = function ()
	{
		hashover.closeEmbeddedImage (this);
	};

	// Set placeholder image to embedded source
	image.src = image.dataset.url;
};

// Changes Element.textContent onmouseover and reverts onmouseout
HashOver.prototype.mouseOverChanger = function (element, over, out)
{
	// Reference to this object
	var hashover = this;

	if (over === null || out === null) {
		element.onmouseover = null;
		element.onmouseout = null;

		return false;
	}

	element.onmouseover = function ()
	{
		this.textContent = hashover.locale[over];
	};

	element.onmouseout = function ()
	{
		this.textContent = hashover.locale[out];
	};
};

<?php if ($likesOrDislikes !== false): ?>

// For liking comments
HashOver.prototype.likeComment = function (action, permalink)
{
	// Reference to this object
	var hashover = this;

	// Get file
	var file = this.fileFromPermalink (permalink);

	var actionLink = this.elements.get ('hashover-' + action + '-' + permalink, true);
	var likesElement = this.elements.get ('hashover-' + action + 's-' + permalink, true);

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
		if (hashover.classes.contains (actionLink, 'hashover-' + action) === true) {
			// Change class to indicate the comment has been liked/disliked
			hashover.classes.add (actionLink, 'hashover-' + action + 'd');
			hashover.classes.remove (actionLink, 'hashover-' + action);
			actionLink.title = (action === 'like') ? hashover.locale['liked-comment'] : hashover.locale['disliked-comment'];
			actionLink.textContent = (action === 'like') ? hashover.locale['liked'] : hashover.locale['disliked'];

			// Add listener to change link text to "Unlike" on mouse over
			if (action === 'like') {
				hashover.mouseOverChanger (actionLink, 'unlike', 'liked');
			}
		} else {
			// Change class to indicate the comment is unliked
			hashover.classes.add (actionLink, 'hashover-' + action);
			hashover.classes.remove (actionLink, 'hashover-' + action + 'd');
			actionLink.title = (action === 'like') ? hashover.locale['like-comment'] : hashover.locale['dislike-comment'];
			actionLink.textContent = (action === 'like') ? hashover.locale['like'][0] : hashover.locale['dislike'][0];

			// Add listener to change link text to "Unlike" on mouse over
			if (action === 'like') {
				hashover.mouseOverChanger (actionLink, null, null);
			}
		}

		if (likes > 0) {
			// Decide if locale is pluralized
			var plural = (likes !== 1) ? 1 : 0;
			var likeLocale = (action !== 'like') ? 'dislike' : 'like';
			var likeCount = likes + ' ' + hashover.locale[likeLocale][plural];

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
	queries  = 'url=' + encodeURIComponent (this.instance['page-url']);
	queries += '&thread=' + this.instance['thread-directory'];
	queries += '&comment=' + file;
	queries += '&action=' + action;

	// Send request
	like.open ('POST', this.setup['http-scripts'] + '/like.php', true);
	like.setRequestHeader ('Content-type', 'application/x-www-form-urlencoded');
	like.send (queries);
};
<?php endif; ?>

// Add various events to various elements in each comment
HashOver.prototype.addControls = function (json, popular)
{
	// Reference to this object
	var hashover = this;

	function stepIntoReplies ()
	{
		if (json.replies !== undefined) {
			for (var reply = 0, total = json.replies.length; reply < total; reply++) {
				hashover.addControls (json.replies[reply]);
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
		embeddedImgs[i].onclick = function ()
		{
			hashover.embeddedImageCallback (this);
		};
	}

<?php if ($hashover->setup->collapsesComments !== false): ?>
	// Get thread link of comment
	this.elements.exists ('hashover-thread-link-' + permalink, function (threadLink) {
		// Add onClick event to thread hyperlink
		threadLink.onclick = function ()
		{
			hashover.showMoreComments (threadLink, function () {
				var parentThread = permalink.replace (hashover.regex.thread, '$1');
				var scrollToElement = hashover.elements.get (parentThread, true);

				// Scroll to the comment
				scrollToElement.scrollIntoView ({ behavior: 'smooth' });
			});

			return false;
		};
	});

<?php endif; ?>
	// Get reply link of comment
	this.elements.exists ('hashover-reply-link-' + permalink, function (replyLink) {
		// Add onClick event to "Reply" hyperlink
		replyLink.onclick = function ()
		{
			hashover.replyToComment (permalink);
			return false;
		};
	});

	// Check if logged in user owns the comment
	if (json['user-owned'] === true) {
		this.elements.exists ('hashover-edit-link-' + permalink, function (editLink) {
			// Add onClick event to "Edit" hyperlinks
			editLink.onclick = function ()
			{
				hashover.editComment (json);
				return false;
			};
		});
<?php if ($likesOrDislikes): ?>
	} else {
<?php if ($allowsLikes !== false): ?>
		this.elements.exists ('hashover-like-' + permalink, function (likeLink) {
			// Add onClick event to "Like" hyperlinks
			likeLink.onclick = function ()
			{
				hashover.likeComment ('like', permalink);
				return false;
			};

			if (hashover.classes.contains (likeLink, 'hashover-liked') === true) {
				hashover.mouseOverChanger (likeLink, 'unlike', 'liked');
			}
		});
<?php endif; ?>
<?php if ($allowsDislikes === true): ?>

		this.elements.exists ('hashover-dislike-' + permalink, function (dislikeLink) {
			// Add onClick event to "Dislike" hyperlinks
			dislikeLink.onclick = function ()
			{
				hashover.likeComment ('dislike', permalink);
				return false;
			};
		});
<?php endif; ?>
<?php endif; ?>
	}

	// Recursively execute this function on replies
	stepIntoReplies ();
};

// Returns a clone of an object
HashOver.prototype.cloneObject = function (object)
{
	return JSON.parse (JSON.stringify (object));
};

// "Flatten" the comments object
HashOver.prototype.getAllComments = function (comments)
{
	var commentsCopy = this.cloneObject (comments);
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
};

// Run all comments in array data through comments.parse function
HashOver.prototype.parseAll = function (comments, element, collapse, popular, sort, method)
{
	popular = popular || false;
	sort = sort || false;
	method = method || 'ascending';

	var commentHTML = '';

	// Parse every comment
	for (var comment = 0, total = comments.length; comment < total; comment++) {
		commentHTML += this.comments.parse (comments[comment], null, collapse, sort, method, popular);
	}

	// Add comments to element's innerHTML
	if ('insertAdjacentHTML' in element) {
		element.insertAdjacentHTML ('beforeend', commentHTML);
	} else {
		element.innerHTML = commentHTML;
	}

	// Add control events
	for (var comment = 0, total = comments.length; comment < total; comment++) {
		this.addControls (comments[comment]);
	}
};

// Comment sorting
HashOver.prototype.sortComments = function (method)
{
	var tmpArray;
	var sortArray;
	var defaultName = this.setup['default-name'];

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
			tmpArray = this.getAllComments (this.instance.comments.primary);
			sortArray = tmpArray.reverse ();
			break;
		}

		case 'by-date': {
			sortArray = this.getAllComments (this.instance.comments.primary).sort (function (a, b) {
				if (a['sort-date'] === b['sort-date']) {
					return 1;
				}

				return b['sort-date'] - a['sort-date'];
			});

			break;
		}

		case 'by-likes': {
			sortArray = this.getAllComments (this.instance.comments.primary).sort (function (a, b) {
				a.likes = a.likes || 0;
				b.likes = b.likes || 0;
				a.dislikes = a.dislikes || 0;
				b.dislikes = b.dislikes || 0;

				return (b.likes - b.dislikes) - (a.likes - a.dislikes);
			});

			break;
		}

		case 'by-replies': {
			tmpArray = this.cloneObject (this.instance.comments.primary);

			sortArray = tmpArray.sort (function (a, b) {
				var ac = (!!a.replies) ? a.replies.length : 0;
				var bc = (!!b.replies) ? b.replies.length : 0;

				return bc - ac;
			});

			break;
		}

		case 'by-discussion': {
			tmpArray = this.cloneObject (this.instance.comments.primary);

			sortArray = tmpArray.sort (function (a, b) {
				var replyCountA = replyPropertySum (a, replyCounter);
				var replyCountB = replyPropertySum (b, replyCounter);

				return replyCountB - replyCountA;
			});

			break;
		}

		case 'by-popularity': {
			tmpArray = this.cloneObject (this.instance.comments.primary);

			sortArray = tmpArray.sort (function (a, b) {
				var likeCountA = replyPropertySum (a, netLikes);
				var likeCountB = replyPropertySum (b, netLikes);

				return likeCountB - likeCountA;
			});

			break;
		}

		case 'by-name': {
			tmpArray = this.getAllComments (this.instance.comments.primary);

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
			tmpArray = this.cloneObject (this.instance.comments.primary);
			sortArray = tmpArray.reverse ();
			break;
		}

		case 'threaded-by-date': {
			tmpArray = this.cloneObject (this.instance.comments.primary);

			sortArray = tmpArray.sort (function (a, b) {
				if (a['sort-date'] === b['sort-date']) {
					return 1;
				}

				return b['sort-date'] - a['sort-date'];
			});

			break;
		}

		case 'threaded-by-likes': {
			tmpArray = this.cloneObject (this.instance.comments.primary);

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
			tmpArray = this.cloneObject (this.instance.comments.primary);

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
			sortArray = this.instance.comments.primary;
			break;
		}
	}

	this.parseAll (sortArray, this.instance['sort-section'], false, false, true, method);
};

// FIXME: This will be split into multiple functions in separate files
HashOver.prototype.init = function ()
{
	// Store start time
	this.execStart = Date.now ();

	var URLParts		= window.location.href.split ('#');
	var appendCSS		= true;
	var head		= document.head || document.getElementsByTagName ('head')[0];
	var URLHref		= URLParts[0];
	var mainElement		= document.getElementById ('hashover');
	var formElement		= null;
	var URLHash		= URLParts[1] || '';
	var hashover		= this;

<?php if ($hashover->setup->appendsCSS !== false): ?>
	// Check if comment theme stylesheet is already in page head
	if (typeof (document.querySelector) === 'function') {
		appendCSS = !document.querySelector ('link[href="' + this.setup['theme-css'] + '"]');
	} else {
		// Fallback for old web browsers without querySelector
		var links = head.getElementsByTagName ('link');

		for (var i = 0, il = links.length; i < il; i++) {
			if (links[i].getAttribute ('href') === this.setup['theme-css']) {
				appendCSS = false;
				break;
			}
		}
	}

	// Create link element for comment stylesheet
	if (appendCSS === true) {
		var css = this.elements.create ('link', {
			rel: 'stylesheet',
			href: this.setup['theme-css'],
			type: 'text/css',
		});

		// Append comment stylesheet link element to page head
		head.appendChild (css);
	}

<?php endif; ?>
	// Put number of comments into "hashover-comment-count" identified HTML element
	if (this.instance['total-count'] !== 0) {
		this.elements.exists ('hashover-comment-count', function (countElement) {
			countElement.textContent = hashover.instance['total-count'];
		});
<?php if ($hashover->setup->APIStatus ('rss') !== 'disabled'): ?>

		// Create link element for comment RSS feed
		var rss = this.elements.create ('link', {
			rel: 'alternate',
			href: this.setup['http-root'] + '/api/rss.php?url=' + encodeURIComponent (URLHref),
			type: 'application/rss+xml',
			title: 'Comments'
		});

		// Append comment RSS feed link element to page head
		head.appendChild (rss);
<?php endif; ?>
	}

	// Create div tag for HashOver comments to appear in
	if (mainElement === null) {
		mainElement = this.elements.create ('div', { id: 'hashover' });

		// Place HashOver element on page
		if (this.instance['executing-script'] !== false) {
			var thisScript = this.elements.get ('hashover-script-' + this.instance['executing-script']);
			    thisScript.parentNode.insertBefore (mainElement, thisScript);
		} else {
			document.body.appendChild (mainElement);
		}
	}

	// Add class for differentiating desktop and mobile styling
	mainElement.className = 'hashover-' + this.setup['device-type'];

	// Add class to indicate user login status
	if (this.setup['user-is-logged-in'] === true) {
		this.classes.add (mainElement, 'hashover-logged-in');
	} else {
		this.classes.add (mainElement, 'hashover-logged-out');
	}

	// Add initial HTML to page
	if ('insertAdjacentHTML' in mainElement) {
		mainElement.insertAdjacentHTML ('beforeend', this.ui['initial-html']);
	} else {
		mainElement.innerHTML = this.ui['initial-html'];
	}

	// Get sort div element
	this.instance['sort-section'] = this.elements.get ('hashover-sort-section');

	// Get primary form element
	formElement = this.elements.get ('hashover-form');

	// Display most popular comments
	this.elements.exists ('hashover-top-comments', function (topComments) {
		if (hashover.instance.comments.popular[0] !== undefined) {
			hashover.parseAll (hashover.instance.comments.popular, topComments, false, true);
		}
	});

	// Add initial event handlers
	this.parseAll (this.instance.comments.primary, this.instance['sort-section'], this.setup['collapse-comments']);

<?php if ($hashover->setup->collapsesUI !== false): ?>
	// Decide button text
	var uncollapseLocale = (this.instance['total-count'] >= 1) ? 'show-number-comments' : 'post-comment-on';
	var uncollapseText = this.locale[uncollapseLocale];

	// Create hyperlink to uncollapse the comment UI
	var uncollapseUILink = this.elements.create ('a', {
		href: '#',
		className: 'hashover-more-link',
		title: uncollapseText,
		textContent: uncollapseText,

		onclick: function () {
			// Add class to hide the uncollapse UI hyperlink
			hashover.classes.add (this, 'hashover-hide-more-link');

			setTimeout (function () {
				// Remove the uncollapse UI hyperlink from page
				if (mainElement.contains (uncollapseUILink) === true) {
					mainElement.removeChild (uncollapseUILink);
				}

				// Element to unhide
				var uncollapseIDs = [
					'hashover-form-section',
					'hashover-comments-section',
					'hashover-end-links'
				];

				// Show hidden form elements
				for (var i = 0, il = uncollapseIDs.length; i < il; i++) {
					hashover.elements.exists (uncollapseIDs[i], function (element) {
						element.style.display = '';
					});
				}

				// Show popular comments section
				if (hashover.setup['collapse-limit'] > 0) {
					hashover.elements.exists ('hashover-popular-section', function (popularSection) {
						popularSection.style.display = '';
					});
				}
			}, 350);

			return false;
		}
	});

	// Add uncollapse hyperlink to HashOver div
	mainElement.appendChild (uncollapseUILink);

<?php endif; ?>
<?php if ($hashover->setup->collapsesComments !== false): ?>
	// Check whether there are more than the collapse limit
	if (this.instance['total-count'] > this.setup['collapse-limit']) {
		// Create element for the comments
		this.instance['more-section'] = this.elements.create ('div', {
			className: 'hashover-more-section'
		});

		// If so, create "More Comments" hyperlink
		this.instance['more-link'] = this.elements.create ('a', {
			href: '#',
			className: 'hashover-more-link',
			title: this.locale['more-link-text'],
			textContent: this.locale['more-link-text'],

			onclick: function () {
				return hashover.showMoreComments (this);
			}
		});

		// Add more button link to sort div
		this.instance['sort-section'].appendChild (this.instance['more-section']);

		// Add more button link to sort div
		this.instance['sort-section'].appendChild (this.instance['more-link']);

		// And consider comments collapsed
		this.instance['showing-more'] = false;
	} else {
		// If not, consider all comments shown
		this.instance['showing-more'] = true;
	}

<?php endif; ?>
	// Attach click event to formatting revealer hyperlink
	this.formattingOnclick ('main');

	// Attach event listeners to "Post Comment" button
	var postButton = this.elements.get ('hashover-post-button');

	// Onclick
	postButton.onclick = function ()
	{
		return hashover.postComment (hashover.instance['sort-section'], formElement, postButton, hashover.AJAXPost);
	};

	// Onsubmit
	postButton.onsubmit = function ()
	{
		return hashover.postComment (hashover.instance['sort-section'], formElement, postButton, hashover.AJAXPost);
	};

<?php if ($hashover->setup->allowsLogin !== false): ?>
	// Attach event listeners to "Login" button
	if (this.setup['user-is-logged-in'] !== true) {
		var loginButton = this.elements.get ('hashover-login-button');

		// Onclick
		loginButton.onclick = function ()
		{
			return hashover.validateComment (true, formElement);
		};

		// Onsubmit
		loginButton.onsubmit = function ()
		{
			return hashover.validateComment (true, formElement);
		};
	}

<?php endif; ?>
	// Five method sort
	this.elements.exists ('hashover-sort-select', function (sortSelect) {
		sortSelect.onchange = function ()
		{
<?php if ($hashover->setup->collapsesComments !== false): ?>
			var sortSelectDiv = hashover.elements.get ('hashover-sort');

			hashover.showMoreComments (sortSelectDiv, function () {
				hashover.instance['sort-section'].textContent = '';
				hashover.sortComments (sortSelect.value);
			});
<?php else: ?>
			hashover.instance['sort-section'].textContent = '';
			hashover.sortComments (sortSelect.value);
<?php endif; ?>
		};
	});

	// Display reply or edit form when the proper URL queries are set
	if (URLHref.match (/hashover-(reply|edit)=/)) {
		var permalink = URLHref.replace (/.*?hashover-(edit|reply)=(c[0-9r\-pop]+).*?/, '$2');

		if (!URLHref.match ('hashover-edit=')) {
<?php if ($hashover->setup->collapsesComments !== false): ?>
			// Show more comments
			this.showMoreComments (this.instance['more-link'], function () {
				// Then display reply form
				hashover.replyToComment (permalink);
			});
<?php else: ?>
			// Display reply form
			this.replyToComment (permalink);
<?php endif; ?>
		} else {
			var isPop = permalink.match ('-pop');
			var comments = (isPop) ? this.instance.comments.popular : this.instance.comments.primary;
<?php if ($hashover->setup->collapsesComments !== false): ?>

			// Show more comments
			this.showMoreComments (this.instance['more-link'], function () {
				// Then display edit form
				hashover.editComment (hashover.findByPermalink (permalink, comments));
			});
<?php else: ?>
			// Display edit form
			this.editComment (this.findByPermalink (permalink, comments));
<?php endif; ?>
		}
	}

	// Store end time
	this.execEnd = Date.now ();

	// Store execution time
	this.execTime = this.execEnd - this.execStart;

	// Log execution time in JavaScript console
	if (window.console) {
		console.log ('HashOver executed in ' + this.execTime + ' ms.');
	}

	// Callback for scrolling a comment into view on page load
	var scroller = function ()
	{
		setTimeout (function () {
			// Workaround for stupid Chrome bug
			if (URLHash.match (/comments|hashover/)) {
				hashover.elements.exists (URLHash, function (comments) {
					comments.scrollIntoView ({ behavior: 'smooth' });
				});
			}

			// Jump to linked comment
			if (URLHash.match (/c[0-9]+r*/)) {
<?php if ($hashover->setup->collapsesComments !== false): ?>
				// Get comment from hash
				var existingComment = hashover.elements.get (URLHash);

				// Check if comment exists on the page and is visable
				if (existingComment !== null
				    && hashover.classes.contains (existingComment, 'hashover-hidden') === false)
				{
					// If so, scroll the comment into view
					existingComment.scrollIntoView ({ behavior: 'smooth' });
				} else {
					// If not, show more comments
					hashover.showMoreComments (hashover.instance['more-link'], function () {
						hashover.elements.exists (URLHash, function (comment) {
							comment.scrollIntoView ({ behavior: 'smooth' });
						});
					});
				}
<?php else: ?>
				hashover.elements.exists (URLHash, function (comment) {
					comment.scrollIntoView ({ behavior: 'smooth' });
				});
<?php endif; ?>
			}

			// Open the message element if there's a message
			if (hashover.elements.get ('hashover-message').textContent !== '') {
				hashover.messages.show ();
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
};

// Instanciate HashOver
window.hashover = new HashOver ();

// Initiate HashOver
window.hashover.init ();
