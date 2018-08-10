<?php namespace HashOver;

// Copyright (C) 2018 Jacob Barkdull
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
	// Instantiate general setup class
	$setup = new Setup (array (
		'mode' => 'javascript',
		'context' => 'normal'
	));

	// Instantiate HashOver statistics class
	$statistics = new Statistics ('javascript');

	// Start execution timer
	$statistics->executionStart ();

	// Instantiate JavaScript build class
	$javascript = new JavaScriptBuild ('frontend');

	// Register initial constructor
	$javascript->registerFile ('constructor.js');

	// Register HashOver script tag getter method
	$javascript->registerFile ('script.js');

	// Register backend path setter
	$javascript->registerFile ('backendpath.js', array (
		'dependencies' => array (
			'rootpath.js'
		)
	));

	// Register page URL getter method
	$javascript->registerFile ('geturl.js');

	// Register page title getter method
	$javascript->registerFile ('gettitle.js');

	// Register real constructor method
	$javascript->registerFile ('instantiator.js', array (
		'dependencies' => array (
			'getbackendqueries.js'
		)
	));

	// Register HashOver ready state detection method
	$javascript->registerFile ('onready.js');

	// Register element creation methods
	$javascript->registerFile ('elements.js');

	// Register main HashOver element getter method
	$javascript->registerFile ('getmainelement.js');

	// Register error message handler method
	$javascript->registerFile ('displayerror.js');

	// Register AJAX-related methods
	$javascript->registerFile ('ajax.js');

	// Register pre-compiled regular expressions
	$javascript->registerFile ('regex.js');

	// Register end-of-line trimmer method
	$javascript->registerFile ('eoltrim.js');

	// Register parent permalink getter method
	$javascript->registerFile ('permalinks.js');

	// Register markdown methods
	$javascript->registerFile ('markdown.js', array (
		'include' => $setup->usesMarkdown
	));

	// Register date/time methods
	$javascript->registerFile ('datetime.js', array (
		'include' => $setup->usesUserTimezone
	));

	// Register search and replace methods
	$javascript->registerFile ('strings.js');

	// Register optional method handler method
	$javascript->registerFile ('optionalmethod.js');

	// Register comment parsing methods
	$javascript->registerFile ('comments.js');

	// Register embedded image method
	$javascript->registerFile ('embedimage.js', array (
		'include' => $setup->allowsImages,

		'dependencies' => array (
			'openembeddedimage.js'
		)
	));

	// Register Like/Dislike methods
	$javascript->registerFile ('addratings.js', array (
		'include' => ($setup->allowsLikes or $setup->allowsDislikes)
	));

	// Register cancel button toggler method
	$javascript->registerFile ('cancelswitcher.js');

	// Register miscellaneous form event handler methods
	$javascript->registerFile ('formevents.js');

	// Register classList polyfill methods
	$javascript->registerFile ('classes.js');

	// Register message element methods
	$javascript->registerFile ('messages.js');

	// Register email validator method
	$javascript->registerFile ('emailvalidator.js');

	// Register email validator event handler method
	$javascript->registerFile ('validateemail.js');

	// Register comment validator method
	$javascript->registerFile ('commentvalidator.js');

	// Register comment validator event handler method
	$javascript->registerFile ('validatecomment.js');

	// Register AJAX comment post request method
	$javascript->registerFile ('postrequest.js', array (
		'include' => $setup->usesAjax
	));

	// Register comment post method
	$javascript->registerFile ('postcomment.js');

	// Register control event handler attacher method
	$javascript->registerFile ('addcontrols.js');

	// Register AJAX post comment event handler method
	$javascript->registerFile ('ajaxpost.js', array (
		'include' => $setup->usesAjax,

		'dependencies' => array (
			'addcomments.js',
			'htmltonodelist.js',
			'incrementcounts.js'
		)
	));

	// Register AJAX edit comment event handler method
	$javascript->registerFile ('ajaxedit.js', array (
		'include' => $setup->usesAjax,

		'dependencies' => array (
			'htmltonodelist.js'
		)
	));

	// Register formatting message onclick event handler method
	$javascript->registerFile ('formattingonclick.js');

	// Register reply form adder method
	$javascript->registerFile ('replytocomment.js');

	// Register edit form adder method
	$javascript->registerFile ('editcomment.js');

	// Register append comments method
	$javascript->registerFile ('appendcomments.js', array (
		'include' => $setup->collapsesComments and $setup->usesAjax
	));

	// Register show more comments method
	$javascript->registerFile ('showmorecomments.js', array (
		'include' => $setup->collapsesComments,

		'dependencies' => array (
			'hidemorelink.js'
		)
	));

	// Register like/dislike comment method
	$javascript->registerFile ('likecomment.js', array (
		'include' => ($setup->allowsLikes or $setup->allowsDislikes),

		'dependencies' => array (
			'mouseoverchanger.js'
		)
	));

	// Register clone object method
	$javascript->registerFile ('cloneobject.js');

	// Register get all comments method
	$javascript->registerFile ('getallcomments.js');

	// Register parse all comments method
	$javascript->registerFile ('parseall.js');

	// Register sort comments method
	$javascript->registerFile ('sortcomments.js');

	// Register theme stylesheet appender method
	$javascript->registerFile ('appendcss.js', array (
		'include' => $setup->appendsCss
	));

	// Register comments RSS feed appender method
	$javascript->registerFile ('appendrss.js', array (
		'include' => ($setup->appendsRss and $setup->apiStatus ('rss') !== 'disabled')
	));

	// Register uncollapse interface method
	$javascript->registerFile ('uncollapseinterfacelink.js', array (
		'include' => $setup->collapsesInterface,

		'dependencies' => array (
			'uncollapseinterface.js'
		)
	));

	// Register uncollapse comments method
	$javascript->registerFile ('uncollapsecommentslink.js', array (
		'include' => $setup->collapsesComments
	));

	// Register initialization method
	$javascript->registerFile ('init.js');

	// Register automatic instantiation code
	$javascript->registerFile ('instantiate.js', array (
		'include' => !isset ($_GET['nodefault'])
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
	$misc = new Misc ('javascript');
	$message = $error->getMessage ();
	$misc->displayError ($message);
}
