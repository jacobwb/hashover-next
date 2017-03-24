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

$js_avatar = '';

if ($hashover->setup->iconMode !== 'none') {
	if ($hashover->setup->iconMode === 'image') {
		$js_avatar = '<img width="' . $hashover->setup->iconSize . '" height="' . $hashover->setup->iconSize . '" src="\' + object[\'avatar\'] + \'" alt="#\' + permatext + \'">';
	} else {
		$js_avatar = '<a href="#\' + permalink + \'" title="Permalink">#\' + permatext + \'</a>';
	}
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


var hashover_latest = '';
var head = document.getElementsByTagName ('head')[0];
var hashoverWidget = document.getElementById ('hashover-widget');
var defaultName = '<?php echo $hashover->setup->defaultName; ?>';

<?php if ($hashover->setup->appendsCSS): ?>
// Add comment stylesheet to page <head>
if (document.querySelector ('link[href="<?php echo $hashover->setup->httpRoot; ?>/themes/<?php echo $hashover->setup->theme; ?>/widget-style.css"]') == null) {
	link = document.createElement ('link');
	link.rel = 'stylesheet';
	link.href = '<?php echo $hashover->setup->httpRoot; ?>/themes/<?php echo $hashover->setup->theme; ?>/widget-style.css';
	link.type = 'text/css';
	head.appendChild (link);
}

<?php endif; ?>
// Add comment content to HTML template
function parseTemplate (object, count, sort, method) {
	sort = sort || false;
	method = method || 'ascending';

	var variable = 'hashover_latest';
	var permalink = object.permalink;
	var permatext = permalink.replace ('-pop', '').slice (1).split ('r').pop ();
	var cmtclass = (permalink.match ('r') && (sort == false || method == 'ascending')) ? ' ' + 'hashover-widget-reply' : '';
	var avatar = '';

	window[variable] += '\t<div id="' + permalink + '" class="hashover-widget-comment' + cmtclass + '">\n';

	// Setup avatar icon
	if (object.avatar) {
		avatar = '<span class="hashover-avatar"><?php echo $js_avatar; ?></span>';
	}

	// Add HTML anchor tag to URLs
	var clean_code = object.body.replace (/(((ftp|http|https){1}:\/\/)[a-zA-Z0-9-@:%_\+.~#?&\/=]+)([\s]{0,})/ig, '<a href="$1" target="_blank">$1</a>');

	// Replace [img] tags with external image placeholder if enabled
	clean_code = clean_code.replace (/\[img\]<a.*?>(((ftp|http|https){1}:\/\/)[a-zA-Z0-9-@:%_\+.~#?&\/=]+)<\/a>\[\/img\]/ig, function (fullURL, url) {
<?php if ($hashover->setup->allowsImages): ?>
		var extensions = ['<?php echo implode ('\', \'', $hashover->setup->imageTypes); ?>'];
		var urlExtension = url.split ('.').pop ().split (/\#|\?/)[0];

		for (var ext = 0, length = extensions.length; ext < length; ext++) {
			if (extensions[ext] == urlExtension) {
				return '<br><br><img src="<?php echo $hashover->setup->httpImages, '/place-holder.', $hashover->setup->imageFormat; ?>" title="' + url +  '" alt="Loading..." onClick="((this.src==this.title) ? this.src=\'<?php echo $hashover->setup->httpImages, '/place-holder.', $hashover->setup->imageFormat; ?>\' : this.src=this.title);"><br><br>';
			}
		};

<?php endif; ?>
		return '<a href="' + url + '" target="_blank">' + url + '</a>';
	});

	// Remove repetitive and trailing HTML <br> tags
	clean_code = clean_code.replace (/(<br>){2,}/ig, '<br><br>').replace (/(<br><br>)$/g, '').replace (/^(<br><br>)/g, '');

	var objName = object.name || defaultName;
	var name = object.name ? object.name.replace (/^@(.*?)$/, '$1') : defaultName;
	var date = object.date;
	var comment = clean_code;
	var thread = '<a href="' + object['thread-url'] + '#' + permalink + '" title="Permalink">' + object['thread-title'] + '</a>';

	var name_at = (objName.match (/^@.*?$/)) ? '@' : '';
	var name_class = (objName.match (/^@.*?$/)) ? ' at' : '';

	if (object.website == '') {
		if (objName.match (/^@([a-zA-Z0-9_@]{1,29}$)/)) {
			name = '<a id="hashover-name-' + permalink + '" href="http://twitter.com/' + name + '" target="_blank">' + name + '</a>';
		}
	} else {
		if (object.website != '') {
			name = '<a id="hashover-name-' + permalink + '" href="' + object.website + '" target="_blank">' + name + '</a>';
		}
	}

	name = '<span class="hashover-widget-name' + name_class + '">' + name_at + name + '</span>';

<?php

	// Load HTML template
	$theme_layout = explode (PHP_EOL, file_get_contents ($hashover->setup->rootDirectory . '/themes/' . $hashover->setup->theme . '/widget-layout.html'));
	$theme_layout = str_replace ("\t", '\t', $theme_layout);

	for ($line = 0, $length = count ($theme_layout); $line < $length; $line++) {
		if (!empty ($theme_layout[$line])) {
			echo "\t", 'window[variable] += \'\t\t', $theme_layout[$line], '\n\';', PHP_EOL;
		}
	}

?>
	window[variable] += '\t</div>\n';
}

<?php

	if (!empty ($comments)) {
		echo 'var comments = ';

		if (defined ('JSON_PRETTY_PRINT')) {
			echo str_replace ('    ', "\t", json_encode ($comments, JSON_PRETTY_PRINT));
		} else {
			echo json_encode ($comments);
		}

		echo ';', PHP_EOL, PHP_EOL;

		echo 'for (var comment in comments) {', PHP_EOL;
		echo "\t", 'parseTemplate (comments[comment]);', PHP_EOL;
		echo '}', PHP_EOL;
	}

?>

// Append an HTML div tag for HashOver comments to appear in
if (hashoverWidget == null) {
	hashoverWidget = document.createElement ('div');
	hashoverWidget.id = 'hashover-widget';

<?php if ($hashover->setup->executingScript !== false): ?>
	var hashoverScript = 'hashover-script-<?php echo $hashover->setup->executingScript; ?>';
	var thisScript = document.getElementById (hashoverScript);

	thisScript.parentNode.insertBefore (hashoverWidget, thisScript);
<?php else: ?>
	document.write (hashoverWidget.outerHTML);
<?php endif ?>
}

// Place all content on page
hashoverWidget.className = '<?php echo $hashover->setup->imageFormat; ?>';
hashoverWidget.innerHTML = hashover_latest;
