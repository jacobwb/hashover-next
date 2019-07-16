<?php namespace HashOver;

// Copyright (C) 2018-2019 Jacob Barkdull
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
require (realpath ('../../backend/standard-setup.php'));

// Setup class autoloader
setup_autoloader ();

// Instantiate HashOver class
$hashover = new \HashOver ();

// Instantiate FileWriter class
$data_files = new DataFiles ($hashover->setup);

// Redirects the user back to where they came from
function redirect ($url = '')
{
	// Check if we're redirecting to a specific URL
	if (!empty ($url)) {
		// If so, use it
		header ('Location: ' . $url);
	} else {
		// If not, check if there is a redirect specified
		if (!empty ($_GET['redirect'])) {
			// If so, use it
			header ('Location: ' . $_GET['redirect']);
		} else {
			// If not, redirect to moderation
			header ('Location: ../moderation/');
		}
	}

	// Exit after redirect
	exit;
}

// Exit if the user isn't logged in as admin
if ($hashover->login->userIsAdmin !== true) {
	$uri = $_SERVER['REQUEST_URI'];
	$uri_parts = explode ('?', $uri);

	if (basename ($uri_parts[0]) !== 'login') {
		redirect ('../login/?redirect=' . urlencode ($uri));
	}
}

// Hyperlinks to admin pages
$pages = array (
	new HTMLTag ('a', array (
		'class' => 'view-link',
		'href' => '../moderation/',
		'innerHTML' => $hashover->locale->text['moderation']
	), false),

	new HTMLTag ('a', array (
		'class' => 'view-link',
		'href' => '../blocklist/',
		'innerHTML' => $hashover->locale->text['block-ip-addresses']
	), false),

	new HTMLTag ('a', array (
		'class' => 'view-link',
		'href' => '../url-queries/',
		'innerHTML' => $hashover->locale->text['filter-url-queries']
	), false),

	new HTMLTag ('a', array (
		'class' => 'view-link',
		'href' => '../settings/',
		'innerHTML' => $hashover->locale->text['settings']
	), false),

	new HTMLTag ('a', array (
		'class' => 'view-link',
		'href' => '../updates/',
		'innerHTML' => $hashover->locale->text['check-for-updates']
	), false),

	new HTMLTag ('a', array (
		'class' => 'view-link',
		'href' => '../documentation/',
		'innerHTML' => $hashover->locale->text['documentation']
	), false)
);

// Create navigation sidebar
$sidebar = new HTMLTag ('div', array (
	'id' => 'sidebar',
	'class' => 'special',

	'children' => array (
		new HTMLTag ('img', array (
			'id' => 'logo',
			'src' => '../../images/hashover-logo.png',
			'width' => '229',
			'height' => '229',
			'alt' => 'HashOver'
		), false, true),

		new HTMLTag ('div', array (
			'id' => 'navigation',
			'children' => $pages
		))
	)
));

// Create logout hyperlink
$logout = new HTMLTag ('a', array (
	'class' => 'plain-button right',
	'href' => '../login/?logout=true',
	'target' => '_parent',
	'innerHTML' => $hashover->locale->text['logout']
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
