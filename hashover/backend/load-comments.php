<?php namespace HashOver;

// Copyright (C) 2010-2018 Jacob Barkdull
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
	$hashover->setup->setPageURL ('request');
	$hashover->setup->setPageTitle ('request');
	$hashover->setup->setThreadName ('request');
	$hashover->setup->collapsesComments = false;
	$hashover->initiate ();

	// Setup where to start reading comments
	$start = $hashover->setup->getRequest ('start', 0);

	// Check for comments
	if ($hashover->thread->totalCount > 1) {
		// Parse primary comments
		$hashover->parsePrimary (0);

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
	echo Misc::displayError ($error->getMessage (), 'json');
}
