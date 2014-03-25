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
//	http://<?php echo $domain . $_SERVER['PHP_SELF'] . "?source\n"; ?>


// Default form settings
if (rows	== undefined) { var rows	=  '<?php echo $rows; ?>'; }
if (name_on	== undefined) { var name_on	=  'yes'; }
if (email_on	== undefined) { var email_on	=  'yes'; }
if (sites_on	== undefined) { var sites_on	=  'yes'; }
if (passwd_on	== undefined) { var passwd_on	=  'yes'; }

// Add comment stylesheet to page header
var head = document.getElementsByTagName('head')[0];
var links = document.getElementsByTagName('link');

if (document.querySelector('link[href="/hashover/comments.css"]') == null) {
	link = document.createElement('link');
	link.rel = 'stylesheet';
	link.href = '<?php echo $root_dir; ?>comments.css';
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
function reply(r, f) {
	var reply_form = '\n<span class="optionbuttons" style="float: right;">\n';

	if (name_on == 'yes' || email_on == 'yes' || passwd_on == 'yes' || sites_on == 'yes') {
		reply_form += '<?php echo (isset($_COOKIE['name']) and !empty($_COOKIE['name'])) ? '<input type="button" value="&#x25BC; ' . $text['options'] . '" onClick="options(' . "\''+r+'\'" . '); this.value = (this.value == \\\'&#x25BC; ' . $text['options'] . '\\\') ? \\\'&#x25B2; ' . $text['options'] . '\\\' : \\\'&#x25BC; ' . $text['options'] . '\\\'; return false;">' : ''; ?>\n';
	}

	reply_form += '<input type="button" value="<?php echo $text['cancel']; ?>" onClick="cancelform(\''+r+'\'); return false;">\n\
	</span>\n\
	<b class="cmtfont"><?php echo $text['reply_to_cmt']; ?></b>\n\
	<span<?php echo (isset($_COOKIE['name']) and !empty($_COOKIE['name'])) ? ' style="max-height: 0px;"' : ''; ?> class="options" id="options-'+r+'"><hr style="clear: both;">\n\
	<table width="100%" cellpadding="0" cellspacing="0" align="center">\n\
	<tbody>\n<tr>\n';

	if (name_on == 'yes') {
		reply_form += '<td width="1%" rowspan="2">\n<?php
		if (isset($_COOKIE['name']) and preg_match('/^@([a-zA-Z0-9_@]{1,29}$)/', $_COOKIE['name'])) {
			echo '<img align="left" width="34" height="34" src="' . $root_dir . 'scripts/avatars.php?username=' . $_COOKIE['name'] . '&email=' . md5(strtolower(trim($_COOKIE['email']))) . '">';
		} else {
			echo '<img align="left" width="34" height="34" src="';
			echo (isset($_COOKIE['email'])) ? 'http://gravatar.com/avatar/' . md5(strtolower(trim($_COOKIE['email']))) . '?d=http://' . $domain . $root_dir . 'images/avatar.png&s=34&r=pg">' : $root_dir . 'images/avatar.png">';
		}
		?>\n</td>\n';
	}

	if (name_on == 'yes') {
		reply_form += '<td align="right">\n<input type="text" name="name" title="<?php echo $text['nickname_tip']; ?>" value="<?php echo (isset($_COOKIE['name'])) ? $_COOKIE['name'] : $text['nickname']; ?>" maxlength="30" class="opt-name" onFocus="this.value=(this.value == \'<?php echo $text['nickname']; ?>\') ? \'\' : this.value;" onBlur="this.value=(this.value == \'\') ? \'<?php echo $text['nickname']; ?>\' : this.value;">\n</td>\n';
	}

	if (passwd_on == 'yes') {
		reply_form += '<td align="right">\n<input type="<?php echo (isset($_COOKIE['password']) and !empty($_COOKIE['password'])) ? 'password" value="' . $_COOKIE['password'] : 'text" value="' . $text['password']; ?>" name="password" title="<?php echo $text['password_tip']; ?>" class="opt-password" onFocus="this.value=(this.value == \'<?php echo $text['password']; ?>\') ? \'\' : this.value; this.type=\'password\';" onBlur="this.value=(this.value == \'\') ? \'<?php echo $text['password']; ?>\' : this.value; this.type=(this.value == \'<?php echo $text['password']; ?>\') ? \'text\' : \'password\';">\n</td>\n';
	}
	<?php if ($is_mobile == 'yes') echo 'reply_form += \'</tr>\n<tr>\n\';'; ?>

	if (email_on == 'yes') {
		reply_form += '<td align="right">\n<input type="text" name="email" title="<?php echo $text['email']; ?>" value="<?php echo (isset($_COOKIE['email'])) ? $_COOKIE['email'] : $text['email']; ?>" class="opt-email" onFocus="this.value=(this.value == \'<?php echo $text['email']; ?>\') ? \'\' : this.value;" onBlur="this.value=(this.value == \'\') ? \'<?php echo $text['email']; ?>\' : this.value;">\n</td>\n';
	}

	if (sites_on == 'yes') {
		reply_form += '<td align="right">\n<input type="text" name="website" title="<?php echo $text['website']; ?>" value="<?php echo (isset($_COOKIE['website'])) ? $_COOKIE['website'] : $text['website']; ?>" class="opt-website" onFocus="this.value=(this.value == \'<?php echo $text['website']; ?>\') ? \'\' : this.value;" onBlur="this.value=(this.value == \'\') ? \'<?php echo $text['website']; ?>\' : this.value;">\n</td>\n';
	}

	reply_form += '</tr>\n\
	</tbody>\n</table>\n</span>\n\
	<center>\n\
	<textarea rows="6" cols="62" name="comment" onFocus="this.value=(this.value==\'<?php echo $text['reply_form']; ?>\') ? \'\' : this.value;" onBlur="this.value=(this.value==\'\') ? \'<?php echo $text['reply_form']; ?>\' : this.value;" style="width: 100%;" title="<?php echo $text['cmt_tip']; ?>"><?php echo $text['reply_form']; ?></textarea><br>\n\
	<input class="post_cmt" type="submit" value="<?php echo $text['post_reply']; ?>" style="width: 100%;" onClick="return noemailreply(\''+r+'\');" onsubmit="return noemailreply(\''+r+'\');">\n\<?php
	echo (isset($_GET['canon_url']) or isset($canon_url)) ? "\n\t" . '<input type="hidden" name="canon_url" value="' . $page_url . '">\n\\' . PHP_EOL : PHP_EOL; ?>
	<input type="hidden" name="cmtfile" value="' + f + '">\n\
	<input type="hidden" name="reply_to" value="'+f+'">\n\
	</center>\n';

	document.getElementById('cmtopts-' + r).style.display = 'none';
	document.getElementById('cmtforms-' + r).innerHTML = reply_form;
	return false;
}

// Displays edit form
function editcmt(e, f, s) {
	var cmtdata = document.getElementById('cmtdata-' + e).innerHTML.replace(/<br>/gi, '\n').replace(/<\/?a(\s+.*?>|>)/gi, '').replace(/<img.*?title="(.*?)".*?>/gi, '[img]$1[/img]').replace(/^\s+|\s+$/g, '').replace('<code style="white-space: pre;">', '<code>');
	var website = (document.getElementById('opt-website-' + e) != undefined) ? document.getElementById('opt-website-' + e).href : '<?php echo $text['website']; ?>';
	document.getElementById('cmtopts-' + e).style.display = 'none';

	document.getElementById('cmtforms-' + e).innerHTML = '\n<span class="optionbuttons" style="float: right;">\n\
	<input type="submit" name="edit" value="." style="display: none;">\
	<input type="submit" name="delete" class="delete" value="<?php echo $text['delete']; ?>" onClick="return delwarn();">\n\
	<label for="notify" title="<?php echo $text['subscribe_tip']; ?>">\n\
	<input type="checkbox"' + ((s != '0') ? ' checked="true"' : '') + ' id="notify" name="notify"> <?php echo $text['subscribe']; ?>\n\
	</label>\n\
	<input type="button" value="<?php echo $text['cancel']; ?>" onClick="cancelform(\''+e+'\'); return false;">\n\
	</span>\n\
	<b class="cmtfont"><?php echo $text['edit_cmt']; ?></b>\n\
	<span class="options"><hr style="clear: both;">\n\
	<table width="100%" cellpadding="0" cellspacing="0" align="center">\n\
	<tbody>\n<tr>\n\
	<td width="1%" rowspan="2">\n\
	<?php
		if (isset($_COOKIE['name']) and preg_match('/^@([a-zA-Z0-9_@]{1,29}$)/', $_COOKIE['name'])) {
			echo '<img align="left" width="34" height="34" src="' . $root_dir . 'scripts/avatars.php?username=' . $_COOKIE['name'] . '&email=' . md5(strtolower(trim($_COOKIE['email']))) . '">';
		} else {
			echo '<img align="left" width="34" height="34" src="';
			echo (isset($_COOKIE['email'])) ? 'http://gravatar.com/avatar/' . md5(strtolower(trim($_COOKIE['email']))) . '?d=http://' . $domain . $root_dir . 'images/avatar.png&s=34&r=pg">' : $root_dir . 'images/avatar.png">';
		}
		?>\n\
	</td>\n\
	<td align="right">\n\
	<input type="text" name="name" title="<?php echo $text['nickname_tip']; ?>" value="' + document.getElementById('opt-name-' + e).innerHTML.replace(/<.*?>(.*?)<.*?>/gi, '$1') + '" maxlength="30" class="opt-name" onFocus="this.value=(this.value == \'<?php echo $text['nickname']; ?>\') ? \'\' : this.value;" onBlur="this.value=(this.value == \'\') ? \'<?php echo $text['nickname']; ?>\' : this.value;">\n\
	</td>\n\
	<td align="right">\n\
	<input type="<?php echo (isset($_COOKIE['password']) and !empty($_COOKIE['password'])) ? 'password" value="' . $_COOKIE['password'] : 'text" value="' . $text['password']; ?>" name="password" title="<?php echo $text['password_tip']; ?>" class="opt-password" onFocus="this.value=(this.value == \'<?php echo $text['password']; ?>\') ? \'\' : this.value; this.type=\'password\';" onBlur="this.value=(this.value == \'\') ? \'<?php echo $text['password']; ?>\' : this.value; this.type=(this.value == \'<?php echo $text['password']; ?>\') ? \'text\' : \'password\';">\n\
	</td>\n\
<?php if ($is_mobile == 'yes') echo "\t" . '</tr>\n<tr>\n\\'; ?>
	<td align="right">\n\
	<input type="text" name="email" title="<?php echo $text['email']; ?>" value="<?php echo (isset($_COOKIE['email'])) ? $_COOKIE['email'] : $text['email']; ?>" class="opt-email" onFocus="this.value=(this.value == \'<?php echo $text['email']; ?>\') ? \'\' : this.value;" onBlur="this.value=(this.value == \'\') ? \'<?php echo $text['email']; ?>\' : this.value;">\n\
	</td>\n\
	<td align="right">\n\
	<input type="text" name="website" title="<?php echo $text['website']; ?>" value="' + website + '" class="opt-website" onFocus="this.value=(this.value == \'<?php echo $text['website']; ?>\') ? \'\' : this.value;" onBlur="this.value=(this.value == \'\') ? \'<?php echo $text['website']; ?>\' : this.value;">\n\
	</td>\n\
	</tr>\n\
	</tbody>\n</table>\n</span>\n\
	<center>\n\
	<textarea rows="10" cols="62" name="comment" style="width: 100%;" title="<?php echo $text['cmt_tip']; ?>">' + cmtdata + '</textarea><br>\n\
	<input class="post_cmt" type="submit" name="edit" value="<?php echo $text['save_edit']; ?>" style="width: 100%;">\n\
	<input type="hidden" name="cmtfile" value="' + f + '">\n\<?php
	echo (isset($_GET['canon_url']) or isset($canon_url)) ? "\n\t" . '<input type="hidden" name="canon_url" value="' . $page_url . '">\n\\' . PHP_EOL : PHP_EOL; ?>
	</center>\n';
	return false
}

// Function to cancel reply and edit forms
function cancelform(f) {
	document.getElementById('cmtopts-' + f).style.display = '';
	document.getElementById('cmtforms-' + f).innerHTML = '';
	return false;
}

// Function to like a comment
function like(c, f) {
	// Load "like.php"
	var like = new XMLHttpRequest();
	like.open('GET', '<?php echo $root_dir . 'scripts/like.php?like=' . $ref_path; ?>/' + f);
	like.send();

	// Get number of likes
	if (document.getElementById('likes-' + c).innerHTML != '') {
		var likes = parseInt(document.getElementById('likes-' + c).innerHTML.replace(/[^0-9]/g, ''));
	} else {
		var likes = parseInt(0);
	}

	// Change "Like" button title and class; Increase likes
	if (document.getElementById('like-' + c).className == 'like') {
		document.getElementById('like-' + c).className = 'liked';
		document.getElementById('like-' + c).title = '<?php echo addcslashes($text['liked_cmt'], "'"); ?>';
		likes++;
	} else {
		document.getElementById('like-' + c).className = 'like';
		document.getElementById('like-' + c).title = '<?php echo addcslashes($text['like_cmt'], "'"); ?>';
		likes--;
	}

	// Change number of likes
	var like_count = (likes != 1) ? likes + ' Likes' : likes + ' Like';
	document.getElementById('likes-' + c).innerHTML = (likes > 0) ? '<b>' + like_count + '</b>' : '';
}

// Displays options
function options(r) {
	if (name_on == 'yes' || email_on == 'yes' || passwd_on == 'yes' || sites_on == 'yes') {
		if (document.getElementById('options-' + r).style.maxHeight != '200px') {
			document.getElementById('options-' + r).style.maxHeight = '200px';
		} else {
			document.getElementById('options-' + r).style.maxHeight = '0px';
		}
	}

	return false;
}

// Displays a "blank email address" warning
function noemail() {
	if (email_on == 'yes' && (document.comment_form.email.value == '' || document.comment_form.email.value == '<?php echo $text['email']; ?>')) {
		var answer = confirm('<?php echo $text['no_email_warn']; ?>');

		if (answer == false) {
			document.comment_form.email.focus();
			return false;
		}
	}
}

// Displays a "blank email address" warning when replying
function noemailreply(f) {
	if (email_on == 'yes' && (document.getElementById('reply_form-' + f).email.value == '' || document.getElementById('reply_form-' + f).email.value == '<?php echo $text['email']; ?>')) {
		var answer = confirm('<?php echo $text['no_email_warn']; ?>');

		if (answer == false) {
			document.getElementById('options-' + f).style.display = '';
			document.getElementById('reply_form-' + f).email.focus();
			return false;
		}
	}
}

// Displays confirmation dialog for deletion
function delwarn() {
	var answer = confirm('<?php echo $text['delete_cmt']; ?>');

	if (answer == false) {
		return false;
	}
}

// Get page title
if (document.title != '') {
	var pagetitle = ' on "'+document.title+'"';
} else {
	var pagetitle = '';
}

var show_cmt = '';

function parse_template(object, sort, method) {
	var indent = (sort == false || method == 'ascending') ? object['indent'] : '16px 0px 12px 0px';

	if (!object['deletion_notice']) {
		var 
			permalink = object['permalink'],
			cmtclass = (sort == false || method == 'ascending') ? object['cmtclass'] : 'cmtdiv',
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
			cmtopts_style = ''
		;

<?php
		// Load HTML template
		$html_template = explode(PHP_EOL, file_get_contents('html-templates/' . $template . '.html'));

		for ($line = 0; $line != count($html_template) - 1; $line++) {
			echo "\t\t" . 'show_cmt += \'' . $html_template[$line] . '\n\';' . PHP_EOL;
		}
?>
	} else {
		show_cmt += '<a name="' + object['permalink'] + '"></a>\n';
		show_cmt += '<div style="margin: ' + indent + '; clear: both;" class="' + object['cmtclass'] + '">\n';
		show_cmt += object['deletion_notice'] + '\n';
		show_cmt += '</div>\n';
	}
}

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

	if ($page_title = 'yes') {
		$js_title = "'+pagetitle+'";
		$js_title = (isset($_GET['pagetitle'])) ? ' on "' . $_GET['pagetitle'] . '"' : $js_title;
	}

	echo '// Place "hashover" DIV' . PHP_EOL;
	echo 'if (document.getElementById("hashover") == null) {' . PHP_EOL;
	echo "\t" . 'document.write("<div id=\"hashover\"></div>\n");' . PHP_EOL;
	echo '}' . PHP_EOL . PHP_EOL;

	echo jsAddSlashes('<a name="comments"></a><br><b class="cmtfont">' . $text['post_cmt'] . $js_title . ':</b>');

	if (isset($_COOKIE['message']) and !empty($_COOKIE['message'])) {
		echo jsAddSlashes('<b id="message" class="cmtfont">' . $_COOKIE['message'] . '</b><br><br>\n');
	} else {
		echo jsAddSlashes('<br><br>\n');
	}

	echo jsAddSlashes('<form id="comment_form" name="comment_form" action="/hashover.php" method="post">\n');
	echo jsAddSlashes('<span class="cmtnumber">');

	if (isset($_COOKIE['name']) and preg_match('/^@([a-zA-Z0-9_@]{1,29}$)/', $_COOKIE['name'])) {
		echo "\t" . jsAddSlashes('<img align="left" width="' . $icon_size . '" height="' . $icon_size . '" src="' . $script = $root_dir . 'scripts/avatars.php?username=' . $_COOKIE['name'] . '&email=' . md5(strtolower(trim($_COOKIE['email']))) . '">');
	} else {
		echo "\t" . jsAddSlashes('<img align="left" width="' . $icon_size . '" height="' . $icon_size . '" src="' . $script = (isset($_COOKIE['email'])) ? 'http://gravatar.com/avatar/' . md5(strtolower(trim($_COOKIE['email']))) . '?d=http://' . $domain . $root_dir . 'images/avatar.png&s=' . $icon_size . '&r=pg">\n' : $root_dir . 'images/avatar.png">');
	}

	echo jsAddSlashes('</span>\n');
	echo jsAddSlashes('<div class="cmtbox" align="center">\n');
	echo jsAddSlashes('<table width="100%" cellpadding="0" cellspacing="0">\n<tbody>\n<tr>\n');

	// Display name input tag if told to
	echo "if (name_on == 'yes') {\n";
	echo "\t" . jsAddSlashes('<td align="right">\n');
	echo "\t" . jsAddSlashes('<input type="text" name="name" title="' . $text['nickname_tip'] . '" maxlength="30" class="opt-name" onFocus="this.value=(this.value == \'' . $text['nickname'] . '\') ? \'\' : this.value;" onBlur="this.value=(this.value == \'\') ? \'' . $text['nickname'] . '\' : this.value;" value="' . $script = (isset($_COOKIE['name'])) ? $_COOKIE['name'] . '">\n' : $text['nickname'] . '">\n');
	echo "\t" . jsAddSlashes('</td>\n');
	echo "}\n\n";

	// Display password input tag if told to
	echo "if (passwd_on == 'yes') {\n";
	echo "\t" . jsAddSlashes('<td align="right">\n');
	echo "\t" . jsAddSlashes('<input name="password" title="' . $text['password_tip'] . '" class="opt-password" onFocus="this.value=(this.value == \'' . $text['password'] . '\') ? \'\' : this.value; this.type=\'password\';" onBlur="this.value=(this.value == \'\') ? \'' . $text['password'] . '\' : this.value; this.type=(this.value == \'' . $text['password'] . '\') ? \'text\' : \'password\';" type="' . $script = (isset($_COOKIE['password']) and !empty($_COOKIE['password'])) ? 'password">\n' : 'text" value="' . $text['password'] . '">\n');
	echo "\t" . jsAddSlashes('</td>\n');
	echo "}\n\n";

	// Add second table row on mobile devices
	if ($is_mobile == 'yes') {
		echo "if (name_on == 'yes' && passwd_on == 'yes') {\n";
		echo "\t" . jsAddSlashes('<td width="1%" align="right">\n');
		echo "\t" . jsAddSlashes('<input name="login" title="Login (optional)" class="opt-login" type="submit" value="">\n');
		echo "\t" . jsAddSlashes('</td>\n');
		echo "}\n\n";
		echo jsAddSlashes('</tr>\n<tr>\n');
	}

	// Display email input tag if told to
	echo "if (email_on == 'yes') {\n";
	echo "\t" . jsAddSlashes('<td align="right">\n');
	echo "\t" . jsAddSlashes('<input type="text" name="email" title="' . $text['email'] . '" class="opt-email" onFocus="this.value=(this.value == \'' . $text['email'] . '\') ? \'\' : this.value;" onBlur="this.value=(this.value == \'\') ? \'' . $text['email'] . '\' : this.value;" value="' . $script = (isset($_COOKIE['email'])) ? $_COOKIE['email'] . '">\n' : $text['email'] . '">\n');
	echo "\t" . jsAddSlashes('</td>\n');
	echo "}\n\n";

	// Display website input tag if told to
	echo "if (sites_on == 'yes') {\n";
	echo "\t" . jsAddSlashes('<td' . (($is_mobile == 'yes') ? ' colspan="2"' : '') . ' align="right">\n');
	echo "\t" . jsAddSlashes('<input type="text" name="website" title="' . $text['website'] . '" class="opt-website" onFocus="this.value=(this.value == \'' . $text['website'] . '\') ? \'\' : this.value;" onBlur="this.value=(this.value == \'\') ? \'' . $text['website'] . '\' : this.value;" value="' . $script = (isset($_COOKIE['website'])) ? $_COOKIE['website'] . '">\n' : $text['website'] . '">\n');
	echo "\t" . jsAddSlashes('</td>\n');
	echo "}\n\n";

	if ($is_mobile != 'yes') {
		echo "if (name_on == 'yes' && passwd_on == 'yes') {\n";
		echo "\t" . jsAddSlashes('<td width="1%" align="right">\n');
		echo "\t" . jsAddSlashes('<input name="login" title="Login (optional)" class="opt-login" type="submit" value="">\n');
		echo "\t" . jsAddSlashes('</td>\n');
		echo "}\n\n";
	}
	echo jsAddSlashes('</tr>\n</tbody>\n</table>\n') . PHP_EOL;

	echo jsAddSlashes('<div id="requiredFields" style="display: none;">\n');
	echo jsAddSlashes('<input type="text" name="summary" value="" placeholder="Summary">\n');
	echo jsAddSlashes('<input type="hidden" name="middlename" value="" placeholder="Middle Name">\n');
	echo jsAddSlashes('<input type="text" name="lastname" value="" placeholder="Last Name">\n');
	echo jsAddSlashes('<input type="text" name="address" value="" placeholder="Address">\n');
	echo jsAddSlashes('<input type="hidden" name="zip" value="" placeholder="Last Name">\n');
	echo jsAddSlashes('</div>\n') . PHP_EOL;

	$rows = "'+rows+'";
	$replyborder = (isset($_COOKIE['success']) and $_COOKIE['success'] == "no") ? ' border: 2px solid #FF0000 !important; -moz-border-radius: 5px 5px 0px 0px; border-radius: 5px 5px 0px 0px;' : '';

	echo jsAddSlashes('<textarea rows="' . $rows . '" cols="63" name="comment" onFocus="this.value=(this.value==\'' . $text['comment_form'] . '\') ? \'\' : this.value;" onBlur="this.value=(this.value==\'\') ? \'' . $text['comment_form'] . '\' : this.value;" style="width: 100%;' . $replyborder . '" title="' . $text['cmt_tip'] . '">' . $text['comment_form'] . '</textarea><br>\n');
	echo jsAddSlashes('<input class="post_cmt" type="submit" value="' . $text['post_button'] . '" style="width: 100%;" onClick="return noemail();" onsubmit="return noemail();"><br>\n');
	echo (isset($_GET['canon_url']) or isset($canon_url)) ? jsAddSlashes('<input type="hidden" name="canon_url" value="' . $page_url . '">\n') : '';
	echo (isset($_COOKIE['replied'])) ? jsAddSlashes('<input type="hidden" name="reply_to" value="' . $_COOKIE['replied'] . '">\n') : '';
	echo jsAddSlashes('</div>\n</form><br>\n'). PHP_EOL;

	// Display three most popular comments
	if (!empty($top_likes)) {
		echo jsAddSlashes('<br><b class="cmtfont">' . $text['popular_cmts'] . ' Comment' . ((count($top_likes) != '1') ? 's' : '') . ':</b>\n') . PHP_EOL;
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
	echo jsAddSlashes('<br><b class="cmtfont">' . $text['showing_cmts'] . ' ' . $script = ($cmt_count == "1") ? '0 Comments:</b>\n' : display_count() . ':</b>\n') . PHP_EOL;

	// Display comments, if there are no comments display a note
	if (!empty($show_cmt)) {
		echo jsAddSlashes('<span style="float: right;">\n' . $text['sort'] . ': <select name="sort" size="1" onChange="sort_comments(this.value); return false;">\n');
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
		echo jsAddSlashes('<div style="margin: 16px 0px 12px 0px;" class="cmtdiv">\n');
		echo jsAddSlashes('<span class="cmtnumber"><img width="' . $icon_size . '" height="' . $icon_size . '" src="/hashover/images/first-comment.png"></span>\n');
		echo jsAddSlashes('<div style="height: ' . $icon_size . 'px;" class="cmtbubble">\n');
		echo jsAddSlashes('<b class="cmtnote cmtfont" style="color: #000000;">Be the first to comment!</b>\n</div>');
	}

	echo jsAddSlashes('</div><br>\n') . PHP_EOL;
	echo jsAddSlashes('<center>\n');
	echo jsAddSlashes('HashOver Comments &middot;\n');
	if (!empty($show_cmt)) echo jsAddSlashes('<a href="http://' . $domain . '/hashover.php?rss=' . $page_url . '" target="_blank">RSS Feed</a> &middot;\n');
	echo jsAddSlashes('<a href="http://' . $domain . '/hashover.zip" rel="hashover-source" target="_blank">Source Code</a> &middot;\n');
	echo jsAddSlashes('<a href="http://' . $domain . '/hashover.php" rel="hashover-javascript" target="_blank">JavaScript</a> &middot;\n');
	echo jsAddSlashes('<a href="http://tildehash.com/hashover/changelog.txt" target="_blank">ChangeLog</a> &middot;\n');
	echo jsAddSlashes('<a href="http://tildehash.com/hashover/archives/" target="_blank">Archives</a><br>\n');
	echo jsAddSlashes('</center>\n');

	// Script execution ending time
	$exec_time = explode(' ', microtime());
	$exec_end = $exec_time[1] + $exec_time[0];
	$exec_time = ($exec_end - $exec_start);

	echo PHP_EOL . '// Place all content on page' . PHP_EOL;
	echo 'document.getElementById("hashover").innerHTML = show_cmt;' . PHP_EOL . PHP_EOL;
	echo '// Script Execution Time: ' . round($exec_time, 5) . ' Seconds';

?>
