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


// Change to the HashOver directory
chdir (realpath ('../'));

// Setup HashOver for JSON
require ('backend/json-setup.php');

try {
	// Instantiate HashOver class
	$hashover = new \HashOver ('json', 'api');

	// Display error if the API is disabled
	if ($hashover->setup->apiStatus ('json') === 'disabled') {
		throw new \Exception ('<b>HashOver</b>: This API is not enabled.');
	}

	// Configure HashOver and load comments
	$hashover->setup->setPageURL ('request');
	$hashover->setup->collapsesComments = false;
	$hashover->initiate ();

	// Comments and statistics response array
	$data = array ();

	// Setup where to start reading comments
	$start = $hashover->setup->getRequest ('start', 0);

	// Check for comments
	if ($hashover->thread->totalCount > 1) {
		// Parse comments
		$hashover->parsePrimary ();
		$hashover->parsePopular ();

		// Display as JSON data
		$data['comments'] = $hashover->comments;
	} else {
		// Return no comments message
		$data = array ('No comments.');
	}

	// Generate statistics
	$hashover->statistics->executionEnd ();

	// HashOver statistics
	$data['statistics'] = array (
		'execution-time' => $hashover->statistics->executionTime,
		'script-memory' => $hashover->statistics->scriptMemory,
		'system-memory' => $hashover->statistics->systemMemory
	);

	// Return JSON or JSONP function call
	echo $hashover->misc->jsonData ($data);

} catch (\Exception $error) {
	$misc = new Misc ('json');
	$message = $error->getMessage ();
	$misc->displayError ($message);
}
