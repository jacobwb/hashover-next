<?php

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
	}
}

// Do some standard HashOver setup work
include ('standard-setup.php');
include ('javascript-setup.php');
include ('oop-setup.php');

try {
	// Mode is based on whether request is AJAX
	$mode = isset ($_POST['ajax']) ? 'javascript' : 'php';
	$data = null;

	// Instantiate HashOver class
	$hashover = new HashOver ($mode);
	$hashover->setup->setPageURL ('request');
	$hashover->setup->setPageTitle ('request');
	$hashover->initiate ();
	$hashover->finalize ();

	// Instantiate class for writing and editing comments
	$write_comments = new WriteComments (
		$hashover->setup,
		$hashover->readComments
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
		if (empty ($_POST[$action])) {
			continue;
		}

		switch ($action) {
			case 'login': {
				if ($hashover->setup->allowsLogin !== true) {
					$write_comments->postComment ();
					break;
				}

				$write_comments->login ();
				break;
			}

			case 'logout': {
				$write_comments->logout ();
				break;
			}

			case 'post': {
				$data = $write_comments->postComment ();
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
	if (isset ($_POST['ajax']) and is_array ($data)) {
		// Slit file into parts
		$key_parts = explode ('-', $data['file']);

		// Echo JSON array
		echo json_encode (array (
			'comment' => $hashover->commentParser->parse ($data['comment'], $data['file'], $key_parts),
			'count' => $hashover->getCommentCount ()
		));
	}
} catch (Exception $error) {
	$misc = new Misc (isset ($_POST['ajax']) ? 'json' : 'php');
	$misc->displayError ($error->getMessage ());
}
