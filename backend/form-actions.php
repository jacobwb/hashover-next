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


// Check if request is for JSONP
if (isset ($_GET['jsonp'])) {
	// If so, setup HashOver for JavaScript
	require ('javascript-setup.php');

	// Set mode as JavaScript
	$mode = 'javascript';

	// Get data from GET request
	$request = $_GET;
} else {
	// If not, setup HashOver for JSON
	require ('json-setup.php');

	// Set mode as JSON
	$mode = 'json';

	// Get data from POST request
	$request = $_POST;
}

// Handles posted comment data
function displayJson (\HashOver $hashover, $data)
{
	// Otherwise, slit file into parts
	$key_parts = explode ('-', $data['file']);

	// Parse comment data
	$parsed = $hashover->commentParser->parse (
		$data['comment'], $data['file'], $key_parts
	);

	// Return JSON or JSONP function call
	return Misc::jsonData (array (
		// Current comment count
		'count' => $hashover->getCommentCount (),

		// Parsed comment data
		'comment' => $parsed
	));
}

try {
	// Instantiate HashOver class
	$hashover = new \HashOver ($mode);

	// Throw exception if requested by remote server
	$hashover->setup->refererCheck ();

	// Set page URL from POST/GET data
	$hashover->setup->setPageURL ('request');

	// Set page title from POST/GET data
	$hashover->setup->setPageTitle ('request');

	// Set thread name from POST/GET data
	$hashover->setup->setThreadName ('request');

	// Initiate and finalize comment processing
	$hashover->initiate ();
	$hashover->finalize ();

	// Instantiate class for writing and editing comments
	$write_comments = new WriteComments (
		$hashover->setup,
		$hashover->thread
	);

	// Run through POST/GET data
	foreach (array_keys ($request) as $action) {
		// Handle user login
		if ($action === 'login') {
			$write_comments->login ();
			break;
		}

		// Handle user logout
		if ($action === 'logout') {
			$write_comments->logout ();
			break;
		}

		// Handle new comment post
		if ($action === 'post') {
			// Check IP address for spam
			$write_comments->checkForSpam ($mode);

			// Save posted comment
			$data = $write_comments->postComment ();

			// Create/update page metadata
			$hashover->defaultMetadata ();

			// Display JSON for AJAX requests
			if (isset ($request['ajax']) and is_array ($data)) {
				echo displayJson ($hashover, $data);
			}

			break;
		}

		// Handle comment edit
		if ($action === 'edit') {
			// Check IP address for spam
			$write_comments->checkForSpam ($mode);

			// Save edited comment
			$data = $write_comments->editComment ();

			// Display JSON for AJAX requests
			if (isset ($request['ajax']) and is_array ($data)) {
				echo displayJson ($hashover, $data);
			}

			break;
		}

		// Handle comment deletion
		if ($action === 'delete') {
			// Check IP address for spam
			$write_comments->checkForSpam ($mode);

			// Delete comment
			$write_comments->deleteComment ();

			break;
		}
	}
} catch (\Exception $error) {
	echo Misc::displayException ($error, $mode);
}
