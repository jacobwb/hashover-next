<?php namespace HashOver;

// Copyright (C) 2017-2019 Jacob Barkdull
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


// Use UTF-8 character set
ini_set ('default_charset', 'UTF-8');

// Enable display of PHP errors
ini_set ('display_errors', true);
error_reporting (E_ALL);

// Autoload class files
function setup_autoloader ($method = 'echo')
{
	// Message handler function
	$callback = function ($file) use ($method)
	{
		// Construct an error message
		$message = 'File "' . $file . '" could not be included!';

		// Check if callback is a function
		if (is_callable ($method)) {
			// If so, execute it with message
			$method ($message);
		} else {
			// If not, simply echo message
			echo $message;
		}

		exit;
	};

	// Register a class autoloader
	spl_autoload_register (function ($uri) use ($callback) {
		// Convert to lowercase
		$uri = strtolower ($uri);

		// Convert to UNIX style
		$uri = str_replace ('\\', '/', $uri);

		// Get class file
		$file = basename ($uri) . '.php';

		// Check if class file could be included
		if (!@include (__DIR__ . '/classes/' . $file)) {
			$callback ($file);
		}
	});
}
