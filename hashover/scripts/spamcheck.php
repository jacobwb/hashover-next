<?php

// Copyright (C) 2010-2015 Jacob Barkdull
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

class SpamCheck
{
	public $error;

	public function __construct ()
	{
		// Blocklist file
		$this->blocklist = '../blocklist.txt';
	}

	// Return false if visitor's IP address is in block list file
	public function checkList ()
	{
		// Check if blocklist file exists
		if (file_exists ($this->blocklist)) {
			// Convert blocklist into array
			$ips = explode (PHP_EOL, file_get_contents ($this->blocklist));

			// Run through each IP comparing them to user's IP
			for ($ip = count ($ips) - 1; $ip >= 0; $ip--) {
				// Return true if they match
				if ($ips[$ip] === $_SERVER['REMOTE_ADDR']) {
					return true;
				}
			}
		}

		return false;
	}

	// Stop Forum Spam remote spam database check
	public function remote ()
	{
		// Stop Forum Spam API URL
		$url = 'http://www.stopforumspam.com/api?ip=' . $_SERVER['REMOTE_ADDR'] . '&f=json';

		// Check for cURL
		if (function_exists ('curl_init')) {
			// Initiate cURL
			$ch = curl_init ();
			$options = array (CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true);
			curl_setopt_array ($ch, $options);

			// Fetch response from Stop Forum Spam database check
			$output = curl_exec ($ch);

			// Close cURL
			curl_close ($ch);

			// Parse response as JSON
			$read_json = @json_decode ($output, true);
		} else {
			// Check if opening files via URL is enabled
			if (ini_get ('allow_url_fopen')) {
				// Open file via URL and parse response as JSON
				$read_json = @json_decode (file_get_contents ($url), true);
			}
		}

		// Set error message and return true if spam check failed
		if (!isset ($read_json['success'])) {
			$this->error = 'Spam check failed!';
			return true;
		}

		// Set error message and return true if response was invalid
		if (!isset ($read_json['ip']['appears'])) {
			$this->error = 'Spam check received invalid JSON!';
			return true;
		}

		// If spam check was successful
		if ($read_json['success'] === 1) {
			// Return true if user's IP appears in the database
			if ($read_json['ip']['appears'] === 1) {
				return true;
			}
		}

		return false;
	}

	// Local CSV spam database check
	public function local ()
	{
		// CSV spam database file
		$spam_database = '../spam-database.csv';

		// Check if CSV spam database file exists
		if (file_exists ($spam_database)) {
			// If so, convert CSV database into array
			$ips = explode (',', file_get_contents ($spam_database));

			// Run through each IP comparing them to user's IP
			for ($ip = count ($ips) - 1; $ip >= 0; $ip--) {
				// Return true if they match
				if ($ips[$ip] === $_SERVER['REMOTE_ADDR']) {
					return true;
				}
			}
		} else {
			// If not, set error message
			$this->error = 'No local database found!';
		}

		return false;
	}
}
