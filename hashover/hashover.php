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
	//
	//--------------------
	//
	// Get Complete Source Code
	//
	//	The source code for each PHP script used in HashOver is normally 
	//	accessible directly from each script. You can simply visit the script 
	//	file with the "source" query and the script will generate and display 
	//	its own source code. Like so:
	//
	//	    http://tildehash.com/hashover/scripts/spamcheck.php?source
	//
	//	Besides that, HashOver is available as a ZIP archive:
	//
	//	    http://tildehash.com/hashover.zip
	//
	//	Besides that, HashOver is available on GitHub:
	//
	//	    https://github.com/jacobwb/hashover
	//
	//--------------------
	//
	// Script Description
	//
	//	HashOver is a PHP comment system intended as a replacement for services 
	//	like Disqus, IntenseDebate, Livefyre, Facebook Comments and Google+ 
	//	Comments. HashOver is free and open source software under the GNU 
	//	Affero General Public License. HashOver adds a "comment section" to any 
	//	website, by placing a few simple lines of JavaScript or PHP to the 
	//	source code of any webpage. HashOver is a self-hosted system and allows 
	//	completely anonymous comments to be posted, the only required 
	//	information is the comment itself.
	//
	// Features
	//
	//	Restricted use of HTML tags
	//	Display externally hosted images
	//	Five comment sorting methods
	//	Multiple languages
	//	Spam filtering
	//	IP address blocking
	//	Notification emails
	//	Threaded replies
	//	Avatar icons
	//	Comment editing and deletion
	//	Comment RSS feeds
	//	Likes
	//	Popular comments
	//	Comment layout templates
	//	Administration
	//	Automatic URL links
	//	Customizable HTML
	//	Customizable CSS
	//	Referrer checking
	//	Permalinks
	//
	//--------------------
	//
	// Documentation
	//
	//	http://tildehash.com/?page=hashover
	//
	//--------------------
	//
	// Change Log
	//
	//	Please record your modifications to code:
	//	--
	//	/hashover/changelog.txt


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

	// Autoload class files
	function __autoload($classname) {
		$classname = strtolower($classname);

		if (!@include('./scripts/' . $classname . '.php')) {
			exit('<b>HashOver</b>: "' . $classname . '.php" file could not be included!');
		}
	}

	// Get globals from including script (fix me!)
	$hashover_mode = isset($hashover_mode) ? $hashover_mode : 'javascript';
	$hashover_title = isset($hashover_title) ? $hashover_title : '';

	// Instantiate some classes
	$statistics = new Statistics($hashover_mode);
	$statistics->execution_start(); // Start statistics
	$setup = new Setup($hashover_mode, $hashover_title);
	$cookies = new Cookies($setup->domain, $setup->expire);
	$read_comments = new ReadComments($setup);

	if (isset($_POST['comment'])) {
		$write_comments = new WriteComments($read_comments, $cookies);

		if (isset($_POST['delete']) and !empty($_POST['password'])) {
			$write_comments->delete_comment();
		} else {
			$write_comments->post_comment();
		}
	}

	// Expire set cookies
	$cookies->clear();

	// Display JavaScript or HTML output
	$display_comments = new DisplayComments($read_comments, $setup);
	$display_comments->display();

	// Display statistics
	$statistics->execution_end();

?>
