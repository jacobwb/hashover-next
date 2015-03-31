<?php

	// Copyright (C) 2014 Jacob Barkdull
	//
	//	This program is free software: you can redistribute it and/or modify
	//	it under the terms of the GNU Affero General Public License as
	//	published by the Free Software Foundation, either version 3 of the
	//	License, or (at your option) any later version.
	//
	//	This program is distributed in the hope that it will be useful,
	//	but WITHOUT ANY WARRANTY; without even the implied warranty of
	//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	//	GNU Affero General Public License for more details.
	//
	//	You should have received a copy of the GNU Affero General Public License
	//	along with this program.  If not, see <http://www.gnu.org/licenses/>.


	// Display source code
	if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		if (isset($_GET['source'])) {
			header('Content-type: text/plain; charset=UTF-8');
			exit(file_get_contents(basename(__FILE__)));
		}
	}

	// Use UTF-8 character set
	ini_set('default_charset', 'UTF-8');

	// Enable display of PHP errors
	ini_set('display_errors', true);
	error_reporting(E_ALL);

	// Move up a directory
	chdir('../');

	// Autoload class files
	function __autoload($classname) {
		$classname = strtolower($classname);

		if (!@include('./scripts/' . $classname . '.php')) {
			exit('<b>HashOver</b>: "' . $classname . '.php" file could not be included!');
		}
	}

	// Instantiate necessary classes
	$setup = new Setup('api');
	$read_comments = new ReadComments($setup);

	if (empty($_GET['url'])) {
		exit($setup->escape_output('<b>HashOver</b>: No URL set.', 'single'));
	}

	// If there are more than one comment set a comment count link
	if ($read_comments->total_count > 1) {
		$link_text = $read_comments->show_count;
	} else {
		// If not set a "Post Comment" link in configured language
		$link_text = $setup->text['post_button'];
	}

	// Tell browser this is JavaScript
	header('Content-Type: text/javascript');

?>
// Setup count link
var count_link = document.createElement('a');
count_link.href = '<?php echo $_GET['url']; ?>#comments';
count_link.textContent = '<?php echo $link_text; ?>';

// Display count link
<?php if (!empty($_GET['hashover-script'])) { ?>
var hashover_script = 'hashover-script-<?php echo $_GET['hashover-script']; ?>';
var this_script = document.getElementById(hashover_script);
this_script.parentNode.insertBefore(count_link, this_script);
<?php } else { ?>
document.write(count_link.outerHTML);
<?php } ?>
