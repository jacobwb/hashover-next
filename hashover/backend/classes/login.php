<?php namespace HashOver;

// Copyright (C) 2015-2018 Jacob Barkdull
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


class Login extends PostData
{
	public $setup;
	public $encryption;
	public $cookies;
	public $locale;
	public $loginMethod;
	public $fieldNeeded;
	public $name = '';
	public $password = '';
	public $loginHash = '';
	public $email = '';
	public $website = '';
	public $userIsLoggedIn = false;
	public $userIsAdmin = false;

	public function __construct (Setup $setup)
	{
		parent::__construct ();

		$this->setup = $setup;
		$this->encryption = $setup->encryption;
		$this->cookies = new Cookies ($setup);
		$this->locale = new Locale ($setup);

		// Instantiate login method class
		$login_class = 'HashOver\\' . $this->setup->loginMethod;
		$this->loginMethod = new $login_class ($setup, $this->cookies, $this->locale);

		// Error message to display to the user
		$this->fieldNeeded = $this->locale->text['field-needed'];

		// Check if user is logged in
		$this->getLogin ();
	}

	// Prepares login credentials
	public function prepareCredentials ()
	{
		// Set name
		if (isset ($this->postData['name'])) {
			$this->loginMethod->name = $this->postData['name'];
		}

		// Attempt to get name
		$name = $this->setup->getRequest ('name');

		// Attempt to get password
		$password = $this->setup->getRequest ('password', null);

		// Set password
		if ($password !== null) {
			$this->loginMethod->password = $this->encryption->createHash ($password);
		} else {
			$this->loginMethod->password = '';
		}

		// Check that login hash cookie is not set
		if ($this->cookies->getValue ('login') === null) {
			// If so, generate a random password
			$random_password = bin2hex (openssl_random_pseudo_bytes (16));

			// And use user password or random password
			$password = $password ? $password : $random_password;
		}

		// Generate a RIPEMD-160 hash to indicate user login
		$this->loginMethod->loginHash = hash ('ripemd160', $name . $password);

		// Set e-mail address
		if (isset ($this->postData['email'])) {
			$this->loginMethod->email = $this->postData['email'];
		}

		// Set website URL
		if (isset ($this->postData['website'])) {
			$this->loginMethod->website = $this->postData['website'];
		}
	}

	// Update login credentials
	public function updateCredentials ()
	{
		$this->name = $this->loginMethod->name;
		$this->password = $this->loginMethod->password;
		$this->loginHash = $this->loginMethod->loginHash;
		$this->email = $this->loginMethod->email;
		$this->website = $this->loginMethod->website;

		// Validate e-mail address
		if (!empty ($this->email)) {
			if (!filter_var ($this->email, FILTER_VALIDATE_EMAIL)) {
				$this->email = '';
			}
		}

		// Prepend "http://" to website URL if missing
		if (!empty ($this->website)) {
			if (!preg_match ('/htt(p|ps):\/\//i', $this->website)) {
				$this->website = 'http://' . $this->website;
			}
		}
	}

	// Set login credentials
	public function setCredentials ()
	{
		// Prepare login credentials
		$this->prepareCredentials ();

		// Set login method credentials
		$this->loginMethod->setCredentials ();

		// Update login credentials
		$this->updateCredentials ();
	}

	// Get login method credentials
	public function getCredentials ()
	{
		$this->loginMethod->getCredentials ();
		$this->updateCredentials ();
	}

	// Checks if required fields have values
	public function validateFields ()
	{
		// Check required fields, throw error if any are empty
		foreach ($this->setup->fieldOptions as $field => $status) {
			if ($status === 'required' and empty ($this->$field)) {
				// Don't set cookies if the request is via AJAX
				if ($this->viaAJAX !== true) {
					$this->cookies->setFailedOn ($field, $this->replyTo);
				}

				throw new \Exception (sprintf (
					$this->fieldNeeded, $this->locale->text[$field]
				));
			}
		}

		return true;
	}

	// Main login method
	public function setLogin ()
	{
		// Set login method credentials
		$this->setCredentials ();

		// Check if required fields have values
		$this->validateFields ();

		// Execute login method's setLogin
		if ($this->loginMethod->enabled === true) {
			$this->loginMethod->setLogin ();
		}
	}

	// Check if user is logged in
	public function getLogin ()
	{
		// Get login method credentials
		$this->getCredentials ();

		// Check if user is logged in
		if (!empty ($this->loginHash)) {
			// If so, set login indicator
			$this->userIsLoggedIn = true;

			// Check if user is logged in as admin
			if ($this->setup->adminLogin ($this->loginHash) === true) {
				$this->userIsAdmin = true;
			}
		}
	}

	// Main logout method
	public function clearLogin ()
	{
		$this->loginMethod->clearLogin ();
	}
}
