<?php

// Copyright (C) 2010-2016 Jacob Barkdull
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

// Use UTF-8 character set
ini_set ('default_charset', 'UTF-8');

// Enable display of PHP errors
ini_set ('display_errors', true);
error_reporting (E_ALL);

// Tell browser this is JavaScript
header ('Content-Type: application/javascript');

// Disable browser cache
header ('Expires: Wed, 08 May 1991 12:00:00 GMT');
header ('Last-Modified: ' . gmdate ('D, d M Y H:i:s') . ' GMT');
header ('Cache-Control: no-store, no-cache, must-revalidate');
header ('Cache-Control: post-check=0, pre-check=0', false);
header ('Pragma: no-cache');

// Autoload class files
spl_autoload_register (function ($classname) {
	$classname = strtolower ($classname);
	$error = '"' . $classname . '.php" file could not be included!';

	if (!@include ('../scripts/' . $classname . '.php')) {
		echo '(document.getElementById (\'hashover\') || document.body).innerHTML += \'' . $error . '\';';
		exit;
	}
});

// Instantiate HashOver class
$hashover = new HashOver ('api');

// Display error if the API is disabled
if (!isset ($_POST['ajax']) and $hashover->setup->APIStatus ('json') === 'disabled') {
	exit (json_encode (array ('error' => '<b>HashOver</b>: This API is not enabled.')));
}

// Configure HashOver and load comments
$hashover->setup->setPageURL ('request');
$hashover->initiate ();

// Setup where to start reading comments
$start = !empty ($_POST['start']) ? $_POST['start'] : 0;

// Check for comments
if ($hashover->readComments->totalCount > 1) {
	// Parse primary comments
	// TODO: Use starting point
	$hashover->parsePrimary (false, 0);

	// Display as JSON data
	echo json_encode ($hashover->comments);
} else {
	// Return no comments message
	echo json_encode (array ('No comments.'));
}
