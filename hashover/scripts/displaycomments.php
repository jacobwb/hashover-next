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

	// Parse comments and create deleted comment note
	class DisplayComments
	{
		public $hashover = array(),
		       $top_likes = array();

		public function __construct($read_comments, $setup)
		{
			$this->read_comments = $read_comments;
			$this->setup = $setup;
		}

		public function display()
		{
			// Run all comments through parser
			$comments = $this->read_comments->read();

			foreach ($comments as $key => $comment) {
				$key_parts = explode('-', $key);

				if (strpos($key, '-') !== false) {
					$reply_level =& $this->hashover[$key_parts[0]];

					for ($i = 1, $li = count($key_parts); $i < $li; $i++) {
						$reply_level =& $reply_level['replies'][$key_parts[$i]];
					}

					if ($comment['status'] == 'deleted') {
						$reply_level = $this->deletion_notice($key, $key_parts);
					} else {
						$reply_level = $this->parse($comment, $key);
					}
				} else {
					if ($comment['status'] == 'deleted') {
						$this->hashover[$key] = $this->deletion_notice($key, $key_parts);
					} else {
						$this->hashover[$key] = $this->parse($comment, $key);
					}
				}
			}

			// Load respective script for JavaScript/HTML output
			if ($this->setup->mode != 'php') {
				header('Content-Type: text/javascript');

				if (!include('./scripts/javascript-mode.php')) {
					exit($this->escape_output('<b>HashOver - Error:</b> file "javascript-mode.php" could not be included!', 'single'));
				}
			} else {
				if (!include('./scripts/php-mode.php')) {
					exit($this->escape_output('<b>HashOver - Error:</b> file "php-mode.php" could not be included!', 'single'));
				}
			}
		}

		// Parse comment files
		public function parse(array $comment, $key, $controls = true, $popular = false)
		{
			$output = array();
			$comment['perma'] = 'c' . str_replace('-', 'r', $key);
			$comment['perma_popper'] = $comment['perma'] . (($popular == true) ? '_pop' : '');
			$comment['email'] = $this->setup->encryption->decrypt($comment['email'], $comment['encryption']);
			$get_cmtdate = str_replace(' - ', ' ', $comment['date']);
			$micro_date = strtotime($get_cmtdate);
			$quantified_likes = $comment['likes'] - $comment['dislikes'];
			$user_is_logged_in = false;

			if ($quantified_likes >= $this->setup->pop_threshold) {
				$this->top_likes[$quantified_likes . '.' . $key] = $key;
			}

			// Format date and time
			if ($this->setup->uses_short_dates == 'yes') {
				$make_cmtdate = new DateTime($get_cmtdate);
				$cur_date = new DateTime(date('m/d/Y'));
				$interval = $make_cmtdate->diff($cur_date);

				if ($interval->y != '') {
					$plural = ($interval->y != '1') ? 1 : 0;
					$cmt_date = str_replace('_NUM_', $interval->y, $this->setup->text['date_years'][$plural]);
				} else if ($interval->m != '') {
					$plural = ($interval->m != '1') ? 1 : 0;
					$cmt_date = str_replace('_NUM_', $interval->m, $this->setup->text['date_months'][$plural]);
				} else if ($interval->d != '') {
					$plural = ($interval->d != '1') ? 1 : 0;
					$cmt_date = str_replace('_NUM_', $interval->d, $this->setup->text['date_days'][$plural]);
				} else {
					if ($this->setup->uses_12h_time == 'yes') {
						$get_time = date('g:ia', $micro_date);
					} else {
						$get_time = date('H:i', $micro_date);
					}

					$cmt_date = str_replace('_TIME_', $get_time, $this->setup->text['date_today']);
				}
			} else {
				if ($this->setup->uses_12h_time == 'yes') {
					$cmt_date = date('m/d/Y \a\t g:ia', $micro_date);
				} else {
					$cmt_date = date('m/d/Y \a\t H:i', $micro_date);
				}
			}

			// Get avatar icons
			if ($this->setup->icon_mode != 'none') {
				if ($this->setup->icon_mode == 'image') {
					$avatar = $this->setup->root_dir . '/scripts/avatars.php?format=' . $this->setup->image_format . '&amp;size=' . $this->setup->icon_size;

					if (preg_match('/^@([a-zA-Z0-9_@]{1,29}$)/', $comment['name'])) {
						$avatar .= '&amp;username=' . $comment['name'];
					} else {
						if (preg_match('/(twitter.com\/[a-zA-Z0-9_@]{1,29}$)/i', $comment['website'])) {
							$web_username = preg_replace('/(.*?twitter\.com)\/([a-zA-Z0-9_]{1,20}$)/i', '\\2', $comment['website']);
							$avatar .= '&amp;username=@' . $web_username;
						}
					}

					if (strpos($avatar, 'username=@') === false) {
						if (!empty($comment['email'])) {
							$avatar .= '&amp;email=' . md5(strtolower(trim($comment['email'])));
						} else {
							$avatar = $this->setup->root_dir . '/images/' . $this->setup->image_format . 's/avatar.' . $this->setup->image_format;
						}
					}

					$output['avatar'] =(string) $avatar;
				} else {
					$key_parts = explode('-', $key);
					$output['avatar'] = '#' . end($key_parts);
				}
			}

			// Get "Like" cookie vaue
			$like_cookie_name = md5($_SERVER['SERVER_NAME'] . $this->setup->ref_path . '/' . $key);
			$like_cookie = !empty($_COOKIE[$like_cookie_name]) ? $_COOKIE[$like_cookie_name] : '';

			// Text for "Like" and "Dislike" buttons
			$like_text = $this->setup->text['like'][0];
			$dislike_text = $this->setup->text['dislike'][0];

			// Setup "Like" links
			$like_onclick = 'hashover_like(\'like\', \'' . $comment['perma_popper'] . '\', \'' . $key . '\'); ';
			$like_title = $this->setup->text['like_cmt'];
			$like_class = 'hashover-like';

			// Add keys to comments array
			$output['permalink'] =(string) $comment['perma_popper'];
			$output['name'] =(string) $comment['name'];
			$output['website'] =(string) $comment['website'];
			$output['date'] =(string) $cmt_date;
			$output['likes'] =(string) $comment['likes'];
	
			if ($this->setup->allows_dislikes == 'yes') {
				$output['dislikes'] =(string) $comment['dislikes'];

				$like_class .= ' dislikes';
				$dislike_onclick = 'hashover_like(\'dislike\', \'' . $comment['perma_popper'] . '\', \'' . $key . '\'); ';
				$dislike_title = $this->setup->text['dislike_cmt'];
				$dislike_class = 'hashover-dislike';
			}

			if (!empty($like_cookie)) {
				if ($like_cookie == 'liked') {
					$like_title = $this->setup->text['liked_cmt'];
					$like_class = 'hashover-liked';
					$like_text = $this->setup->text['liked'];
				}

				if ($this->setup->allows_dislikes == 'yes') {
					if ($like_cookie == 'disliked') {
						$like_class .= ' dislikes';
						$dislike_title = $this->setup->text['disliked_cmt'];
						$dislike_class = 'hashover-disliked';
						$dislike_text = $this->setup->text['disliked'];
					}
				}
			}

			if ($controls == true) {
				if ($this->setup->mode != 'php') {
					$output['sort_date'] =(float) $micro_date;
					$reply_onclick = ' onClick="hashover_reply(\'' . $comment['perma_popper'] . '\', \'' . $key . '\'); return false;"';
					$edit_onclick = ' onClick="hashover_edit(\'' . $comment['perma_popper'] . '\', \'' . $key . '\', \'' . (($comment['notifications'] != 'no') ? '1' : '0') . '\'); return false;"';
					$like_link_style = '';
				} else {
					$output['notifications'] =(string) $comment['notifications'];
					$like_link_style = ' style="display: none;"';
					$reply_onclick = '';
					$edit_onclick = '';
				}

				// Check if user is logged in
				if (!empty($_COOKIE['hashover-login']) and !empty($_COOKIE['password'])) {
					if ($_COOKIE['hashover-login'] === $comment['login_id']) {
						$user_is_logged_in = true;
					}
				}

				// Define "Like" link for everyone except original poster
				if ($user_is_logged_in == false) {
					$output['like_link'] =(string) '<a href="#" id="hashover-like-' . $comment['perma_popper'] . '" onClick="' . $like_onclick . 'return false;" title="' . $like_title . '" class="' . $like_class . '"' . $like_link_style . '>' . $like_text . '</a>';

					if ($this->setup->allows_dislikes == 'yes') {
						$output['dislike_link'] =(string) '<a href="#" id="hashover-dislike-' . $comment['perma_popper'] . '" onClick="' . $dislike_onclick . 'return false;" title="' . $dislike_title . '" class="' . $dislike_class . '"' . $like_link_style . '>' . $dislike_text . '</a>';
					}
				}

				// Define "Edit" link if proper login cookie set
				if ($user_is_logged_in or $this->setup->user_is_admin) {
					$output['edit_link'] =(string) '<a href="' . ((!empty($this->setup->ref_queries)) ? '?' . $this->setup->ref_queries . '&' : '?') . 'hashover_edit=' . $comment['perma_popper'] . '#hashover-edit-' . $comment['perma'] . '"' . $edit_onclick . ' title="' . $this->setup->text['edit_your_cmt'] . '" class="hashover-edit">' . $this->setup->text['edit'] . '</a>';
				}

				// Define "Reply" link with appropriate tooltip
				if ($user_is_logged_in) {
					$email_indicator = $this->setup->text['op_cmt_note'] . '" class="hashover-no-email"';
				} else {
					if (!empty($comment['email']) and $comment['notifications'] == 'yes') {
						$email_indicator = $comment['name'] . ' ' . $this->setup->text['subbed_note'] . '" class="hashover-has-email"';
					} else{
						$email_indicator = $comment['name'] . ' ' . $this->setup->text['unsubbed_note'] . '" class="hashover-no-email"';
					}
				}

				$output['reply_link'] =(string) '<a href="' . ((!empty($this->setup->ref_queries)) ? '?' . $this->setup->ref_queries . '&' : '?') . 'hashover_reply=' . $comment['perma_popper'] . '#hashover-reply-' . $comment['perma'] . '"' . $reply_onclick . ' title="' . $this->setup->text['reply_to_cmt'] . ' - ' . $email_indicator . '>' . $this->setup->text['reply'] . '</a>';
			}

			$output['comment'] =(string) str_replace('\n', PHP_EOL, $comment['body']);

			return $output;
		}

		// Function for adding deletion notice to output
		public function deletion_notice($comment, $key_parts)
		{
			$permalink = 'c' . str_replace('-', 'r', $comment);
			$permatext = end($key_parts);

			$output = array();
			$output['avatar'] =(string) $this->setup->root_dir . '/images/' . $this->setup->image_format . 's/delicon.' . $this->setup->image_format;
			$output['permalink'] =(string) $permalink;
			$output['notice'] =(string) $this->setup->text['del_note'];
			$output['notice_class'] =(string) 'hashover-deleted';

			if (isset($this->hashover[$key_parts[0]])) {
				$output['sort_date'] =(float) $this->hashover[$key_parts[0]]['sort_date'];
			} else {
				$output['sort_date'] =(float) 0;
			}

			return $output;
		}
	}

?>
