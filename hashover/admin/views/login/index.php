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

try {
	// View setup
	require (realpath ('../view-setup.php'));

	// Check if the user submitted login information
	if (!empty ($_POST['name']) and !empty ($_POST['password'])) {
		// If so, attempt to log them in
		$hashover->login->setLogin ();

		// Check if the user is not admin
		if ($hashover->login->adminLogin () === false) {
			// If so, logout
			$hashover->login->clearLogin ();

			// Sleep 5 seconds
			sleep (5);
		}

		// And redirect user to desired view
		redirect ();
	}

	// Check if we're logging out
	if (isset ($_GET['logout'])) {
		// If so, attempt to log the user out
		$hashover->login->clearLogin ();

		// And redirect user to main admin page
		redirect ($hashover->setup->getHttpPath ('admin'));
	}

	// Template data
	$template = array (
		'title'		=> $hashover->locale->text['login'],
		'logout'	=> $logout->asHTML ("\t\t\t"),
		'sub-title'	=> $hashover->locale->text['admin-required'],
		'name'		=> $hashover->locale->text['name'],
		'password'	=> $hashover->locale->text['password'],
		'email'		=> $hashover->locale->optionalize ('email'),
		'website'	=> $hashover->locale->optionalize ('website'),
		'login'		=> $hashover->locale->text['login']
	);

	// Load and parse HTML template
	echo $hashover->templater->parseTemplate ('login.html', $template);

} catch (\Exception $error) {
	$misc = new Misc ('php');
	$message = $error->getMessage ();
	$misc->displayError ($message);
}
