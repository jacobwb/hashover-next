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
	if (isset($_GET['source']) and basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		header('Content-type: text/plain; charset=UTF-8');
		exit(file_get_contents(basename(__FILE__)));
	}

	// Avatar icon for edit and reply forms
	if (isset($_COOKIE['name']) and preg_match('/^@([a-zA-Z0-9_@]{1,29}$)/', $_COOKIE['name'])) {
		$form_avatar = '<img width="34" height="34" src="' . $root_dir . 'scripts/avatars.php?username=' . $_COOKIE['name'] . '&email=' . md5(strtolower(trim($_COOKIE['email']))) . '">';
	} else {
		$form_avatar = '<img width="34" height="34" src="' . ((isset($_COOKIE['email'])) ? 'http://gravatar.com/avatar/' . md5(strtolower(trim($_COOKIE['email']))) . '?d=http://' . $domain . $root_dir . 'images/avatar.png&s=34&r=pg">' : $root_dir . 'images/avatar.png">');
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
//	http://<?php echo $domain . $_SERVER['PHP_SELF'] . '?source' . PHP_EOL; ?>


var show_cmt = '';
var pagetitle = (document.title != '') ? ' on "'+ document.title +'"' : '';
var rows = (rows != undefined) ? rows : '<?php echo $rows; ?>';
var name_on = (name_on != undefined) ? name_on : 'yes';
var email_on = (email_on != undefined) ? email_on : 'yes';
var sites_on = (sites_on != undefined) ? sites_on : 'yes';
var passwd_on = (passwd_on != undefined) ? passwd_on : 'yes';
var head = document.getElementsByTagName('head')[0];

// Add comment stylesheet to page header
if (document.querySelector('link[href="/hashover/style-sheets/<?php echo $style_sheet;?>.css"]') == null) {
	link = document.createElement('link');
	link.rel = 'stylesheet';
	link.href = '<?php echo $root_dir; ?>style-sheets/<?php echo $style_sheet;?>.css';
	link.type = 'text/css';
	head.appendChild(link);
}

// Add comment RSS feed to page header
link = document.createElement('link');
link.rel = 'alternate';
link.href = '/hashover.php?rss=' + location.href.replace(/#.*$/g, '') + "&title=<?php echo (isset($_GET['pagetitle'])) ? $_GET['pagetitle'] . '"' : '" + document.title'; ?>;
link.type = 'application/rss+xml';
link.title = 'Comments';
head.appendChild(link);

// Put number of comments into "cmtcount" identified HTML element
if (document.getElementById('cmtcount') != null) {
	if (<?php echo $total_count - 1; ?> != 0) {
		document.getElementById('cmtcount').innerHTML = '<?php echo $total_count - 1; ?>';
	}
}

// Displays reply form
function hashover_reply(r, f) {
	var reply_form = '\n<b class="hashover-title"><?php echo $text['reply_to_cmt']; ?></b>\n';
	reply_form += '<span class="hashover-form-buttons">\n';

<?php
	if (isset($_COOKIE['name']) and !empty($_COOKIE['name'])) {
		echo "\t" . 'if (name_on == \'yes\' || email_on == \'yes\' || passwd_on == \'yes\' || sites_on == \'yes\') {' . PHP_EOL;
		echo "\t\t" . 'reply_form += \'<input type="button" value="\u25BC ' . $text['options'] . '" onclick="hashover_showoptions(\\\'\' + r + \'\\\', this); return false;">\n\';' . PHP_EOL;
		echo "\t" . '}' . PHP_EOL . PHP_EOL;
	}
?>
	reply_form += '<input type="button" value="<?php echo $text['cancel']; ?>" onclick="hashover_cancel(\'' + r + '\'); return false;">\n';
	reply_form += '</span>\n';
	reply_form += '<span id="hashover-message-' + r + '" class="hashover-message"></span>\n';
	reply_form += '<div id="hashover-options-' + r + '" class="hashover-options<?php if (!isset($_COOKIE['name']) or empty($_COOKIE['name'])) echo ' open'; ?>">\n';
	reply_form += '<div class="hashover-inputs">\n';

<?php
	if ($icons == 'yes') {
		echo "\t" . 'if (name_on == \'yes\') {' . PHP_EOL;
		echo "\t\t" . 'reply_form += \'<div class="hashover-avatar-image">' . $form_avatar . '</div>\n\';' . PHP_EOL;
		echo "\t" . '}' . PHP_EOL . PHP_EOL;
	}
?>
	if (name_on == 'yes') {
		reply_form += '<div class="hashover-name-input">\n<input type="text" name="name" title="<?php echo $text['nickname_tip']; ?>" value="<?php echo (isset($_COOKIE['name'])) ? $_COOKIE['name'] : $text['nickname']; ?>" maxlength="30" onFocus="this.value=(this.value == \'<?php echo $text['nickname']; ?>\') ? \'\' : this.value;" onBlur="this.value=(this.value == \'\') ? \'<?php echo $text['nickname']; ?>\' : this.value;">\n</div>\n';
	}

	if (passwd_on == 'yes') {
		reply_form += '<div class="hashover-password-input">\n<input type="<?php echo (isset($_COOKIE['password']) and !empty($_COOKIE['password'])) ? 'password" value="' . $_COOKIE['password'] : 'text" value="' . $text['password']; ?>" name="password" title="<?php echo $text['password_tip']; ?>" onFocus="this.value=(this.value == \'<?php echo $text['password']; ?>\') ? \'\' : this.value; this.type=\'password\';" onBlur="this.value=(this.value == \'\') ? \'<?php echo $text['password']; ?>\' : this.value; this.type=(this.value == \'<?php echo $text['password']; ?>\') ? \'text\' : \'password\';">\n</div>\n';
	}

<?php
	if ($is_mobile == 'yes') {
		echo "\t" . 'reply_form += \'</div>\n<div class="hashover-inputs">\n\';' . PHP_EOL . PHP_EOL;
	}
?>
	if (email_on == 'yes') {
		reply_form += '<div class="hashover-email-input">\n<input type="text" name="email" title="<?php echo $text['email']; ?>" value="<?php echo (isset($_COOKIE['email'])) ? $_COOKIE['email'] : $text['email']; ?>" onFocus="this.value=(this.value == \'<?php echo $text['email']; ?>\') ? \'\' : this.value;" onBlur="this.value=(this.value == \'\') ? \'<?php echo $text['email']; ?>\' : this.value;">\n</div>\n';
	}

	if (sites_on == 'yes') {
		reply_form += '<div class="hashover-website-input">\n<input type="text" name="website" title="<?php echo $text['website']; ?>" value="<?php echo (isset($_COOKIE['website'])) ? $_COOKIE['website'] : $text['website']; ?>" onFocus="this.value=(this.value == \'<?php echo $text['website']; ?>\') ? \'\' : this.value;" onBlur="this.value=(this.value == \'\') ? \'<?php echo $text['website']; ?>\' : this.value;">\n</div>\n';
	}

	reply_form += '</div>\n</div>\n';
	reply_form += '<textarea rows="6" cols="62" name="comment" onFocus="this.value=(this.value==\'<?php echo $text['reply_form']; ?>\') ? \'\' : this.value;" onBlur="this.value=(this.value==\'\') ? \'<?php echo $text['reply_form']; ?>\' : this.value;" title="<?php echo $text['cmt_tip']; ?>"><?php echo $text['reply_form']; ?></textarea>\n';
<?php
	if (isset($_GET['canon_url']) or isset($canon_url)) {
		echo "\t" . 'reply_form += \'<input type="hidden" name="canon_url" value="' . $page_url . '">\n\';' . PHP_EOL;
	}
?>
	reply_form += '<input type="hidden" name="cmtfile" value="' + f + '">\n<input type="hidden" name="reply_to" value="' + f + '">\n';
	reply_form += '<input class="hashover-submit" type="submit" value="<?php echo $text['post_reply']; ?>" onclick="return hashover_submit(\'' + r + '\', this);" onsubmit="return hashover_submit(\'' + r + '\', this);">\n';

	document.getElementById('hashover-footer-' + r).style.display = 'none';
	document.getElementById('hashover-forms-' + r).innerHTML = reply_form;
	return false;
}

// Displays edit form
function hashover_edit(e, f, s) {
	var cmtdata = document.getElementById('hashover-content-' + e).innerHTML.replace(/<br>/gi, '\n').replace(/<\/?a(\s+.*?>|>)/gi, '').replace(/<img.*?title="(.*?)".*?>/gi, '[img]$1[/img]').replace(/^\s+|\s+$/g, '').replace('<code style="white-space: pre;">', '<code>');
	var website = (document.getElementById('hashover-website-' + e) != undefined) ? document.getElementById('hashover-website-' + e).href : '<?php echo $text['website']; ?>';

	var edit_form = '\n<b class="hashover-title"><?php echo $text['edit_cmt']; ?></b>\n';
	edit_form += '<span class="hashover-form-buttons">\n';
	edit_form += '<input type="submit" name="edit" value="." style="display: none;">';
	edit_form += '<input type="submit" name="delete" class="hashover-delete" value="<?php echo $text['delete']; ?>" onclick="return hashover_deletion_warning();">\n';
	edit_form += '<label for="notify" title="<?php echo $text['subscribe_tip']; ?>">\n';
	edit_form += '<input type="checkbox"' + ((s != '0') ? ' checked="true"' : '') + ' id="notify" name="notify"> <?php echo $text['subscribe']; ?>\n';
	edit_form += '</label>\n';
	edit_form += '<input type="button" value="<?php echo $text['cancel']; ?>" onclick="hashover_cancel(\'' + e + '\'); return false;">\n';
	edit_form += '</span>\n';
	edit_form += '<div class="hashover-options open">\n';
	edit_form += '<div class="hashover-inputs">\n';
<?php
	if ($icons == 'yes') {
		echo "\t" . 'edit_form += \'<div class="hashover-avatar-image">' . $form_avatar . '</div>\n\';' . PHP_EOL;
	}
?>
	edit_form += '<div class="hashover-name-input"><input type="text" name="name" title="<?php echo $text['nickname_tip']; ?>" value="' + document.getElementById('hashover-name-' + e).innerHTML.replace(/<.*?>(.*?)<.*?>/gi, '$1') + '" maxlength="30" onFocus="this.value=(this.value == \'<?php echo $text['nickname']; ?>\') ? \'\' : this.value;" onBlur="this.value=(this.value == \'\') ? \'<?php echo $text['nickname']; ?>\' : this.value;"></div>\n';
	edit_form += '<div class="hashover-password-input"><input type="<?php echo (isset($_COOKIE['password']) and !empty($_COOKIE['password'])) ? 'password" value="' . $_COOKIE['password'] : 'text" value="' . $text['password']; ?>" name="password" title="<?php echo $text['password_tip']; ?>" onFocus="this.value=(this.value == \'<?php echo $text['password']; ?>\') ? \'\' : this.value; this.type=\'password\';" onBlur="this.value=(this.value == \'\') ? \'<?php echo $text['password']; ?>\' : this.value; this.type=(this.value == \'<?php echo $text['password']; ?>\') ? \'text\' : \'password\';"></div>\n';
<?php
	if ($is_mobile == 'yes') {
		echo "\t" . 'edit_form += \'</div>\n<div class="hashover-inputs">\n\';' . PHP_EOL;
	}
?>
	edit_form += '<div class="hashover-email-input"><input type="text" name="email" title="<?php echo $text['email']; ?>" value="<?php echo (isset($_COOKIE['email'])) ? $_COOKIE['email'] : $text['email']; ?>" onFocus="this.value=(this.value == \'<?php echo $text['email']; ?>\') ? \'\' : this.value;" onBlur="this.value=(this.value == \'\') ? \'<?php echo $text['email']; ?>\' : this.value;"></div>\n';
	edit_form += '<div class="hashover-website-input"><input type="text" name="website" title="<?php echo $text['website']; ?>" value="' + website + '" onFocus="this.value=(this.value == \'<?php echo $text['website']; ?>\') ? \'\' : this.value;" onBlur="this.value=(this.value == \'\') ? \'<?php echo $text['website']; ?>\' : this.value;"></div>\n';
	edit_form += '</div>\n</div>\n';
	edit_form += '<textarea rows="10" cols="62" name="comment" title="<?php echo $text['cmt_tip']; ?>">' + cmtdata + '</textarea>\n';
<?php
	if (isset($_GET['canon_url']) or isset($canon_url)) {
		echo "\t" . 'edit_form += \'<input type="hidden" name="canon_url" value="' . $page_url . '">\n\';' . PHP_EOL;
	}
?>
	edit_form += '<input type="hidden" name="cmtfile" value="' + f + '">\n';
	edit_form += '<input class="hashover-submit" type="submit" name="edit" value="<?php echo $text['save_edit']; ?>">\n';

	document.getElementById('hashover-forms-' + e).innerHTML = edit_form;
	document.getElementById('hashover-footer-' + e).style.display = 'none';
	return false
}

// Disable submit buttons on submissions
function hashover_submit(f, b) {
	if (hashover_validate(f) != false) {
		setTimeout(function() { b.disabled = true; }, 1000);
		setTimeout(function() { b.disabled = false; }, 20000);
	} else {
		return false;
	}
}

// Function to cancel reply and edit forms
function hashover_cancel(f) {
	document.getElementById('hashover-footer-' + f).style.display = '';
	document.getElementById('hashover-forms-' + f).innerHTML = '';
	return false;
}

// Function to like a comment
function hashover_like(c, f) {
	// Load "like.php"
	var like = new XMLHttpRequest();
	like.open('GET', '<?php echo $root_dir . 'scripts/like.php?like=' . $ref_path; ?>/' + f);
	like.send();

	// Get number of likes
	if (document.getElementById('hashover-likes-' + c).innerHTML != '') {
		var likes = parseInt(document.getElementById('hashover-likes-' + c).innerHTML.replace(/[^0-9]/g, ''));
	} else {
		var likes = parseInt(0);
	}

	// Change "Like" button title and class; Increase likes
	if (document.getElementById('hashover-like-' + c).className == 'hashover-like') {
		document.getElementById('hashover-like-' + c).className = 'hashover-liked';
		document.getElementById('hashover-like-' + c).title = '<?php echo addcslashes($text['liked_cmt'], "'"); ?>';
		document.getElementById('hashover-like-' + c).innerHTML = '<?php echo $text['liked']; ?>';
		likes++;
	} else {
		document.getElementById('hashover-like-' + c).className = 'hashover-like';
		document.getElementById('hashover-like-' + c).title = '<?php echo addcslashes($text['like_cmt'], "'"); ?>';
		document.getElementById('hashover-like-' + c).innerHTML = '<?php echo $text['like']; ?>';
		likes--;
	}

	// Change number of likes
	var like_count = (likes != 1) ? likes + ' Likes' : likes + ' Like';
	document.getElementById('hashover-likes-' + c).innerHTML = (likes > 0) ? '<b>' + like_count + '</b>' : '';
}

// Displays options
function hashover_showoptions(r, b) {
	if (name_on == 'yes' || email_on == 'yes' || passwd_on == 'yes' || sites_on == 'yes') {
		if (document.getElementById('hashover-options-' + r).className != 'hashover-options open') {
			document.getElementById('hashover-options-' + r).className = 'hashover-options open';
			b.value = '\u25B2 <?php echo $text['options']; ?>';
		} else {
			document.getElementById('hashover-options-' + r).className = 'hashover-options';
			b.value = '\u25BC <?php echo $text['options']; ?>';
		}
	}

	return false;
}

// Displays a "blank email address" warning
function hashover_validate(f) {
	if (f == true) {
		if (email_on == 'yes') {
			if (document.hashover_form.email.value == '' || document.hashover_form.email.value == '<?php echo $text['email']; ?>') {
				var answer = confirm('<?php echo $text['no_email_warn']; ?>');

				if (answer == false) {
					document.hashover_form.email.focus();
					return false;
				}
			} else {
				if (!document.hashover_form.email.value.match(/\S+@\S+/)) {
					document.getElementById('hashover-message').innerHTML = '<?php echo $text['invalid_email']; ?>';
					document.getElementById('hashover-message').style.display = null;
					document.hashover_form.email.focus();

					setTimeout(function() {
						document.getElementById('hashover-message').style.display = 'none';
					}, 10000);

					return false;
				}
			}
		}

		if (document.hashover_form.comment.value == '' || document.hashover_form.comment.value == '<?php echo $text['comment_form']; ?>') {
			document.getElementById('hashover-message').innerHTML = '<?php echo $text['cmt_needed']; ?>';
			document.getElementById('hashover-message').style.display = null;
			document.hashover_form.comment.focus();

			setTimeout(function() {
				document.getElementById('hashover-message').style.display = 'none';
			}, 10000);

			return false;
		}
	} else {
		if (email_on == 'yes') {
			if (document.getElementById('hashover-reply-form-' + f).email.value == '' || document.getElementById('hashover-reply-form-' + f).email.value == '<?php echo $text['email']; ?>') {
				var answer = confirm('<?php echo $text['no_email_warn']; ?>');

				if (answer == false) {
					document.getElementById('hashover-options-' + f).style.display = '';
					document.getElementById('hashover-reply-form-' + f).email.focus();
					return false;
				}
			} else {
				if (!document.getElementById('hashover-reply-form-' + f).email.value.match(/\S+@\S+/)) {
					document.getElementById('hashover-message-' + f).innerHTML = '<?php echo $text['invalid_email']; ?>';
					document.getElementById('hashover-message-' + f).className = 'hashover-message open';
					document.getElementById('hashover-reply-form-' + f).email.focus();

					setTimeout(function() {
						document.getElementById('hashover-message-' + f).className = 'hashover-message';
					}, 10000);

					return false;
				}
			}
		}

		if (document.getElementById('hashover-reply-form-' + f).comment.value == '' || document.getElementById('hashover-reply-form-' + f).comment.value == '<?php echo $text['reply_form']; ?>') {
			document.getElementById('hashover-message-' + f).innerHTML = '<?php echo $text['reply_needed']; ?>';
			document.getElementById('hashover-message-' + f).className = 'hashover-message open';
			document.getElementById('hashover-reply-form-' + f).comment.focus();

			setTimeout(function() {
				document.getElementById('hashover-message-' + f).className = 'hashover-message';
			}, 10000);

			return false;
		}
	}
}

// Displays confirmation dialog for deletion
function hashover_deletion_warning() {
	var answer = confirm('<?php echo $text['delete_cmt']; ?>');

	if (answer == false) {
		return false;
	}
}

// Add comment content to HTML template
function parse_template(object, sort, method) {
	var indent = (sort == false || method == 'ascending') ? object['indent'] : '16px 0px 12px 0px';

	if (!object['deletion_notice']) {
		var 
			permalink = object['permalink'],
			cmtclass = (sort == false || method == 'ascending') ? object['cmtclass'] : 'hashover-comment',
			avatar = object['avatar'],
			name = object['name'],
			thread = (object['thread']) ? object['thread'] : '',
			date = object['date'],
			likes = (object['likes']) ? object['likes'] : '',
			like_link = (object['like_link']) ? object['like_link'] : '',
			edit_link = (object['edit_link']) ? object['edit_link'] : '',
			reply_link = object['reply_link'],
			comment = object['comment'],
			form = '',
			hashover_footer_style = ''
		;

<?php
		// Load HTML template
		$load_html_template = explode(PHP_EOL, file_get_contents('html-templates/' . $html_template . '.html'));

		for ($line = 0; $line != count($load_html_template) - 1; $line++) {
			echo "\t\t" . 'show_cmt += \'' . $load_html_template[$line] . '\n\';' . PHP_EOL;
		}
?>
	} else {
		show_cmt += '<a name="' + object['permalink'] + '"></a>\n';
		show_cmt += '<div style="margin: ' + indent + '; clear: both;" class="' + object['cmtclass'] + '">\n';
		show_cmt += object['deletion_notice'] + '\n';
		show_cmt += '</div>\n';
	}
}

// Five method sort
function sort_comments(method) {
	var methods = {
		ascending: function() {
			for (var comment in comments) {
				parse_template(comments[comment], true, method);
			}
		},

		descending: function() {
			for (var comment = (comments.length - 1); comment >= 0; comment--) {
				parse_template(comments[comment], true, method);
			}
		},

		byname: function() {
			var tmpSortArray = comments.slice(0).sort(function(a, b) {
				if(a.sort_name < b.sort_name) return -1;
				if(a.sort_name > b.sort_name) return 1;
			})

			for (var comment in tmpSortArray) {
				parse_template(tmpSortArray[comment], true, method);
			}
		},

		bydate: function() {
			var tmpSortArray = comments.slice(0).sort(function(a, b) {
				return b.sort_date - a.sort_date;
			})

			for (var comment in tmpSortArray) {
				parse_template(tmpSortArray[comment], true, method);
			}
		},

		bylikes: function() {
			var tmpSortArray = comments.slice(0).sort(function(a, b) {
				return b.sort_likes - a.sort_likes;
			})

			for (var comment in tmpSortArray) {
				parse_template(tmpSortArray[comment], true, method);
			}
		}
	}

	show_cmt = '';
	document.getElementById('sort_div').innerHTML = 'Loading...' + '\n';
	methods[method]();
	document.getElementById('sort_div').innerHTML = show_cmt + '\n';
}
<?php

	if ($page_title == 'yes') {
		$js_title = "'+ pagetitle +'";
		$js_title = (isset($_GET['pagetitle'])) ? ' on "' . $_GET['pagetitle'] . '"' : $js_title;
	}

	echo '// Place "hashover" DIV' . PHP_EOL;
	echo 'if (document.getElementById("hashover") == null) {' . PHP_EOL;
	echo "\t" . 'document.write("<div id=\"hashover\"></div>\n");' . PHP_EOL;
	echo '}' . PHP_EOL . PHP_EOL;

	echo jsAddSlashes('<a name="comments"></a><b class="hashover-post-comment">' . $text['post_cmt'] . $js_title . ':</b>\n');

	if (isset($_COOKIE['message']) and !empty($_COOKIE['message'])) {
		echo jsAddSlashes('<b id="hashover-message" class="hashover-title">' . $_COOKIE['message'] . '</b>\n');
	} else {
		echo jsAddSlashes('<b id="hashover-message" class="hashover-title" style="display: none;"></b>\n');
	}

	echo jsAddSlashes('<form id="hashover_form" name="hashover_form" action="/hashover.php" method="post">\n');
	echo jsAddSlashes('<span class="hashover-avatar">');

	if ($icons == 'yes') {
		if (isset($_COOKIE['name']) and preg_match('/^@([a-zA-Z0-9_@]{1,29}$)/', $_COOKIE['name'])) {
			echo "\t" . jsAddSlashes('<img width="' . $icon_size . '" height="' . $icon_size . '" src="' . $script = $root_dir . 'scripts/avatars.php?username=' . $_COOKIE['name'] . '&email=' . md5(strtolower(trim($_COOKIE['email']))) . '">');
		} else {
			echo "\t" . jsAddSlashes('<img width="' . $icon_size . '" height="' . $icon_size . '" src="' . $script = (isset($_COOKIE['email'])) ? 'http://gravatar.com/avatar/' . md5(strtolower(trim($_COOKIE['email']))) . '?d=http://' . $domain . $root_dir . 'images/avatar.png&s=' . $icon_size . '&r=pg">\n' : $root_dir . 'images/avatar.png">');
		}
	} else {
		echo "\t" . jsAddSlashes('<span title="Permalink">#' . $cmt_count . '</span>');
	}

	echo jsAddSlashes('</span>\n');
	echo jsAddSlashes('<div class="hashover-balloon">\n');
	echo jsAddSlashes('<div class="hashover-inputs">\n');

	// Display name input tag if told to
	echo 'if (name_on == \'yes\') {' . PHP_EOL;
	echo "\t" . jsAddSlashes('<div class="hashover-name-input">\n');
	echo "\t" . jsAddSlashes('<input type="text" name="name" title="' . $text['nickname_tip'] . '" maxlength="30" onFocus="this.value=(this.value == \'' . $text['nickname'] . '\') ? \'\' : this.value;" onBlur="this.value=(this.value == \'\') ? \'' . $text['nickname'] . '\' : this.value;" value="' . $script = (isset($_COOKIE['name'])) ? $_COOKIE['name'] . '">\n' : $text['nickname'] . '">\n');
	echo "\t" . jsAddSlashes('</div>\n');
	echo '}' . PHP_EOL . PHP_EOL;

	// Display password input tag if told to
	echo 'if (passwd_on == \'yes\') {' . PHP_EOL;
	echo "\t" . jsAddSlashes('<div class="hashover-password-input">\n');
	echo "\t" . jsAddSlashes('<input name="password" title="' . $text['password_tip'] . '" onFocus="this.value=(this.value == \'' . $text['password'] . '\') ? \'\' : this.value; this.type=\'password\';" onBlur="this.value=(this.value == \'\') ? \'' . $text['password'] . '\' : this.value; this.type=(this.value == \'' . $text['password'] . '\') ? \'text\' : \'password\';" type="' . $script = (isset($_COOKIE['password']) and !empty($_COOKIE['password'])) ? 'password">\n' : 'text" value="' . $text['password'] . '">\n');
	echo "\t" . jsAddSlashes('</div>\n');
	echo '}' . PHP_EOL . PHP_EOL;

	// Add second table row on mobile devices
	if ($is_mobile == 'yes') {
		echo 'if (name_on == \'yes\' && passwd_on == \'yes\') {' . PHP_EOL;
		echo "\t" . jsAddSlashes('<div class="hashover-login-input">\n');
		echo "\t" . jsAddSlashes('<input name="login" title="Login (optional)" type="submit" value="">\n');
		echo "\t" . jsAddSlashes('</div>\n');
		echo '}' . PHP_EOL . PHP_EOL;
		echo jsAddSlashes('</div>\n<div class="hashover-inputs">\n');
	}

	// Display email input tag if told to
	echo 'if (email_on == \'yes\') {' . PHP_EOL;
	echo "\t" . jsAddSlashes('<div class="hashover-email-input">\n');
	echo "\t" . jsAddSlashes('<input type="text" name="email" title="' . $text['email'] . '" onFocus="this.value=(this.value == \'' . $text['email'] . '\') ? \'\' : this.value;" onBlur="this.value=(this.value == \'\') ? \'' . $text['email'] . '\' : this.value;" value="' . $script = (isset($_COOKIE['email'])) ? $_COOKIE['email'] . '">\n' : $text['email'] . '">\n');
	echo "\t" . jsAddSlashes('</div>\n');
	echo '}' . PHP_EOL . PHP_EOL;

	// Display website input tag if told to
	echo 'if (sites_on == \'yes\') {' . PHP_EOL;
	echo "\t" . jsAddSlashes('<div class="hashover-website-input">\n');
	echo "\t" . jsAddSlashes('<input type="text" name="website" title="' . $text['website'] . '" onFocus="this.value=(this.value == \'' . $text['website'] . '\') ? \'\' : this.value;" onBlur="this.value=(this.value == \'\') ? \'' . $text['website'] . '\' : this.value;" value="' . $script = (isset($_COOKIE['website'])) ? $_COOKIE['website'] . '">\n' : $text['website'] . '">\n');
	echo "\t" . jsAddSlashes('</div>\n');
	echo '}' . PHP_EOL . PHP_EOL;

	if ($is_mobile != 'yes') {
		echo 'if (name_on == \'yes\' && passwd_on == \'yes\') {' . PHP_EOL;
		echo "\t" . jsAddSlashes('<div class="hashover-login-input">\n');
		echo "\t" . jsAddSlashes('<input name="login" title="Login (optional)" type="submit" value="">\n');
		echo "\t" . jsAddSlashes('</div>\n');
		echo '}' . PHP_EOL . PHP_EOL;
	}

	echo jsAddSlashes('</div>\n') . PHP_EOL;
	echo jsAddSlashes('<div id="requiredFields" style="display: none;">\n');
	echo jsAddSlashes('<input type="text" name="summary" value="" placeholder="Summary">\n');
	echo jsAddSlashes('<input type="hidden" name="middlename" value="" placeholder="Middle Name">\n');
	echo jsAddSlashes('<input type="text" name="lastname" value="" placeholder="Last Name">\n');
	echo jsAddSlashes('<input type="text" name="address" value="" placeholder="Address">\n');
	echo jsAddSlashes('<input type="hidden" name="zip" value="" placeholder="Last Name">\n');
	echo jsAddSlashes('</div>\n') . PHP_EOL;

	$rows = "'+ rows +'";
	$replyborder = (isset($_COOKIE['success']) and $_COOKIE['success'] == "no") ? ' style="border: 2px solid #FF0000 !important; -moz-border-radius: 5px 5px 0px 0px; border-radius: 5px 5px 0px 0px;"' : '';

	echo jsAddSlashes('<textarea rows="' . $rows . '" cols="63" name="comment" onFocus="this.value=(this.value==\'' . $text['comment_form'] . '\') ? \'\' : this.value;" onBlur="this.value=(this.value==\'\') ? \'' . $text['comment_form'] . '\' : this.value;"' . $replyborder . ' title="' . $text['cmt_tip'] . '">' . $text['comment_form'] . '</textarea>\n');
	echo (isset($_GET['canon_url']) or isset($canon_url)) ? jsAddSlashes('<input type="hidden" name="canon_url" value="' . $page_url . '">\n') : '';
	echo (isset($_COOKIE['replied'])) ? jsAddSlashes('<input type="hidden" name="reply_to" value="' . $_COOKIE['replied'] . '">\n') : '';
	echo jsAddSlashes('<input class="hashover-submit" type="submit" value="' . $text['post_button'] . '" onclick="return hashover_submit(true, this);" onsubmit="return hashover_submit(true, this);">\n');
	echo jsAddSlashes('</div>\n</form>\n'). PHP_EOL;

	// Display three most popular comments
	if (!empty($top_likes)) {
		echo jsAddSlashes('<b class="hashover-title">' . $text['popular_cmts'] . ' Comment' . ((count($top_likes) != '1') ? 's' : '') . ':</b>\n') . PHP_EOL;
		echo 'var popComments = [' . PHP_EOL;

		for ($p = 1; $p <= count($top_likes) and $p <= $top_cmts; $p++) {
			if (!empty($top_likes)) {
				echo parse_comments(array_shift($top_likes), '', 'no');
			}
		}

		echo '];' . PHP_EOL . PHP_EOL;
		echo 'for (var comment in popComments) {' . PHP_EOL;
		echo "\t" . 'parse_template(popComments[comment], false);' . PHP_EOL;
		echo '}' . PHP_EOL . PHP_EOL;
	}

	if (!empty($show_cmt)) {
		echo 'var comments = [' . PHP_EOL;
		echo $show_cmt;
		echo '];' . PHP_EOL . PHP_EOL;
	}

	// Display comment count
	echo jsAddSlashes('<b class="hashover-count">' . $text['showing_cmts'] . ' ' . $script = ($cmt_count == "1") ? '0 Comments:</b>\n' : display_count() . ':</b>\n') . PHP_EOL;

	// Display comments, if there are no comments display a note
	if (!empty($show_cmt)) {
		echo jsAddSlashes('<span class="hashover-sort">\n' . $text['sort'] . ': <select name="sort" size="1" onChange="sort_comments(this.value); return false;">\n');
		echo jsAddSlashes('<option value="ascending">' . $text['sort_ascend'] . '</option>\n');
		echo jsAddSlashes('<option value="descending">' . $text['sort_descend'] . '</option>\n');
		echo jsAddSlashes('<option value="byname">' . $text['sort_byname'] . '</option>\n');
		echo jsAddSlashes('<option value="bydate">' . $text['sort_bydate'] . '</option>\n');
		echo jsAddSlashes('<option value="bylikes">' . $text['sort_bylikes'] . '</option>\n');
		echo jsAddSlashes('</select>\n</span>\n') . PHP_EOL;

		echo jsAddSlashes('<div id="sort_div">\n'). PHP_EOL;
		echo 'for (var comment in comments) {' . PHP_EOL;
		echo "\t" . 'parse_template(comments[comment], false);' . PHP_EOL;
		echo '}' . PHP_EOL . PHP_EOL;
		echo jsAddSlashes('</div>\n') . PHP_EOL;
	} else {
		echo jsAddSlashes('<div style="margin: 16px 0px 12px 0px;" class="hashover-comment">\n');
		echo jsAddSlashes('<span class="hashover-avatar"><img width="' . $icon_size . '" height="' . $icon_size . '" src="/hashover/images/first-comment.png"></span>\n');
		echo jsAddSlashes('<div style="height: ' . $icon_size . 'px;" class="hashover-balloon">\n');
		echo jsAddSlashes('<b class="hashover-first hashover-title" style="color: #000000;">Be the first to comment!</b>\n</div>');
	}

	echo jsAddSlashes('</div>\n') . PHP_EOL;
	echo jsAddSlashes('<div id="hashover-end-links">\n');
	echo jsAddSlashes('HashOver Comments &middot;\n');
	if (!empty($show_cmt)) echo jsAddSlashes('<a href="/hashover.php?rss=' . $page_url . '" target="_blank">RSS Feed</a> &middot;\n');
	echo jsAddSlashes('<a href="/hashover.php?source" rel="hashover-source" target="_blank">Source Code</a> &middot;\n');
	echo jsAddSlashes('<a href="/hashover.php" rel="hashover-javascript" target="_blank">JavaScript</a> &middot;\n');
	echo jsAddSlashes('<a href="http://tildehash.com/hashover/changelog.txt" target="_blank">ChangeLog</a> &middot;\n');
	echo jsAddSlashes('<a href="http://tildehash.com/hashover/archives/" target="_blank">Archives</a>\n');
	echo jsAddSlashes('</div>\n');

	// Script execution ending time
	$exec_time = explode(' ', microtime());
	$exec_end = $exec_time[1] + $exec_time[0];
	$exec_time = ($exec_end - $exec_start);

	echo PHP_EOL . '// Place all content on page' . PHP_EOL;
	echo 'document.getElementById("hashover").innerHTML = show_cmt;' . PHP_EOL . PHP_EOL;
	echo '/*' . PHP_EOL;
	echo "\t" . 'Statistics:' . PHP_EOL . PHP_EOL;
	echo "\t\t" . 'Execution Time' . "\t\t" . ': ' . round($exec_time, 5) . ' Seconds' . PHP_EOL;
	echo "\t\t" . 'Script Memory Peak' . "\t" . ': ' . round(memory_get_peak_usage() / 1048576, 2) . 'Mb' . PHP_EOL;
	echo "\t\t" . 'System Memory Peak' . "\t" . ': ' . round(memory_get_peak_usage(true) / 1048576, 2) . 'Mb' . PHP_EOL;
	echo '*/';

?>
