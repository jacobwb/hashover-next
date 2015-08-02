<?php

	// Copyright (C) 2010-2015 Jacob Barkdull
	//
	//	This file is part of HashOver.
	//
	//	HashOver is free software: you can redistribute it and/or modify
	//	it under the terms of the GNU Affero General Public License as
	//	published by the Free Software Foundation, either version 3 of the
	//	License, or (at your option) any later version.
	//
	//	HashOver is distributed in the hope that it will be useful,
	//	but WITHOUT ANY WARRANTY; without even the implied warranty of
	//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	//	GNU Affero General Public License for more details.
	//
	//	You should have received a copy of the GNU Affero General Public License
	//	along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


	// Display source code
	if (basename ($_SERVER['PHP_SELF']) === basename (__FILE__)) {
		if (isset ($_GET['source'])) {
			header ('Content-type: text/plain; charset=UTF-8');
			exit (file_get_contents (basename (__FILE__)));
		} else {
			exit ('<b>HashOver</b>: This is a class file.');
		}
	}

	// Parse comments and create deleted comment note
	class CommentParser
	{
		public $setup;
		public $popularList = array ();

		protected $ampm;

		protected $intervals = array (
			'y' => 'date_years',
			'm' => 'date_months',
			'd' => 'date_days'
		);

		public
		function __construct (Setup $setup, array $locale)
		{
			$this->setup = $setup;
			$this->locale = $locale;
			$this->ampm = ($setup->uses12HourTime === false) ? 'H:i' : 'g:ia';
		}

		// Parse comment files
		public
		function parse (array $comment, $key, $key_parts, $popular = false, $popular_listed = true)
		{
			$output = array ();
			$micro_date = strtotime ($comment['date']);
			$popularity = 0;

			// Generate permalink
			if (count ($key_parts) > 1) {
				$output['permalink'] = 'c' . str_replace ('-', 'r', $key);
			} else {
				$output['permalink'] = 'c' . $key;
			}

			// Append "_pop" to end of permalink if popular comment
			if ($popular === true) {
				$output['permalink'] .= '_pop';
			}

			// Format date and time
			if ($this->setup->usesShortDates === true) {
				$make_cmtdate = new DateTime ($comment['date']);
				$cur_date = new DateTime (date ('m/d/Y'));
				$interval = $make_cmtdate->diff ($cur_date);

				foreach ($this->intervals as $i => $string) {
					if ($interval->$i > 0) {
						$date_locale = $this->locale[$string][($interval->$i !== 1)];
						$comment_date = str_replace ('_NUM_', $interval->$i, $date_locale);

						break;
					}
				}

				if (empty ($comment_date)) {
					$get_time = date ($this->ampm, $micro_date);
					$comment_date = str_replace ('_TIME_', $get_time, $this->locale['date_today']);
				}
			} else {
				$comment_date = date ('m/d/Y \a\t ' . $this->ampm, $micro_date);
			}

			// Add name to comment data
			if (!empty ($comment['name'])) {
				$output['name'] = $comment['name'];
			}

			// Get avatar icons
			if ($this->setup->iconMode !== 'none') {
				if ($this->setup->iconMode === 'image') {
					// Get MD5 hash for Gravatar
					if (!empty ($comment['email_hash'])) {
						$output['avatar'] = '/scripts/avatars.php?hash=' . $comment['email_hash'];
					} else {
						// Or default avatar
						$output['avatar'] = '/images/' . $this->setup->imageFormat . 's/avatar.' . $this->setup->imageFormat;
					}
				} else {
					$output['avatar'] = '#' . end ($key_parts);
				}
			}

			// Add website URL to comment data
			if (!empty ($comment['website'])) {
				$output['website'] = $comment['website'];
			}

			// Output whether commenter receives notifications
			if (!empty ($comment['email']) and !empty ($comment['notifications'])) {
				if ($comment['notifications'] === 'yes') {
					$output['subscribed'] = true;
				}
			}

			// Add number of likes to comment data
			if (!empty ($comment['likes'])) {
				$output['likes'] =(int) $comment['likes'];

				// Add number of likes to popularity value
				$popularity += $output['likes'];
			}

			// If enabled, add number of dislikes to comment data
			if ($this->setup->allowsDislikes === true) {
				if (!empty ($comment['dislikes'])) {
					$output['dislikes'] =(int) $comment['dislikes'];

					// Subtract number of dislikes to popularity value
					$popularity -= $output['dislikes'];
				}
			}

			// Add comment to popular comments list if popular enough
			if ($popular_listed === true) {
				if ($popularity >= $this->setup->popularityThreshold) {
					$this->popularList[$popularity . '.' . $key] = array ($key, $key_parts);
				}
			}

			// Authenticate comment editing rights
			if ($this->setup->userIsAdmin === true) {
				// Admin owns every comment
				$output['user_owned'] = true;
			} else {
				// Check this comment belongs to logged in user
				if ($this->setup->userIsLoggedIn === true and !empty ($comment['login_id'])) {
					if ($_COOKIE['hashover-login'] === $comment['login_id']) {
						$output['user_owned'] = true;
					}
				}
			}

			// Get "Like" cookie value
			$like_cookie_name = md5 ($this->setup->domain . $this->setup->threadDirectory . '/' . $key);

			// Check if comment has been liked or disliked
			if (!empty ($_COOKIE[$like_cookie_name])) {
				switch ($_COOKIE[$like_cookie_name]) {
					case 'liked': {
						$output['liked'] = true;
						break;
					}

					case 'disliked': {
						if ($this->setup->allowsDislikes === true) {
							$output['disliked'] = true;
						}

						break;
					}
				}
			}

			$output['date'] =(string) $comment_date;
			$output['sort_date'] =(int) $micro_date;
			$output['body'] =(string) $comment['body'];

			return $output;
		}

		// Function for adding notices to output
		public
		function notice ($type, $key, &$last_date)
		{
			$output = array ();
			$output['title'] = $this->locale['comment_' . $type];
			$last_date++;

			if ($this->setup->iconMode !== 'none') {
				$imageFormat =& $this->setup->imageFormat;
				$output['avatar'] = '/images/' . $imageFormat . 's/' . $type . '-icon.' . $imageFormat;
			}

			$output['permalink'] = 'c' . str_replace ('-', 'r', $key);
			$output['notice'] = $this->locale[$type . '_note'];
			$output['notice_class'] = 'hashover-' . $type;
			$output['sort_date'] =(int) $last_date;

			return $output;
		}
	}

?>
