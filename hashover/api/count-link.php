<?php namespace HashOver;

// Copyright (C) 2010-2017 Jacob Barkdull
// Generates a comment count hyperlink pointing to a specific page.
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

// Change to the scripts directory
chdir ('../scripts/');

// Do some standard HashOver setup work
require ('standard-setup.php');
require ('javascript-setup.php');
require ('oop-setup.php');

try {
	// Instantiate HashOver class
	$hashover = new \HashOver ('javascript', 'api');
	$hashover->setup->setPageURL ('request');
	$hashover->initiate ();

	// Executing script number
	$hashover_script = $hashover->setup->executingScript;

	// Link URL
	$link_url = $hashover->misc->makeXSSsafe ($hashover->setup->pageURL);

	// Check if there is more than one comment
	if ($hashover->readComments->totalCount > 1) {
		// If so, set comment count as link text
		$link_text = $hashover->commentCount;
	} else {
		// If not, set "Post Comment" locale string as link text
		$link_text = $hashover->locale->get ('post-button');
	}

} catch (\Exception $error) {
	$hashover_script = null;
	$link_url = null;
	$link_text = 'Error!';
}

?>
// Copyright (C) 2010-2017 Jacob Barkdull
// This file is part of HashOver.
//
// I, Jacob Barkdull, hereby release this work into the public domain.
// This applies worldwide. If this is not legally possible, I grant any
// entity the right to use this work for any purpose, without any
// conditions, unless such conditions are required by law.


// Setup count link
var countLink = document.createElement ('a');
    countLink.href = '<?php echo $link_url; ?>#comments';
    countLink.textContent = '<?php echo $link_text; ?>';

<?php if (!empty ($hashover_script)): ?>
// Get count link element
var hashoverScript = 'hashover-script-<?php echo $hashover_script; ?>';
var thisScript = document.getElementById (hashoverScript);

// Display count link
if (thisScript !== null) {
	thisScript.parentNode.insertBefore (countLink, thisScript);
}
<?php else: ?>
// Display count link
document.write (countLink.outerHTML);
<?php endif; ?>
