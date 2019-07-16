<?php namespace HashOver;

// Copyright (C) 2010-2019 Jacob Barkdull
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
} else {
	// If not, setup HashOver for JSON
	require ('json-setup.php');
}

try {
	// Instantiate HashOver class
	$hashover = new \HashOver ('json');

	// Throw exception if requested by remote server
	$hashover->setup->refererCheck ();

	// Set page URL from POST/GET data
	$hashover->setup->setPageURL ('request');

	// Set page title from POST/GET data
	$hashover->setup->setPageTitle ('request');

	// Set thread name from POST/GET data
	$hashover->setup->setThreadName ('request');

	// Set website from POST/GET data
	$hashover->setup->setWebsite ('request');

	// Initiate comment processing
	$hashover->initiate ();

	// Check for comments
	if ($hashover->thread->totalCount > 1) {
		// Parse primary comments
		$hashover->parsePrimary ();

		// Display as JSON data
		$data = $hashover->comments;

		// Generate statistics
		$hashover->statistics->executionEnd ();

		// HashOver statistics
		$data['statistics'] = array (
			'execution-time' => $hashover->statistics->executionTime,
			'script-memory' => $hashover->statistics->scriptMemory,
			'system-memory' => $hashover->statistics->systemMemory
		);
	} else {
		// Return no comments message
		$data = array ('No comments.');
	}

	// Return JSON or JSONP function call
	echo Misc::jsonData ($data);

} catch (\Exception $error) {
	echo Misc::displayException ($error, 'json');
}
