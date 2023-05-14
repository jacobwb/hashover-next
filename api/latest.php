<?php namespace HashOver;

// Copyright (C) 2018-2021 Jacob Barkdull
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

// Setup HashOver for JavaScript
require ('backend/javascript-setup.php');

// Mostly static asset (unless settings are changed); suitable for client-
// side caching. 30 min and 1 day - 30 min. Override it on your webserver.
header('Cache-Control: max-age=1800, stale-while-revalidate=84600', true);
header('Pragma: ', true);

try {
	// Instantiate general setup class
	$setup = new Setup ('javascript');

	// Throw exception if API is disabled
	$setup->apiCheck ('latest');

	// Enable remote access
	$setup->setupRemoteAccess ();

	// Instantiate HashOver statistics class
	$statistics = new Statistics ('javascript');

	// Start execution timer
	$statistics->executionStart ();

	// Instantiate JavaScript build class
	$javascript = new JavaScriptBuild ('api/frontends/latest');

	// Register initial constructor
	$javascript->registerFile ('constructor.js');

	// Change to standard frontend directory
	$javascript->changeDirectory ('frontend');

	// Register HashOver script tag getter method
	$javascript->registerFile ('script.js');

	// Register page URL getter method
	$javascript->registerFile ('geturl.js');

	// Register page title getter method
	$javascript->registerFile ('gettitle.js');

	// Register frontend settings URL queries converter method
	$javascript->registerFile ('cfgqueries.js');

	// Register client time getter method
	$javascript->registerFile ('getclienttime.js');

	// Register backend path setter
	$javascript->registerFile ('backendpath.js', array (
		'dependencies' => array (
			'rootpath.js'
		)
	));

	// Register HashOver ready state detection method
	$javascript->registerFile ('onready.js');

	// Register instance prefix method
	$javascript->registerFile ('prefix.js');

	// Register element creation method
	$javascript->registerFile ('createelement.js');

	// Register classList polyfill methods
	$javascript->registerFile ('classes.js');

	// Register main HashOver element getter method
	$javascript->registerFile ('getmainelement.js');

	// Register error message handler method
	$javascript->registerFile ('displayerror.js');

	// Register backend URL queries getter method
	$javascript->registerFile ('getbackendqueries.js');

	// Register AJAX-related methods
	$javascript->registerFile ('ajax.js');

	// Register pre-compiled regular expressions
	$javascript->registerFile ('regex.js');

	// Register end-of-line trimmer method
	$javascript->registerFile ('eoltrim.js');

	// Register parent permalink getter method
	$javascript->registerFile ('permalinks.js');

	// Register markdown methods
	$javascript->registerFile ('markdown.js');

	// Register search and replace methods
	$javascript->registerFile ('strings.js');

	// Register optional method handler method
	$javascript->registerFile ('optionalmethod.js');

	// Register comment parsing methods
	$javascript->registerFile ('parsecomment.js');

	// Register embedded image method
	$javascript->registerFile ('embedimage.js', array (
		'dependencies' => array (
			'openembeddedimage.js'
		)
	));

	// Register theme stylesheet appender method
	$javascript->registerFile ('appendcss.js');

	// Change back to latest frontend directory
	$javascript->changeDirectory ('api/frontends/latest');

	// Register Like/Dislike methods
	$javascript->registerFile ('addratings.js');

	// Register control event handler attacher method
	$javascript->registerFile ('addcontrols.js');

	// Register initialization method
	$javascript->registerFile ('init.js');

	// Register automatic instantiation code
	$javascript->registerFile ('instantiate.js', array (
		'include' => $setup->getRequest ('auto', 'yes') === 'yes'
	));

	// JavaScript build process output
	$output = $javascript->build (
		$setup->minifiesJavascript,
		$setup->minifyLevel
	);

	// Display JavaScript build process output
	echo $output, PHP_EOL;

	// Display statistics
	echo $statistics->executionEnd ();

} catch (\Exception $error) {
	echo Misc::displayException ($error, 'javascript');
}
