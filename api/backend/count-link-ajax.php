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


// Change to the HashOver directory
chdir (realpath ('../../'));

// Check if request is for JSONP
if (isset ($_GET['jsonp'])) {
	// If so, setup HashOver for JavaScript
	require ('backend/javascript-setup.php');
} else {
	// If not, setup HashOver for JSON
	require ('backend/json-setup.php');
}

try {
	// Instantiate HashOver class
	$hashover = new \HashOver ('json');

	// Throw exception if requested by remote server
	$hashover->setup->refererCheck ();

	// Set page URL from POST/GET data
	$hashover->setup->setPageURL ('request');

	// Load user settings
	$hashover->setup->loadFrontendSettings ();

	// Initiate and finalize comment processing
	$hashover->initiate ();
	$hashover->finalize ();

	// Count response array
	$data = array (
		'primary-count'		=> $hashover->thread->primaryCount - 1,
		'total-count'		=> $hashover->thread->totalCount - 1
	);

	// Check if there are any comments
	if ($hashover->thread->totalCount > 1) {
		// If so, set the count link text to the comment count
		$data['link-text'] = $hashover->getCommentCount ();
	} else {
		// If not, set the count link text to "Post Comment"
		$data['link-text'] = $hashover->locale->text['post-button'];
	}

	// Generate statistics
	$hashover->statistics->executionEnd ();

	// HashOver statistics
	$data['statistics'] = array (
		'execution-time'	=> $hashover->statistics->executionTime,
		'script-memory'		=> $hashover->statistics->scriptMemory,
		'system-memory'		=> $hashover->statistics->systemMemory
	);

	// Encode JSON data
	echo Misc::jsonData ($data);

} catch (\Exception $error) {
	echo Misc::displayException ($error, 'json');
}
