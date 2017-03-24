<?php namespace HashOver;

// Copyright (C) 2010-2017 Jacob Barkdull
// This file is part of HashOver.
//
// HashOver is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// HashOver is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


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
	public $login;
	public $locale;
	public $avatars;
	public $popularList = array ();

	protected $ampm;

	protected $intervals = array (
		'y' => 'date-years',
		'm' => 'date-months',
		'd' => 'date-days'
	);

	public function __construct (Setup $setup)
	{
		$this->setup = $setup;
		$this->login = new Login ($setup);
		$this->locale = new Locale ($setup);
		$this->avatars = new Avatars ($setup);
		$this->ampm = ($setup->uses12HourTime === false) ? 'H:i' : 'g:ia';
		$this->current_datetime = new \DateTime (date('Y-m-d'));
	}

	// Parse comment files
	public function parse (array $comment, $key, $key_parts, $popular = false, $popular_listed = true)
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

		// Append "-pop" to end of permalink if popular comment
		if ($popular === true) {
			$output['permalink'] .= '-pop';
		}

		// Format date and time
		if ($this->setup->usesShortDates === true) {
			$comment_datetime = new \DateTime (date ('Y-m-d', $micro_date));
			$interval = $comment_datetime->diff ($this->current_datetime);

			foreach ($this->intervals as $i => $string) {
				if ($interval->$i > 0) {
					$date_locale = $this->locale->text[$string][($interval->$i !== 1)];
					$comment_date = sprintf ($date_locale, $interval->$i);

					break;
				}
			}

			if (empty ($comment_date)) {
				$get_time = date ($this->ampm, $micro_date);
				$comment_date = sprintf ($this->locale->text['date-today'], $get_time);
			}
		} else {
			$comment_date = date ('m/d/Y \a\t ' . $this->ampm, $micro_date);
		}

		// Add name to output
		if (!empty ($comment['name'])) {
			$output['name'] = $comment['name'];
		}

		// Get avatar icons
		if ($this->setup->iconMode !== 'none') {
			if ($this->setup->iconMode === 'image') {
				// Get MD5 hash for Gravatar
				$hash = !empty ($comment['email_hash']) ? $comment['email_hash'] : '';
				$output['avatar'] = $this->avatars->getGravatar ($hash);
			} else {
				$output['avatar'] = '#' . end ($key_parts);
			}
		}

		// Add website URL to output
		if (!empty ($comment['website'])) {
			$output['website'] = $comment['website'];
		}

		// Output whether commenter receives notifications
		if (!empty ($comment['email']) and !empty ($comment['notifications'])) {
			if ($comment['notifications'] === 'yes') {
				$output['subscribed'] = true;
			}
		}

		// Add number of likes to output
		if (!empty ($comment['likes'])) {
			$output['likes'] =(int) $comment['likes'];

			// Add number of likes to popularity value
			$popularity += $output['likes'];
		}

		// If enabled, add number of dislikes to output
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
				$this->popularList[] = array (
					'popularity' => $popularity,
					'key' => $key,
					'parts' => $key_parts
				);
			}
		}

		// Authenticate comment editing rights
		if ($this->login->userIsAdmin === true) {
			// Admin owns every comment
			$output['user-owned'] = true;
		} else {
			// Check this comment belongs to logged in user
			if ($this->login->userIsLoggedIn === true and !empty ($comment['login_id'])) {
				if ($this->login->loginHash === $comment['login_id']) {
					$output['user-owned'] = true;
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

		// Add comment date to output
		$output['date'] =(string) $comment_date;

		if (!empty ($comment['status'])) {
			$status = $comment['status'];

			// Check if comment has a status other than approved
			if ($status !== 'approved') {
				// If so, add comment status to output
				$output['status'] =(string) $status;

				// And append status to date
				$status_locale = mb_strtolower ($this->locale->text[$status . '-name']);
				$output['date'] .= ' (' . $status_locale . ')';
			}
		}

		// Add comment date as Unix timestamp to output
		$output['sort-date'] =(int) $micro_date;

		// Add comment body to output
		$output['body'] =(string) $comment['body'];

		return $output;
	}

	// Function for adding notices to output
	public function notice ($type, $key, &$last_date)
	{
		$output = array ();
		$output['title'] = $this->locale->text[$type . '-name'];
		$last_date++;

		if ($this->setup->iconMode !== 'none') {
			$output['avatar'] = $this->setup->httpImages . '/' . $type . '-icon.' . $this->setup->imageFormat;
		}

		$output['permalink'] = 'c' . str_replace ('-', 'r', $key);
		$output['notice'] = $this->locale->text[$type . '-note'];
		$output['notice-class'] = 'hashover-' . $type;
		$output['sort-date'] =(int) $last_date;

		return $output;
	}
}
