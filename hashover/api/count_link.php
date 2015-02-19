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
		$link = '<a href="' . $_GET['url'] . '#comments">' . $read_comments->show_count . '</a>';
	} else {
		// If not set a "Post Comment" link in configured language
		$link = '<a href="' . $_GET['url'] . '#comments">' . $setup->text['post_button'] . '</a>';
	}

	// Tell browser this is JavaScript
	header('Content-Type: text/javascript');

	// Display the link
	echo 'document.write(\'', $link, '\');';

?>
