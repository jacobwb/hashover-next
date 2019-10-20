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


class Login extends Secrets
{
	protected $setup;
	protected $cookies;
	protected $formData;
	protected $locale;
	protected $crypto;
	protected $loginMethod;
	protected $fieldNeeded;

	public $name;
	public $password;
	public $loginHash;
	public $email;
	public $website;
	public $userIsLoggedIn = false;
	public $userIsAdmin = false;

	public function __construct (Setup $setup)
	{
		// Store parameters as properties
		$this->setup = $setup;

		// Instantiate various classes
		$this->cookies = new Cookies ($setup, $this);
		$this->formData = new FormData ($setup, $this->cookies);
		$this->locale = new Locale ($setup);
		$this->crypto = new Crypto ();

		// Name of login method class to instantiate
		$login_class = 'HashOver\\' . $setup->loginMethod;

		// Instantiate login method class
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
		if (isset ($this->formData->data['name'])) {
			$this->loginMethod->name = $this->formData->data['name'];
		}

		// Attempt to get name
		$name = $this->setup->getRequest ('name');

		// Attempt to get password
		$password = $this->setup->getRequest ('password');

		// Set password
		if ($password !== false) {
			$this->loginMethod->password = $this->crypto->createHash ($password);
		} else {
			$this->loginMethod->password = '';
		}

		// Check if login hash is not set
		if ($this->loginHash === null) {
			// If so, generate a random password
			$random_password = bin2hex (openssl_random_pseudo_bytes (16));

			// And use user password or random password
			$password = $password ?: $random_password;
		}

		// Generate a RIPEMD-160 hash to indicate user login
		$this->loginMethod->loginHash = hash ('ripemd160', $name . $password);

		// Set e-mail address
		if (isset ($this->formData->data['email'])) {
			$this->loginMethod->email = $this->formData->data['email'];
		}

		// Set website URL
		if (isset ($this->formData->data['website'])) {
			$this->loginMethod->website = $this->formData->data['website'];
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
		// Run through login field options
		foreach ($this->setup->formFields as $field => $status) {
			// Check if current field is required and is empty
			if ($status === 'required' and empty ($this->$field)) {
				// If so, set cookies if request is not AJAX
				if ($this->formData->viaAJAX !== true) {
					$this->cookies->setFailedOn ($field, $this->formData->replyTo);
				}

				// And throw exception
				throw new \Exception (sprintf (
					$this->fieldNeeded, $this->locale->text[$field]
				));
			}
		}

		// Otherwise, fields are valid
		return true;
	}

	// Checks login requirements
	public function checkRequirements ($message)
	{
		// Check if a login is required
		if ($this->setup->requiresLogin === true) {
			// If so, throw exception if user is not logged in
			if ($this->userIsLoggedIn === false) {
				throw new \Exception ($message);
			}
		}

		// Otherwise, login requirements are met
		return true;
	}

	// Main login method
	public function setLogin ()
	{
		// Do nothing if login is disabled
		if ($this->setup->allowsLogin === false) {
			return;
		}

		// Do nothing if login method is disabled
		if ($this->loginMethod->enabled === false) {
			return;
		}

		// Otherwise, throw exception if a non-HashOver login is required
		$this->checkRequirements ('Normal login not allowed!');

		// Set login method credentials
		$this->setCredentials ();

		// Check if required fields have values
		$this->validateFields ();

		// Execute login method's setLogin
		$this->loginMethod->setLogin ();
	}

	// Admin login method
	public function setAdminLogin ()
	{
		// Set login method credentials
		$this->setCredentials ();

		// Execute login method's setLogin
		$this->loginMethod->setLogin ();
	}

	// Weak verification of an admin login
	public function isAdmin ()
	{
		// Create login hash
		$hash = hash ('ripemd160', $this->adminName . $this->adminPassword);

		// Check if the hashes match
		$match = ($this->loginHash === $hash);

		// And return match
		return $match;
	}

	// Logs user in as admin
	public function adminLogin ()
	{
		// Do nothing if login isn't admin
		if ($this->isAdmin () === false) {
			return;
		}

		// Set e-mail to admin e-mail address
		$this->loginMethod->email = $this->notificationEmail;

		// Set website to current domain
		$this->loginMethod->website = $this->setup->scheme . '://' . $this->setup->domain;

		// Set login method credentials
		$this->loginMethod->setCredentials ();

		// Update login credentials
		$this->updateCredentials ();

		// And login method's setLogin
		$this->loginMethod->setLogin ();
	}

	// Strict verification of an admin login
	public function verifyAdmin ($password = false)
	{
		// If no password was given use the current password
		$password = ($password === false) ? $this->password : $password;

		// Check if passwords match
		$match = $this->crypto->verifyHash ($this->adminPassword, $password);

		//  And return match
		return $match;
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
			if ($this->isAdmin () === true) {
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
