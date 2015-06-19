<?php

	// Copyright (C) 2010-2015 Jacob Barkdull
	//
	//	This file is part of HashOver.
	//
	//	HashOver is free software: you can redistribute it and/or modify
	//	it under the terms of the GNU Affero General Public License as
	//	published by the Free Software Foundation, either version 3 of the
	//	License, or (at your option) any later version.
	//
	//	HashOver is distributed in the hope that it will be useful,
	//	but WITHOUT ANY WARRANTY; without even the implied warranty of
	//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	//	GNU Affero General Public License for more details.
	//
	//	You should have received a copy of the GNU Affero General Public License
	//	along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


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
	header ('Content-Type: text/javascript');

	// Autoload class files
	spl_autoload_register (function ($classname) {
		$classname = strtolower ($classname);

		if (!@include ('../scripts/' . $classname . '.php')) {
			exit ('<b>HashOver</b>: "' . $classname . '.php" file could not be included!');
		}
	});

	// Instantiate HashOver class
	$hashover = new HashOver ('api', !empty ($_GET['url']) ? $_GET['url'] : '');
	$hashover->parseAll ();

	// Display error if the API is disabled
	if ($hashover->setup->APIStatus ('json') === 'disabled') {
		exit (json_encode (array ('error' => '<b>HashOver</b>: This API is not enabled.')));
	}

	// Display the JSON data
	if (!empty ($hashover->comments['comments'])) {
		echo json_encode ($hashover->comments['comments'], JSON_NUMERIC_CHECK);
	} else {
		echo json_encode (array ('No comments.'));
	}

?>
