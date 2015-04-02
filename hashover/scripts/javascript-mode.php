<?php

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


	// Display source code
	if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		if (isset($_GET['source'])) {
			header('Content-type: text/plain; charset=UTF-8');
			exit(file_get_contents(basename(__FILE__)));
		}
	}

	// Disable browser cache
	header('Expires: Tues, 08 May 1991 12:00:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');

	// Avatar icon for edit and reply forms
	if (!empty($_COOKIE['name']) and preg_match('/^@([a-zA-Z0-9_@]{1,29}$)/', $_COOKIE['name'])) {
		$form_avatar = '<img width="' . $this->setup->icon_size . '" height="' . $this->setup->icon_size . '" src="' . $this->setup->root_dir . '/scripts/avatars.php?format=' . $this->setup->image_format . '&amp;size=' . $this->setup->icon_size . '&amp;username=' . $_COOKIE['name'] . '&amp;email=' . md5(strtolower(trim($_COOKIE['email']))) . '" alt="#' . $this->read_comments->cmt_count . '">';
	} else {
		$form_avatar = '<img width="' . $this->setup->icon_size . '" height="' . $this->setup->icon_size . '" src="' . $this->setup->root_dir . '/scripts/avatars.php?format=' . $this->setup->image_format . '&amp;size=' . $this->setup->icon_size . ((isset($_COOKIE['email'])) ? '&amp;email=' . md5(strtolower(trim($_COOKIE['email']))) : '') . '" alt="#' . $this->read_comments->cmt_count . '">';
	}

	$form_first_image = '<img width="' . $this->setup->icon_size . '" height="' . $this->setup->icon_size . '" src="/hashover/images/' . $this->setup->image_format . 's/first-comment.' . $this->setup->image_format . '" alt="+">';
	$page_title = addcslashes($this->setup->page_title, "'");
	$page_url = addcslashes($this->setup->page_url, "'");

	$json_search = array('    ', '\\\r', '\\\\');
	$json_replace = array("\t", '', '');
	$js_title = $this->setup->text['post_cmt_on'][0];
	$js_avatar = '';

	if ($this->setup->display_title == 'yes') {
		$js_title .= str_replace('_TITLE_', $page_title, $this->setup->text['post_cmt_on'][1]);
	}

	if ($this->setup->icon_mode != 'none') {
		if ($this->setup->icon_mode == 'image') {
			$js_avatar = '<img width="' . $this->setup->icon_size . '" height="' . $this->setup->icon_size . '" src="\' + object[\'avatar\'] + \'" alt="#\' + permatext + \'">';
		} else {
			$js_avatar = '<a href="#\' + permalink + \'" title="Permalink">#\' + permatext + \'</a>';
		}
	}

?>
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
//	http://<?php echo $this->setup->domain, $_SERVER['PHP_SELF'], '?source', PHP_EOL; ?>


var hashover = '', collapse_limit = 0, href = window.location.href;
var hashover_div = document.getElementById('hashover');
var page_title = (document.title != '') ? document.title : '';
var name_on = (name_on != undefined) ? name_on : true;
var email_on = (email_on != undefined) ? email_on : true;
var website_on = (website_on != undefined) ? website_on : true;
var password_on = (password_on != undefined) ? password_on : true;
var head = document.getElementsByTagName('head')[0];

<?php if ($this->setup->appends_css_link == 'yes') { ?>
// Append comment stylesheet to page <head>
if (document.querySelector('link[href="<?php echo $this->setup->root_dir; ?>/themes/<?php echo $this->setup->theme; ?>/style.css"]') == null) {
	link = document.createElement('link');
	link.rel = 'stylesheet';
	link.href = '<?php echo $this->setup->root_dir; ?>/themes/<?php echo $this->setup->theme; ?>/style.css';
	link.type = 'text/css';
	head.appendChild(link);
}

<?php } ?>
<?php if (!empty($this->hashover) and $this->setup->api_status('rss') != 'disabled') { ?>
// Append comment RSS feed to page <head>
link = document.createElement('link');
link.rel = 'alternate';
link.href = '<?php echo $this->setup->root_dir; ?>/api/rss.php?url=' + encodeURIComponent(location.href.replace(/#.*$/g, ''));
link.type = 'application/rss+xml';
link.title = 'Comments';
head.appendChild(link);

<?php } ?>
// Append an HTML div tag for HashOver comments to appear in
if (hashover_div == null) {
	hashover_div = document.createElement('div');
	hashover_div.id = 'hashover';

<?php if (!empty($_GET['hashover-script'])) { ?>
	var hashover_script = 'hashover-script-<?php echo $_GET['hashover-script']; ?>';
	var this_script = document.getElementById(hashover_script);
	script.parentNode.insertBefore(hashover_div, this_script);
<?php } else { ?>
	document.write(hashover_div.outerHTML);
<?php } ?>
}

if (document.getElementById('comments') == null) {
	hashover += '<span id="comments"></span>';
}

<?php if ($this->read_comments->total_count - 1 != 0) { ?>
// Put number of comments into "hashover-cmtcount" identified HTML element
if (document.getElementById('hashover-cmtcount') != null) {
	document.getElementById('hashover-cmtcount').textContent = '<?php echo $this->read_comments->total_count - 1; ?>';
}

<?php } ?>
// Displays reply form
function hashover_reply(r, f) {
	var reply_form = '<div class="hashover-balloon">';
<?php

	$first_cmt_image = '<div class="hashover-avatar-image hashover-avatar-image">' . $form_first_image . '</div>';

	if (!empty($_COOKIE['hashover-login'])) {
		$first_cmt_image = '<div class="hashover-avatar-image">' . $form_avatar . '</div>';

		if ($this->setup->icon_mode != 'none') {
			echo "\t", 'reply_form += \'', $first_cmt_image, '\';', PHP_EOL;
		}

?>
	reply_form += '<input type="hidden" name="name" value="<?php if (!empty($_COOKIE['name'])) echo $_COOKIE['name']; ?>">\n';
	reply_form += '<input type="hidden" name="password" value="<?php if (!empty($_COOKIE['password'])) echo $_COOKIE['password']; ?>">\n';
	reply_form += '<input type="hidden" name="email" value="<?php if (!empty($_COOKIE['email'])) echo $_COOKIE['email']; ?>">\n';
	reply_form += '<input type="hidden" name="website" value="<?php if (!empty($_COOKIE['website'])) echo $_COOKIE['website']; ?>">\n';
<?php

	} else {

?>
	reply_form += '<div class="hashover-inputs">\n';
<?php

	if ($this->setup->icon_mode != 'none') {
		echo 'reply_form += \'', $first_cmt_image, '\'', PHP_EOL;
	}

?>

	if (name_on) {
		reply_form += '<div class="hashover-name-input">\n<input type="text" name="name" title="<?php echo $this->setup->text['name_tip']; ?>" value="<?php if (!empty($_COOKIE['name'])) echo $_COOKIE['name']; ?>" maxlength="30" placeholder="<?php echo $this->setup->text['name']; ?>">\n</div>\n';
	}

	if (password_on) {
		reply_form += '<div class="hashover-password-input">\n<input type="password" name="password" title="<?php echo $this->setup->text['password_tip']; ?>" value="<?php if (!empty($_COOKIE['password'])) echo $_COOKIE['password']; ?>" placeholder="<?php echo $this->setup->text['password']; ?>">\n</div>\n';
	}

<?php
	if ($this->setup->is_mobile) {
		echo "\t", 'reply_form += \'</div>\n<div class="hashover-inputs">\n\';', PHP_EOL, PHP_EOL;
	}
?>
	if (email_on) {
		reply_form += '<div class="hashover-email-input">\n<input type="text" name="email" title="<?php echo $this->setup->text['email_tip']; ?>" value="<?php if (!empty($_COOKIE['email'])) echo $_COOKIE['email']; ?>" placeholder="<?php echo $this->setup->text['email']; ?>">\n</div>\n';
	}

	if (website_on) {
		reply_form += '<div class="hashover-website-input">\n<input type="text" name="website" title="<?php echo $this->setup->text['website_tip']; ?>" value="<?php if (!empty($_COOKIE['website'])) echo $_COOKIE['website']; ?>" placeholder="<?php echo $this->setup->text['website']; ?>">\n</div>\n';
	}

	reply_form += '</div>\n';
<?php } ?>
	reply_form += '<div id="hashover-message-' + r + '" class="hashover-message"></div>\n';
	reply_form += '<textarea rows="5" cols="62" name="comment" title="<?php echo $this->setup->text['cmt_tip']; ?>" placeholder="<?php echo $this->setup->text['reply_form']; ?>"></textarea>\n';
	reply_form += '<div class="hashover-form-buttons">\n';
<?php

	if (empty($_COOKIE['hashover-login']) or !empty($_COOKIE['email'])) {

?>
	reply_form += '<label for="subscribe-' + r + '" title="<?php echo $this->setup->text['subscribe_tip']; ?>">\n';
	reply_form += '<input type="checkbox" checked="true" id="subscribe-' + r + '" name="subscribe"> <?php echo $this->setup->text['subscribe']; ?>\n';
	reply_form += '</label>\n';
<?php } ?>
	reply_form += '<input type="hidden" name="title" value="<?php echo $page_title; ?>">\n';
	reply_form += '<input type="hidden" name="url" value="<?php echo $page_url; ?>">\n';
	reply_form += '<input type="hidden" name="reply_to" value="' + f + '">\n';
	reply_form += '<input class="hashover-submit" type="submit" value="<?php echo $this->setup->text['post_reply']; ?>" onclick="return hashover_submit(\'' + r + '\', this);" onsubmit="return hashover_submit(\'' + r + '\', this);">\n';
	reply_form += '<input class="hashover-submit" type="button" value="<?php echo $this->setup->text['cancel']; ?>" title="<?php echo $this->setup->text['cancel']; ?>" onclick="hashover_cancel_reply(\'' + r + '\'); return false;">\n';
	reply_form += '</div>\n</div>';

	document.getElementById('hashover-reply-' + r).className = 'hashover-comment hashover-reply';
	document.getElementById('hashover-reply-' + r).innerHTML = reply_form;
	return false;
}

// Displays edit form
function hashover_edit(e, f, s) {
	var cmtdata = document.getElementById('hashover-content-' + e).innerHTML.replace(/<br>/gi, '\n').replace(/<\/?a(\s+.*?>|>)/gi, '').replace(/<img.*?title="(.*?)".*?>/gi, '[img]$1[/img]').replace(/^\s+|\s+$/g, '').replace('<code style="white-space: pre;">', '<code>');
	var name = document.getElementById('hashover-name-' + e).textContent;
	var website = '';

	if (document.getElementById('hashover-name-' + e).href) {
		website = document.getElementById('hashover-name-' + e).href;
	}

	var edit_form = '<div class="hashover-dashed-title hashover-title"><?php echo $this->setup->text['edit_cmt']; ?></div>\n';
	edit_form += '<div class="hashover-inputs">\n';
	edit_form += '<div class="hashover-name-input"><input type="text" name="name" title="<?php echo $this->setup->text['name_tip']; ?>" value="' + name + '" maxlength="30" placeholder="<?php echo $this->setup->text['name']; ?>"></div>\n';
	edit_form += '<div class="hashover-password-input"><input type="password" name="password" title="<?php echo $this->setup->text['password_tip']; ?>" value="<?php if (!empty($_COOKIE['password'])) echo $_COOKIE['password']; ?>" placeholder="<?php echo $this->setup->text['password']; ?>"></div>\n';
<?php
	if ($this->setup->is_mobile) {
		echo "\t", 'edit_form += \'</div>\n<div class="hashover-inputs">\n\';', PHP_EOL;
	}
?>
	edit_form += '<div class="hashover-email-input"><input type="text" name="email" title="<?php echo $this->setup->text['email_tip']; ?>" value="<?php if (!empty($_COOKIE['email'])) echo $_COOKIE['email']; ?>" placeholder="<?php echo $this->setup->text['email']; ?>"></div>\n';
	edit_form += '<div class="hashover-website-input"><input type="text" name="website" title="<?php echo $this->setup->text['website_tip']; ?>" value="' + website + '" placeholder="<?php echo $this->setup->text['website']; ?>"></div>\n';
	edit_form += '</div>\n';
	edit_form += '<textarea rows="10" cols="62" name="comment" title="<?php echo $this->setup->text['cmt_tip']; ?>">' + cmtdata + '</textarea>\n';
	edit_form += '<div class="hashover-form-buttons">\n';
	edit_form += '<label for="notify" title="<?php echo $this->setup->text['subscribe_tip']; ?>">\n';
	edit_form += '<input type="checkbox"' + ((s != '0') ? ' checked="true"' : '') + ' id="notify" name="notify"> <?php echo $this->setup->text['subscribe']; ?>\n';
	edit_form += '</label>\n';
	edit_form += '<input type="hidden" name="title" value="<?php echo $page_title; ?>">\n';
	edit_form += '<input type="hidden" name="url" value="<?php echo $page_url; ?>">\n';
	edit_form += '<input type="hidden" name="cmtfile" value="' + f + '">\n';
	edit_form += '<input class="hashover-submit" type="submit" name="edit" value="<?php echo $this->setup->text['save_edit']; ?>">\n';
	edit_form += '<input class="hashover-submit" type="button" value="<?php echo $this->setup->text['cancel']; ?>" onclick="hashover_cancel_edit(\'' + e + '\'); return false;">\n';
	edit_form += '<input class="hashover-submit hashover-post-button" type="submit" name="delete" value="<?php echo $this->setup->text['delete']; ?>" onclick="return hashover_deletion_warning();">\n';
	edit_form += '</div>\n';

	document.getElementById('hashover-edit-' + e).innerHTML = edit_form;
	document.getElementById('hashover-footer-' + e).style.display = 'none';
	return false
}

// Disable submit buttons on submissions
function hashover_submit(f, b) {
	var return_value = true;

	if (f == true) {
		if (document.hashover_form.comment.value == '') {
			document.getElementById('hashover-message').textContent = '<?php echo $this->setup->text['cmt_needed']; ?>';
			document.getElementById('hashover-message').style.display = null;
			document.hashover_form.comment.focus();

			setTimeout(function() {
				document.getElementById('hashover-message').style.display = 'none';
			}, 10000);

			return_value = false;
		}
	} else {
		var hashover_reply_forms = document.getElementById('hashover-reply-' + f);

		if (hashover_reply_forms.comment.value == '') {
			document.getElementById('hashover-message-' + f).textContent = '<?php echo $this->setup->text['reply_needed']; ?>';
			document.getElementById('hashover-message-' + f).className = 'hashover-message open';
			hashover_reply_forms.comment.focus();

			setTimeout(function() {
				document.getElementById('hashover-message-' + f).className = 'hashover-message';
			}, 10000);

			return_value = false;
		}
	}

	if (return_value != false) {
		if (hashover_validate(f) == false) {
			return false;
		}

		setTimeout(function() { b.disabled = true; }, 1000);
		setTimeout(function() { b.disabled = false; }, 20000);
	}

	return return_value;
}

// Handles display of various warnings when user attempts to post or login
function hashover_validate(f) {
	if (email_on == false) {
		return true;
	}

	if (f == true) {
		var form_email = document.hashover_form.email;

		if (document.getElementById('hashover-subscribe').checked == false) {
			form_email.value = '';
			return true;
		}

		if (form_email.value == '') {
			var answer = confirm('<?php echo $this->setup->text['no_email_warn']; ?>');

			if (answer == false) {
				document.hashover_form.email.focus();
				return false;
			}
		} else {
			if (!form_email.value.match(/\S+@\S+/)) {
				document.getElementById('hashover-message').textContent = '<?php echo $this->setup->text['invalid_email']; ?>';
				document.getElementById('hashover-message').style.display = null;
				document.hashover_form.email.focus();

				setTimeout(function() {
					document.getElementById('hashover-message').style.display = 'none';
				}, 10000);

				return false;
			}
		}
	} else {
		var hashover_reply_forms = document.getElementById('hashover-reply-' + f);

		if (document.getElementById('subscribe-' + f).checked == false) {
			hashover_reply_forms.email.value = '';
			return true;
		}

		if (hashover_reply_forms.email.value == '') {
			var answer = confirm('<?php echo $this->setup->text['no_email_warn']; ?>');

			if (answer == false) {
				hashover_reply_forms.email.focus();
				return false;
			}
		} else {
			if (!hashover_reply_forms.email.value.match(/\S+@\S+/)) {
				document.getElementById('hashover-message-' + f).textContent = '<?php echo $this->setup->text['invalid_email']; ?>';
				document.getElementById('hashover-message-' + f).className = 'hashover-message open';
				hashover_reply_forms.email.focus();

				setTimeout(function() {
					document.getElementById('hashover-message-' + f).className = 'hashover-message';
				}, 10000);

				return false;
			}
		}
	}

	return true;
}

// Function to cancel reply forms
function hashover_cancel_reply(f) {
	document.getElementById('hashover-reply-' + f).textContent = '';
	document.getElementById('hashover-reply-' + f).className = '';
	return false;
}

// Function to cancel edit forms
function hashover_cancel_edit(f) {
	document.getElementById('hashover-footer-' + f).style.display = '';
	document.getElementById('hashover-edit-' + f).textContent = '';
	return false;
}

// Function to like a comment
function hashover_like(a, c, f) {
	// Load "like.php"
	var like = new XMLHttpRequest();
	var likeElement = document.getElementById('hashover-' + a + '-' + c);
	var likesElement = document.getElementById('hashover-' + a + 's-' + c);
	var dislikesClass = (a == 'like') ? '<?php if ($this->setup->allows_dislikes == 'yes') echo ' dislikes'; ?>' : '';
	like.open('GET', '<?php echo $this->setup->root_dir, '/scripts/like.php?like=', $this->setup->ref_path; ?>/' + f + '&action=' + a);
	like.send();

	// Get number of likes
	if (likesElement.textContent != '') {
		var likes = parseInt(likesElement.textContent.replace(/[^0-9]/g, ''));
	} else {
		var likes = parseInt(0);
	}

	// Change "Like" button title and class; Increase likes
	if (likeElement.className == 'hashover-' + a + dislikesClass) {
		likeElement.className = 'hashover-' + a + 'd' + dislikesClass;
		likeElement.title = (a == 'like') ? '<?php echo $this->setup->text['liked_cmt']; ?>' : '<?php echo $this->setup->text['disliked_cmt']; ?>';
		likeElement.textContent = (a == 'like') ? '<?php echo $this->setup->text['liked']; ?>' : '<?php echo $this->setup->text['disliked']; ?>';
		likes++;
	} else {
		likeElement.className = 'hashover-' + a + dislikesClass;
		likeElement.title = (a == 'like') ? '<?php echo $this->setup->text['like_cmt']; ?>' : '<?php echo $this->setup->text['dislike_cmt']; ?>';
		likeElement.textContent = (a == 'like') ? '<?php echo $this->setup->text['like'][0]; ?>' : '<?php echo $this->setup->text['dislike'][0]; ?>';
		likes--;
	}

	// Change number of likes
	var like_count = likes + ((a == 'like') ? ' Like' : ' Dislike') + ((likes != 1) ? 's' : '');
	likesElement.innerHTML = (likes > 0) ? '<b>' + like_count + '</b>' : '';
}

// Displays confirmation dialog for deletion
function hashover_deletion_warning() {
	var answer = confirm('<?php echo $this->setup->text['delete_cmt']; ?>');

	if (answer == false) {
		return false;
	}
}

// Add comment content to HTML template
function parse_template(object, count, sort, method, forpop) {
	count = count || false;
	sort = sort || false;
	method = method || 'ascending';
	forpop = forpop || false;

	var replies = 0;
	var permalink = object['permalink'];
	var permatext = permalink.replace('_pop', '').slice(1).split('r').pop();
	var cmtclass = '';
	var avatar = '';

	if (forpop == false) {
		if (permalink.match('r') && (sort == false || method == 'ascending')) {
			cmtclass = ' hashover-reply';
		}
	}

	if (count) {
		if (collapse_limit >= <?php echo $this->setup->collapse_limit; ?>) {
			cmtclass += ' hashover-collapsed';
		} else {
			collapse_limit++;
		}
	}

	if (object['notice_class']) {
		cmtclass += ' ' + object['notice_class'];
	}

	window['hashover'] += '\t<div id="' + permalink + '" class="hashover-comment' + cmtclass + '">\n';

	// Setup avatar icon
	if (object['avatar']) {
		avatar = '<span class="hashover-avatar"><?php echo $js_avatar; ?></span>';
	}

	if (!object['notice']) {
		// Add HTML anchor tag to URLs
		var clean_code = object['comment'].replace(/(((ftp|http|https){1}:\/\/)[a-zA-Z0-9-@:%_\+.~#?&\/=]+)([\s]{0,})/ig, '<a href="$1" target="_blank">$1</a>');

		// Replace [img] tags with external image placeholder if enabled
		clean_code = clean_code.replace(/\[img\]<a.*?>(((ftp|http|https){1}:\/\/)[a-zA-Z0-9-@:%_\+.~#?&\/=]+)<\/a>\[\/img\]/ig, function(fullURL, url) {
<?php if ($this->setup->allows_images == 'yes') { ?>
			var extensions = ['<?php echo implode('\', \'', $this->setup->image_types); ?>'];
			var urlExtension = url.split('.').pop().split(/\#|\?/)[0];

			// Check if the image URL is of an allowed type
			for (var ext = 0, length = extensions.length; ext < length; ext++) {
				if (extensions[ext] == urlExtension) {
					var imgtag = document.createElement('img');
					var placeholder = '<?php echo $this->setup->root_dir, '/images/', $this->setup->image_format, 's/place-holder.', $this->setup->image_format; ?>';

					// Add external image tag attributes
					imgtag.className = 'hashover-imgtag';
					imgtag.src = placeholder;
					imgtag.title = 'Click to view external image';
					imgtag.dataset.placeholder = placeholder;
					imgtag.dataset.url = url;
					imgtag.alt = 'External Image';

					return '<br><br>' + imgtag.outerHTML + '<br><br>';
				}
			};

<?php } ?>
			return '<a href="' + url + '" target="_blank">' + url + '</a>';
		});

		// Remove repetitive and trailing HTML <br> tags
		clean_code = clean_code.replace(/(<br>){2,}/ig, '<br><br>').replace(/(<br><br>)$/g, '').replace(/^(<br><br>)/g, '');

		for (var reply in object['replies']) {
			replies++;
		}

		var likes_num = (object['likes'] != '0') ? object['likes'] + ' ' + ((object['likes'] == '1') ? '<?php echo $this->setup->text['like'][0]; ?>' : '<?php echo $this->setup->text['like'][1]; ?>') : '';
		var dislikes_num = (object['dislikes'] != '0') ? object['dislikes'] + ' ' + ((object['dislikes'] == '1') ? '<?php echo $this->setup->text['dislike'][0]; ?>' : '<?php echo $this->setup->text['dislike'][1]; ?>') : '';

		var 
			name = object['name'].replace(/^@(.*?)$/, '$1'),
			date = '<a href="#' + permalink + '" title="Permalink">' + object['date'] + '</a>',
			thread = (permalink.match('r')) ? '<a href="#' + permalink.replace(/^(.*)r.*$/, '$1') + '" title="<?php echo $this->setup->text['thread_tip']; ?>" class="hashover-thread-link"><?php echo $this->setup->text['thread']; ?></a>' : '',
			replies = (replies > 0) ? '<span class="hashover-replies">' + replies + ((replies != 1) ? ' <?php echo $this->setup->text['replies']; ?>' : ' <?php echo $this->setup->text['reply']; ?>') + '</span>' : '',
			likes = (object['likes']) ? '<span id="hashover-likes-' + permalink + '" class="hashover-likes">' + likes_num + '</span>' : '',
			like_link = (object['like_link']) ? object['like_link'] : '',
			dislikes = (object['dislikes']) ? '<span id="hashover-dislikes-' + permalink + '" class="hashover-dislikes">' + dislikes_num + '</span>' : '',
			dislike_link = (object['dislike_link']) ? object['dislike_link'] : '',
			edit_link = (object['edit_link']) ? object['edit_link'] : '',
			reply_link = object['reply_link'],
			comment = clean_code,
			action = '<?php echo $this->setup->root_dir; ?>/hashover.php',
			reply_form = '',
			edit_form = '',
			hashover_footer_style = '',
			hashover_reply_form_class = ''
		;

		var name_at = (object['name'].match(/^@.*?$/)) ? '@' : '';
		var name_class = (object['name'].match(/^@.*?$/)) ? ' at' : '';

		if (object['website'] == '') {
			if (object['name'].match(/^@([a-zA-Z0-9_@]{1,29}$)/)) {
				name = '<a id="hashover-name-' + permalink + '" href="http://twitter.com/' + name + '" target="_blank">' + name + '</a>';
			} else {
				name = '<span id="hashover-name-' + permalink + '">' + name + '</span>';
			}
		} else {
			name = '<a id="hashover-name-' + permalink + '" href="' + object['website'] + '" target="_blank">' + name + '</a>';
		}

		name = '<span class="hashover-name' + name_class + '">' + name_at + name + '</span>';

<?php
		// Load HTML template
		$theme_layout = explode(PHP_EOL, file_get_contents('./themes/' . $this->setup->theme . '/layout.html'));
		$theme_layout = str_replace("\t", '\t', $theme_layout);

		for ($line = 0, $length = count($theme_layout); $line < $length; $line++) {
			if (!empty($theme_layout[$line])) {
				echo "\t\t", 'window[\'hashover\'] += \'\t\t', $theme_layout[$line], '\n\';', PHP_EOL;
			}
		}
?>
	} else {
		if (object['avatar']) {
			window['hashover'] += '\t\t<div class="hashover-header">\n';
			window['hashover'] += '\t\t\t' + avatar + '\n';
			window['hashover'] += '\t\t</div>\n';
		}

		window['hashover'] += '\t\t<div class="hashover-balloon">\n';
		window['hashover'] += '\t\t\t<div id="hashover-content-' + permalink + '" class="hashover-content">\n';
		window['hashover'] += '\t\t\t\t<span class="hashover-title">' + object['notice'] + '</span>\n';
		window['hashover'] += '\t\t\t</div>\n';
		window['hashover'] += '\t\t</div>\n';
	}

	if (object['replies']<?php echo ($this->setup->reply_mode == 'stream') ? ' && !permalink.match(\'r\')' : ''; ?>) {
		for (var reply in object['replies']) {
			parse_template(object['replies'][reply], count);
		}
<?php if ($this->setup->reply_mode == 'stream') { ?>

		window['hashover'] += '\t</div>\n';
	} else {
		window['hashover'] += '\t</div>\n';

		for (var reply in object['replies']) {
			parse_template(object['replies'][reply], count);
		}
	}
<?php } else { ?>
	}

	window['hashover'] += '\t</div>\n';
<?php } ?>
}

// "Flatten" the comments object
function getAllComments(object) {
	var objectCopy = JSON.parse(JSON.stringify(object));
	var output = [];

	var descend = function(object) {
		output.push(object);

		if (object.replies) {
			for (var comment in object.replies) {
				descend(object.replies[comment]);
			}

			delete object.replies;
		}
	};

	for (var comment in objectCopy) {
		descend(objectCopy[comment]);
	}

	return output;
}

// Five method sort
function sort_comments(method) {
	var methods = {
		ascending: function() {
			for (var comment in comments) {
				parse_template(comments[comment], false, true, method);
			}
		},

		descending: function() {
			var tmpArray = getAllComments(comments);

			var tmpSortArray = Object.keys(tmpArray).map(function(key) {
				return tmpArray[key];
			});

			for (var i = tmpSortArray.length - 1; i >= 0; i--) {
				parse_template(tmpSortArray[i], false, true, method);
			}
		},

		byname: function() {
			var tmpArray = getAllComments(comments);

			var tmpSortArray = Object.keys(tmpArray).map(function(key) {
				return tmpArray[key];
			});

			tmpSortArray = tmpSortArray.sort(function(a, b) {
				if(a.name < b.name) return -1;
				if(a.name > b.name) return 1;
			});

			for (var comment in tmpSortArray) {
				parse_template(tmpSortArray[comment], false, true, method);
			}
		},

		bydate: function() {
			var tmpSortArray = getAllComments(comments).sort(function(a, b) {
				if (a.sort_date === b.sort_date) return 1;
				return b.sort_date - a.sort_date;
			});

			for (var comment in tmpSortArray) {
				parse_template(tmpSortArray[comment], false, true, method);
			}
		},

		bylikes: function() {
			var tmpSortArray = getAllComments(comments).sort(function(a, b) {
				a.likes = a.likes || 0;
				b.likes = b.likes || 0;
				a.dislikes = a.dislikes || 0;
				b.dislikes = b.dislikes || 0;

				return (b.likes - b.dislikes) - (a.likes - a.dislikes);
			});

			for (var comment in tmpSortArray) {
				parse_template(tmpSortArray[comment], false, true, method);
			}
		},

		descending_threaded: function() {
			var tmpSortArray = Object.keys(comments).map(function(key) {
				return comments[key];
			});

			for (var i = tmpSortArray.length - 1; i >= 0; i--) {
				parse_template(tmpSortArray[i], false, true, method);
			}
		},

		byname_threaded: function() {
			var tmpSortArray = Object.keys(comments).map(function(key) {
				return comments[key];
			});

			tmpSortArray = tmpSortArray.sort(function(a, b) {
				if(a.name < b.name) return -1;
				if(a.name > b.name) return 1;
			});

			for (var comment in tmpSortArray) {
				parse_template(tmpSortArray[comment], false, true, method);
			}
		},

		bydate_threaded: function() {
			var tmpSortArray = Object.keys(comments).map(function(key) {
				return comments[key];
			});

			tmpSortArray = tmpSortArray.sort(function(a, b) {
				if (a.sort_date === b.sort_date) return 1;
				return b.sort_date - a.sort_date;
			});

			for (var comment in tmpSortArray) {
				parse_template(tmpSortArray[comment], false, true, method);
			}
		},

		bylikes_threaded: function() {
			var tmpSortArray = Object.keys(comments).map(function(key) {
				return comments[key];
			});

			tmpSortArray = tmpSortArray.sort(function(a, b) {
				a.likes = a.likes || 0;
				b.likes = b.likes || 0;
				a.dislikes = a.dislikes || 0;
				b.dislikes = b.dislikes || 0;

				return (b.likes - b.dislikes) - (a.likes - a.dislikes);
			});

			for (var comment in tmpSortArray) {
				parse_template(tmpSortArray[comment], false, true, method);
			}
		}
	}

	hashover = '';
	document.getElementById('hashover-sort-div').textContent = 'Loading...';
	methods[method]();
	document.getElementById('hashover-sort-div').innerHTML = hashover;
}

<?php if ($this->setup->collapses_comments == 'yes') { ?>
function show_cmts(element) {
	element.className = 'hidden';

	setTimeout(function() {
		element.style.display = 'none';
		document.getElementById('hashover-sort').style.display = '';
		document.getElementById('hashover-count').style.display = '';

		var collapsed_comments = document.getElementsByClassName('hashover-comment');

		for (var i = 0, il = collapsed_comments.length; i < il; i++) {
			if (collapsed_comments[i].className.match('hashover-collapsed')) {
				collapsed_comments[i].className = collapsed_comments[i].className.replace(' hashover-collapsed', '');
			}
		}
	}, 350);
}

<?php } ?>
<?php

	echo $this->setup->escape_output('<span class="hashover-title hashover-main-title hashover-dashed-title">' . $js_title . '</span>\n');

	if (!empty($_COOKIE['message'])) {
		echo $this->setup->escape_output('<span id="hashover-message" class="hashover-title">' . $_COOKIE['message'] . '</span>\n');
	} else {
		echo 'hashover += \'<span id="hashover-message" class="hashover-title" style="display: none;"></span>\n\';';
	}

?>

hashover += '<form id="hashover_form" name="hashover_form" action="<?php echo $this->setup->root_dir; ?>/hashover.php" method="post">\n';
hashover += '\t<div class="hashover-balloon">\n';
hashover += '\t\t<div class="hashover-inputs">\n';
<?php

	if ($this->setup->icon_mode != 'none') {
		if ($this->setup->icon_mode == 'image') {
			if (!empty($_COOKIE['hashover-login'])) {
				echo $this->setup->escape_output('\t\t\t<div class="hashover-avatar-image">' . $form_avatar . '</div>');
			} else {
				echo $this->setup->escape_output('\t\t\t<div class="hashover-avatar-image hashover-avatar-first">' . $form_first_image . '</div>');
			}
		} else {
			echo $this->setup->escape_output('\t\t\t<div class="hashover-avatar-image"><span>#' . $this->read_comments->cmt_count . '</span></div>');
		}
	}

	if (!empty($_COOKIE['hashover-login'])) {
		$name = !empty($_COOKIE['name']) ? $_COOKIE['name'] : $this->setup->default_name;
		echo 'hashover += \'\t\t\t<div>\n\';', PHP_EOL;

		if (!empty($_COOKIE['website'])) {
			echo $this->setup->escape_output('\t\t\t\t<a class="hashover-name hashover-top-name" href="' . $_COOKIE['website'] . '" target="_blank">' . $name . '</a>\n');
		} else {
			echo $this->setup->escape_output('\t\t\t\t<span class="hashover-name hashover-top-name">' . $name . '</span>\n');
		}

		echo 'hashover += \'\t\t\t</div>\n\';', PHP_EOL;
		echo $this->setup->escape_output('\t\t\t<input type="hidden" name="name" value="' . ((!empty($_COOKIE['name'])) ? $_COOKIE['name'] : '') . '">\n');
		echo $this->setup->escape_output('\t\t\t<input type="hidden" name="password" value="' . ((!empty($_COOKIE['password'])) ? $_COOKIE['password'] : '') . '">\n');
		echo $this->setup->escape_output('\t\t\t<input type="hidden" name="email" value="' . ((!empty($_COOKIE['email'])) ? $_COOKIE['email'] : '') . '">\n');
		echo $this->setup->escape_output('\t\t\t<input type="hidden" name="website" value="' . ((!empty($_COOKIE['website'])) ? $_COOKIE['website'] : '') . '">\n');
	} else {

?>

// Display name input tag if told to
if (name_on) {
	hashover += '\t\t\t<div class="hashover-name-input">\n';
	<?php echo $this->setup->escape_output('\t\t\t\t<input type="text" name="name" title="' . $this->setup->text['name_tip'] . '" value="' . ((!empty($_COOKIE['name'])) ? $_COOKIE['name'] : '') . '" maxlength="30" placeholder="' . $this->setup->text['name'] . '">\n'); ?>
	hashover += '\t\t\t</div>\n';
}

// Display password input tag if told to
if (password_on) {
	hashover += '\t\t\t<div class="hashover-password-input">\n';
	<?php echo $this->setup->escape_output('\t\t\t\t<input type="password" name="password" title="' . $this->setup->text['password_tip'] . '" value="' . ((!empty($_COOKIE['password'])) ? $_COOKIE['password'] : '') . '" placeholder="' . $this->setup->text['password'] . '">\n'); ?>
	hashover += '\t\t\t</div>\n';
}

<?php

	if ($this->setup->is_mobile) {
		echo 'hashover += \'\t\t\t</div>\n\t\t\t<div class="hashover-inputs">\n\';', PHP_EOL;

		if ($this->setup->icon_mode != 'none') {
			echo 'hashover += \'\t\t\t\t<div class="hashover-avatar-image"></div>\n\';', PHP_EOL;
		}
	}

?>

// Display email input tag if told to
if (email_on) {
	hashover += '\t\t\t<div class="hashover-email-input">\n';
	<?php echo $this->setup->escape_output('\t\t\t\t<input type="text" name="email" title="' . $this->setup->text['email_tip'] . '" value="' . ((!empty($_COOKIE['email'])) ? $_COOKIE['email'] : '') . '" placeholder="' . $this->setup->text['email'] . '">\n'); ?>
	hashover += '\t\t\t</div>\n';
}

// Display website input tag if told to
if (website_on) {
	hashover += '\t\t\t<div class="hashover-website-input">\n';
	<?php echo $this->setup->escape_output('\t\t\t\t<input type="text" name="website" title="' . $this->setup->text['website_tip'] . '" value="' . ((!empty($_COOKIE['website'])) ? $_COOKIE['website'] : '') . '" placeholder="' . $this->setup->text['website'] . '">\n'); ?>
	hashover += '\t\t\t</div>\n';
}

<?php } ?>
hashover += '\t\t</div>\n'
hashover += '\t\t<div id="hashover-requiredFields">\n';
hashover += '\t\t\t<input type="text" name="summary" value="" placeholder="Summary">\n';
hashover += '\t\t\t<input type="hidden" name="age" value="" placeholder="Age">\n';
hashover += '\t\t\t<input type="text" name="lastname" value="" placeholder="Last Name">\n';
hashover += '\t\t\t<input type="text" name="address" value="" placeholder="Address">\n';
hashover += '\t\t\t<input type="hidden" name="zip" value="" placeholder="Last Name">\n';
hashover += '\t\t</div>\n';

<?php

	$replyborder = (isset($_COOKIE['success']) and $_COOKIE['success'] == 'no') ? ' style="border: 2px solid #FF0000 !important;"' : '';
	echo $this->setup->escape_output('\t\t<textarea rows="5" cols="63" name="comment"' . $replyborder . ' title="' . $this->setup->text['cmt_tip'] . '" placeholder="' . $this->setup->text['comment_form'] . '"></textarea>\n');
	echo $this->setup->escape_output('\t\t<input type="hidden" name="title" value="' . $page_title . '">\n');
	echo $this->setup->escape_output('\t\t<input type="hidden" name="url" value="' . $page_url . '">\n');

	if (isset($_COOKIE['replied'])) {
		echo $this->setup->escape_output('\t\t<input type="hidden" name="reply_to" value="' . $_COOKIE['replied'] . '">\n');
	}

	echo $this->setup->escape_output('\t\t<div class="hashover-main-buttons">\n');

	if (empty($_COOKIE['hashover-login']) or !empty($_COOKIE['email'])) {
		echo $this->setup->escape_output('\t\t\t<label for="hashover-subscribe" title="' . $this->setup->text['subscribe_tip'] . '">\n');
		echo $this->setup->escape_output('\t\t\t\t<input id="hashover-subscribe" type="checkbox" name="subscribe" checked="true"> ' . $this->setup->text['subscribe'] . '\n');
		echo $this->setup->escape_output('\t\t\t</label>\n');
	}

	echo $this->setup->escape_output('\t\t\t<input class="hashover-submit hashover-post-button" type="submit" value="' . $this->setup->text['post_button'] . '" onclick="return hashover_submit(true, this);" onsubmit="return hashover_submit(true, this);">\n');

	if (empty($_COOKIE['hashover-login'])) {
		echo $this->setup->escape_output('\t\t\t<input class="hashover-submit hashover-login" type="submit" name="login" title="' . $this->setup->text['login_tip'] . '" value="' . $this->setup->text['login'] . '" onclick="return hashover_validate(true);" onsubmit="return hashover_validate(true);">\n');
	} else {
		echo $this->setup->escape_output('\t\t\t<input class="hashover-submit hashover-logout" type="submit" name="logout" title="' . $this->setup->text['logout'] . '" value="' . $this->setup->text['logout'] . '">\n');
	}

	echo $this->setup->escape_output('\t\t</div>\n');

?>
hashover += '\t</div>\n</form>\n';

<?php
	// Display most popular comments
	if (!empty($this->top_likes) and $this->setup->pop_limit > 0) {
		krsort($this->top_likes); // Sort popular comments
		$popPlural = (count($this->top_likes) != 1) ? 1 : 0;
		$js_out = array();

		if (!empty($this->top_likes)) {
			for ($p = 1, $pl = count($this->top_likes); $p <= $pl and $p <= $this->setup->pop_limit; $p++) {
				$popKey = array_shift($this->top_likes);
				$popComment = $this->read_comments->data->read($popKey);
				$js_out[$p] = $this->parse($popComment, $popKey, true, true);
			}
		}

		if (defined('JSON_PRETTY_PRINT')) {
			$json_popComments = json_encode($js_out, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);
		} else {
			$json_popComments = json_encode($js_out, JSON_FORCE_OBJECT);
		}

		echo 'var popComments = ';
		echo str_replace($json_search, $json_replace, $json_popComments);
		echo ';', PHP_EOL, PHP_EOL;

		echo 'hashover += \'<div class="hashover-dashed-title">\n\';', PHP_EOL;
		echo 'hashover += \'<span class="hashover-title">\n\';', PHP_EOL;
		echo $this->setup->escape_output($this->setup->text['popular_cmts'][$popPlural] . '\n');
		echo 'hashover += \'</span>\n\';', PHP_EOL;
		echo 'hashover += \'</div>\n\';', PHP_EOL, PHP_EOL;
		echo 'hashover += \'<div id="hashover-top-comments">\n\';', PHP_EOL, PHP_EOL;

		echo 'for (var comment in popComments) {', PHP_EOL;
		echo "\t", 'parse_template(popComments[comment], false, false, false, true);', PHP_EOL;
		echo '}', PHP_EOL, PHP_EOL;

		echo 'hashover += \'</div>\n\';', PHP_EOL, PHP_EOL;
	}

	if (!empty($this->hashover)) {
		if (defined('JSON_PRETTY_PRINT')) {
			$json_comments = json_encode($this->hashover, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);
		} else {
			$json_comments = json_encode($this->hashover, JSON_FORCE_OBJECT);
		}

		echo 'var comments = ';
		echo str_replace($json_search, $json_replace, $json_comments);
		echo ';', PHP_EOL, PHP_EOL;
	}

	// Display comments, if there are no comments display a note
	if (!empty($this->hashover)) {
		echo 'hashover += \'<div class="hashover-dashed-title hashover-sort-count">\';';

		// Display comment count
		if ($this->setup->collapse_limit >= 1) {
			echo $this->setup->escape_output('<span id="hashover-count">' . $this->read_comments->show_count . '</span>\n');
		} else {
			echo $this->setup->escape_output('<span id="hashover-count" style="display: none;">' . $this->read_comments->show_count . '</span>\n');
		}

		if ($this->setup->collapse_limit >= 1) {
			echo 'hashover += \'<span id="hashover-sort">\n\';', PHP_EOL;
		} else {
			echo 'hashover += \'<span id="hashover-sort" style="display: none;">\n\';', PHP_EOL;
		}

		echo 'hashover += \'\t<select name="sort" size="1" onChange="sort_comments(this.value); return false;">\n\';', PHP_EOL;
		echo 'hashover += \'\t\t<option value="ascending">', $this->setup->text['sort_ascend'], '</option>\n\';', PHP_EOL;
		echo 'hashover += \'\t\t<option value="descending">', $this->setup->text['sort_descend'], '</option>\n\';', PHP_EOL;
		echo 'hashover += \'\t\t<option value="bydate">', $this->setup->text['sort_bydate'], '</option>\n\';', PHP_EOL;
		echo 'hashover += \'\t\t<option value="byname">', $this->setup->text['sort_byname'], '</option>\n\';', PHP_EOL;
		echo 'hashover += \'\t\t<option value="bylikes">', $this->setup->text['sort_bylikes'], '</option>\n\';', PHP_EOL;
		echo 'hashover += \'\t\t<optgroup label="&nbsp;"></optgroup>\n\';', PHP_EOL;
		echo 'hashover += \'\t\t<optgroup label="', $this->setup->text['threaded'], '">\n\';', PHP_EOL;
		echo 'hashover += \'\t\t\t<option value="descending_threaded">', $this->setup->text['sort_descend'], '</option>\n\';', PHP_EOL;
		echo 'hashover += \'\t\t\t<option value="bydate_threaded">', $this->setup->text['sort_bydate'], '</option>\n\';', PHP_EOL;
		echo 'hashover += \'\t\t\t<option value="byname_threaded">', $this->setup->text['sort_byname'], '</option>\n\';', PHP_EOL;
		echo 'hashover += \'\t\t\t<option value="bylikes_threaded">', $this->setup->text['sort_bylikes'], '</option>\n\';', PHP_EOL;
		echo 'hashover += \'\t\t</optgroup>\n\t</select>\n</span>\n</div>\n\';', PHP_EOL;
		echo 'hashover += \'<div id="hashover-sort-div">\n\';', PHP_EOL, PHP_EOL;

		if ($this->setup->collapses_comments == 'yes' and ($this->read_comments->total_count - 1) > $this->setup->collapse_limit) {
			echo 'for (var comment in comments) {', PHP_EOL;
			echo "\t", 'parse_template(comments[comment], !href.match(/#(hashover-(edit|reply)-|)c[1-9r]+/));', PHP_EOL;
			echo '}', PHP_EOL, PHP_EOL;

			echo 'if (!href.match(/#(hashover-(edit|reply)-|)c[1-9r]+/)) {', PHP_EOL;
			$collapse_plural = ($this->read_comments->total_count != 1) ? 1 : 0;

			if ($this->setup->collapse_limit >= 1) {
				$collapse_num_cmts = (($this->read_comments->total_count - 1) - $this->setup->collapse_limit);
				$collapse_link_text = str_replace('_NUM_', $collapse_num_cmts, $this->setup->text['other_cmts'][$collapse_plural]);
			} else {
				$collapse_link_text = str_replace('_NUM_', $this->read_comments->total_count - 1, $this->setup->text['show_num_cmts'][$collapse_plural]);
			}

			echo "\t", $this->setup->escape_output('<a href="#" id="hashover-more-link" onclick="show_cmts(this); return false;">' . $collapse_link_text . '</a>');
			echo '}', PHP_EOL, PHP_EOL;
		} else {
			echo 'for (var comment in comments) {', PHP_EOL;
			echo "\t", 'parse_template(comments[comment]);', PHP_EOL;
			echo '}', PHP_EOL, PHP_EOL;
		}

		echo $this->setup->escape_output('</div>\n');
	} else {
		echo 'parse_template({', PHP_EOL;
		echo "\t", '"avatar": "', $this->setup->root_dir, '/images/', $this->setup->image_format, 's/first-comment.', $this->setup->image_format, '",', PHP_EOL;
		echo "\t", '"permalink": "c1",', PHP_EOL;
		echo "\t", '"notice": "', $this->setup->text['first_cmt'], '",', PHP_EOL;
		echo "\t", '"notice_class": "hashover-first"', PHP_EOL;
		echo '});', PHP_EOL, PHP_EOL;
	}

?>
hashover += '</div>\n';

hashover += '<div id="hashover-end-links">\n';
hashover += '\t<a href="http://tildehash.com/?page=hashover" target="_blank"><b>HashOver Comments</b></a> &#8210;\n';
<?php

	if (!empty($this->hashover) and $this->setup->displays_rss_link == 'yes') {
		if ($this->setup->api_status('rss') != 'disabled') {
			echo $this->setup->escape_output('\t<a href="' . $this->setup->root_dir . '/api/rss.php?url=' . urlencode($this->setup->page_url) . '" target="_blank">RSS Feed</a> &middot;\n');
		}
	}

?>
hashover += '\t<a href="<?php echo $this->setup->root_dir; ?>/hashover.php?source" rel="hashover-source" target="_blank">Source Code</a> &middot;\n';
hashover += '\t<a href="<?php echo $this->setup->root_dir; ?>/hashover.php?url=<?php echo urlencode($page_url); ?>&title=<?php echo $page_title; ?>" rel="hashover-javascript" target="_blank">JavaScript</a>\n';
hashover += '</div>';

// Place all content on page
hashover_div.className = '<?php echo $this->setup->image_format; ?>';
hashover_div.innerHTML = hashover;

// Get all external image tags by class name
imgtags = document.getElementsByClassName('hashover-imgtag');

// Set onclick functions for external images
for (var i = 0, il = imgtags.length; i < il; i++) {
	imgtags[i].onclick = function() {
		if (this.src == this.dataset.url) {
			this.src = this.dataset.placeholder;
			this.title = 'Click to view external image';
			return false;
		}

		this.src = this.dataset.url;
		this.title = 'Loading...';

		this.onload = function() {
			this.title = 'Click to close';
			this.onload = null;
		};
	};
}

// Display reply form when the "hashover_reply" URL query is set
if (href.match(/hashover_reply=/)) {
	var comment = href.replace(/(.*?hashover_reply=c)([0-9r_pop]+)(.*)/, '$2');
	hashover_reply('c' + comment, comment.replace('r', '-'));
}

// Display edit form when the "hashover_edit" URL query is set
if (href.match(/hashover_edit=/)) {
	var comment = href.replace(/(.*?hashover_edit=c)([0-9r_pop]+)(.*)/, '$2');
	hashover_edit('c' + comment, comment.replace('r', '-'), 1);
}

// Workaround for stupid Chrome bug
window.onload = function() {
	var url_hash = this.location.hash;

	if (url_hash != '') {
		var permalink_anchor = document.getElementById(url_hash.slice(1));
		permalink_anchor.scrollIntoView(true);
	}
}
