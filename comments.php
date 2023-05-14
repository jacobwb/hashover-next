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


// Setup HashOver for JavaScript
require ('backend/javascript-setup.php');

// Mostly static asset (unless settings are changed); suitable for client-
// side caching. 30 min and 1 day - 30 min. Override it on your webserver.
header('Cache-Control: max-age=1800, stale-while-revalidate=84600', true);
header('Pragma: ', true);

try {
	// Instantiate general setup class
	$setup = new Setup ('javascript');

	// Throw exception if requested by remote server
	$setup->refererCheck ();

	// Load user settings
	$setup->loadFrontendSettings ();

	// Instantiate HashOver statistics class
	$statistics = new Statistics ('javascript');

	// Start execution timer
	$statistics->executionStart ();

	// Instantiate JavaScript build class
	$javascript = new JavaScriptBuild ('frontend');

	// Register initial constructor
	$javascript->registerFile ('constructor.js');

	// Register HashOver ready state detection method
	$javascript->registerFile ('onready.js');

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

	// Register backend URL queries getter method
	$javascript->registerFile ('getbackendqueries.js');

	// Register AJAX-related methods
	$javascript->registerFile ('ajax.js');

	// Register backend path setter
	$javascript->registerFile ('backendpath.js', array (
		'dependencies' => array (
			'rootpath.js'
		)
	));

	// Register real constructor method
	$javascript->registerFile ('instantiator.js');

	// Register comment thread/section creation method
	$javascript->registerFile ('createthread.js');

	// Register element creation method
	$javascript->registerFile ('createelement.js');

	// Register classList polyfill methods
	$javascript->registerFile ('classes.js');

	// Register main HashOver element getter method
	$javascript->registerFile ('getmainelement.js');

	// Register error message handler method
	$javascript->registerFile ('displayerror.js');

	// Register instance prefix method
	$javascript->registerFile ('prefix.js');

	// Register pre-compiled regular expressions
	$javascript->registerFile ('regex.js');

	// Register end-of-line trimmer method
	$javascript->registerFile ('eoltrim.js');

	// Register search and replace methods
	$javascript->registerFile ('strings.js');

	// Register permalink methods
	$javascript->registerFile ('permalinks.js');

	// Register Like/Dislike methods
	$javascript->registerFile ('addratings.js');

	// Register optional method handler method
	$javascript->registerFile ('optionalmethod.js');

	// Register markdown methods
	$javascript->registerFile ('markdown.js');

	// Register embedded image method
	$javascript->registerFile ('embedimage.js', array (
		'dependencies' => array (
			'openembeddedimage.js'
		)
	));

	// Register comment parsing methods
	$javascript->registerFile ('parsecomment.js');

	// Register element retriever method
	$javascript->registerFile ('getelement.js');

	// Register element class processor method
	$javascript->registerFile ('eachclass.js');

	// Register parse all comments method
	$javascript->registerFile ('parseall.js');

	// Register sort comments method
	$javascript->registerFile ('sortcomments.js', array (
		'dependencies' => array (
			'cloneobject.js',
			'getallcomments.js'
		)
	));

	// Register append comments method
	$javascript->registerFile ('appendcomments.js', array (
		'dependencies' => array (
			'htmlchildren.js'
		)
	));

	// Register message element methods
	$javascript->registerFile ('messages.js');

	// Register email validator event handler method
	$javascript->registerFile ('validateemail.js');

	// Register comment validator event handler method
	$javascript->registerFile ('validatecomment.js');

	// Register AJAX post comment event handler method
	$javascript->registerFile ('ajaxpost.js', array (
		'dependencies' => array (
			'addcomments.js',
			'htmlchildren.js'
		)
	));

	// Register AJAX edit comment event handler method
	$javascript->registerFile ('ajaxedit.js', array (
		'dependencies' => array (
			'htmlchildren.js'
		)
	));

	// Register AJAX comment post request method
	$javascript->registerFile ('postrequest.js');

	// Register comment post method
	$javascript->registerFile ('postcomment.js');

	// Register file from permalink method
	$javascript->registerFile ('permalinkfile.js');

	// Register cancel button toggler method
	$javascript->registerFile ('cancelswitcher.js');

	// Register formatting message onclick event handler method
	$javascript->registerFile ('formattingonclick.js');

	// Register element property duplicator method
	$javascript->registerFile ('duplicateproperties.js');

	// Register miscellaneous form event handler methods
	$javascript->registerFile ('formevents.js');

	// Register reply form adder method
	$javascript->registerFile ('replytocomment.js');

	// Register edit form adder method
	$javascript->registerFile ('editcomment.js');

	// Register like/dislike comment method
	$javascript->registerFile ('likecomment.js', array (
		'dependencies' => array (
			'mouseoverchanger.js'
		)
	));

	// Register control event handler attacher method
	$javascript->registerFile ('addcontrols.js');

	// Register theme stylesheet appender method
	$javascript->registerFile ('appendcss.js');

	// Register comments RSS feed appender method
	$javascript->registerFile ('appendrss.js');

	// Register show interface method
	$javascript->registerFile ('showinterfacelink.js', array (
		'dependencies' => array (
			'showinterface.js'
		)
	));

	// Register show comments method
	$javascript->registerFile ('showmorelink.js', array (
		'dependencies' => array (
			'showmorecomments.js',
			'hidemorelink.js'
		)
	));

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
