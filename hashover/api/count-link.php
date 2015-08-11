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

		if (!@include ('../scripts/' . $classname . '.php')) {
			exit ('<b>HashOver</b>: "' . $classname . '.php" file could not be included!');
		}
	});

	// Attempt to obtain URL via GET
	if (!empty ($_GET['url'])) {
		$page_url = $_GET['url'];
	} else {
		// Attempt to obtain URL via POST
		if (!empty ($_POST['url'])) {
			$page_url = $_POST['url'];
		}
	}

	// Error on failure
	if (empty ($page_url)) {
		exit ('document.write (\'<b>HashOver</b>: Failed to obtain page URL.\');');
	}

	// Instantiate HashOver class
	$hashover = new HashOver ('javascript', $page_url);

	// If there are more than one comment set a comment count link
	if ($hashover->readComments->totalCount > 1) {
		$link_text = $hashover->commentCount;
	} else {
		// If not set a "Post Comment" link in configured language
		$link_text = $hashover->locales->locale['post_button'];
	}

?>
// Copyright (C) 2015 Jacob Barkdull
//
//	I, Jacob Barkdull, hereby release this work into the public domain. 
//	This applies worldwide. If this is not legally possible, I grant any 
//	entity the right to use this work for any purpose, without any 
//	conditions, unless such conditions are required by law.


// Setup count link
var countLink = document.createElement ('a');
    countLink.href = '<?php echo $_GET['url']; ?>#comments';
    countLink.textContent = '<?php echo $link_text; ?>';

<?php if (!empty ($_GET['hashover-script'])) { ?>
// Get count link element
var hashoverScript = 'hashover-script-<?php echo $_GET['hashover-script']; ?>';
var thisScript = document.getElementById (hashoverScript);

// Display count link
if (thisScript !== null) {
	thisScript.parentNode.insertBefore (countLink, thisScript);
}
<?php } else { ?>
// Display count link
document.write (countLink.outerHTML);
<?php } ?>
