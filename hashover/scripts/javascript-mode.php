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
		$form_avatar = '<img width="32" height="32" src="' . $this->setting['root_dir'] . 'scripts/avatars.php?format=' . $this->setting['image_format'] . '&size=' . $this->setting['icon_size'] . '&username=' . $_COOKIE['name'] . '&email=' . md5(strtolower(trim($_COOKIE['email']))) . '">';
	} else {
		$form_avatar = '<img width="32" height="32" src="' . $this->setting['root_dir'] . 'scripts/avatars.php?format=' . $this->setting['image_format'] . '&size=' . $this->setting['icon_size'] . ((isset($_COOKIE['email'])) ? '&email=' . md5(strtolower(trim($_COOKIE['email']))) : '') . '">';
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
//	http://<?php echo $this->setting['domain'] . $_SERVER['PHP_SELF'] . '?source' . PHP_EOL; ?>


var hashover = '';
var pagetitle = (document.title != '') ? ' on "'+ document.title +'"' : '';
var rows = (rows != undefined) ? rows : '<?php echo $this->setting['rows']; ?>';
var nickname_on = (nickname_on != undefined) ? nickname_on : true;
var email_on = (email_on != undefined) ? email_on : true;
var website_on = (website_on != undefined) ? website_on : true;
var password_on = (password_on != undefined) ? password_on : true;
var head = document.getElementsByTagName('head')[0];

// Add comment stylesheet to page header
if (document.querySelector('link[href="/hashover/style-sheets/<?php echo $this->setting['style_sheet'];?>.css"]') == null) {
	link = document.createElement('link');
	link.rel = 'stylesheet';
	link.href = '<?php echo $this->setting['root_dir']; ?>style-sheets/<?php echo $this->setting['style_sheet'];?>.css';
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
	if (<?php echo $this->total_count - 1; ?> != 0) {
		document.getElementById('cmtcount').innerHTML = '<?php echo $this->total_count - 1; ?>';
	}
}

// Displays reply form
function hashover_reply(r, f) {
	var reply_form = '\n<b class="hashover-title"><?php echo $this->text['reply_to_cmt']; ?></b>\n';
	reply_form += '<span class="hashover-form-buttons">\n';

<?php
	if (!empty($_COOKIE['name'])) {
		echo "\t" . 'if (nickname_on || email_on || password_on || website_on) {' . PHP_EOL;
		echo "\t\t" . 'reply_form += \'<input type="button" value="\u25BC ' . $this->text['options'] . '" onclick="hashover_showoptions(\\\'\' + r + \'\\\', this); return false;">\n\';' . PHP_EOL;
		echo "\t" . '}' . PHP_EOL . PHP_EOL;
	}
?>
	reply_form += '<input type="button" value="<?php echo $this->text['cancel']; ?>" onclick="hashover_cancel(\'' + r + '\'); return false;">\n';
	reply_form += '</span>\n';
	reply_form += '<div id="hashover-options-' + r + '" class="hashover-options<?php if (empty($_COOKIE['name'])) echo ' open'; ?>">\n';
	reply_form += '<div class="hashover-inputs">\n';

<?php
	if ($this->setting['icons'] == 'yes') {
		echo "\t" . 'if (nickname_on) {' . PHP_EOL;
		echo "\t\t" . 'reply_form += \'<div class="hashover-avatar-image">' . $form_avatar . '</div>\n\';' . PHP_EOL;
		echo "\t" . '}' . PHP_EOL . PHP_EOL;
	}
?>
	if (nickname_on) {
		reply_form += '<div class="hashover-name-input">\n<input type="text" name="name" title="<?php echo $this->text['nickname_tip']; ?>" value="<?php if (!empty($_COOKIE['name'])) echo $_COOKIE['name']; ?>" maxlength="30" placeholder="<?php echo $this->text['nickname']; ?>">\n</div>\n';
	}

	if (password_on) {
		reply_form += '<div class="hashover-password-input">\n<input type="password" name="password" title="<?php echo $this->text['password_tip']; ?>" value="<?php if (!empty($_COOKIE['password'])) echo $_COOKIE['password']; ?>" placeholder="<?php echo $this->text['password']; ?>">\n</div>\n';
	}

<?php
	if ($this->is_mobile) {
		echo "\t" . 'reply_form += \'</div>\n<div class="hashover-inputs">\n\';' . PHP_EOL . PHP_EOL;
	}
?>
	if (email_on) {
		reply_form += '<div class="hashover-email-input">\n<input type="text" name="email" title="<?php echo $this->text['email']; ?>" value="<?php if (!empty($_COOKIE['email'])) echo $_COOKIE['email']; ?>" placeholder="<?php echo $this->text['email']; ?>">\n</div>\n';
	}

	if (website_on) {
		reply_form += '<div class="hashover-website-input">\n<input type="text" name="website" title="<?php echo $this->text['website']; ?>" value="<?php if (!empty($_COOKIE['website'])) echo $_COOKIE['website']; ?>" placeholder="<?php echo $this->text['website']; ?>">\n</div>\n';
	}

	reply_form += '</div>\n</div>\n';
	reply_form += '<div id="hashover-message-' + r + '" class="hashover-message"></div>\n';
	reply_form += '<textarea rows="6" cols="62" name="comment" title="<?php echo $this->text['cmt_tip']; ?>" placeholder="<?php echo $this->text['reply_form']; ?>"></textarea>\n';
<?php
	if (isset($_GET['canon_url']) or isset($canon_url)) {
		echo "\t" . 'reply_form += \'<input type="hidden" name="canon_url" value="' . $this->page_url . '">\n\';' . PHP_EOL;
	}
?>
	reply_form += '<input type="hidden" name="reply_to" value="' + f + '">\n';
	reply_form += '<input class="hashover-submit" type="submit" value="<?php echo $this->text['post_reply']; ?>" onclick="return hashover_submit(\'' + r + '\', this);" onsubmit="return hashover_submit(\'' + r + '\', this);">\n';

	document.getElementById('hashover-footer-' + r).style.display = 'none';
	document.getElementById('hashover-forms-' + r).innerHTML = reply_form;
	return false;
}

// Displays edit form
function hashover_edit(e, f, s) {
	var cmtdata = document.getElementById('hashover-content-' + e).innerHTML.replace(/<br>/gi, '\n').replace(/<\/?a(\s+.*?>|>)/gi, '').replace(/<img.*?title="(.*?)".*?>/gi, '[img]$1[/img]').replace(/^\s+|\s+$/g, '').replace('<code style="white-space: pre;">', '<code>');
	var website = (document.getElementById('hashover-website-' + e) != undefined) ? document.getElementById('hashover-website-' + e).href : '<?php echo $this->text['website']; ?>';

	var edit_form = '\n<b class="hashover-title"><?php echo $this->text['edit_cmt']; ?></b>\n';
	edit_form += '<span class="hashover-form-buttons">\n';
	edit_form += '<input type="submit" name="edit" value="." style="display: none;">';
	edit_form += '<input type="submit" name="delete" class="hashover-delete" value="<?php echo $this->text['delete']; ?>" onclick="return hashover_deletion_warning();">\n';
	edit_form += '<label for="notify" title="<?php echo $this->text['subscribe_tip']; ?>">\n';
	edit_form += '<input type="checkbox"' + ((s != '0') ? ' checked="true"' : '') + ' id="notify" name="notify"> <?php echo $this->text['subscribe']; ?>\n';
	edit_form += '</label>\n';
	edit_form += '<input type="button" value="<?php echo $this->text['cancel']; ?>" onclick="hashover_cancel(\'' + e + '\'); return false;">\n';
	edit_form += '</span>\n';
	edit_form += '<div class="hashover-options open">\n';
	edit_form += '<div class="hashover-inputs">\n';
<?php
	if ($this->setting['icons'] == 'yes') {
		echo "\t" . 'edit_form += \'<div class="hashover-avatar-image">' . $form_avatar . '</div>\n\';' . PHP_EOL;
	}
?>
	edit_form += '<div class="hashover-name-input"><input type="text" name="name" title="<?php echo $this->text['nickname_tip']; ?>" value="' + document.getElementById('hashover-name-' + e).innerHTML.replace(/<.*?>(.*?)<.*?>/gi, '$1') + '" maxlength="30" placeholder="<?php echo $this->text['nickname']; ?>"></div>\n';
	edit_form += '<div class="hashover-password-input"><input type="password" name="password" title="<?php echo $this->text['password_tip']; ?>" value="<?php if (!empty($_COOKIE['password'])) echo $_COOKIE['password']; ?>" placeholder="<?php echo $this->text['password']; ?>"></div>\n';
<?php
	if ($this->is_mobile) {
		echo "\t" . 'edit_form += \'</div>\n<div class="hashover-inputs">\n\';' . PHP_EOL;
	}
?>
	edit_form += '<div class="hashover-email-input"><input type="text" name="email" title="<?php echo $this->text['email']; ?>" value="<?php if (!empty($_COOKIE['email'])) echo $_COOKIE['email']; ?>" placeholder="<?php echo $this->text['email']; ?>"></div>\n';
	edit_form += '<div class="hashover-website-input"><input type="text" name="website" title="<?php echo $this->text['website']; ?>" value="' + website + '" placeholder="<?php echo $this->text['website']; ?>"></div>\n';
	edit_form += '</div>\n</div>\n';
	edit_form += '<textarea rows="10" cols="62" name="comment" title="<?php echo $this->text['cmt_tip']; ?>">' + cmtdata + '</textarea>\n';
<?php
	if (isset($_GET['canon_url']) or isset($canon_url)) {
		echo "\t" . 'edit_form += \'<input type="hidden" name="canon_url" value="' . $this->page_url . '">\n\';' . PHP_EOL;
	}
?>
	edit_form += '<input type="hidden" name="cmtfile" value="' + f + '">\n';
	edit_form += '<input class="hashover-submit" type="submit" name="edit" value="<?php echo $this->text['save_edit']; ?>">\n';

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
	like.open('GET', '<?php echo $this->setting['root_dir'] . 'scripts/like.php?like=' . $this->ref_path; ?>/' + f);
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
		document.getElementById('hashover-like-' + c).title = '<?php echo addcslashes($this->text['liked_cmt'], "'"); ?>';
		document.getElementById('hashover-like-' + c).innerHTML = '<?php echo $this->text['liked']; ?>';
		likes++;
	} else {
		document.getElementById('hashover-like-' + c).className = 'hashover-like';
		document.getElementById('hashover-like-' + c).title = '<?php echo addcslashes($this->text['like_cmt'], "'"); ?>';
		document.getElementById('hashover-like-' + c).innerHTML = '<?php echo $this->text['like']; ?>';
		likes--;
	}

	// Change number of likes
	var like_count = (likes != 1) ? likes + ' Likes' : likes + ' Like';
	document.getElementById('hashover-likes-' + c).innerHTML = (likes > 0) ? '<b>' + like_count + '</b>' : '';
}

// Displays options
function hashover_showoptions(r, b) {
	if (nickname_on || email_on || password_on || website_on) {
		if (document.getElementById('hashover-options-' + r).className != 'hashover-options open') {
			document.getElementById('hashover-options-' + r).className = 'hashover-options open';
			b.value = '\u25B2 <?php echo $this->text['options']; ?>';
		} else {
			document.getElementById('hashover-options-' + r).className = 'hashover-options';
			b.value = '\u25BC <?php echo $this->text['options']; ?>';
		}
	}

	return false;
}

// Displays a "blank email address" warning
function hashover_validate(f) {
	if (f == true) {
		if (email_on) {
			if (document.hashover_form.email.value == '' || document.hashover_form.email.value == '<?php echo $this->text['email']; ?>') {
				var answer = confirm('<?php echo $this->text['no_email_warn']; ?>');

				if (answer == false) {
					document.hashover_form.email.focus();
					return false;
				}
			} else {
				if (!document.hashover_form.email.value.match(/\S+@\S+/)) {
					document.getElementById('hashover-message').innerHTML = '<?php echo $this->text['invalid_email']; ?>';
					document.getElementById('hashover-message').style.display = null;
					document.hashover_form.email.focus();

					setTimeout(function() {
						document.getElementById('hashover-message').style.display = 'none';
					}, 10000);

					return false;
				}
			}
		}

		if (document.hashover_form.comment.value == '' || document.hashover_form.comment.value == '<?php echo $this->text['comment_form']; ?>') {
			document.getElementById('hashover-message').innerHTML = '<?php echo $this->text['cmt_needed']; ?>';
			document.getElementById('hashover-message').style.display = null;
			document.hashover_form.comment.focus();

			setTimeout(function() {
				document.getElementById('hashover-message').style.display = 'none';
			}, 10000);

			return false;
		}
	} else {
		if (email_on) {
			if (document.getElementById('hashover-reply-form-' + f).email.value == '' || document.getElementById('hashover-reply-form-' + f).email.value == '<?php echo $this->text['email']; ?>') {
				var answer = confirm('<?php echo $this->text['no_email_warn']; ?>');

				if (answer == false) {
					document.getElementById('hashover-options-' + f).style.display = '';
					document.getElementById('hashover-reply-form-' + f).email.focus();
					return false;
				}
			} else {
				if (!document.getElementById('hashover-reply-form-' + f).email.value.match(/\S+@\S+/)) {
					document.getElementById('hashover-message-' + f).innerHTML = '<?php echo $this->text['invalid_email']; ?>';
					document.getElementById('hashover-message-' + f).className = 'hashover-message open';
					document.getElementById('hashover-reply-form-' + f).email.focus();

					setTimeout(function() {
						document.getElementById('hashover-message-' + f).className = 'hashover-message';
					}, 10000);

					return false;
				}
			}
		}

		if (document.getElementById('hashover-reply-form-' + f).comment.value == '' || document.getElementById('hashover-reply-form-' + f).comment.value == '<?php echo $this->text['reply_form']; ?>') {
			document.getElementById('hashover-message-' + f).innerHTML = '<?php echo $this->text['reply_needed']; ?>';
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
	var answer = confirm('<?php echo $this->text['delete_cmt']; ?>');

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
			cmtclass = (object['cmtclass'] && (sort == false || method == 'ascending')) ? ' ' + object['cmtclass'] : '',
			avatar = object['avatar'],
			name = object['name'],
			thread = (object['thread']) ? object['thread'] : '',
			date = object['date'],
			likes = (object['likes']) ? object['likes'] : '',
			like_link = (object['like_link']) ? object['like_link'] : '',
			edit_link = (object['edit_link']) ? object['edit_link'] : '',
			reply_link = object['reply_link'],
			comment = object['comment'],
			action = '/hashover.php',
			form = '',
			hashover_footer_style = ''
		;

<?php
		// Load HTML template
		$load_html_template = explode(PHP_EOL, file_get_contents('.' . $this->setting['root_dir'] . 'html-templates/' . $this->setting['html_template'] . '.html'));

		for ($line = 0; $line < count($load_html_template); $line++) {
			echo "\t\t" . 'hashover += \'' . $load_html_template[$line] . '\n\';' . PHP_EOL;
		}
?>
	} else {
		hashover += '<a name="' + object['permalink'] + '"></a>\n';
		hashover += '<div style="margin: ' + indent + ';" class="' + object['cmtclass'] + '">\n';
		hashover += object['deletion_notice'] + '\n';
		hashover += '</div>\n';
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

	hashover = '';
	document.getElementById('sort_div').innerHTML = 'Loading...' + '\n';
	methods[method]();
	document.getElementById('sort_div').innerHTML = hashover + '\n';
}

<?php

	if ($this->setting['page_title'] == 'yes') {
		$js_title = "'+ pagetitle +'";
		$js_title = (isset($_GET['pagetitle'])) ? ' on "' . $_GET['pagetitle'] . '"' : $js_title;
	} else {
		$js_title = '';
	}

	echo '// Place "hashover" DIV' . PHP_EOL;
	echo 'if (document.getElementById("hashover") == null) {' . PHP_EOL;
	echo "\t" . 'document.write("<div id=\"hashover\" class=\"' . $this->setting['image_format'] . '\"></div>\n");' . PHP_EOL;
	echo '} else {' . PHP_EOL;
	echo "\t" . 'document.getElementById("hashover").className = \'' . $this->setting['image_format'] . '\';' . PHP_EOL;
	echo '}' . PHP_EOL . PHP_EOL;

	echo $this->escape_output('<a name="comments"></a><b class="hashover-post-comment">' . $this->text['post_cmt'] . $js_title . ':</b>\n');

	if (!empty($_COOKIE['message'])) {
		echo $this->escape_output('<b id="hashover-message" class="hashover-title">' . $_COOKIE['message'] . '</b>\n');
	} else {
		echo $this->escape_output('<b id="hashover-message" class="hashover-title" style="display: none;"></b>\n');
	}

	echo $this->escape_output('<form id="hashover_form" name="hashover_form" action="/hashover.php" method="post">\n');
	echo $this->escape_output('<span class="hashover-avatar">');
	echo $this->escape_output((($this->setting['icons'] == 'yes') ? $form_avatar : '<span title="Permalink">#' . $this->cmt_count . '</span>'));
	echo $this->escape_output('</span>\n');
	echo $this->escape_output('<div class="hashover-balloon">\n');
	echo $this->escape_output('<div class="hashover-inputs">\n') . PHP_EOL;

	// Display name input tag if told to
	echo 'if (nickname_on) {' . PHP_EOL;
	echo "\t" . $this->escape_output('<div class="hashover-name-input">\n');
	echo "\t" . $this->escape_output('<input type="text" name="name" title="' . $this->text['nickname_tip'] . '" value="' . ((!empty($_COOKIE['name'])) ? $_COOKIE['name'] : '') . '" maxlength="30" placeholder="' . $this->text['nickname'] . '">\n');
	echo "\t" . $this->escape_output('</div>\n');
	echo '}' . PHP_EOL . PHP_EOL;

	// Display password input tag if told to
	echo 'if (password_on) {' . PHP_EOL;
	echo "\t" . $this->escape_output('<div class="hashover-password-input">\n');
	echo "\t" . $this->escape_output('<input type="password" name="password" title="' . $this->text['password_tip'] . '" value="' . ((!empty($_COOKIE['password'])) ? $_COOKIE['password'] : '') . '" placeholder="' . $this->text['password'] . '">\n');
	echo "\t" . $this->escape_output('</div>\n');
	echo '}' . PHP_EOL . PHP_EOL;

	// Add second table row on mobile devices
	if ($this->is_mobile) {
		echo 'if (nickname_on && password_on) {' . PHP_EOL;
		echo "\t" . $this->escape_output('<div class="hashover-login-input">\n');
		echo "\t" . $this->escape_output('<input type="submit" name="login" title="Login (optional)" value="">\n');
		echo "\t" . $this->escape_output('</div>\n');
		echo '}' . PHP_EOL . PHP_EOL;
		echo $this->escape_output('</div>\n<div class="hashover-inputs">\n') . PHP_EOL;
	}

	// Display email input tag if told to
	echo 'if (email_on) {' . PHP_EOL;
	echo "\t" . $this->escape_output('<div class="hashover-email-input">\n');
	echo "\t" . $this->escape_output('<input type="text" name="email" title="' . $this->text['email'] . '" value="' . ((!empty($_COOKIE['email'])) ? $_COOKIE['email'] : '') . '" placeholder="' . $this->text['email'] . '">\n');
	echo "\t" . $this->escape_output('</div>\n');
	echo '}' . PHP_EOL . PHP_EOL;

	// Display website input tag if told to
	echo 'if (website_on) {' . PHP_EOL;
	echo "\t" . $this->escape_output('<div class="hashover-website-input">\n');
	echo "\t" . $this->escape_output('<input type="text" name="website" title="' . $this->text['website'] . '" value="' . ((!empty($_COOKIE['website'])) ? $_COOKIE['website'] : '') . '" placeholder="' . $this->text['website'] . '">\n');
	echo "\t" . $this->escape_output('</div>\n');
	echo '}' . PHP_EOL . PHP_EOL;

	if ($this->is_mobile == false) {
		echo 'if (nickname_on && password_on) {' . PHP_EOL;
		echo "\t" . $this->escape_output('<div class="hashover-login-input">\n');
		echo "\t" . $this->escape_output('<input type="submit" name="login" title="Login (optional)" value="">\n');
		echo "\t" . $this->escape_output('</div>\n');
		echo '}' . PHP_EOL . PHP_EOL;
	}

	echo $this->escape_output('</div>\n') . PHP_EOL;
	echo $this->escape_output('<div id="requiredFields" style="display: none;">\n');
	echo $this->escape_output('<input type="text" name="summary" value="" placeholder="Summary">\n');
	echo $this->escape_output('<input type="hidden" name="middlename" value="" placeholder="Middle Name">\n');
	echo $this->escape_output('<input type="text" name="lastname" value="" placeholder="Last Name">\n');
	echo $this->escape_output('<input type="text" name="address" value="" placeholder="Address">\n');
	echo $this->escape_output('<input type="hidden" name="zip" value="" placeholder="Last Name">\n');
	echo $this->escape_output('</div>\n') . PHP_EOL;

	$replyborder = (isset($_COOKIE['success']) and $_COOKIE['success'] == "no") ? ' style="border: 2px solid #FF0000 !important; -moz-border-radius: 5px 5px 0px 0px; border-radius: 5px 5px 0px 0px;"' : '';
	echo $this->escape_output('<textarea rows="\'+ rows +\'" cols="63" name="comment"' . $replyborder . ' title="' . $this->text['cmt_tip'] . '" placeholder="' . $this->text['comment_form'] . '"></textarea>\n');
	echo (isset($_GET['canon_url']) or isset($canon_url)) ? $this->escape_output('<input type="hidden" name="canon_url" value="' . $this->page_url . '">\n') : '';
	echo (isset($_COOKIE['replied'])) ? $this->escape_output('<input type="hidden" name="reply_to" value="' . $_COOKIE['replied'] . '">\n') : '';
	echo $this->escape_output('<input class="hashover-submit" type="submit" value="' . $this->text['post_button'] . '" onclick="return hashover_submit(true, this);" onsubmit="return hashover_submit(true, this);">\n');
	echo $this->escape_output('</div>\n</form>\n'). PHP_EOL;

	// Display three most popular comments
	if (!empty($this->top_likes)) {
		echo 'var popComments = [' . PHP_EOL;
		krsort($this->top_likes); // Sort popular comments

		for ($p = 1; $p <= count($this->top_likes) and $p <= $this->setting['top_cmts']; $p++) {
			if (!empty($this->top_likes)) {
				echo $this->parse(array_shift($this->top_likes), true);
			}
		}

		echo '];' . PHP_EOL . PHP_EOL;

		echo $this->escape_output('<b class="hashover-title">' . $this->text['popular_cmts'] . ' Comment' . ((count($this->top_likes) != '1') ? 's' : '') . ':</b>\n');
		echo 'hashover += \'<div id="hashover-top-comments">\n\';' . PHP_EOL . PHP_EOL;
		echo 'for (var comment in popComments) {' . PHP_EOL;
		echo "\t" . 'parse_template(popComments[comment], false);' . PHP_EOL;
		echo '}' . PHP_EOL . PHP_EOL;
		echo 'hashover += \'</div>\n\';' . PHP_EOL . PHP_EOL;
	}

	if (!empty($this->hashover)) {
		echo 'var comments = [' . PHP_EOL;
		echo $this->hashover;
		echo '];' . PHP_EOL . PHP_EOL;
	}

	// Display comment count
	echo $this->escape_output('<b class="hashover-count">' . $this->text['showing_cmts'] . ' ' . $script = ($this->cmt_count == "1") ? '0 Comments:</b>\n' : $this->show_count . ':</b>\n');

	// Display comments, if there are no comments display a note
	if (!empty($this->hashover)) {
		echo $this->escape_output('<span class="hashover-sort">\n' . $this->text['sort'] . ': <select name="sort" size="1" onChange="sort_comments(this.value); return false;">\n');
		echo $this->escape_output('<option value="ascending">' . $this->text['sort_ascend'] . '</option>\n');
		echo $this->escape_output('<option value="descending">' . $this->text['sort_descend'] . '</option>\n');
		echo $this->escape_output('<option value="byname">' . $this->text['sort_byname'] . '</option>\n');
		echo $this->escape_output('<option value="bydate">' . $this->text['sort_bydate'] . '</option>\n');
		echo $this->escape_output('<option value="bylikes">' . $this->text['sort_bylikes'] . '</option>\n');
		echo $this->escape_output('</select>\n</span>\n') . PHP_EOL;

		echo $this->escape_output('<div id="sort_div">\n'). PHP_EOL;
		echo 'for (var comment in comments) {' . PHP_EOL;
		echo "\t" . 'parse_template(comments[comment], false);' . PHP_EOL;
		echo '}' . PHP_EOL . PHP_EOL;
		echo $this->escape_output('</div>\n');
	} else {
		echo $this->escape_output('<div style="margin: 16px 0px 12px 0px;" class="hashover-comment hashover-first">\n');
		echo $this->escape_output('<span class="hashover-avatar"><img width="' . $this->setting['icon_size'] . '" height="' . $this->setting['icon_size'] . '" src="/hashover/images/' . $this->setting['image_format'] . 's/first-comment.' . $this->setting['image_format'] . '"></span>\n');
		echo $this->escape_output('<div style="height: ' . $this->setting['icon_size'] . 'px;" class="hashover-balloon">\n');
		echo $this->escape_output('<b class="hashover-title">Be the first to comment!</b>\n</div>');
	}

	echo $this->escape_output('</div>\n') . PHP_EOL;
	echo $this->escape_output('<div id="hashover-end-links">\n');
	echo $this->escape_output('HashOver Comments &middot;\n');
	if (!empty($this->hashover)) echo $this->escape_output('<a href="/hashover.php?rss=' . $this->page_url . '" target="_blank">RSS Feed</a> &middot;\n');
	echo $this->escape_output('<a href="/hashover.php?source" rel="hashover-source" target="_blank">Source Code</a> &middot;\n');
	echo $this->escape_output('<a href="/hashover.php" rel="hashover-javascript" target="_blank">JavaScript</a> &middot;\n');
	echo $this->escape_output('<a href="http://tildehash.com/hashover/changelog.txt" target="_blank">ChangeLog</a> &middot;\n');
	echo $this->escape_output('<a href="http://tildehash.com/hashover/archives/" target="_blank">Archives</a>\n');
	echo $this->escape_output('</div>\n');

	echo PHP_EOL . '// Place all content on page' . PHP_EOL;
	echo 'document.getElementById("hashover").innerHTML = hashover;' . PHP_EOL . PHP_EOL;

?>
