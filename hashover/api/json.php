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

	// Display error if the API is disabled
	if ($setup->api_status('json') == 'disabled') {
		exit($setup->escape_output('<b>HashOver</b>: This API is not enabled.', 'single'));
	}

	$json = array();
	$comments = $read_comments->read(true);

	if (!empty($comments)) {
		$display_comments = new DisplayComments($read_comments, $setup);

		foreach ($comments as $key => $comment) {
			$json[] = $display_comments->parse($comment, $key, false);
		}
	} else {
		$json[(string) '0'] = 'No comments.';
	}

	// Tell browser this is JavaScript
	header('Content-Type: text/javascript');

	// Display the JSON data
	if (defined('JSON_PRETTY_PRINT')) {
		echo str_replace('    ', "\t", json_encode($json, JSON_PRETTY_PRINT));
	} else {
		echo str_replace('    ', "\t", json_encode($json));
	}

?>
