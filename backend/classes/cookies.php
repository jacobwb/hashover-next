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


class Cookies
{
	protected $setup;
	protected $login;

	protected $domain;
	protected $enabled = true;
	protected $secure = false;

	public function __construct (Setup $setup, Login $login)
	{
		// Store parameters as properties
		$this->setup = $setup;
		$this->login = $login;

		// Store domain from setup locally
		$this->domain = $setup->domain;

		// Remove port from domain
		if (mb_strpos ($this->domain, ':') !== false) {
			$this->domain = mb_substr ($this->domain, 0, strrpos ($this->domain, ':'));
		}

		// Transmit cookies over HTTPS if set so in Settings
		if ($setup->secureCookies === true) {
			$this->secure = $setup->isHTTPS ();
		}
	}

	// Returns cookie expiration date after datetime modification
	protected function getCookieExpiration ()
	{
		// Get current datetime
		$datetime = new \DateTime ();

		// Return zero expiration date is session
		if ($this->setup->cookieExpiration === 'session') {
			return 0;
		}

		// Set date/time to configured expiration date/time
		$modified = @$datetime->modify ($this->setup->cookieExpiration);

		// Set datetime to next month if modification failed
		if ($modified === false) {
			$datetime->modify ('next month');
		}

		// Get datetime timestamp
		$timestamp = $datetime->getTimestamp ();

		// And return timestamp
		return $timestamp;
	}

	// Set a cookie with expiration date
	public function set ($name, $value = '', $date = false)
	{
		// Add pseudo-namespacing prefix to cookie name
		$name = 'hashover-' . $name;

		// Use specific expiration date or configured date
		$date = $date ?: $this->getCookieExpiration ();

		// Shorter variables for cleaner code
		$set_cookie = $this->setup->setsCookies;
		$is_admin = $this->login->userIsAdmin;

		// Set cookie only if they are enable or user is Admin
		if ($set_cookie === true or $is_admin === true) {
			setcookie ($name, $value, $date, '/', $this->domain, $this->secure, true);
		}
	}

	// Set cookies for remembering state login actions
	public function setFailedOn ($input, $reply_to, $replied = true)
	{
		// Set success status cookie
		$this->set ('failed-on', $input);

		// Set reply cookie
		if ($replied === true and !empty ($reply_to)) {
			$this->set ('replied', $reply_to);
		}

		// Set comment text cookie
		$comment = $this->setup->getRequest ('comment');

		// Check if comment is set
		if ($comment !== false) {
			$this->set ('comment', $comment);
		}
	}

	// Get cookie value
	public function getValue ($name, $trim = false)
	{
		// Add pseudo-namespacing prefix to cookie name
		$name = 'hashover-' . $name;

		// Check if cookie exists
		if (!empty ($_COOKIE[$name])) {
			// If so, store as value for cleaner code
			$value = $_COOKIE[$name];

			// Strip escaping backslashes from cookie value
			if (get_magic_quotes_gpc ()) {
				$value = stripslashes ($value);
			}

			// Return trimmed value if told to
			if ($trim === true) {
				$value = trim ($value, " \r\n\t");
			}

			// Otherwise, return value as-is
			return $value;
		}

		// If not, return null
		return null;
	}

	// Expire a cookie
	public function expireCookie ($cookie)
	{
		// Set its expiration date to 1 if it exists
		if ($this->getValue ($cookie) !== null) {
			$this->set ($cookie, '', 1);
		}
	}

	// Expire HashOver's default cookies
	public function clear ()
	{
		// Expire message cookie
		$this->expireCookie ('message');

		// Expire error cookie
		$this->expireCookie ('error');

		// Expire comment failure cookie
		$this->expireCookie ('failed-on');

		// Expire reply failure cookie
		$this->expireCookie ('replied');

		// Expire comment text cookie
		$this->expireCookie ('comment');
	}
}
