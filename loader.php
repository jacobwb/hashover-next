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


// Setup HashOver for JavaScript
require ('backend/javascript-setup.php');

try {
	// Instantiate general settings class
	$settings = new Settings ();

	// Instantiate HashOver statistics class
	$statistics = new Statistics ('javascript');

	// Start execution timer
	$statistics->executionStart ();

	// Instantiate JavaScript build class
	$javascript = new JavaScriptBuild ('frontend');

	// Register initial constructor
	$javascript->registerFile ('loader-constructor.js');

	// Register HashOver ready state detection method
	$javascript->registerFile ('onready.js');

	// Register HashOver script tag getter method
	$javascript->registerFile ('script.js');

	// Register backend path setter
	$javascript->registerFile ('rootpath.js');

	// Register frontend settings URL queries converter method
	$javascript->registerFile ('cfgqueries.js');

	// JavaScript build process output
	$output = $javascript->build (
		$settings->minifiesJavascript,
		$settings->minifyLevel
	);

	// Display JavaScript build process output
	echo $output, PHP_EOL;

	// Display statistics
	echo $statistics->executionEnd ();

} catch (\Exception $error) {
	echo Misc::displayException ($error, 'javascript');
}
