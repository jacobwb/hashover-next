// Copyright (C) 2014 Jacob Barkdull
//
//	This program is free software: you can redistribute it and/or modify
//	it under the terms of the GNU Affero General Public License as
//	published by the Free Software Foundation, either version 3 of the
//	License, or (at your option) any later version.
//
//	This program is distributed in the hope that it will be useful,
//	but WITHOUT ANY WARRANTY; without even the implied warranty of
//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//	GNU Affero General Public License for more details.
//
//	You should have received a copy of the GNU Affero General Public License
//	along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
//--------------------
//
// Source Code and Installation Instructions:
//	http://<?php echo $setup->domain, $_SERVER['PHP_SELF'], '?source', PHP_EOL; ?>

var hashover_latest = '';
var head = document.getElementsByTagName('head')[0];
var hashover_widget_div = document.getElementById('hashover-widget');

<?php if ($setup->appends_css_link == 'yes') { ?>
// Add comment stylesheet to page <head>
if (document.querySelector('link[href="<?php echo $setup->root_dir; ?>/themes/<?php echo $setup->theme; ?>/widget-style.css"]') == null) {
	link = document.createElement('link');
	link.rel = 'stylesheet';
	link.href = '<?php echo $setup->root_dir; ?>/themes/<?php echo $setup->theme; ?>/widget-style.css';
	link.type = 'text/css';
	head.appendChild(link);
}

<?php } ?>
// Add comment content to HTML template
function parse_template(object, count, sort, method) {
	sort = sort || false;
	method = method || 'ascending';

	var variable = 'hashover_latest';
	var permalink = object['permalink'];
	var cmtclass = (permalink.match('r') && (sort == false || method == 'ascending')) ? ' ' + 'hashover-reply' : '';

	if (object['avatar']) {
		window[variable] += '\t<a name="' + permalink + '"></a>\n'; 
	}
	window[variable] += '\t<div id="' + permalink + '" class="hashover-comment' + cmtclass + '">\n';

	if (!object['deletion_notice']) {
		// Add HTML anchor tag to URLs
		var clean_code = object['comment'].replace(/(((ftp|http|https){1}:\/\/)[a-zA-Z0-9-@:%_\+.~#?&\/=]+)([\s]{0,})/ig, '<a href="$1" target="_blank">$1</a>');

		// Replace [img] tags with external image placeholder if enabled
		clean_code = clean_code.replace(/\[img\]<a.*?>(((ftp|http|https){1}:\/\/)[a-zA-Z0-9-@:%_\+.~#?&\/=]+)<\/a>\[\/img\]/ig, function(fullURL, url) {
<?php if ($setup->allows_images == 'yes') { ?>
			var extensions = ['<?php echo implode('\', \'', $setup->image_types); ?>'];
			var urlExtension = url.split('.').pop().split(/\#|\?/)[0];

			for (var ext = 0, length = extensions.length; ext < length; ext++) {
				if (extensions[ext] == urlExtension) {
					return '<br><br><img src="<?php echo $setup->root_dir, '/images/', $setup->image_format, 's/place-holder.', $setup->image_format; ?>" title="' + url +  '" alt="Loading..." onClick="((this.src==this.title) ? this.src=\'<?php echo $setup->root_dir, '/images/', $setup->image_format, 's/place-holder.', $setup->image_format; ?>\' : this.src=this.title);"><br><br>';
				}
			};

<?php } ?>
			return '<a href="' + url + '" target="_blank">' + url + '</a>';
		});

		// Remove repetitive and trailing HTML <br> tags
		clean_code = clean_code.replace(/(<br>){2,}/ig, '<br><br>').replace(/(<br><br>)$/g, '').replace(/^(<br><br>)/g, '');

		if (object['avatar']) {
			var avatar = '<img width="<?php echo $setup->icon_size; ?>" height="<?php echo $setup->icon_size; ?>" src="' + object['avatar'] + '" alt="#' + permalink + '">';
		} else {
			var avatar = '<a href="#' + permalink + '" title="Permalink">#' + permalink + '</a>';
		}

		var 
			name = object['name'].replace(/^@(.*?)$/, '$1'),
			thread = '<a href="' + object['thread_url'] + '#' + permalink + '" title="Permalink">' + object['thread_title'] + '</a>',
			date = object['date'],
			likes = (object['likes']) ? object['likes'] + ' Like' + ((object['likes'] != 1) ? 's' : '') : '',
			like_link = (object['like_link']) ? object['like_link'] : '',
			dislikes = (object['dislikes']) ? object['dislikes'] + ' Dislike' + ((object['likes'] != 1) ? 's' : '') : '',
			dislike_link = (object['dislike_link']) ? object['dislike_link'] : '',
			edit_link = (object['edit_link']) ? object['edit_link'] : '',
			reply_link = object['reply_link'],
			comment = clean_code,
			action = '/hashover.php',
			form = '',
			hashover_footer_style = ''
		;

		var name_at = (object['name'].match(/^@.*?$/)) ? '@' : '';
		var name_class = (object['name'].match(/^@.*?$/)) ? ' at' : '';

		if (object['website'] == '') {
			if (object['name'].match(/^@([a-zA-Z0-9_@]{1,29}$)/)) {
				name = '<a id="hashover-name-' + permalink + '" href="http://twitter.com/' + name + '" target="_blank">' + name + '</a>';
			}
		} else {
			if (object['website'] != '') {
				name = '<a id="hashover-name-' + permalink + '" href="' + object['website'] + '" target="_blank">' + name + '</a>';
			}
		}

		name = '<span class="hashover-name' + name_class + '">' + name_at + name + '</span>';

<?php
		// Load HTML template
		$theme_layout = explode(PHP_EOL, file_get_contents('./themes/' . $setup->theme . '/widget-layout.html'));
		$theme_layout = str_replace("\t", '\t', $theme_layout);

		for ($line = 0, $length = count($theme_layout); $line < $length; $line++) {
			if (!empty($theme_layout[$line])) {
				echo "\t\t", 'window[variable] += \'\t\t', $theme_layout[$line], '\n\';', PHP_EOL;
			}
		}
?>
	} else {
		window[variable] += object['deletion_notice'] + '\n';
	}

	window[variable] += '\t</div>\n';
}

<?php

	if (!empty($hashover)) {
		echo 'var comments = ';

		if (defined('JSON_PRETTY_PRINT')) {
			echo str_replace('    ', "\t", json_encode($hashover, JSON_PRETTY_PRINT));
		} else {
			echo str_replace('    ', "\t", json_encode($hashover));
		}

		echo ';', PHP_EOL, PHP_EOL;

		echo 'for (var comment in comments) {', PHP_EOL;
		echo "\t", 'parse_template(comments[comment]);', PHP_EOL;
		echo '}', PHP_EOL;
	}

?>

// Append an HTML div tag for HashOver comments to appear in
if (hashover_widget_div == null) {
	var scripts = document.getElementsByTagName('script');
	var this_script = scripts[scripts.length - 1];
	hashover_widget_div = document.createElement('div');
	hashover_widget_div.id = 'hashover-widget';
	this_script.parentNode.insertBefore(hashover_widget_div, this_script);
}

// Place all content on page
hashover_widget_div.className = '<?php echo $setup->image_format; ?>';
hashover_widget_div.innerHTML = hashover_latest;
