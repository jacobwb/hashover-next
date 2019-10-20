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


class FormData
{
	protected $setup;
	protected $locale;
	protected $spamCheck;
	protected $cookies;

	protected $referer;

	public $data = array ();
	public $remoteAccess = false;
	public $file;
	public $replyTo;
	public $viaAJAX = false;

	// Fake inputs used as spam trap fields
	protected $trapFields = array (
		'summary',
		'age',
		'lastname',
		'address',
		'zip'
	);

	public function __construct (Setup $setup, Cookies $cookies)
	{
		// Store parameters as properties
		$this->setup = $setup;

		// Instantiate various classes
		$this->locale = new Locale ($setup);
		$this->spamCheck = new SpamCheck ($setup);
		$this->cookies = $cookies;

		// Use POST or GET based on whether request is for JSONP
		$request = isset ($_GET['jsonp']) ? $_GET : $_POST;

		// Get regular expression escaped admin path
		$admin_path = preg_quote ($this->setup->getHttpPath ('admin'), '/');

		// Attempt to get referer
		$referer = Misc::getArrayItem ($_SERVER, 'HTTP_REFERER');

		// Set status
		if (isset ($request['status'])) {
			$this->data['status'] = $this->forceUTF8 ($request['status']);
		}

		// Set name
		if (isset ($request['name'])) {
			$this->data['name'] = $this->forceUTF8 ($request['name']);
		}

		// Set password
		if (isset ($request['password'])) {
			$this->data['password'] = $this->forceUTF8 ($request['password']);
		}

		// Set e-mail address
		if (isset ($request['email'])) {
			$this->data['email'] = $this->forceUTF8 ($request['email']);
		}

		// Set website URL
		if (isset ($request['website'])) {
			$this->data['website'] = $this->forceUTF8 ($request['website']);
		}

		// Set comment
		if (isset ($request['comment'])) {
			$this->data['comment'] = $this->forceUTF8 ($request['comment']);
		}

		// Set indicator of remote access
		if (isset ($request['remote-access'])) {
			$this->remoteAccess = true;
		}

		// Get comment file
		if (isset ($request['file'])) {
			$this->file = $request['file'];
		}

		// Get reply comment file
		if (isset ($request['reply-to'])) {
			$this->replyTo = $request['reply-to'];
		}

		// Set indicator of AJAX requests
		if (isset ($request['ajax'])) {
			$this->viaAJAX = true;
		}

		// Check if we're coming from an admin page
		if (preg_match ('/' . $admin_path . '/i', $referer)) {
			// If so, use it as the kickback URL
			$this->referer = $_SERVER['HTTP_REFERER'];
		} else {
			// If not, check if posting from remote domain
			if ($this->remoteAccess === true) {
				// If so, use absolute path
				$this->referer = $setup->pageURL;
			} else {
				// If not, use relative path
				$this->referer = $setup->filePath;
			}

			// Add URL queries to kickback URL
			if (!empty ($setup->urlQueries)) {
				$this->referer .= '?' . $setup->urlQueries;
			}
		}
	}

	// Force a string to UTF-8 encoding and acceptable character range
	protected function forceUTF8 ($string)
	{
		$string = mb_convert_encoding ($string, 'UTF-16', 'UTF-8');
		$string = mb_convert_encoding ($string, 'UTF-8', 'UTF-16');
		$string = preg_replace ('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '?', $string);

		return trim ($string, " \r\n\t");
	}

	// Sets header to redirect user back to the previous page
	public function kickback ($anchor = 'comments')
	{
		header ('Location: ' . $this->referer . '#' . $anchor);
	}

	// Displays message to visitor, via AJAX or redirect
	public function displayMessage ($locale, $error = false)
	{
		// Message type as string
		$message_type = ($error === true) ? 'error' : 'message';

		// Get message text from locales if it exists
		if (!empty ($this->locale->text[$locale])) {
			$locale = $this->locale->text[$locale];
		}

		// Check if request is not over an AJAX request
		if ($this->viaAJAX !== true) {
			// If so, set cookie to specified message
			$this->cookies->set ($message_type, $locale);

			// And redirect user to previous page
			return $this->kickback ('hashover-form-section');
		}

		// Otherwise, display JSON for JavaScript frontend
		echo Misc::jsonData (array (
			'message' => $locale,
			'type' => $message_type
		));
	}

	// Checks user IP against spam databases
	public function checkForSpam ($mode = 'javascript')
	{
		// Treat JSON mode as JavaScript mode
		if ($mode === 'json') {
			$mode = 'javascript';
		}

		// Block user if they fill any trap fields
		foreach ($this->trapFields as $name) {
			if ($this->setup->getRequest ($name)) {
				throw new \Exception ('You are blocked!');
			}
		}

		// Check user's IP address against local blocklist
		if ($this->spamCheck->checkList () === true) {
			throw new \Exception ('You are blocked!');
		}

		// Whether to check for spam in current mode
		if ($this->setup->spamCheckModes === $mode
		    or $this->setup->spamCheckModes === 'both')
		{
			// Check user's IP address against local or remote database
			if ($this->spamCheck->{$this->setup->spamDatabase}() === true) {
				throw new \Exception ('You are blocked!');
			}

			// Throw any error message as exception
			if (!empty ($this->spamCheck->error)) {
				throw new \Exception ($this->spamCheck->error);
			}
		}
	}
}
