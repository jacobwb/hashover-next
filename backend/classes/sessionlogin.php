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


class SessionLogin
{
	protected $setup;
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
		} else {
			// If not, start session
			@session_start ();
		}
	}

	// Sets a session value
	protected function sessionSet ($name, $value = '')
	{
		// Add pseudo-namespacing prefix to session key
		$name = 'hashover-' . $name;

		// Set session value
		$_SESSION[$name] = $value;
	}

	// Get session value
	protected function sessionGet ($name, $trim = false)
	{
		// Add pseudo-namespacing prefix to session key
		$name = 'hashover-' . $name;

		// Check if session value exists
		if (!empty ($_SESSION[$name])) {
			// If so, store as value for cleaner code
			$value = $_SESSION[$name];

			// Strip escaping backslashes from session value
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

	// Set login credentials
	public function setCredentials ()
	{
		// Set login session values
		$this->sessionSet ('name', $this->name);
		$this->sessionSet ('password', $this->password);
		$this->sessionSet ('website', $this->website);

		// Check if an email was given
		if (!empty ($this->email)) {
			// If so, generate encrypted string / decryption keys from email
			$email = $this->crypto->encrypt ($this->email);

			// And set email and encryption session values
			$this->sessionSet ('email', $email['encrypted']);
			$this->sessionSet ('encryption', $email['keys']);
		} else {
			// If not, remove email and encryption session values
			$this->sessionSet ('email', '');
			$this->sessionSet ('encryption', '');
		}
	}

	// Get login credentials
	public function getCredentials ()
	{
		// Get user name via session value
		$this->name = $this->sessionGet ('name', true);

		// Get user password via session value
		$this->password = $this->sessionGet ('password', true);

		// Decrypt email session value
		$encrypted_email = $this->sessionGet ('email', true);
		$encryption = $this->sessionGet ('encryption', true);
		$email = $this->crypto->decrypt ($encrypted_email, $encryption);

		// Validate email address
		if (filter_var ($email, FILTER_VALIDATE_EMAIL)) {
			$this->email = trim ($email, " \r\n\t");
		}

		// Get user website via session value
		$this->website = $this->sessionGet ('website', true);

		// Get login hash via session value
		$this->loginHash = $this->sessionGet ('login', true);
	}

	// Main login method
	public function setLogin ()
	{
		// Set login session value
		$this->sessionSet ('login', $this->loginHash);
	}

	// Main logout method
	public function clearLogin ()
	{
		// Remove login session value
		$this->sessionSet ('login', '');
	}
}
