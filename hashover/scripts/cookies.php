<?php

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

class Cookies
{
	public $setup;
	public $secure = false;

	public function __construct (Setup $setup)
	{
		$this->setup = $setup;
		$this->domain = $setup->domain;

		// Remove port from domain
		if (mb_strpos ($this->domain, ':') !== false) {
			$this->domain = mb_substr ($this->domain, 0, strrpos ($this->domain, ':'));
		}

		// Transmit cookies over HTTPS if set so in Settings
		if ($setup->secureCookies === true) {
			$this->secure = !empty ($_SERVER['HTTPS']) ? true : false;
		}
	}

	// Set a cookie with expiration date
	public function set ($name, $value = '', $date = '')
	{
		// Use specific expiration date or the one in Settings
		$date = !empty ($date) ? $date : $this->setup->cookieExpiration;

		// Set the cookie if cookies are enabled
		if ($this->setup->setsCookies !== false) {
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
	}

	// Expire a cookie
	public function expireCookie ($cookie)
	{
		// Set its expiration date to 1 if it exists
		if (isset ($_COOKIE[$cookie])) {
			$this->set ($cookie, '', 1);
		}
	}

	// Get cookie value
	public function getValue ($name)
	{
		// Check if it exists
		if (!empty ($_COOKIE[$name])) {
			// Strip escaping backslashes from cookie value
			if (get_magic_quotes_gpc ()) {
				return stripslashes ($_COOKIE[$name]);
			}

			return $_COOKIE[$name];
		}

		// If not set return null
		return null;
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
	}
}
