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


// Check if request is for JSONP
if (isset ($_GET['jsonp'])) {
	// If so, setup HashOver for JavaScript
	require ('javascript-setup.php');
	$mode = 'javascript';
	$postget = $_GET;
} else {
	// If not, setup HashOver for JSON
	require ('json-setup.php');
	$mode = 'json';
	$postget = $_POST;
}

try {
	// Instantiate HashOver class
	$hashover = new \HashOver ($mode);
	$hashover->setup->setPageURL ('request');
	$hashover->setup->setPageTitle ('request');
	$hashover->setup->setThreadName ('request');
	$hashover->initiate ();
	$hashover->finalize ();

	// Instantiate class for writing and editing comments
	$write_comments = new WriteComments (
		$hashover->setup,
		$hashover->thread
	);

	// Various POST data actions
	$post_actions = array (
		'login',
		'logout',
		'post',
		'edit',
		'delete'
	);

	// Execute an action (write/edit/login/etc)
	foreach ($post_actions as $action) {
		if (empty ($postget[$action])) {
			continue;
		}

		switch ($action) {
			case 'login': {
				$write_comments->login ();
				break;
			}

			case 'logout': {
				$write_comments->logout ();
				break;
			}

			case 'post': {
				$data = $write_comments->postComment ();
				$hashover->defaultMetadata ();
				break;
			}

			case 'edit': {
				$data = $write_comments->editComment ();
				break;
			}

			case 'delete': {
				$write_comments->deleteComment ();
				break;
			}
		}

		break;
	}

	// Returns comment being saved as JSON
	if (isset ($postget['ajax']) and isset ($data) and is_array ($data)) {
		// Slit file into parts
		$file = $data['file'];
		$key_parts = explode ('-', $file);

		// Parsed comment data
		$comment = $data['comment'];
		$parsed = $hashover->commentParser->parse ($comment, $file, $key_parts);

		// Return JSON or JSONP function call
		echo Misc::jsonData (array (
			'comment' => $parsed,
			'count' => $hashover->getCommentCount ()
		));
	}
} catch (\Exception $error) {
	echo Misc::displayError ($error->getMessage (), $mode);
}
