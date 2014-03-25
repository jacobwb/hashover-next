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

	// Read comment files, and wrap them in HTML divs
	$array_count = 0;

	function parse_comments($file, $variable, $check) {
		global $mode, $root_dir, $ref_path, $text, $html_template, $icons, $icon_size, $short_dates, $top_likes, $popular, $domain, $indention, $admin_nickname, $admin_password, $script_query;

		// Generate permalink
		$permalink = 'c' . str_replace('-', 'r', basename($file, '.xml'));
		$permatext = end(explode('-', basename($file, '.xml')));

		// Calculate CSS padding for reply indention
		if (($dashes = substr_count(basename($file), '-')) != '0' and $check == 'yes') {
			$indent = ($dashes >= 1) ? (($icon_size + 4) * $dashes) + 16 : ($icon_size + 20) * $dashes;
		} else {
			$indent = '0';
		}

		if (!isset($_GET['count_link']) or !isset($script_query)) {
			if (($read_cmt = @simplexml_load_file($file)) !== false) {
				$permalink .= ($check == 'yes') ? '' : '_pop';
				if ($read_cmt['likes'] >= $popular) $top_likes["{$read_cmt['likes']}"] = $file;

				$name_at = (preg_match('/^@.*?$/', $read_cmt->name)) ? '@' : '';
				$name_class = (preg_match('/^@.*?$/', $read_cmt->name)) ? ' at' : '';

				if (empty($read_cmt->website)) {
					if (preg_match('/^@([a-zA-Z0-9_@]{1,29}$)/', $read_cmt->name)) {
						$variable_name = $name_at . '<a id="opt-website-' . $permalink . '" href="http://' . ((!preg_match('/@identica/i', $read_cmt->name)) ? 'twitter.com/' : 'identi.ca/') . str_replace(array('@identica', '@'), '', $read_cmt->name) . '" target="_blank">' . preg_replace('/^@(.*?)$/', '\\1', str_replace('@identica', '<span style="display: none;">@identica</span>', $read_cmt->name)) . '</a>';
					} else {
						$variable_name = preg_replace('/^@(.*?)$/', '\\1', str_replace('@identica', '<span style="display: none;">@identica</span>', $read_cmt->name));
					}
				} else {
					$variable_name = $name_at . '<a id="opt-website-' . $permalink . '" href="' . $read_cmt->website . '" target="_blank">' . preg_replace('/^@(.*?)$/', '\\1', str_replace('@identica', '<span style="display: none;">@identica</span>', $read_cmt->name)) . '</a>';
				}

				// Format date and time
				if ($short_dates == 'yes') {
					$get_cmtdate = explode(' - ', $read_cmt->date);
					$make_cmtdate = new DateTime($get_cmtdate[0]);
					$cur_date = new DateTime(date('m/d/Y'));
					$interval = $make_cmtdate->diff($cur_date);

					if ($interval->y != '') {
						$cmt_date = $interval->y . ' year';
						$cmt_date .= ($interval->y != '1') ? 's ago' : ' ago';
					} else if ($interval->m != '') {
						$cmt_date = $interval->m . ' month';
						$cmt_date .= ($interval->m != '1') ? 's ago' : ' ago';
					} else if ($interval->d != '') {
						$cmt_date = $interval->d . ' day';
							$cmt_date .= ($interval->d != '1') ? 's ago' : ' ago';
					} else {
						$cmt_date = $get_cmtdate[1] . ' today';
					}
				} else {
					$cmt_date = $read_cmt->date;
				}

				// Get avatar icons
				if ($icons == 'yes') {
					if (preg_match('/^@([a-zA-Z0-9_@]{1,29}$)/', $read_cmt->name)) {
						$avatar = $root_dir . 'scripts/avatars.php?username=' . $read_cmt->name . '&email=' . md5(strtolower(trim(encrypt($read_cmt->email))));
					} else {
						if (preg_match('/([twitter.com|identi.ca]\/[a-zA-Z0-9_@]{1,29}$)/i', $read_cmt->website)) {
							$web_username = (preg_match('/twitter.com/i', $read_cmt->website)) ? preg_replace('/(.*?twitter\.com)\/([a-zA-Z0-9_]{1,20}$)/i', '\\2', $read_cmt->website) : preg_replace('/(.*?identi\.ca)\/([a-zA-Z0-9_]{1,20}$)/i', '\\2', $read_cmt->website) . '@identica';
							$avatar = $root_dir . 'scripts/avatars.php?username=@' . $web_username . '&email=' . md5(strtolower(trim(encrypt($read_cmt->email))));
						} else {
							// Get user's Gravatar icon from gravatar.com
							if (!empty($read_cmt->email)) {
								$avatar = 'http://gravatar.com/avatar/' . md5(strtolower(trim(encrypt($read_cmt->email)))) . '.png?d=http://' . $domain . $root_dir . 'images/avatar.png&amp;s=' . $icon_size . '&amp;r=pg';
							} else {
								$avatar = $root_dir . 'images/avatar.png';
							}
						}
					}

					$avatar_icon = '<img width="' . $icon_size . '" height="' . $icon_size . '" src="' . $avatar . '" alt="#' . $permatext . '" style="vertical-align: top;">';
				} else {
					$avatar_icon = '<a href="#' . $permalink . '" title="Permalink">#' . $permatext . '</a>';
				}

				// "Edit" and "Like" cookies
				$edit_cookie = 'hashover-' . strtolower(str_replace(' ', '-', $read_cmt->name));
				$like_cookie = md5($_SERVER['SERVER_NAME'] . $ref_path . '/' . basename($file, '.xml'));

				// Setup "Like" link
				if (!isset($_COOKIE[$like_cookie])) {
					$like_onclick = 'like(\'' . $permalink . '\', \'' . basename($file, '.xml') . '\'); ';
					$like_title = $text['like_cmt'];
					$like_class = 'like';
				} else {
					if ($_COOKIE[$like_cookie] == 'liked') {
						$like_onclick = 'like(\'' . $permalink . '\', \'' . basename($file, '.xml') . '\'); ';
						$like_title = $text['liked_cmt'];
						$like_class = 'liked';
					} else {
						$like_onclick = 'like(\'' . $permalink . '\', \'' . basename($file, '.xml') . '\'); ';
						$like_title = $text['like_cmt'];
						$like_class = 'like';
					}
				}

				// Define "Reply" link with appropriate tooltip
				if (!empty($read_cmt->email) and $read_cmt['notifications'] == 'yes') {
					if (isset($_COOKIE['email']) and encrypt($_COOKIE['email']) == $read_cmt->email) {
						$email_indicator = $text['op_cmt_note'] . '" class="no-email"';
					} else{
						$email_indicator = $read_cmt->name . ' ' . $text['subbed_note'] . '" class="has-email"';
					}
				} else {
					$email_indicator = $read_cmt->name . ' ' . $text['unsubbed_note'] . '" class="no-email"';
				}

				// Add HTML anchor tag to URLs
				$clean_code = preg_replace('/(((ftp|http|https){1}:\/\/)[a-zA-Z0-9-@:%_\+.~#?&\/=]+)([\s]{0,})/i', '<a href="\\1" target="_blank">\\1</a>', $read_cmt->body);

				// Replace [img] tags with external image placeholder if enabled
				$clean_code = preg_replace_callback('/\[img\]<a.*?>(((ftp|http|https){1}:\/\/)[a-zA-Z0-9-@:%_\+.~#?&\/=]+)<\/a>\[\/img\]/i', function($arr) {
					global $root_dir;

					if (in_array(pathinfo($arr[1], PATHINFO_EXTENSION), array('jpeg', 'jpg', 'png', 'gif'))) {
						return '<br><br><img src="' . $root_dir . 'images/place-holder.png" title="' . $arr[1] . '" alt="Loading..." onClick="((this.src==this.title) ? this.src=\'' . $root_dir . 'images/place-holder.png\' : this.src=this.title);"><br><br>';
					} else {
						return '<a href="' . $arr[1] . '" target="_blank">' . $arr[1] . '</a>';
					}
				}, $clean_code);

				// Remove repetitive and trailing HTML <br> tags
				$clean_code = preg_replace('/^(<br><br>)/', '', preg_replace('/(<br><br>)$/', '', preg_replace('/(<br>){2,}/i', '<br><br>', $clean_code)));

				if ($mode == 'php') {
					global $variable, $array_count;
					$variable["$array_count"]['permalink'] = $permalink;

					// Add keys to comments object
					$variable["$array_count"]['avatar'] = $avatar_icon;
					$variable["$array_count"]['cmtclass'] = ((preg_match('/r/', $permalink)) ? 'cmtdiv reply' : 'cmtdiv');
					$variable["$array_count"]['indent'] = (($indention == 'right') ? '16px ' . $indent . 'px 12px 0px' : '16px 0px 12px ' . $indent . 'px');
					$variable["$array_count"]['name'] = '<b class="cmtfont' . $name_class . '" id="opt-name-' . $permalink . '">' . $variable_name . '</b>';
					if (preg_match("/r/", $permalink)) $variable["$array_count"]['thread'] = '<a href="#' . preg_replace('/^(.*)r.*$/', '\\1', $permalink) . '" title="' . $text['thread_tip'] . '" style="float: right;">' . $text['thread'] . '</a>';
					$variable["$array_count"]['date'] = '<a href="#' . str_replace('_pop', '', $permalink) . '" title="Permalink">' . $cmt_date . '</a>';
					if ($read_cmt['likes'] > '0') $variable["$array_count"]['likes'] = $read_cmt['likes'] . ' Like' . (($read_cmt['likes'] != '1') ? 's' : '');
					$variable["$array_count"]['sort_name'] = $read_cmt->name;
					$variable["$array_count"]['sort_date'] = strtotime(str_replace('- ', '', $read_cmt->date));
					$variable["$array_count"]['sort_likes'] = $read_cmt['likes'];
					$variable["$array_count"]['notifications'] = $read_cmt['notifications'];

					// Define "Like" link for everyone except original poster
					if (!isset($_COOKIE[$edit_cookie]) or $_COOKIE[$edit_cookie] != hash('ripemd160', $read_cmt->name . $read_cmt->passwd)) {
						if (!isset($_COOKIE['email']) or encrypt($_COOKIE['email']) != $read_cmt->email) {
							$variable["$array_count"]['like_link'] = '<a href="#" id="like-' . $permalink . '" onClick="' . $like_onclick . 'return false;" title="' . $like_title . '" class="' . $like_class . '">Like</a>';
						}
					}

					// Define "Edit" link if proper login cookie set
					if ((isset($_COOKIE[$edit_cookie]) and $_COOKIE[$edit_cookie] == hash('ripemd160', $read_cmt->name . $read_cmt->passwd)) or (isset($_COOKIE['name']) && isset($_COOKIE['hashover-' . strtolower(str_replace(' ', '-', $_COOKIE['name']))]) and $_COOKIE['hashover-' . strtolower(str_replace(' ', '-', $_COOKIE['name']))] == hash('ripemd160', $admin_nickname . md5(encrypt($admin_password))))) {
						if (!empty($read_cmt->passwd) or $_COOKIE['hashover-' . strtolower(str_replace(' ', '-', $_COOKIE['name']))] == hash('ripemd160', $admin_nickname . md5(encrypt($admin_password)))) {
							$variable["$array_count"]['edit_link'] = '<a href="?hashover_edit=' . $permalink . '#' . $permalink . '" title="' . $text['edit_your_cmt'] . '" class="edit">Edit</a>';
						}
					}

					$variable["$array_count"]['reply_link'] = '<a href="?hashover_reply=' . $permalink . '#' . $permalink . '" title="' . $text['reply_to_cmt'] . ' - ' . $email_indicator . '>Reply</a>';
					$variable["$array_count"]['comment'] = str_replace('\n', PHP_EOL, $clean_code);
					$array_count++;
				} else {
					// Add keys to comments object
					$variable .= "\t" . '{' . PHP_EOL;
					$variable .= "\t\t" . 'permalink: \'' . $permalink . '\',' . PHP_EOL;
					$variable .= "\t\t" . 'cmtclass: \'' . ((preg_match('/r/', $permalink)) ? 'cmtdiv reply' : 'cmtdiv') . '\',' . PHP_EOL;
					$variable .= "\t\t" . 'avatar: \'' . addcslashes($avatar_icon, "'") . '\',' . PHP_EOL;
					$variable .= "\t\t" . 'indent: \'' . (($indention == 'right') ? '16px ' . $indent . 'px 12px 0px' : '16px 0px 12px ' . $indent . 'px') . '\',' . PHP_EOL;
					$variable .= "\t\t" . 'name: \'' . addcslashes('<b class="cmtfont' . $name_class . '" id="opt-name-' . $permalink . '">' . $variable_name . '</b>', "'") . '\',' . PHP_EOL;
					$variable .= (preg_match("/r/", $permalink)) ? "\t\t" . 'thread: \'' . addcslashes('<a href="#' . preg_replace('/^(.*)r.*$/', '\\1', $permalink) . '" title="' . $text['thread_tip'] . '" style="float: right;">' . $text['thread'] . '</a>', "'") . '\',' . PHP_EOL : '';
					$variable .= "\t\t" . 'date: \'' . addcslashes('<a href="#' . str_replace('_pop', '', $permalink) . '" title="Permalink">' . $cmt_date . '</a>', "'") . '\',' . PHP_EOL;
					$variable .= ($read_cmt['likes'] > '0') ? "\t\t" . 'likes: \'' . $read_cmt['likes'] . ' Like' . (($read_cmt['likes'] != '1') ? 's' : '') . '\',' . PHP_EOL : '';
					$variable .= "\t\t" . 'sort_name: \'' . addcslashes($read_cmt->name, "'") . '\',' . PHP_EOL;
					$variable .= "\t\t" . 'sort_date: ' . '\'' . strtotime(str_replace('- ', '', $read_cmt->date)) . '\',' . PHP_EOL;
					$variable .= "\t\t" . 'sort_likes: \'' . $read_cmt['likes'] . '\',' . PHP_EOL;

					// Define "Like" link for everyone except original poster
					if (!isset($_COOKIE[$edit_cookie]) or $_COOKIE[$edit_cookie] != hash('ripemd160', $read_cmt->name . $read_cmt->passwd)) {
						if (!isset($_COOKIE['email']) or encrypt($_COOKIE['email']) != $read_cmt->email) {
							$variable .= "\t\t" . 'like_link: \'' . addcslashes('<a href="#" id="like-' . $permalink . '" onClick="' . $like_onclick . 'return false;" title="' . $like_title . '" class="' . $like_class . '">Like</a>', "'") . '\',' . PHP_EOL;
						}
					}

					// Define "Edit" link if proper login cookie set
					if ((isset($_COOKIE[$edit_cookie]) and $_COOKIE[$edit_cookie] == hash('ripemd160', $read_cmt->name . $read_cmt->passwd)) or (isset($_COOKIE['name']) && isset($_COOKIE['hashover-' . strtolower(str_replace(' ', '-', $_COOKIE['name']))]) and $_COOKIE['hashover-' . strtolower(str_replace(' ', '-', $_COOKIE['name']))] == hash('ripemd160', $admin_nickname . md5(encrypt($admin_password))))) {
						$variable .= "\t\t" . 'edit_link: \'' . ((!empty($read_cmt->passwd) or $_COOKIE['hashover-' . strtolower(str_replace(' ', '-', $_COOKIE['name']))] == hash('ripemd160', $admin_nickname . md5(encrypt($admin_password)))) ? addcslashes('<a href="#" onClick="editcmt(\'' . $permalink . '\', \'' . basename($file, '.xml') . '\', \'' . (($read_cmt['notifications'] != 'no') ? '1' : '0') . '\'); return false;" title="' . $text['edit_your_cmt'] . '" class="edit">Edit</a>', "'") : '') . '\',' . PHP_EOL;
					}

					$variable .= "\t\t" . 'reply_link: \'' . addcslashes('<a href="#" onClick="reply(\'' . $permalink . '\', \'' . basename($file, '.xml') . '\'); return false;" title="' . $text['reply_to_cmt'] . ' - ' . $email_indicator . '>Reply</a>', "'") . '\',' . PHP_EOL;
					$variable .= "\t\t" . 'comment: \'' . addcslashes($clean_code, "'") . '\'' . PHP_EOL;
					$variable .= "\t" . '},' . PHP_EOL . PHP_EOL;
				}
			}
		}

		return $variable;
	}

?>
