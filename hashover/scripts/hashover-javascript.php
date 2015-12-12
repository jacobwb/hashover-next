<?php

// Copyright (C) 2010-2015 Jacob Barkdull
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

// Tell browser output is JavaScript
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

	if (!@include ('./' . $classname . '.php')) {
		echo '(document.getElementById (\'hashover\') || document.body).innerHTML += \'' . $error . '\';';
		exit;
	}
});

// Instantiate HashOver class
$hashover = new HashOver ('javascript');
$hashover->setup->setPageURL ('request');
$hashover->setup->setPageTitle ('request');
$hashover->initiate ();
$hashover->parsePrimary ($hashover->setup->collapsesComments);
$hashover->parsePopular ();
$hashover->finalize ();

// Start output buffer
ob_start ();

// Attempt to include JavaScript frontend code
if (!include ('./javascript-mode.php')) {
	ob_end_clean ();
	exit ('document.getElementById (\'hashover\').innerHTML = \'<b>HashOver - Error:</b> file "javascript-mode.php" could not be included!\';');
}

// Get output buffer contents and turn off output buffering
$javascript = ob_get_contents ();
ob_end_clean ();

// Minify JavaScript if enabled, and non-minified version isn't requested
if ($hashover->setup->minifiesJavaScript === true) {
	if (!isset ($_GET['hashover-unminified'])) {
		$jsminifier = new JSMinifier ();
		$javascript = $jsminifier->minify ($javascript, $hashover->setup->minifyLevel);
	}
}

// Use JSON Pretty Print if defined, so long as minification is not enabled
if (defined ('JSON_PRETTY_PRINT')
    and $hashover->setup->minifiesJavaScript !== true
    or $hashover->setup->minifyLevel < 2)
{
	// Encode comments as JSON
	$json_comments = json_encode ($hashover->comments, JSON_PRETTY_PRINT);

	// Convert JSON space indention to tab indention, when minification is not enabled
	$json_search = array ('    ', PHP_EOL);
	$json_replace = array ("\t", PHP_EOL . "\t");
	$json_comments = str_replace ($json_search, $json_replace, $json_comments);
} else {
	// Encode comments as JSON
	$json_comments = json_encode ($hashover->comments);
}

// Return JavaScript
echo str_replace ('HASHOVER_PHP_CONTENT', $json_comments, $javascript);

// Display statistics
echo $hashover->statistics->executionEnd ();

