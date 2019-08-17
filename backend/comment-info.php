<?php namespace HashOver;

// Copyright (C) 2019 Jacob Barkdull
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


// Check if request is for JSONP
if (isset ($_GET['jsonp'])) {
	// If so, setup HashOver for JavaScript
	require ('javascript-setup.php');
} else {
	// If not, setup HashOver for JSON
	require ('json-setup.php');
}

// Returns comment data or authentication error
function get_json_response ($hashover)
{
	// Initial JSON data
	$data = array ();

	// Get comment from POST/GET data
	$key = $hashover->setup->getRequest ('comment', null);

	// Return error if we're missing necessary post data
	if ($key === null) {
		return array ('error' => 'Missing comment file.');
	}

	// Sanitize file path
	$file = str_replace ('../', '', $key);

	// Store references to some long variables
	$thread = $hashover->setup->threadName;

	// Read comment
	$comment = $hashover->thread->data->read ($file, $thread);

	// Return error message if failed to read comment
	if ($comment === false) {
		return array ('error' => 'Failed to read file: "' . $file . '"');
	}

	// User is not authorized by default
	$authorized = false;

	// Check if user is logged in
	if ($hashover->login->userIsLoggedIn === true) {
		// If so, user is authorized if they own the comment
		if (!empty ($comment['login_id'])) {
			if ($hashover->login->loginHash === $comment['login_id']) {
				$authorized = true;
			}
		}

		// Or, user is authorized if they are Admin
		if ($hashover->login->isAdmin () === true) {
			$authorized = true;
		}
	}

	// Check if user is authorized to receive comment data
	if ($authorized === true) {
		// If so, instantiate Crypto class
		$crypto = new Crypto ();

		// Specific comment data to return
		$data = array (
			// Commenter name
			'name' => Misc::getArrayItem ($comment, 'name') ?: '',

			// Commenter website URL
			'website' => Misc::getArrayItem ($comment, 'website') ?: '',

			// Commenter's comment
			'body' => Misc::getArrayItem ($comment, 'body') ?: ''
		);

		// Add decrypted email address to data if an email exists
		if (!empty ($comment['email']) and !empty ($comment['encryption'])) {
			$data ['email'] = $crypto->decrypt ($comment['email'], $comment['encryption']);
		}

		// And return comment data
		return $data;
	}

	// Otherwise, wait 5 seconds
	sleep (5);

	// And return authentication error
	return array (
		'error' => $hashover->locale->text['post-fail']
	);
}

try {
	// Instanciate HashOver class
	$hashover = new \HashOver ('json');

	// Throw exception if requested by remote server
	$hashover->setup->refererCheck ();

	// Set page URL from POST/GET data
	$hashover->setup->setPageURL ('request');

	// Initiate comment processing
	$hashover->initiate ();

	// Get JSON response
	$data = get_json_response ($hashover);

	// Return JSON or JSONP function call
	echo Misc::jsonData ($data);

} catch (\Exception $error) {
	echo Misc::displayException ($error, 'json');
}
