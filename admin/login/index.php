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


try {
	// Do some standard HashOver setup work
	require (realpath ('../../backend/standard-setup.php'));

	// View setup
	require (realpath ('../view-setup.php'));

	// Check if the user submitted login information
	if (!empty ($_POST['name']) and !empty ($_POST['password'])) {
		// If so, attempt to log them in
		$hashover->login->setAdminLogin ();

		// Check if user is admin
		if ($hashover->login->isAdmin () === true) {
			// If so, login as admin
			$hashover->login->adminLogin ();
		} else {
			// If not, logout
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

		// Get path to main admin page
		$admin_path = $hashover->setup->getHttpPath ('admin');

		// And redirect user to main admin page
		redirect ($admin_path . '/');
	}

	// Template data
	$template = array (
		'title'		=> $hashover->locale->text['login'],
		'sub-title'	=> $hashover->locale->text['admin-required'],
		'name'		=> $hashover->locale->text['name'],
		'password'	=> $hashover->locale->text['password'],
		'login'		=> $hashover->locale->text['login']
	);

	// Load and parse HTML template
	echo $hashover->templater->parseTemplate ('login.html', $template);

} catch (\Exception $error) {
	echo Misc::displayException ($error);
}
