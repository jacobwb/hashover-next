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

	// Parse comments and create deleted comment note
	class DisplayComments extends ReadComments {
		public $encryption;

		public function display($output) {
			$this->read($output);
			$this->encryption = new Encryption();

			if (isset($_GET['rss'])) {
				if (!include('.' . $this->setting['root_dir'] . 'scripts/rss-output.php')) {
					exit($this->escape_output('<b>HashOver - Error:</b> file "rss-output.php" could not be included!', 'single'));
				}
			}

			foreach ($this->comments as $comment) {
				static $deleted_files = array();
				$cmt_tree = '';

				// Find deleted comments; display deletion notice
				if ($this->setting['data_format'] == 'xml' or $this->setting['data_format'] == 'json') {
					foreach (explode('-', basename($comment['file'], '.' . $this->setting['data_format'])) as $reply) {
						for ($i = 1; $i <= $reply; $i++) {
							if (!isset($this->comments[$cmt_tree . ((!empty($cmt_tree) ? '-' : '') . $i)])) {
								if (!in_array($cmt_tree . ((!empty($cmt_tree) ? '-' : '') . $i), $deleted_files)) {
									if ($this->mode != 'php') {
										$this->hashover .= $this->deletion_notice(array('file' => $cmt_tree . ((!empty($cmt_tree) ? '-' : '') . $i)), $output);
									} else {
										$this->hashover[] = $this->deletion_notice(array('file' => $cmt_tree . ((!empty($cmt_tree) ? '-' : '') . $i)), $output);
									}

									$deleted_files[] = $cmt_tree . ((!empty($cmt_tree) ? '-' : '') . $i);
								}
							}
						}

						$cmt_tree .= ((!empty($cmt_tree) ? '-' : '')) . $reply;
					}
				}

				// Check whether to generate output
				if ($output == true) {
					if ($this->mode != 'php') {
						$this->hashover .= $this->parse($comment);
					} else {
						$this->hashover[] = $this->parse($comment);
					}
				}
			}

			$this->show_count = ($this->cmt_count - 1) . ' Comment' . (($this->cmt_count != 2) ? 's' : '');

			if ($this->total_count != $this->cmt_count) {
				$this->show_count .= ' (' . ($this->total_count - 1) . ' counting repl';
				$this->show_count .= (abs($this->total_count - $this->cmt_count) > 1) ? 'ies)' : 'y)';
			}

			if ($output == true) {
				$cookies = new Cookies($this->setting['expire'], $this->setting['domain']);
				$cookies->clear();

				// Load script for writing comments
				if (!@include('.' . $this->setting['root_dir'] . 'scripts/write_comments.php')) {
					exit($this->escape_output('<b>HashOver - Error:</b> "./scripts/write_comments.php" file could not be included!', 'single'));
				}

				// Load respective script for JavaScript/HTML output
				if ($this->mode != 'php') {
					header('Content-Type: text/javascript');

					if (!include('.' . $this->setting['root_dir'] . 'scripts/javascript-mode.php')) {
						exit($this->escape_output('<b>HashOver - Error:</b> file "javascript-mode.php" could not be included!', 'single'));
					}
				} else {
					if (!include('.' . $this->setting['root_dir'] . 'scripts/php-mode.php')) {
						exit($this->escape_output('<b>HashOver - Error:</b> file "php-mode.php" could not be included!', 'single'));
					}
				}
			}
		}

		// Parse comment files
		public function parse($comment, $popular = false) {
			$comment['permalink'] .= ($popular == true) ? '_pop' : '';
			$comment['email'] = $this->encryption->decrypt($comment['email'], $comment['encryption']);
			$name_at = (preg_match('/^@.*?$/', $comment['name'])) ? '@' : '';
			$name_class = (preg_match('/^@.*?$/', $comment['name'])) ? ' at' : '';
			$file_basename = basename($comment['file'], '.' . $this->setting['data_format']);

			if (empty($comment['website'])) {
				if (preg_match('/^@([a-zA-Z0-9_@]{1,29}$)/', $comment['name'])) {
					$name = $name_at . '<a id="hashover-website-' . $comment['permalink'] . '" href="http://twitter.com/' . str_replace('@', '', $comment['name']) . '" target="_blank">' . preg_replace('/^@(.*?)$/', '\\1', $comment['name']) . '</a>';
				} else {
					$name = preg_replace('/^@(.*?)$/', '\\1', $comment['name']);
				}
			} else {
				$name = $name_at . '<a id="hashover-website-' . $comment['permalink'] . '" href="' . $comment['website'] . '" target="_blank">' . preg_replace('/^@(.*?)$/', '\\1', $comment['name']) . '</a>';
			}

			// Format date and time
			if ($this->setting['short_dates'] == 'yes') {
				$get_cmtdate = explode(' - ', $comment['date']);
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
				$cmt_date = $comment['date'];
			}

			// Get avatar icons
			$avatar = $this->setting['root_dir'] . 'scripts/avatars.php?format=' . $this->setting['image_format'] . '&size=' . $this->setting['icon_size'];

			if ($this->setting['icons'] == 'yes') {
				if (preg_match('/^@([a-zA-Z0-9_@]{1,29}$)/', $comment['name'])) {
					$avatar .= '&username=' . $comment['name'];
				} else {
					if (preg_match('/(twitter.com\/[a-zA-Z0-9_@]{1,29}$)/i', $comment['website'])) {
						$web_username = preg_replace('/(.*?twitter\.com)\/([a-zA-Z0-9_]{1,20}$)/i', '\\2', $comment['website']);
						$avatar .= '&username=@' . $web_username;
					}
				}

				if (!empty($comment['email'])) {
					$avatar .= '&email=' . md5(strtolower(trim($comment['email'])));
				} else {
					$avatar = $this->setting['root_dir'] . 'images/' . $this->setting['image_format'] . 's/avatar.' . $this->setting['image_format'];
				}

				$avatar_icon = '<img width="' . $this->setting['icon_size'] . '" height="' . $this->setting['icon_size'] . '" src="' . $avatar . '" alt="#' . $comment['permatext'] . '">';
			} else {
				$avatar_icon = '<a href="#' . $comment['permalink'] . '" title="Permalink">#' . $comment['permatext'] . '</a>';
			}

			// "Edit" and "Like" cookies
			$edit_cookie = 'hashover-' . strtolower(str_replace(' ', '-', $comment['name']));
			$like_cookie = md5($_SERVER['SERVER_NAME'] . $this->ref_path . '/' . $file_basename);
			$like_text = $this->text['like'];

			// Setup "Like" link
			if (!isset($_COOKIE[$like_cookie])) {
				$like_onclick = 'hashover_like(\'' . $comment['permalink'] . '\', \'' . $file_basename . '\'); ';
				$like_title = $this->text['like_cmt'];
				$like_class = 'hashover-like';
			} else {
				if ($_COOKIE[$like_cookie] == 'liked') {
					$like_onclick = 'hashover_like(\'' . $comment['permalink'] . '\', \'' . $file_basename . '\'); ';
					$like_title = $this->text['liked_cmt'];
					$like_class = 'hashover-liked';
					$like_text = $this->text['liked'];
				} else {
					$like_onclick = 'hashover_like(\'' . $comment['permalink'] . '\', \'' . $file_basename . '\'); ';
					$like_title = $this->text['like_cmt'];
					$like_class = 'hashover-like';
				}
			}

			// Define "Reply" link with appropriate tooltip
			if (!empty($comment['email']) and $comment['notifications'] == 'yes') {
				if (isset($_COOKIE['email']) and $_COOKIE['email'] == $comment['email']) {
					$email_indicator = $this->text['op_cmt_note'] . '" class="hashover-no-email"';
				} else{
					$email_indicator = $comment['name'] . ' ' . $this->text['subbed_note'] . '" class="hashover-has-email"';
				}
			} else {
				$email_indicator = $comment['name'] . ' ' . $this->text['unsubbed_note'] . '" class="hashover-no-email"';
			}

			// Add HTML anchor tag to URLs
			$clean_code = preg_replace('/(((ftp|http|https){1}:\/\/)[a-zA-Z0-9-@:%_\+.~#?&\/=]+)([\s]{0,})/i', '<a href="\\1" target="_blank">\\1</a>', $comment['body']);

			// Replace [img] tags with external image placeholder if enabled
			$classThis = $this;

			$clean_code = preg_replace_callback('/\[img\]<a.*?>(((ftp|http|https){1}:\/\/)[a-zA-Z0-9-@:%_\+.~#?&\/=]+)<\/a>\[\/img\]/i', function($arr) use($classThis) {
				if (in_array(pathinfo($arr[1], PATHINFO_EXTENSION), array('jpeg', 'jpg', 'png', 'gif'))) {
					return '<br><br><img src="' . $classThis->setting['root_dir'] . 'images/' . $classThis->setting['image_format'] . 's/place-holder.' . $classThis->setting['image_format'] . '" title="' . $arr[1] . '" alt="Loading..." onClick="((this.src==this.title) ? this.src=\'' . $classThis->setting['root_dir'] . 'images/' . $classThis->setting['image_format'] . 's/place-holder.' . $classThis->setting['image_format'] . '\' : this.src=this.title);"><br><br>';
				} else {
					return '<a href="' . $arr[1] . '" target="_blank">' . $arr[1] . '</a>';
				}
			}, $clean_code);

			// Remove repetitive and trailing HTML <br> tags
			$clean_code = preg_replace('/^(<br><br>)/', '', preg_replace('/(<br><br>)$/', '', preg_replace('/(<br>){2,}/i', '<br><br>', $clean_code)));

			if ($this->mode != 'php') {
				// Add keys to JavaScript object
				$output = "\t" . '{' . PHP_EOL;
				$output .= "\t\t" . 'permalink: \'' . $comment['permalink'] . '\',' . PHP_EOL;
				$output .= (preg_match('/r/', $comment['permalink'])) ? "\t\t" . 'cmtclass: \'hashover-reply\',' . PHP_EOL : '';
				$output .= "\t\t" . 'avatar: \'' . addcslashes($avatar_icon, "'") . '\',' . PHP_EOL;
				$output .= "\t\t" . 'indent: \'' . (($this->setting['indention'] == 'right') ? '16px ' . $comment['indent'] . 'px 12px 0px' : '16px 0px 12px ' . $comment['indent'] . 'px') . '\',' . PHP_EOL;
				$output .= "\t\t" . 'name: \'' . addcslashes('<b class="hashover-name' . $name_class . '" id="hashover-name-' . $comment['permalink'] . '">' . $name . '</b>', "'") . '\',' . PHP_EOL;
				$output .= (preg_match("/r/", $comment['permalink'])) ? "\t\t" . 'thread: \'' . addcslashes('<a href="#' . preg_replace('/^(.*)r.*$/', '\\1', $comment['permalink']) . '" title="' . $this->text['thread_tip'] . '" class="hashover-thread-link">' . $this->text['thread'] . '</a>', "'") . '\',' . PHP_EOL : '';
				$output .= "\t\t" . 'date: \'' . addcslashes('<a href="#' . $comment['permalink'] . '" title="Permalink">' . $cmt_date . '</a>', "'") . '\',' . PHP_EOL;
				$output .= ($comment['likes'] > '0') ? "\t\t" . 'likes: \'' . $comment['likes'] . ' Like' . (($comment['likes'] != '1') ? 's' : '') . '\',' . PHP_EOL : '';
				$output .= "\t\t" . 'sort_name: \'' . addcslashes($comment['name'], "'") . '\',' . PHP_EOL;
				$output .= "\t\t" . 'sort_date: ' . '\'' . strtotime(str_replace('- ', '', $comment['date'])) . '\',' . PHP_EOL;
				$output .= "\t\t" . 'sort_likes: \'' . $comment['likes'] . '\',' . PHP_EOL;

				// Define "Like" link for everyone except original poster
				if (!isset($_COOKIE[$edit_cookie]) or $_COOKIE[$edit_cookie] != hash('ripemd160', $comment['name'] . $comment['email'])) {
					if (!isset($_COOKIE['email']) or $_COOKIE['email'] != $comment['email']) {
						$output .= "\t\t" . 'like_link: \'' . addcslashes('<a href="#" id="hashover-like-' . $comment['permalink'] . '" onClick="' . $like_onclick . 'return false;" title="' . $like_title . '" class="' . $like_class . '">' . $like_text . '</a>', "'") . '\',' . PHP_EOL;
					}
				}

				// Define "Edit" link if proper login cookie set
				if ((isset($_COOKIE[$edit_cookie]) and $_COOKIE[$edit_cookie] == hash('ripemd160', $comment['name'] . $comment['email'])) or (isset($_COOKIE['name']) and isset($_COOKIE['password']) and $_COOKIE['name'] === $this->admin_nickname and $_COOKIE['password'] === $this->admin_password)) {
					$output .= "\t\t" . 'edit_link: \'' . ((!empty($comment['passwd']) or (isset($_COOKIE['name']) and isset($_COOKIE['password']) and $_COOKIE['name'] === $this->admin_nickname and $_COOKIE['password'] === $this->admin_password)) ? addcslashes('<a href="#" onClick="hashover_edit(\'' . $comment['permalink'] . '\', \'' . $file_basename . '\', \'' . (($comment['notifications'] != 'no') ? '1' : '0') . '\'); return false;" title="' . $this->text['edit_your_cmt'] . '" class="hashover-edit">' . $this->text['edit'] . '</a>', "'") : '') . '\',' . PHP_EOL;
				}

				$output .= "\t\t" . 'reply_link: \'' . addcslashes('<a href="#" onClick="hashover_reply(\'' . $comment['permalink'] . '\', \'' . $file_basename . '\'); return false;" title="' . $this->text['reply_to_cmt'] . ' - ' . $email_indicator . '>' . $this->text['reply'] . '</a>', "'") . '\',' . PHP_EOL;
				$output .= "\t\t" . 'comment: \'' . addcslashes($clean_code, "'") . '\'' . PHP_EOL;
				$output .= "\t" . '},' . PHP_EOL;
			} else {
				// Add keys to comments array
				$output = array();
				$output['permalink'] = $comment['permalink'];
				$output['avatar'] = $avatar_icon;
				if (preg_match('/r/', $comment['permalink'])) $output['cmtclass'] = 'hashover-reply';
				$output['indent'] = (($this->setting['indention'] == 'right') ? '16px ' . $comment['indent'] . 'px 12px 0px' : '16px 0px 12px ' . $comment['indent'] . 'px');
				$output['name'] = '<b class="hashover-name' . $name_class . '" id="hashover-name-' . $comment['permalink'] . '">' . $name . '</b>';
				if (preg_match("/r/", $comment['permalink'])) $output['thread'] = '<a href="#' . preg_replace('/^(.*)r.*$/', '\\1', $comment['permalink']) . '" title="' . $this->text['thread_tip'] . '" class="hashover-thread-link">' . $this->text['thread'] . '</a>';
				$output['date'] = '<a href="#' . $comment['permalink'] . '" title="Permalink">' . $cmt_date . '</a>';
				if ($comment['likes'] > '0') $output['likes'] = $comment['likes'] . ' Like' . (($comment['likes'] != '1') ? 's' : '');
				$output['sort_name'] = $comment['name'];
				$output['sort_date'] = strtotime(str_replace('- ', '', $comment['date']));
				$output['sort_likes'] = $comment['likes'];
				$output['notifications'] = $comment['notifications'];

				// Define "Like" link for everyone except original poster
				if (!isset($_COOKIE[$edit_cookie]) or $_COOKIE[$edit_cookie] != hash('ripemd160', $comment['name'] . $comment['email'])) {
					if (!isset($_COOKIE['email']) or $_COOKIE['email'] != $comment['email']) {
						$output['like_link'] = '<a href="#" id="hashover-like-' . $comment['permalink'] . '" onClick="' . $like_onclick . 'return false;" title="' . $like_title . '" class="' . $like_class . '">' . $like_text . '</a>';
					}
				}

				// Define "Edit" link if proper login cookie set
				if ((isset($_COOKIE[$edit_cookie]) and $_COOKIE[$edit_cookie] == hash('ripemd160', $comment['name'] . $comment['email'])) or (isset($_COOKIE['name']) and isset($_COOKIE['password']) and $_COOKIE['name'] === $this->admin_nickname and $_COOKIE['password'] === $this->admin_password)) {
					if (!empty($comment['passwd']) or (isset($_COOKIE['name']) and isset($_COOKIE['password']) and $_COOKIE['name'] === $this->admin_nickname and $_COOKIE['password'] === $this->admin_password)) {
						$output['edit_link'] = '<a href="' . ((!empty($this->parse_url['query'])) ? '?' . $this->parse_url['query'] . '&' : '?') . 'hashover_edit=' . $comment['permalink'] . '#' . $comment['permalink'] . '-form" title="' . $this->text['edit_your_cmt'] . '" class="hashover-edit">' . $this->text['edit'] . '</a>';
					}
				}

				$output['reply_link'] = '<a href="' . ((!empty($this->parse_url['query'])) ? '?' . $this->parse_url['query'] . '&' : '?') . 'hashover_reply=' . $comment['permalink'] . '#' . $comment['permalink'] . '-form" title="' . $this->text['reply_to_cmt'] . ' - ' . $email_indicator . '>' . $this->text['reply'] . '</a>';
				$output['comment'] = str_replace('\n', PHP_EOL, $clean_code);
			}

			if ($comment['likes'] >= $this->setting['popular']) {
				$this->top_likes[$comment['likes'] . '-' . $comment['permalink']] = $comment;
			}

			return $output;
		}

		// Function for adding deletion notice to output
		public function deletion_notice($comment, $output = false) {
			// Check whether to generate output
			if ($output == true) {
				$file_parts = explode('-', $comment['file']);
				$comment['permalink'] = 'c' . str_replace('-', 'r', $comment['file']);
				$comment['permatext'] = end($file_parts);

				// Calculate CSS padding for reply indention
				if (($dashes = substr_count(basename($comment['file']), '-')) != '0') {
					$comment['indent'] = ($dashes >= 1) ? (($this->setting['icon_size'] + 4) * $dashes) + 16 : ($this->setting['icon_size'] + 20) * $dashes;
				} else {
					$comment['indent'] = '0';
				}

				if ($this->setting['icons'] == 'yes') {
					$icon_fmt = '<img width="' . $this->setting['icon_size'] . '" height="' . $this->setting['icon_size'] . '" src="' . $this->setting['root_dir'] . 'images/' . $this->setting['image_format'] . 's/delicon.' . $this->setting['image_format'] . '" alt="#' . $comment['permatext'] . '" align="left">';
				} else {
					$icon_fmt = '<a href="#' . $comment['permalink'] . '" title="Permalink">#' . $comment['permatext'] . '</a>&nbsp;';
				}

				if ($this->mode != 'php') {
					$output = "\t" . '{' . PHP_EOL;
					$output .= "\t\t" . 'permalink: \'' . $comment['permalink'] . '\',' . PHP_EOL;
					$output .= "\t\t" . 'cmtclass: \'' . ((preg_match('/r/', $comment['permalink'])) ? 'hashover-comment hashover-deleted hashover-reply' : 'hashover-comment hashover-deleted') . '\',' . PHP_EOL;
					$output .= "\t\t" . 'indent: \'' . (($this->setting['indention'] == 'right') ? '16px ' . $comment['indent'] . 'px 12px 0px' : '16px 0px 12px ' . $comment['indent'] . 'px') . '\',' . PHP_EOL;
					$output .= "\t\t" . 'deletion_notice: \'<span class="hashover-avatar">' . $icon_fmt . '</span><div style="height: ' . $this->setting['icon_size'] . 'px;" class="hashover-balloon">\n<b class="hashover-title">' . $this->text['del_note'] . '</b>\n</div>\n\'' . PHP_EOL;
					$output .= "\t" . '},' . PHP_EOL . PHP_EOL;
				} else {
					$output = array();
					$output['permalink'] = $comment['permalink'];
					$output['cmtclass'] = ((preg_match('/r/', $comment['permalink'])) ? 'hashover-comment hashover-deleted hashover-reply' : 'hashover-comment hashover-deleted');
					$output['indent'] = (($this->setting['indention'] == 'right') ? '16px ' . $comment['indent'] . 'px 12px 0px' : '16px 0px 12px ' . $comment['indent'] . 'px');
					$output['deletion_notice'] = '<span class="hashover-avatar">' . $icon_fmt . '</span><div style="height: ' . $this->setting['icon_size'] . 'px;" class="hashover-balloon">' . PHP_EOL . '<b class="hashover-title">' . $this->text['del_note'] . '</b>' . PHP_EOL . '</div>' . PHP_EOL;
				}
			}

			$this->count_comments($comment['file']);

			return $output;
		}
	}

?>
