<?php namespace HashOver;

// Copyright (C) 2015-2017 Jacob Barkdull
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

class DefaultLogin
{
	public $setup;
	public $cookies;
	public $locale;
	public $name;
	public $password;
	public $loginHash;
	public $email;
	public $website;

	public function __construct (Setup $setup, Cookies $cookies, Locale $locale)
	{
		$this->setup = $setup;
		$this->cookies = $cookies;
		$this->locale = $locale;

		// Disable login is cookies are disabled
		if ($setup->setsCookies === false) {
			$setup->allowsLogin = false;
			$setup->syncSettings ();
		}
	}

	// Set login credentials
	public function setCredentials ()
	{
		// Generate encrypted string / decryption key from e-mail
		$encryption_keys = $this->setup->encryption->encrypt ($this->email);

		// Set login cookies
		$this->cookies->set ('name', $this->name);
		$this->cookies->set ('password', $this->password);
		$this->cookies->set ('email', $encryption_keys['encrypted']);
		$this->cookies->set ('encryption', $encryption_keys['keys']);
		$this->cookies->set ('website', $this->website);
	}

	// Get login credentials
	public function getCredentials ()
	{
		// Set user name via cookie
		$this->name = trim ($this->cookies->getValue ('name'), " \r\n\t");

		// Set user password via cookie
		$this->password = trim ($this->cookies->getValue ('password'), " \r\n\t");

		// Decrypt email cookie
		$encrypted_email = trim ($this->cookies->getValue ('email'), " \r\n\t");
		$encryption_keys = trim ($this->cookies->getValue ('encryption'), " \r\n\t");
		$decrypted_email = $this->setup->encryption->decrypt ($encrypted_email, $encryption_keys);

		// Validate e-mail address
		if (filter_var ($decrypted_email, FILTER_VALIDATE_EMAIL)) {
			$this->email = trim ($decrypted_email, " \r\n\t");
		}

		// Set user website via cookie
		$this->website = trim ($this->cookies->getValue ('website'), " \r\n\t");

		// Set login hash via cookie
		$this->loginHash = trim ($this->cookies->getValue ('hashover-login'), " \r\n\t");
	}

	// Main login method
	public function setLogin ()
	{
		// Set login cookie
		$this->cookies->set ('hashover-login', $this->loginHash);
	}

	// Main logout method
	public function clearLogin ()
	{
		// Expire login cookie
		$this->cookies->expireCookie ('hashover-login');
	}
}
