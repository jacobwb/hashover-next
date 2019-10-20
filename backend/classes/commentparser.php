<?php namespace HashOver;

// Copyright (C) 2010-2019 Jacob Barkdull
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
	protected $cookies;
	protected $locale;
	protected $avatars;

	protected $timeModify;
	protected $currentDate;
	protected $shortDateLocales;
	protected $todayLocale;
	protected $date;
	protected $time;

	public function __construct (Setup $setup)
	{
		// Store parameters as properties
		$this->setup = $setup;

		// Instantiate various classes
		$this->login = new Login ($setup);
		$this->cookies = new Cookies ($setup, $this->login);
		$this->locale = new Locale ($setup);
		$this->avatars = new Avatars ($setup);

		// Get current time
		$current_time = new \DateTime ();

		// Get 24-hour time from client
		$client_time = new \DateTime ($setup->getRequest ('time'));

		// Server-side hours and minutes as integers
		$current_hours = (int)($current_time->format ('H'));
		$current_mins = (int)($current_time->format ('i'));

		// Client hours and minutes as integers
		$client_hours = (int)($client_time->format ('H'));
		$client_mins = (int)($client_time->format ('i'));

		// Hours and minutes to adjust posting time by
		$this->timeModify = sprintf (
			'%+d hours %+d minutes',
			$client_hours - $current_hours,
			$client_mins - $current_mins
		);

		// Get current date without time
		$this->currentDate = new \DateTime (date ('Y-m-d'));

		// Known short date interval locales
		$this->shortDateLocales = array (
			'y' => 'date-year',
			'm' => 'date-month',
			'd' => 'date-day'
		);

		// Short date when a comment was posted today
		$this->todayLocale = $this->locale->text['date-today'];

		// Get date formatter for comment post date
		$this->date = $this->getDateFormatter ($setup->datePattern);

		// Get date formatter for comment post time
		$this->time = $this->getDateFormatter ($setup->timePattern);
	}

	// Get date and time formatter
	protected function getDateFormatter ($pattern)
	{
		// Instantiate International Date Formatter
		return \IntlDateFormatter::create (
			// Set language as configured
			$this->setup->language,

			// We're setting our own format
			\IntlDateFormatter::NONE,
			\IntlDateFormatter::NONE,

			// Set timezone as configured
			$this->setup->serverTimezone,

			// Use Gregorian calender
			\IntlDateFormatter::GREGORIAN,

			// Set date format
			$pattern
		);
	}

	// Get short form date and time
	protected function getDateTime (\DateTime $dt, $time)
	{
		// Remove time from datetime
		$datetime = new \DateTime ($dt->format ('Y-m-d'));

		// Get difference between today's date and timeless date
		$interval = $datetime->diff ($this->currentDate);

		// Attempt to get a day, month, or year interval
		foreach ($this->shortDateLocales as $i => $key) {
			// Skip intervals that are zero
			if ($interval->$i <= 0) {
				continue;
			}

			// Otherwise, check if interval is more than one
			if ($interval->$i !== 1) {
				// If so, use plural locale string
				$locale = $this->locale->text[$key . 's'];
			} else {
				// If not, use singlur locale string
				$locale = $this->locale->text[$key];
			}

			// Inject interval into locale string
			$date = sprintf ($locale, $interval->$i);

			// And return short date
			return $date;
		}

		// Otherwise, inject time into today locale string
		$date = sprintf ($this->todayLocale, $time);

		return $date;
	}

	// Parse comment files
	public function parse (array $comment, $key, $key_parts, $popular = false)
	{
		// Initial parsed comment data output
		$output = array ();

		// Get post date as-is
		$date = Misc::getArrayItem ($comment, 'date');

		// Get comment post datetime
		$post_date = new \DateTime ($date ?: date ('Y-m-d', 0));

		// Adjust post date to client timezone if enabled
		if ($this->setup->usesUserTimezone === true) {
			$post_date->modify ($this->timeModify);
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

		// Get micro time of comment post date
		$timestamp = $post_date->getTimestamp ();

		// Get localized full comment post date
		$full_date = $this->date->format ($timestamp);

		// Get localized full comment post time
		$post_time = $this->time->format ($timestamp);

		// Check if short dates are enabled
		if ($this->setup->usesShortDates === true) {
			// If so, get localized short date
			$comment_date = $this->getDateTime ($post_date, $post_time);
		} else {
			// If not, use full localized date and time
			$comment_date = $full_date;
		}

		// Check if we have a status
		if (!empty ($comment['status'])) {
			// If so, get comment status
			$status = $comment['status'];

			// Check if comment has a status other than approved
			if ($status !== 'approved') {
				// If so, add comment status to output
				$output['status'] = (string)($status);

				// Get status locale
				$status_locale = $this->locale->text[$status . '-name'];

				// And add status text to output
				$output['status-text'] = mb_strtolower ($status_locale);
			}
		}

		// Add comment date to output
		$output['date'] = $comment_date;

		// Set full date and time
		$output['date-time'] = $full_date . ' - ' . $post_time;

		// Add comment date as Unix timestamp to output
		$output['timestamp'] = $timestamp;

		// Add comment body to output
		$output['body'] = $comment['body'];

		// And return parsed comment data
		return $output;
	}

	// Function for adding notices to output
	public function notice ($type, $key, &$last_date)
	{
		// Initial notice data output
		$output = array ();

		// Set notice name as comment title
		$output['title'] = $this->locale->text[$type . '-name'];

		// Increase fallback last comment date
		$last_date++;

		// Add notice icon if icons are enabled
		if ($this->setup->iconMode !== 'none') {
			$output['avatar'] = $this->setup->getImagePath ($type . '-icon');
		}

		// Add notice permalink to notice data
		$output['permalink'] = 'c' . str_replace ('-', 'r', $key);

		// Add notice text to notice data
		$output['notice'] = $this->locale->text[$type . '-note'];

		// Add notice class to notice data
		$output['notice-class'] = 'hashover-' . $type;

		// Add last comment date as notice timestamp
		$output['timestamp'] = (int)($last_date);

		// And return notice data
		return $output;
	}
}
