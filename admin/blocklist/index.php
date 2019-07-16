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

	// Default blocklist array
	$blocklist = array ();

	// Blocklist JSON file location
	$blocklist_file = $hashover->setup->getAbsolutePath ('config/blocklist.json');

	// Check if the form has been submitted
	if (!empty ($_POST['addresses']) and is_array ($_POST['addresses'])) {
		// If so, run through submitted addresses
		foreach ($_POST['addresses'] as $address) {
			// Add each non-empty address value to the blocklist array
			if (!empty ($address)) {
				$blocklist[] = $address;
			}
		}

		// Check if the user login is admin
		if ($hashover->login->verifyAdmin () === true) {
			// If so, attempt to save the JSON data
			$saved = $data_files->saveJSON ($blocklist_file, $blocklist);

			// If saved successfully, redirect with success indicator
			if ($saved === true) {
				redirect ('./?status=success');
			}
		}

		// Otherwise, redirect with failure indicator
		redirect ('./?status=failure');
	}

	// Otherwise, load and parse blocklist file
	$json = $data_files->readJSON ($blocklist_file);

	// Check for JSON parse error
	if (is_array ($json)) {
		$blocklist = $json;
	}

	// IP Address inputs
	$inputs = new HTMLTag ('span');

	// Create IP address inputs
	for ($i = 0, $il = max (3, count ($blocklist)); $i < $il; $i++) {
		// Create input tag
		$input = new HTMLTag ('input', array (
			'class'		=> 'addresses',
			'type'		=> 'text',
			'name'		=> 'addresses[]',
			'value'		=> Misc::getArrayItem ($blocklist, $i) ?: '',
			'size'		=> '15',
			'maxlength'	=> '15',
			'placeholder'	=> '127.0.0.1',
			'title'		=> $hashover->locale->text['blocklist-ip-tip']
		), false, true);

		// Add input to inputs container
		$inputs->appendChild ($input);
	}

	// Template data
	$template = array (
		'title'		=> $hashover->locale->text['blocklist-title'],
		'sub-title'	=> $hashover->locale->text['blocklist-sub'],
		'inputs'	=> $inputs->getInnerHTML ("\t\t"),
		'save-button'	=> $hashover->locale->text['save']
	);

	// Load and parse HTML template
	echo parse_templates ('admin', 'blocklist.html', $template, $hashover);

} catch (\Exception $error) {
	echo Misc::displayException ($error);
}
