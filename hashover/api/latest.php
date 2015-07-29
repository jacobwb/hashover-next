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
	header ('Content-Type: text/javascript');

	// Disable browser cache
	header ('Expires: Wed, 08 May 1991 12:00:00 GMT');
	header ('Last-Modified: ' . gmdate ('D, d M Y H:i:s') . ' GMT');
	header ('Cache-Control: no-store, no-cache, must-revalidate');
	header ('Cache-Control: post-check=0, pre-check=0', false);
	header ('Pragma: no-cache');

	// Attempt to obtain URL via GET or POST
	if (!empty ($_GET['url'])) {
		$page_url = $_GET['url'];
	} else {
		if (!empty ($_POST['url'])) {
			$page_url = $_POST['url'];
		} else {
			if (!empty ($_SERVER['HTTP_REFERER'])) {
				$page_url = $_SERVER['HTTP_REFERER'];
			} else {
				// Error on failure
				exit ('document.write (\'<b>HashOver</b>: Failed to obtain page URL.\');');
			}
		}

		// Error if the script wasn't requested by this server
		if (!empty ($_SERVER['HTTP_REFERER'])) {
			if (!preg_match ('/' . $_SERVER['HTTP_HOST'] . '/i', parse_url ($_SERVER['HTTP_REFERER'], PHP_URL_HOST))) {
				exit ('document.write (\'<b>HashOver</b>: External use not allowed.\');');
			}
		}
	}

	// Autoload class files
	spl_autoload_register (function ($classname) {
		$classname = strtolower ($classname);

		if (!@include ('../scripts/' . $classname . '.php')) {
			exit ('<b>HashOver</b>: "' . $classname . '.php" file could not be included!');
		}
	});

	// Instantiate HashOver class
	$hashover = new HashOver ('api', !empty ($_GET['url']) ? $_GET['url'] : '');
	$hashover->statistics->mode = 'javascript';

	// Display error if the API is disabled
	if ($hashover->setup->APIStatus ('latest') === 'disabled') {
		exit ('document.write (\'<b>HashOver</b>: This API is not enabled.\');');
	}

	$latest = array ();
	$comments = array ();
	$output_key = 0;

	if (!empty ($_GET['global']) and $_GET['global'] === 'yes') {
		$file = './pages/';
		$metadata = json_decode (file_get_contents ($file . '.metadata'), true);
	} else {
		$file = $hashover->setup->dir . '/';
		$metadata = $hashover->setup->metadata;
	}

	if (!empty ($metadata)) {
		for ($i = 0, $il = count ($metadata['latest']); $i < $il; $i++) {
			$tryfile = $file . $metadata['latest'][$i] . '.' . $hashover->settings->dataFormat;

			if (file_exists ($tryfile) and is_readable ($tryfile)) {
				$latest[basename ($metadata['latest'][$i])] = $tryfile;
			}
		}

		if (!empty ($_GET['global']) and $_GET['global'] === 'yes') {
			foreach ($latest as $key => $comment) {
				if (empty ($metadata_files[dirname ($comment)])) {
					$metadata_files[dirname ($comment)] = json_decode (file_get_contents (dirname ($comment) . '/.metadata'), true);
				}

				$comment_data = $hashover->readComments->data->read ($comment, true);

				if ($hashover->settings->latestTrimWidth > 0) {
					if (mb_strwidth ($comment_data['body']) > $hashover->settings->latestTrimWidth) {
						$comment_data['body'] = mb_strimwidth ($comment_data['body'], 0, $hashover->settings->latestTrimWidth, '...');
					}
				}

				$comments[$output_key] = $hashover->commentParser->parse ($comment_data, $key, false);
				$comments[$output_key]['thread_title'] = $metadata_files[dirname ($comment)]['title'];
				$comments[$output_key]['thread_url'] = $metadata_files[dirname ($comment)]['url'];
				$output_key++;
			}
		} else {
			foreach ($hashover->readComments->data->query ($latest, false) as $key => $comment) {
				$comment_data = $hashover->readComments->data->read ($comment);

				if ($hashover->settings->latestTrimWidth > 0) {
					$comment_data['body'] = rtrim (mb_strimwidth ($comment_data['body'], 0, $hashover->settings->latestTrimWidth, '...'));
				}

				$comments[$output_key] = $hashover->commentParser->parse ($comment_data, $key, false);
				$comments[$output_key]['thread_title'] = $hashover->setup->metadata['title'];
				$comments[$output_key]['thread_url'] = $hashover->setup->metadata['url'];
				$output_key++;
			}
		}
	}

	if (!include ($hashover->settings->rootDirectory . '/scripts/widget-output.php')) {
		exit ('document.write (\'<b>HashOver</b>: Error! File "widget-output.php" could not be included!\');');
	}

	// End statistics and add them as code comment
	echo $hashover->statistics->executionEnd ();

?>
