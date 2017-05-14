<?php namespace HashOver;

// Copyright (C) 2010-2017 Jacob Barkdull
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

// Change to the scripts directory
chdir ('../scripts/');

// Setup HashOver for JSON
require ('json-setup.php');

try {
	// Instantiate HashOver class
	$hashover = new \HashOver ('json', 'api');

	// Display error if the API is disabled
	if (empty ($_POST['ajax']) and $hashover->setup->APIStatus ('json') === 'disabled') {
		throw new \Exception ('<b>HashOver</b>: This API is not enabled.');
	}

	// Configure HashOver and load comments
	$hashover->setup->setPageURL ('request');
	$hashover->setup->collapsesComments = false;
	$hashover->initiate ();

	// Setup where to start reading comments
	$start = !empty ($_POST['start']) ? $_POST['start'] : 0;

	// Check for comments
	if ($hashover->readComments->totalCount > 1) {
		// Parse primary comments
		// TODO: Use starting point
		$hashover->parsePrimary (0);

		// Display as JSON data
		$data = $hashover->comments;
	} else {
		// Return no comments message
		$data = array ('No comments.');
	}

	// Return JSON data
	echo json_encode ($data);

} catch (\Exception $error) {
	$misc = new Misc ('json');
	$misc->displayError ($error->getMessage ());
}
