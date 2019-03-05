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
	protected $setup;
	protected $login;
	protected $locale;
	protected $avatars;
	protected $cookies;

	protected $currentDate;
	protected $shortDateLocales;
	protected $todayLocale;
	protected $dateLocale;

	public function __construct (Setup $setup)
	{
		// Store parameters as properties
		$this->setup = $setup;

		// Instantiate various classes
		$this->login = new Login ($setup);
		$this->locale = new Locale ($setup);
		$this->avatars = new Avatars ($setup);
		$this->cookies = new Cookies ($setup);

		// Get current date without time
		$this->currentDate = new \DateTime (date('Y-m-d'));

		// Known short date interval locales
		$this->shortDateLocales = array (
			'y' => $this->locale->text['date-years'],
			'm' => $this->locale->text['date-months'],
			'd' => $this->locale->text['date-days']
		);

		// Short date when a comment was posted today
		$this->todayLocale = $this->locale->text['date-today'];

		// Full configurable date locale
		$this->dateLocale = $this->locale->text['date-time'];
	}

	// Get localized comment posting date and time from microtime
	protected function getDateTime ($micro_date)
	{
		// Get DateTime from microtime date
		$datetime = new \DateTime (date ('Y-m-d', $micro_date));

		// Get the difference between today's date and microtime date
		$interval = $datetime->diff ($this->currentDate);

		// Attempt to get a day, month, or year interval
		foreach ($this->shortDateLocales as $i => $locale) {
			// Check if a desired interval is non-zero
			if ($interval->$i > 0) {
				// If so, get plural key
				$plural = ($interval->$i !== 1) ? 1 : 0;

				// Inject interval into locale string
				$date = sprintf ($locale[$plural], $interval->$i);

				return $date;
			}
		}

		// Otherwise, get time from microtime
		$time = date ($this->setup->timeFormat, $micro_date);

		// Inject time into today locale string
		$date = sprintf ($this->todayLocale, $time);

		return $date;
	}

	// Parse comment files
	public function parse (array $comment, $key, $key_parts, $popular = false)
	{
		// Initial parsed comment date output
		$output = array ();

		// Get micro time of comment post date or zero
		$micro_date = strtotime (Misc::getArrayItem ($comment, 'date')) ?: 0;

		// Get localized full comment post date
		$full_date = date ($this->dateLocale, $micro_date);

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

		// Add name to output
		if (!empty ($comment['name'])) {
			$output['name'] = $comment['name'];
		}

		// Check if icons are enabled
		if ($this->setup->iconMode !== 'none') {
			// If so, check if icons are images
			if ($this->setup->iconMode === 'image') {
				// If so, get MD5 hash for Gravatar from comment
				$hash = Misc::getArrayItem ($comment, 'email_hash') ?: '';

				// Add Gravatar URL to output
				$output['avatar'] = $this->avatars->getGravatar ($hash);
			} else {
				// If not, use comment permalink number
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
			$output['likes'] = (int)($comment['likes']);
		}

		// If enabled, add number of dislikes to output
		if ($this->setup->allowsDislikes === true) {
			if (!empty ($comment['dislikes'])) {
				$output['dislikes'] = (int)($comment['dislikes']);
			}
		}

		// Check if the user is logged in
		if ($this->login->userIsLoggedIn === true and !empty ($comment['login_id'])) {
			// If so, check if this comment belongs to logged in user
			if ($this->login->loginHash === $comment['login_id']) {
				// If so, set user comment indictor to true
				$output['user-owned'] = true;

				// And set editable indictor if comment has a password
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

		// Set liked/disliked indictor
		switch ($like_cookie) {
			// Comment was liked
			case 'liked': {
				$output['liked'] = true;
				break;
			}

			// Comment was disliked
			case 'disliked': {
				// Only set indictor if disliked are enabled
				if ($this->setup->allowsDislikes === true) {
					$output['disliked'] = true;
				}

				break;
			}
		}

		// Check if short dates are enabled
		if ($this->setup->usesShortDates === true) {
			// If so, get localized short date
			$comment_date = $this->getDateTime ($micro_date);
		} else {
			// If not, use full localized date and time
			$comment_date = $full_date;
		}

		// Add comment date to output
		$output['date'] = (string)($comment_date);

		// Set full date and time
		$output['full-date'] = (string)($full_date);

		// Check if we have a status
		if (!empty ($comment['status'])) {
			// If so, get comment status
			$status = $comment['status'];

			// Check if comment has a status other than approved
			if ($status !== 'approved') {
				// If so, add comment status to output
				$output['status'] = (string)($status);

				// And add status text to output
				$output['status-text'] = mb_strtolower ($this->locale->text[$status . '-name']);
			}
		}

		// Add comment date as Unix timestamp to output
		$output['sort-date'] = (int)($micro_date);

		// Add comment body to output
		$output['body'] = (string)($comment['body']);

		return $output;
	}

	// Function for adding notices to output
	public function notice ($type, $key, &$last_date)
	{
		$output = array ();
		$output['title'] = $this->locale->text[$type . '-name'];
		$last_date++;

		if ($this->setup->iconMode !== 'none') {
			$output['avatar'] = $this->setup->getImagePath ($type . '-icon');
		}

		$output['permalink'] = 'c' . str_replace ('-', 'r', $key);
		$output['notice'] = $this->locale->text[$type . '-note'];
		$output['notice-class'] = 'hashover-' . $type;
		$output['sort-date'] = (int)($last_date);

		return $output;
	}
}
