<?php namespace HashOver;

// Copyright (C) 2015-2019 Jacob Barkdull
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


class DefaultLogin
{
	protected $setup;
	protected $cookies;
	protected $locale;
	protected $crypto;

	public $enabled = true;
	public $name;
	public $password;
	public $loginHash;
	public $email;
	public $website;

	public function __construct (Setup $setup, Cookies $cookies, Locale $locale)
	{
		// Store parameters as properties
		$this->setup = $setup;
		$this->cookies = $cookies;
		$this->locale = $locale;

		// Instantiate Crypto class
		$this->crypto = new Crypto ();

		// Check if cookies are disabled
		if ($setup->setsCookies === false) {
			// If so, disable login method
			$this->enabled = false;

			// Disable login setting
			$setup->allowsLogin = false;
			$setup->syncSettings ();
		}
	}

	// Set login credentials
	public function setCredentials ()
	{
		// Set login cookies
		$this->cookies->set ('name', $this->name);
		$this->cookies->set ('password', $this->password);
		$this->cookies->set ('website', $this->website);

		// Check if an email was given
		if (!empty ($this->email)) {
			// If so, generate encrypted string / decryption keys from email
			$email = $this->crypto->encrypt ($this->email);

			// And set email and encryption cookies
			$this->cookies->set ('email', $email['encrypted']);
			$this->cookies->set ('encryption', $email['keys']);
		} else {
			// If not, expire email and encryption cookies
			$this->cookies->expireCookie ('email');
			$this->cookies->expireCookie ('encryption');
		}
	}

	// Get login credentials
	public function getCredentials ()
	{
		// Get user name via cookie
		$this->name = $this->cookies->getValue ('name', true);

		// Get user password via cookie
		$this->password = $this->cookies->getValue ('password', true);

		// Decrypt email cookie
		$encrypted_email = $this->cookies->getValue ('email', true);
		$encryption = $this->cookies->getValue ('encryption', true);
		$email = $this->crypto->decrypt ($encrypted_email, $encryption);

		// Validate email address
		if (filter_var ($email, FILTER_VALIDATE_EMAIL)) {
			$this->email = trim ($email, " \r\n\t");
		}

		// Get user website via cookie
		$this->website = $this->cookies->getValue ('website', true);

		// Get login hash via cookie
		$this->loginHash = $this->cookies->getValue ('login', true);
	}

	// Main login method
	public function setLogin ()
	{
		// Set login cookie
		$this->cookies->set ('login', $this->loginHash);
	}

	// Main logout method
	public function clearLogin ()
	{
		// Expire login cookie
		$this->cookies->expireCookie ('login');
	}
}
