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


try {
	// View setup
	require (realpath ('../view-setup.php'));

	// Default blocklist array
	$blocklist = array ();

	// Blocklist JSON file location
	$blocklist_file = $hashover->setup->getAbsolutePath ('config/blocklist.json');

	// Submission indicators
	$title = 'IP Address Blocklist';
	$submitted = false;

	// Check if the form has been submitted
	if (!empty ($_POST['addresses']) and is_array ($_POST['addresses'])) {
		// If so, run through submitted addresses
		foreach ($_POST['addresses'] as $address) {
			// Add each non-empty address value to the blocklist array
			if (!empty ($address)) {
				$blocklist[] = $address;
			}
		}

		// Save the JSON data to the blocklist file
		if ($data_files->saveJSON ($blocklist_file, $blocklist)) {
			// Set submission indicators
			$title = 'Blocklist Saved!';
			$submitted = true;
		} else {
			// Set submission indicators
			$title = 'Failed to Save Blocklist!';
		}
	} else {
		// Load and parse blocklist file
		$json = $data_files->readJSON ($blocklist_file);

		// Check for JSON parse error
		if (is_array ($json)) {
			$blocklist = $json;
		}
	}

	// IP Address inputs
	$inputs = new HTMLTag ('span');

	// Create IP address inputs
	for ($i = 0, $il = max (3, count ($blocklist)); $i < $il; $i++) {
		// Use IP address from file or blank
		$address = !empty ($blocklist[$i]) ? $blocklist[$i] : '';

		// Create input tag
		$input = new HTMLTag ('input', array (
			'type' => 'text',
			'name' => 'addresses[]',
			'value' => $address,
			'size' => '15',
			'maxlength' => '15',
			'placeholder' => '127.0.0.1',
			'title' => 'IP Address or blank to remove'
		), false, true);

		// Add input to inputs container
		$inputs->appendChild ($input);
	}

	// Template data
	$template = array (
		'title'		=> $title,
		'logout'	=> $logout->asHTML ("\t\t\t"),
		'sub-title'	=> 'Block specific IP addresses',
		'inputs'	=> $inputs->getInnerHTML ("\t\t\t\t"),
		'new-button'	=> '+ New Address',
		'save-button'	=> 'Save Blocklist'
	);

	// Load and parse HTML template
	echo $hashover->templater->parseTemplate ('blocklist.html', $template);

} catch (\Exception $error) {
	$misc = new Misc ('php');
	$message = $error->getMessage ();
	$misc->displayError ($message);
}
