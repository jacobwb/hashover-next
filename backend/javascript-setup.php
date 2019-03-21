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


// Tell browser output is JavaScript
header ('Content-Type: application/javascript');

// Do some standard HashOver setup work
require ('nocache-headers.php');
require ('standard-setup.php');

// Setup class autoloader
setup_autoloader (function ($error) {
	// Construct JavaScript code to display error
	$js_error  = 'var hashover = document.getElementById (\'hashover\') || document.body;' . PHP_EOL;
	$js_error .= 'var error = \'<p><b>HashOver</b>: ' . $error . '</p>\';' . PHP_EOL . PHP_EOL;
	$js_error .= 'hashover.innerHTML += error;';

	// Display JavaScript code
	echo $js_error;
});
