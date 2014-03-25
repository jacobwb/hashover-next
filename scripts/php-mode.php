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

	// Default form settings
	$name_on	= 'yes';
	$email_on	= 'yes';
	$sites_on	= 'yes';
	$passwd_on	= 'yes';

	if ($page_title = 'yes') {
		$js_title = '<script type="text/javascript">if (document.title != "") { document.write(" on \"" + document.title + "\"") };</script>';
		$js_title = (isset($_GET['pagetitle'])) ? ' on "' . $_GET['pagetitle'] . '"' : $js_title;
	}

?>

<div id="hashover">
	<a name="comments"></a><br>
	<b class="cmtfont"><?php echo $text['post_cmt'] . $js_title; ?>:</b>
<?php
	if (isset($_COOKIE['message']) and !empty($_COOKIE['message'])) {
		echo "\t" . '<b id="message" class="cmtfont">' . $_COOKIE['message'] . '</b><br><br>' . PHP_EOL;
	} else {
		echo "\t" . '<br><br>' . PHP_EOL;
	}
?>

	<form name="comment_form" action="/hashover.php" method="post">
		<span class="cmtnumber"><?php
			if (isset($_COOKIE['name']) and preg_match('/^@([a-zA-Z0-9_@]{1,29}$)/', $_COOKIE['name'])) {
				echo '<img align="left" width="' . $icon_size . '" height="' . $icon_size . '" src="' . $script = $root_dir . 'scripts/avatars.php?username=' . $_COOKIE['name'] . '&email=' . md5(strtolower(trim($_COOKIE['email']))) . '">';
			} else {
				echo '<img align="left" width="' . $icon_size . '" height="' . $icon_size . '" src="' . $script = (isset($_COOKIE['email'])) ? 'http://gravatar.com/avatar/' . md5(strtolower(trim($_COOKIE['email']))) . '?d=http://' . $domain . $root_dir . 'images/avatar.png&s=' . $icon_size . '&r=pg">' : $root_dir . 'images/avatar.png">';
			}
		?></span>

		<div class="cmtbox" align="center">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tbody>
					<tr>
<?php

	// Display name input tag if told to
	if ($name_on == 'yes') {
		echo "\t\t\t\t\t\t" . '<td align="right">' . PHP_EOL;
		echo "\t\t\t\t\t\t\t" . '<input type="text" name="name" title="' . $text['nickname_tip'] . '" maxlength="30" class="opt-name" value="' . ((isset($_COOKIE['name'])) ? $_COOKIE['name'] : '') . '" placeholder="' . $text['nickname'] . '">' . PHP_EOL;
		echo "\t\t\t\t\t\t" . '</td>' . PHP_EOL;
	}

	// Display password input tag if told to
	if ($passwd_on == 'yes') {
		echo "\t\t\t\t\t\t" . '<td align="right">' . PHP_EOL;
		echo "\t\t\t\t\t\t\t" . '<input name="password" title="' . $text['password_tip'] . '" class="opt-password" type="password" value="' . ((isset($_COOKIE['password']) and !empty($_COOKIE['password'])) ? $_COOKIE['password'] : '') . '" placeholder="' . $text['password'] . '">' . PHP_EOL;
		echo "\t\t\t\t\t\t" . '</td>' . PHP_EOL;
	}

	// Add second table row on mobile devices
	if ($is_mobile == 'yes') {
		if ($name_on == 'yes' and $passwd_on == 'yes') {
			echo "\t\t\t\t\t\t" . '<td width="1%" align="right">' . PHP_EOL;
			echo "\t\t\t\t\t\t\t" . '<input name="login" title="Login (optional)" class="opt-login" type="submit" value="">' . PHP_EOL;
			echo "\t\t\t\t\t\t" . '</td>' . PHP_EOL;
		}

		echo "\t\t\t\t\t\t" . '</tr>' . PHP_EOL;
		echo "\t\t\t\t\t\t" . '<tr>' . PHP_EOL;
	}

	// Display email input tag if told to
	if ($email_on == 'yes') {
		echo "\t\t\t\t\t\t" . '<td align="right">' . PHP_EOL;
		echo "\t\t\t\t\t\t\t" . '<input type="text" name="email" title="' . $text['email'] . '" class="opt-email" value="' . ((isset($_COOKIE['email'])) ? $_COOKIE['email'] : '') . '" placeholder="' . $text['email'] . '">' . PHP_EOL;
		echo "\t\t\t\t\t\t" . '</td>' . PHP_EOL;
	}

	// Display website input tag if told to
	if ($sites_on == 'yes') {
		echo "\t\t\t\t\t\t" . '<td' . (($is_mobile == 'yes') ? ' colspan="2"' : '') . ' align="right">' . PHP_EOL;
		echo "\t\t\t\t\t\t\t" . '<input type="text" name="website" title="' . $text['website'] . '" class="opt-website" value="' . ((isset($_COOKIE['website'])) ? $_COOKIE['website'] : '') . '" placeholder="' . $text['website'] . '">' . PHP_EOL;
		echo "\t\t\t\t\t\t" . '</td>' . PHP_EOL;
	}

	if ($is_mobile != 'yes') {
		if ($name_on == 'yes' and $passwd_on == 'yes') {
			echo "\t\t\t\t\t\t" . '<td width="1%" align="right">' . PHP_EOL;
			echo "\t\t\t\t\t\t\t" . '<input name="login" title="Login (optional)" class="opt-login" type="submit" value="">' . PHP_EOL;
			echo "\t\t\t\t\t\t" . '</td>' . PHP_EOL;
		}
	}

	echo "\t\t\t\t\t" . '</tr>' . PHP_EOL;
	echo "\t\t\t\t" . '</tbody>' . PHP_EOL;
	echo "\t\t\t" . '</table>' . PHP_EOL . PHP_EOL;

	echo "\t\t\t" . '<div id="requiredFields" style="display: none;">' . PHP_EOL;
	echo "\t\t\t\t" . '<input type="text" name="summary" value="" placeholder="Summary">' . PHP_EOL;
	echo "\t\t\t\t" . '<input type="hidden" name="middlename" value="" placeholder="Middle Name">' . PHP_EOL;
	echo "\t\t\t\t" . '<input type="text" name="lastname" value="" placeholder="Last Name">' . PHP_EOL;
	echo "\t\t\t\t" . '<input type="text" name="address" value="" placeholder="Address">' . PHP_EOL;
	echo "\t\t\t\t" . '<input type="hidden" name="zip" value="" placeholder="Last Name">' . PHP_EOL;
	echo "\t\t\t" . '</div>' . PHP_EOL . PHP_EOL;

	$replyborder = (isset($_COOKIE['success']) and $_COOKIE['success'] == "no") ? ' border: 2px solid #FF0000 !important; -moz-border-radius: 5px 5px 0px 0px; border-radius: 5px 5px 0px 0px;' : '';
	echo "\t\t\t" . '<textarea rows="' . $rows . '" cols="63" name="comment" style="width: 100%;' . $replyborder . '" title="' . $text['cmt_tip'] . '" placeholder="' . $text['comment_form'] . '"></textarea><br>' . PHP_EOL;
	echo "\t\t\t" . '<input class="post_cmt" type="submit" value="' . $text['post_button'] . '" style="width: 100%;"><br>' . PHP_EOL;
	if (isset($_GET['canon_url']) or isset($canon_url)) echo "\t\t\t\t\t" . '<input type="hidden" name="canon_url" value="' . $page_url . '">' . PHP_EOL;
	if (isset($_COOKIE['replied'])) echo "\t\t\t\t\t" . '<input type="hidden" name="reply_to" value="' . $_COOKIE['replied'] . '">' . PHP_EOL;
	echo "\t\t" . '</div>' . PHP_EOL;
	echo "\t" . '</form><br>' . PHP_EOL . PHP_EOL;

	function parse_template($comments, $count) {
		global $notifications, $top_cmts, $template_replace, $template, $permalink, $ref_queries;

		for ($array = 0; $array != count($comments) and $array != $count; $array++) {
			if (!isset($comments["$array"]['deletion_notice'])) {
				$template_replace = array(
					'indent' => $comments["$array"]['indent'],
					'cmtclass' => $comments["$array"]['cmtclass'],
					'permalink' => $comments["$array"]['permalink'],
					'avatar' => $comments["$array"]['avatar'],
					'name' => $comments["$array"]['name'],
					'thread' => (isset($comments["$array"]['thread'])) ? $comments["$array"]['thread'] : '',
					'comment' => $comments["$array"]['comment'],
					'likes' => (isset($comments["$array"]['likes'])) ? $comments["$array"]['likes'] : ''
				);

				if (!in_array('hashover_reply=' . $template_replace['permalink'], $ref_queries) and !in_array('hashover_edit=' . $template_replace['permalink'], $ref_queries)) {
					$template_replace['date'] = $comments["$array"]['date'];
					$template_replace['like_link'] = (isset($comments["$array"]['like_link'])) ? $comments["$array"]['like_link'] : '';
					$template_replace['edit_link'] = (isset($comments["$array"]['edit_link'])) ? $comments["$array"]['edit_link'] : '';
					$template_replace['reply_link'] = $comments["$array"]['reply_link'];
				} else {
					$template_replace['cmtopts_style'] = ' style="display: none;"';
				}

				// Load HTML template
				$html_template = str_replace(PHP_EOL, PHP_EOL . "\t", trim(file_get_contents('html-templates/' . $template . '.html'), "\n"));
				$notifications = $comments["$array"]['notifications'];

				// Comment information into template; add reply or edit form
				echo "\t" . preg_replace_callback('/\\\' \+ (.*?) \+ \\\'/', function($arr) {
					global $notifications, $template_replace, $ref_queries, $ref_path, $root_dir, $domain, $name_on, $passwd_on, $is_mobile, $email_on, $sites_on, $text, $parse_url;

					if ($arr[1] != 'form') {
						return (isset($template_replace["$arr[1]"])) ? $template_replace["$arr[1]"] : '';
					} else {
						$return_form = '';

						if (in_array('hashover_reply=' . $template_replace['permalink'], $ref_queries)) {
							$return_form .= PHP_EOL . '<span class="optionbuttons" style="float: right;">' . PHP_EOL;
							$return_form .= "\t" . '<a href="' . $parse_url['path'] . ((!empty($parse_url['query'])) ? '?' . $parse_url['query'] : '') . '#' . $template_replace['permalink'] . '">' . $text['cancel'] . '</a>' . PHP_EOL;
							$return_form .= '</span>' . PHP_EOL;
							$return_form .= '<b class="cmtfont">' . $text['reply_to_cmt'] . '</b>' . PHP_EOL;
							$return_form .= '<span class="options" id="options-' . $template_replace['permalink'] . '"><hr style="clear: both;">' . PHP_EOL;
							$return_form .= "\t" . '<table width="100%" cellpadding="0" cellspacing="0" align="center">' . PHP_EOL;
							$return_form .= "\t\t" . '<tbody>' . PHP_EOL . "\t\t\t" . '<tr>' . PHP_EOL;

							if ($name_on == 'yes') {
								$return_form .= "\t\t\t\t" . '<td width="1%" rowspan="2">' . PHP_EOL;

								if (isset($_COOKIE['name']) and preg_match('/^@([a-zA-Z0-9_@]{1,29}$)/', $_COOKIE['name'])) {
									$return_form .= "\t\t\t\t\t" . '<img align="left" width="34" height="34" src="' . $root_dir . 'scripts/avatars.php?username=' . $_COOKIE['name'] . '&email=' . md5(strtolower(trim($_COOKIE['email']))) . '">';
								} else {
									$return_form .= "\t\t\t\t\t" . '<img align="left" width="34" height="34" src="';
									$return_form .= (isset($_COOKIE['email'])) ? 'http://gravatar.com/avatar/' . md5(strtolower(trim($_COOKIE['email']))) . '?d=http://' . $domain . $root_dir . 'images/avatar.png&s=34&r=pg">' : $root_dir . 'images/avatar.png">';
								}

								$return_form .= PHP_EOL . "\t\t\t\t" . '</td>' . PHP_EOL;
							}

							if ($name_on == 'yes') {
								$return_form .= "\t\t\t\t" . '<td align="right">' . PHP_EOL;
								$return_form .= "\t\t\t\t\t" . '<input type="text" name="name" title="' . $text['nickname_tip'] . '" class="opt-name" value="' . ((isset($_COOKIE['name'])) ? $_COOKIE['name'] : '') . '" placeholder="' . $text['nickname'] . '" maxlength="30">' . PHP_EOL;
								$return_form .= "\t\t\t\t" . '</td>' . PHP_EOL;
							}

							if ($passwd_on == 'yes') {
								$return_form .= "\t\t\t\t" . '<td align="right">' . PHP_EOL;
								$return_form .= "\t\t\t\t\t" . '<input name="password" title="' . $text['password_tip'] . '" class="opt-password" type="password" value="' . ((isset($_COOKIE['password']) and !empty($_COOKIE['password'])) ? $_COOKIE['password'] : '') . '" placeholder="' . $text['password'] . '">' . PHP_EOL;
								$return_form .= "\t\t\t\t" . '</td>' . PHP_EOL;
							}

							if ($is_mobile == 'yes') {
								$return_form .= "\t\t\t" . '</tr>' . PHP_EOL . "\t\t\t" . '<tr>' . PHP_EOL;
							}

							if ($email_on == 'yes') {
								$return_form .= "\t\t\t\t" . '<td align="right">' . PHP_EOL;
								$return_form .= "\t\t\t\t\t" . '<input type="text" name="email" title="' . $text['email'] . '" class="opt-email" value="' . ((isset($_COOKIE['email'])) ? $_COOKIE['email'] : '') . '" placeholder="' . $text['email'] . '">' . PHP_EOL;
								$return_form .= "\t\t\t\t" . '</td>' . PHP_EOL;
							}

							if ($sites_on == 'yes') {
								$return_form .= "\t\t\t\t" . '<td align="right">' . PHP_EOL;
								$return_form .= "\t\t\t\t\t" . '<input type="text" name="website" title="' . $text['website'] . '" class="opt-website" value="' . ((isset($_COOKIE['website'])) ? $_COOKIE['website'] : '') . '" placeholder="' . $text['website'] . '">' . PHP_EOL;
								$return_form .= "\t\t\t\t" . '</td>' . PHP_EOL;
							}

							$return_form .= "\t\t\t" . '</tr>' . PHP_EOL . "\t\t" . '</tbody>' . PHP_EOL . "\t" . '</table>' . PHP_EOL . '</span>' . PHP_EOL . '<center>' . PHP_EOL;
							$return_form .= "\t" . '<textarea rows="6" cols="62" name="comment" style="width: 100%;" title="' . $text['cmt_tip'] . '" placeholder="' . $text['comment_form'] . '"></textarea><br>' . PHP_EOL;
							$return_form .= "\t" . '<input class="post_cmt" type="submit" value="' . $text['post_button'] . '" style="width: 100%;">' . PHP_EOL;
							$return_form .= (isset($_GET['canon_url']) or isset($canon_url)) ? "\t" . '<input type="hidden" name="canon_url" value="' . $parse_url['path'] . ((!empty($parse_url['query'])) ? '?' . $parse_url['query'] : '') . '">' . PHP_EOL : '';
							$return_form .= "\t" . '<input type="hidden" name="cmtfile" value="' . str_replace(array('c', 'r', '_pop'), array('', '-', ''), $template_replace['permalink']) . '">' . PHP_EOL;
							$return_form .= "\t" . '<input type="hidden" name="reply_to" value="' . str_replace(array('c', 'r', '_pop'), array('', '-', ''), $template_replace['permalink']) . '">' . PHP_EOL;
							$return_form .= '</center>';
						} else {
							if (in_array('hashover_edit=' . $template_replace['permalink'], $ref_queries)) {
								$return_form .= PHP_EOL . '<span class="optionbuttons" style="float: right;">' . PHP_EOL;
								$return_form .= "\t" . '<input type="submit" name="edit" value="." style="display: none;">';
								$return_form .= "\t" . '<input type="submit" name="delete" class="delete" value="' . $text['delete'] . '">' . PHP_EOL;
								$return_form .= "\t" . '<label for="notify" title="' . $text['subscribe_tip'] . '">' . PHP_EOL;
								$return_form .= "\t\t" . '<input type="checkbox"' . (($notifications != 'no') ? ' checked="true"' : '') . ' id="notify" name="notify"> ' . $text['subscribe'] . PHP_EOL;
								$return_form .= "\t" . '</label>' . PHP_EOL;
								$return_form .= "\t" . '<a href="' . $parse_url['path'] . ((!empty($parse_url['query'])) ? '?' . $parse_url['query'] : '') . '#' . $template_replace['permalink'] . '">' . $text['cancel'] . '</a>' . PHP_EOL;
								$return_form .= '</span>' . PHP_EOL;
								$return_form .= '<b class="cmtfont">' . $text['edit_cmt'] . '</b>' . PHP_EOL;
								$return_form .= '<span class="options"><hr style="clear: both;">' . PHP_EOL;
								$return_form .= "\t" . '<table width="100%" cellpadding="0" cellspacing="0" align="center">' . PHP_EOL;
								$return_form .= "\t\t" . '<tbody>' . PHP_EOL . "\t\t\t" . '<tr>' . PHP_EOL . "\t\t\t\t" . '<td width="1%" rowspan="2">' . PHP_EOL;

								if (isset($_COOKIE['name']) and preg_match('/^@([a-zA-Z0-9_@]{1,29}$)/', $_COOKIE['name'])) {
									$return_form .= "\t\t\t\t\t" . '<img align="left" width="34" height="34" src="' . $root_dir . 'scripts/avatars.php?username=' . $_COOKIE['name'] . '&email=' . md5(strtolower(trim($_COOKIE['email']))) . '">';
								} else {
									$return_form .= "\t\t\t\t\t" . '<img align="left" width="34" height="34" src="';
									$return_form .= (isset($_COOKIE['email'])) ? 'http://gravatar.com/avatar/' . md5(strtolower(trim($_COOKIE['email']))) . '?d=http://' . $domain . $root_dir . 'images/avatar.png&s=34&r=pg">' : $root_dir . 'images/avatar.png">' . PHP_EOL;
								}
								$return_form .= "\t\t\t\t" . '</td>' . PHP_EOL;

								$return_form .= "\t\t\t\t" . '<td align="right">' . PHP_EOL;
								$return_form .= "\t\t\t\t\t" . '<input type="text" name="name" title="' . $text['nickname_tip'] . '" class="opt-name" value="' . ((isset($_COOKIE['name'])) ? $_COOKIE['name'] : '') . '" placeholder="' . $text['nickname'] . '" maxlength="30">' . PHP_EOL;
								$return_form .= "\t\t\t\t" . '</td>' . PHP_EOL;

								$return_form .= "\t\t\t\t" . '<td align="right">' . PHP_EOL;
								$return_form .= "\t\t\t\t\t" . '<input name="password" title="' . $text['password_tip'] . '" class="opt-password" type="password" value="' . ((isset($_COOKIE['password']) and !empty($_COOKIE['password'])) ? $_COOKIE['password'] : '') . '" placeholder="' . $text['password'] . '">' . PHP_EOL;
								$return_form .= "\t\t\t\t" . '</td>' . PHP_EOL;

								if ($is_mobile == 'yes') {
									$return_form .= "\t\t\t" . '</tr>' . PHP_EOL . "\t\t\t" . '<tr>' . PHP_EOL;
								}

								$return_form .= "\t\t\t\t" . '<td align="right">' . PHP_EOL;
								$return_form .= "\t\t\t\t\t" . '<input type="text" name="email" title="' . $text['email'] . '" class="opt-email" value="' . ((isset($_COOKIE['email'])) ? $_COOKIE['email'] : '') . '" placeholder="' . $text['email'] . '">' . PHP_EOL;
								$return_form .= "\t\t\t\t" . '</td>' . PHP_EOL;

								$return_form .= "\t\t\t\t" . '<td align="right">' . PHP_EOL;
								$return_form .= "\t\t\t\t\t" . '<input type="text" name="website" title="' . $text['website'] . '" class="opt-website" value="' . ((isset($_COOKIE['website'])) ? $_COOKIE['website'] : '') . '" placeholder="' . $text['website'] . '">' . PHP_EOL;
								$return_form .= "\t\t\t\t" . '</td>' . PHP_EOL;

								// Clean HTML in comment
								$template_replace['comment'] = preg_replace('/<br>/i', '&#10;', $template_replace['comment']);
								$template_replace['comment'] = preg_replace('/<\/?a(\s+.*?>|>)/i', '', $template_replace['comment']);
								$template_replace['comment'] = preg_replace('/<img.*?title="(.*?)".*?>/i', '[img]\\1[/img]', $template_replace['comment']);
								$template_replace['comment'] = preg_replace('/^\s+|\s+$/i', '', $template_replace['comment']);
								$template_replace['comment'] = preg_replace('/<code style="white-space: pre;">/i', '<code>', $template_replace['comment']);

								$return_form .= "\t\t\t" . '</tr>' . PHP_EOL . "\t\t" . '</tbody>' . PHP_EOL . "\t" . '</table>' . PHP_EOL . '</span>' . PHP_EOL . '<center>' . PHP_EOL;
								$return_form .= "\t" . '<textarea rows="10" cols="62" name="comment" style="width: 100%;" title="' . $text['cmt_tip'] . '" placeholder="' . $text['reply_form'] . '">' . $template_replace['comment'] . '</textarea><br>' . PHP_EOL;
								$return_form .= "\t" . '<input class="post_cmt" type="submit" name="edit" value="' . $text['save_edit'] . '" style="width: 100%;">' . PHP_EOL;
								$return_form .= "\t" . '<input type="hidden" name="cmtfile" value="' . str_replace(array('c', 'r', '_pop'), array('', '-', ''), $template_replace['permalink']) . '">' . PHP_EOL;
								$return_form .= (isset($_GET['canon_url']) or isset($canon_url)) ? "\t" . '<input type="hidden" name="canon_url" value="' . $parse_url['path'] . ((!empty($parse_url['query'])) ? '?' . $parse_url['query'] : '') . '">' . PHP_EOL : '';
								$return_form .= '</center>';
							}
						}

						return str_replace(PHP_EOL, PHP_EOL . "\t\t\t\t\t", $return_form) . PHP_EOL . "\t\t\t\t";
					}
				}, $html_template) . PHP_EOL;
			} else {
				echo '<a name="' . $comments["$array"]['permalink'] . '"></a>' . PHP_EOL;
				echo '<div style="margin: ' . $comments["$array"]['indent'] . '; clear: both;" class="' . $comments["$array"]['cmtclass'] . '">' . PHP_EOL;
				echo $comments["$array"]['deletion_notice'] . PHP_EOL;
				echo '</div>' . PHP_EOL;
			}
		}
	}

	// Display most popular comments
	if (!empty($top_likes)) {
		echo "\t" . '<br><b class="cmtfont">' . $text['popular_cmts'] . ' Comment' . ((count($top_likes) != '1') ? 's' : '') . ':</b>' . PHP_EOL;
		$variable = '';

		foreach ($top_likes as $file) {
			$likes_array = parse_comments($file, array(), 'no');
		}

		parse_template(array_values($likes_array), $top_cmts);
	}

	// Display comment count
	echo "\t" . '<br><b class="cmtfont">' . $text['showing_cmts'] . ' ' . $script = ($cmt_count == "1") ? '0 Comments:</b>' . PHP_EOL : display_count() . ':</b>' . PHP_EOL;

	// Display comments, if there are no comments display a note
	if (!empty($show_cmt)) {
		parse_template($show_cmt, $total_count);
	} else {
		echo "\t" . '<div style="margin: 16px 0px 12px 0px;" class="cmtdiv">' . PHP_EOL;
		echo "\t\t" . '<span class="cmtnumber"><img width="' . $icon_size . '" height="' . $icon_size . '" src="/hashover/images/first-comment.png"></span>' . PHP_EOL;
		echo "\t\t" . '<div style="height: ' . $icon_size . 'px;" class="cmtbubble">' . PHP_EOL;
		echo "\t\t\t" . '<b class="cmtnote cmtfont" style="color: #000000;">Be the first to comment!</b>' . PHP_EOL;
		echo "\t\t" . '</div>' . PHP_EOL;
		echo "\t" . '</div>' . PHP_EOL;
	}

?>

	<br><center>
		HashOver Comments &middot;
<?php if (!empty($show_cmt)) echo "\t\t" . '<a href="http://' . $domain . '/hashover.php?rss=' . $page_url . '" target="_blank">RSS Feed</a> &middot;' . PHP_EOL; ?>
		<a href="http://<?php echo $domain; ?>/hashover.zip" rel="hashover-source" target="_blank">Source Code</a> &middot;
		<a href="http://tildehash.com/hashover/changelog.txt" target="_blank">ChangeLog</a> &middot;
		<a href="http://tildehash.com/hashover/archives/" target="_blank">Archives</a><br>
	</center>
</div>

<script type="text/javascript">
// Copyright (C) 2013 Jacob Barkdull, Jeremiah Stoddard
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
//	http://<?php echo $domain . $_SERVER['PHP_SELF'] . "?source"; ?>


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
</script>

<?php

	// Script execution ending time
	$exec_time = explode(' ', microtime());
	$exec_end = $exec_time[1] + $exec_time[0];
	$exec_time = ($exec_end - $exec_start);

	echo '<!-- Script Execution Time: ' . round($exec_time, 5) . ' Seconds -->' . PHP_EOL;

?>
