<?php namespace HashOver;

// Copyright (C) 2018 Jacob Barkdull
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


// Do some standard HashOver setup work
require (realpath ('../../../backend/standard-setup.php'));

// Autoload class files
spl_autoload_register (function ($uri) {
	$uri = str_replace ('\\', '/', strtolower ($uri));
	$class_name = basename ($uri);

	if (!@include (realpath ('../../../backend/classes/' . $class_name . '.php'))) {
		echo '"' . $class_name . '.php" file could not be included!';
		exit;
	}
});

// Instantiate HashOver class
$hashover = new \HashOver ();
$hashover->initiate ();
$hashover->finalize ();

// Instantiate FileWriter class
$data_files = new DataFiles ($hashover->setup);

// Exit if the user isn't logged in as admin
if ($hashover->login->userIsAdmin !== true) {
	$uri = $_SERVER['REQUEST_URI'];
	$uri_parts = explode ('?', $uri);

	if (basename ($uri_parts[0]) !== 'login') {
		header ('Location: ../login/?redirect=' . urlencode ($uri));
		exit;
	}
}

// Create logout hyperlink
$logout = new HTMLTag ('span', array (
	'class' => 'right',

	'children' => array (
		new HTMLTag ('a', array (
			'href' => '../login/?logout=true',
			'target' => '_parent',
			'innerHTML' => $hashover->locale->text['logout']
		))
	)
));

// Check if the form has been submitted
if (!empty ($_GET['status'])) {
	// Check if the form submission was successful
	if ($_GET['status'] === 'success') {
		// If so, create message element for success message
		$message = new HTMLTag ('div', array (
			'id' => 'message',
			'class' => 'success',

			'children' => array (
				new HTMLTag ('p', array (
					'innerHTML' => $hashover->locale->text['successful-save']
				), false)
			)
		));
	} else {
		// If so, create message element for error message
		$message = new HTMLTag ('div', array (
			'id' => 'message',
			'class' => 'error',

			'children' => array (
				// Main error message
				new HTMLTag ('p', array (
					'innerHTML' => $hashover->locale->text['failed-to-save']
				), false),

				// File permissions explanation
				new HTMLTag ('p', array (
					'innerHTML' => $hashover->locale->permissionsInfo ('config')
				), false)
			)
		));
	}

	// Set message as HTML
	$form_message = $message->asHTML ("\t\t");

} else {
	// If not, set the message as an empty string
	$form_message = '';
}
