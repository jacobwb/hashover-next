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
		$script_query = 'true';

		if (!isset($_GET['rss']) and !isset($_GET['canon_url'])) {
			if (isset($_GET['source']) or !isset($_SERVER['HTTP_REFERER'])) {
				header('Content-type: text/plain; charset=UTF-8');
				exit(file_get_contents(basename(__FILE__)));
			}
		}
	}

	// ini_set('display_errors', '1');
	ini_set('default_charset', 'UTF-8');

	// Script execution starting time
	$exec_time = explode(' ', microtime());
	$exec_start = $exec_time[1] + $exec_time[0];

	// Output for JavaScript mode
	function jsAddSlashes($script, $type = '') {
		global $mode;

		if (!isset($mode) or $mode == 'javascript') {
			if ($type != 'single') {
				return 'show_cmt += \'' . str_replace(array('\\\n', '\\\r', '\\\\n', "\'+", "+\'", "\t"), array('\n', '\r', '\\n', "'+", "+'", ''), addcslashes($script, "'")) . '\';' . PHP_EOL;
				break;
			} else {
				return 'document.write("' . str_replace(array('\\\n', '\\\r', '\"+', '+\"'), array('\n', '\r', '"+', '+"'), addslashes($script)) . '");' . PHP_EOL;
				break;
			}
		} else {
			return str_replace(array('\n', '\r'), '', $script) . PHP_EOL;
		}
	}

	if (version_compare(PHP_VERSION, '5.3.3') < 0) {
		exit(jsAddSlashes('<b>HashOver - Error:</b> PHP ' . current(explode('-', PHP_VERSION)) . ' is too old. Must be at least version 5.3.3.', 'single'));
	}

	// Include settings file, error on fail
	if (!include('./hashover/scripts/settings.php')) {
		if (empty($notification_email) and empty($encryption_key)) {
			exit(jsAddSlashes('<b>HashOver - Error:</b> file "settings.php" is required (with permission 0755)', 'single'));
		}
	}

	// Include encryption key & notification e-mail, error on fail
	if (!include('./scripts/secrets.php')) {
		if (empty($notification_email) and empty($encryption_key)) {
			exit(jsAddSlashes('<b>HashOver - Error:</b> file "secrets.php" is required (with permission 0755)', 'single'));
		}
	}

	// Exit if encryption key, notification email, or administrative nickname or password set to defaults
	if ($encryption_key == '8CharKey' || $notification_email == 'example@example.com' || $admin_nickname == 'admin' || $admin_password == 'passwd') {
		exit(jsAddSlashes('<b>HashOver:</b> The variable values in /hashover/scripts/secrets.php need to be UNIQUE.', 'single'));
	}

	// Exit if visitor's IP address is in block list file
	if (file_exists('./blocklist.txt')) {
		$blockedIPs = explode(PHP_EOL, file_get_contents('./blocklist.txt'));

		if (in_array($_SERVER['REMOTE_ADDR'], $blockedIPs)) {
			exit(jsAddSlashes('<b>HashOver:</b> You are blocked!', 'single'));
		}
	}

	// Check user's IP address against stopforumspam.com
	if ($spam_IP_check == 'both') {
		if (preg_match('/yes/', file_get_contents('http://www.stopforumspam.com/api?ip=' . $_SERVER['REMOTE_ADDR']))) {
			exit(jsAddSlashes('<b>HashOver:</b> You are blocked!', 'single'));
		}
	} else {
		if ($spam_IP_check == $mode) {
			if (preg_match('/yes/', file_get_contents('http://www.stopforumspam.com/api?ip=' . $_SERVER['REMOTE_ADDR']))) {
				exit(jsAddSlashes('<b>HashOver:</b> You are blocked!', 'single'));
			}
		}
	}

	// Default scripts to be included
	$include_files = array(
		'./scripts/urlwork.php',
		'./scripts/encryption.php',
		'./scripts/global_variables.php',
		'./scripts/locales.php'
	);

	// Load scripts for displaying comments or RSS feed
	if (!isset($_GET['rss'])) {
		array_push($include_files,
			'./scripts/parse_comments.php',
			'./scripts/deletion_notice.php',
			'./scripts/read_comments.php',
			'./scripts/write_comments.php'
		);
	} else {
		array_push($include_files,
			'./scripts/rss-output.php'
		);
	}

	// Actually include the scripts; display error on failure
	foreach ($include_files as $script) {
		if (!include($script)) {
			exit(jsAddSlashes('<b>HashOver - Error:</b> "' . $script . '" file could not be included!', 'single'));
		}
	}

	// Create comment thread directory & error on fail
	if (!file_exists($dir) and !isset($_GET['count_link'])) {
		if (!mkdir($dir, 0755) and !chmod($dir, 0755)) {
			exit(jsAddSlashes('<b>HashOver - Error:</b> Failed to create comment thread directory at "' . $dir . '"', 'single'));
		}
	}

	// If the "count_link" query is set, display link to comment
	if (isset($script_query)) {
		if (isset($_GET['count_link']) and !empty($_GET['count_link'])) {
			if (!file_exists($dir)) {
				exit(jsAddSlashes('<a href="' . $_GET['count_link'] . '#comments">Post Comment</a>', 'single'));
			}
		}
	}

	// Function for displaying comment count
	function display_count() {
		global $cmt_count, $total_count;
		$cmt_count--; $total_count--;

		if ($total_count == $cmt_count) {
			$show_count = $cmt_count . ' Comment';
			if ($cmt_count != '1') $show_count .= 's';
		} else {
			$show_count = $cmt_count . ' Comment';
			if ($cmt_count != '1') $show_count .= 's';
			$show_count .= ' (' . $total_count . ' counting repl';
			$show_count .= ($total_count != '2') ? 'ies)' : 'y)';
		}

		return $show_count;
	}

	// If the "count_link" query is set, echo comment count as link
	if (isset($script_query)) {
		if (isset($_GET['count_link']) and !empty($_GET['count_link'])) {
			read_comments($dir, 'no'); // Run read_comments function

			if ($total_count > 1) {
				exit(jsAddSlashes('<a href="' . $_GET['count_link'] . '#comments">' . display_count() . '</a>', 'single'));
			} else {
				exit(jsAddSlashes('<a href="' . $_GET['count_link'] . '#comments">Post Comment</a>', 'single'));
			}
		}
	}

	// Clear message cookie
	if (isset($_COOKIE['message']) and !empty($_COOKIE['message'])) {
		setcookie('message', '', 1, '/', str_replace('www.', '', $domain));
	}

	// Check if either a comment or reply failed to post
	if (isset($_COOKIE['success']) and $_COOKIE['success'] == 'no') {
		setcookie('success', '', 1, '/', str_replace('www.', '', $domain));

		if (isset($_COOKIE['replied']) and !empty($_COOKIE['replied'])) {
			$text['comment_form'] = $text['reply_form'];
			$text['post_button'] = $text['post_reply'];
			setcookie('replied', '', 1, '/', str_replace('www.', '', $domain));
		}
	}

	// Check if visitor is on mobile device
	if (preg_match('/android/i', $_SERVER['HTTP_USER_AGENT']) or preg_match('/blackberry/i', $_SERVER['HTTP_USER_AGENT']) or preg_match('/phone/i', $_SERVER['HTTP_USER_AGENT'])) {
		$is_mobile = 'yes';
	} else {
		$is_mobile = 'no';
	}

	read_comments($dir, 'yes'); // Run read_comments function
	krsort($top_likes); // Sort popular comments

	if ($mode == 'php') {
		if (!include('./scripts/php-mode.php')) {
			exit(jsAddSlashes('<b>HashOver - Error:</b> file "php-mode.php" could not be included!', 'single'));
		}
	} else {
		header('Content-Type: text/javascript');

		if (!include('./scripts/javascript-mode.php')) {
			exit(jsAddSlashes('<b>HashOver - Error:</b> file "javascript-mode.php" could not be included!', 'single'));
		}
	}

?>
