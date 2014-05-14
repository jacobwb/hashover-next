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
	// Script Description:
	//
	//	Free / Open Source PHP comment system intended to replace Disqus. 
	//	Allows completely anonymous comments to be posted, the only 
	//	required information is the comment itself. The comments are stored 
	//	as individual XML files, example: "1.xml" is the first comment, 
	//	"2.xml" is the second, and "1-1.xml" is the first reply to the first 
	//	comment, "1-2.xml" is the second reply, and so on.
	//
	//	Features restricted use of HTML tags, automatic URL links, avatar 
	//	icons, replies, comment editing and deletion, notification emails, 
	//	comment RSS feeds, likes, popular comments, customizable CSS, 
	//	referrer checking, permalinks, and more!
	//
	//--------------------
	//
	// Change Log:
	//
	//	Please record your modifications to code:
	//	/hashover/changelog.txt


	// Display source code
	if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		$script_query = true;

		if (!isset($_GET['rss']) and !isset($_GET['canon_url'])) {
			if (isset($_GET['source']) or !isset($_SERVER['HTTP_REFERER'])) {
				header('Content-type: text/plain; charset=UTF-8');
				exit(file_get_contents(basename(__FILE__)));
			}
		}
	}

	// Use UTF-8 character set
	ini_set('default_charset', 'UTF-8');

	// Enable display of PHP errors
	ini_set('display_errors', '1');
	error_reporting(E_ALL);

	// Autoload class files
	function __autoload($classname) {
		if (!@include_once('./hashover/scripts/' . strtolower($classname) . '.php')) {
			exit('<b>HashOver - Error:</b> "' . strtolower($classname) . '.php" file could not be included!');
		}
	}

	// Main HashOver class
	class HashOver extends Setup {
		public function init() {
			// Check if PHP version is the minimum required
			if (version_compare(PHP_VERSION, '5.3.3') < 0) {
				exit($this->escape_output('<b>HashOver - Error:</b> PHP ' . current(explode('-', PHP_VERSION)) . ' is too old. Must be at least version 5.3.3.', 'single'));
			}

			// Check for Blowfish hashing support
			if (!(defined('CRYPT_BLOWFISH') and CRYPT_BLOWFISH)) {
				exit($this->escape_output('<b>HashOver - Error:</b> Failed to find CRYPT_BLOWFISH. Blowfish hashing support is required.', 'single'));
			}

			$statistics = new Statistics(); // Instantiate statistics
			$statistics->execution_start(); // Start statistics
			$display_comments = new DisplayComments();

			// Exit if encryption key, notification email, or administrative nickname or password set to defaults
			if ($this->encryption_key == '8CharKey' || $this->notification_email == 'example@example.com' || $this->admin_nickname == 'admin' || $this->admin_password == 'passwd') {
				exit($this->escape_output('<b>HashOver:</b> The "Required setup" variables in /hashover/scripts/settings.php need to be UNIQUE.', 'single'));
			}

			// Exit if visitor's IP address is in block list file
			if (file_exists('./blocklist.txt')) {
				$blockedIPs = explode(PHP_EOL, file_get_contents('./blocklist.txt'));

				if (in_array($_SERVER['REMOTE_ADDR'], $blockedIPs)) {
					exit($this->escape_output('<b>HashOver:</b> You are blocked!', 'single'));
				}
			}

			// If the "count_link" query is set, display link count as link to comments
			if (basename($_SERVER['PHP_SELF']) == basename(__FILE__) and !empty($_GET['count_link'])) {
				$display_comments->display(false);

				if (file_exists($this->dir)) {
					exit($this->escape_output('<a href="' . $_GET['count_link'] . '#comments">' . (($display_comments->total_count > 0) ? $display_comments->show_count : 'Post Comment') . '</a>', 'single'));
				} else {
					exit($this->escape_output('<a href="' . $_GET['count_link'] . '#comments">Post Comment</a>', 'single'));
				}
			}

			$display_comments->display(true);
			$statistics->execution_end();
		}
	}

	$hashover = new HashOver();
	$hashover->init();

?>
