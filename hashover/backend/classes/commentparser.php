<?php namespace HashOver;

// Copyright (C) 2010-2018 Jacob Barkdull
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


// Parse comments and create deleted comment note
class CommentParser
{
	public $setup;
	public $login;
	public $locale;
	public $avatars;
	public $cookies;

	protected $dateIntervalLocales;
	protected $todayLocale;
	protected $currentDateTime;

	public function __construct (Setup $setup)
	{
		$this->setup = $setup;
		$this->login = new Login ($setup);
		$this->locale = new Locale ($setup);
		$this->avatars = new Avatars ($setup);
		$this->cookies = new Cookies ($setup);
		$this->currentDateTime = new \DateTime (date('Y-m-d'));

		$this->dateIntervalLocales = array (
			'y' => $this->locale->text['date-years'],
			'm' => $this->locale->text['date-months'],
			'd' => $this->locale->text['date-days']
		);

		$this->todayLocale = $this->locale->text['date-today'];
		$this->dateTimeLocale = $this->locale->text['date-time'];
	}

	// Parse comment files
	public function parse (array $comment, $key, $key_parts, $popular = false)
	{
		$micro_date = 0;
		$output = array ();

		// Get micro time of comment post date
		if (!empty ($comment['date'])) {
			$micro_date = strtotime ($comment['date']);
		}

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

		// Check if short dates are enabled
		if ($this->setup->usesShortDates === true) {
			// If so, get DateTime from comment
			$comment_datetime = new \DateTime (date ('Y-m-d', $micro_date));

			// Get the difference between today's date and the comment post date
			$interval = $comment_datetime->diff ($this->currentDateTime);

			// And attempt to get a day, month, or year interval
			foreach ($this->dateIntervalLocales as $i => $date_locale) {
				if ($interval->$i > 0) {
					$date_plural = ($interval->$i !== 1);
					$comment_date = sprintf ($date_locale[$date_plural], $interval->$i);
					break;
				}
			}

			// Otherwise, use today locale
			if (empty ($comment_date)) {
				$comment_time = date ($this->setup->timeFormat, $micro_date);
				$comment_date = sprintf ($this->todayLocale, $comment_time);
			}
		} else {
			// If not, use configurable date and time locale
			$comment_date = date ($this->dateTimeLocale, $micro_date);
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
				$output['avatar'] = end ($key_parts);
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
		}

		// If enabled, add number of dislikes to output
		if ($this->setup->allowsDislikes === true) {
			if (!empty ($comment['dislikes'])) {
				$output['dislikes'] =(int) $comment['dislikes'];
			}
		}

		// Check if the user is logged in
		if ($this->login->userIsLoggedIn === true and !empty ($comment['login_id'])) {
			// If so, check this comment belongs to logged in user
			if ($this->login->loginHash === $comment['login_id']) {
				$output['user-owned'] = true;

				// Check if the comment is editable
				if (!empty ($comment['password'])) {
					$output['editable'] = true;
				}
			}
		}

		// Admin is allowed to edit every comment
		if ($this->login->userIsAdmin === true) {
			$output['editable'] = true;
		}

		// Create like cookie hash
		$like_hash = md5 ($this->setup->domain . $this->setup->threadName . '/' . $key);

		// Get like cookie
		$like_cookie = $this->cookies->getValue ($like_hash);

		// Check if comment has been liked or disliked
		if ($like_cookie !== null) {
			switch ($like_cookie) {
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

				// And add status text to output
				$output['status-text'] = mb_strtolower ($this->locale->text[$status . '-name']);
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
