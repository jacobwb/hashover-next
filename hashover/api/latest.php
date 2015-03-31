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
	$setup = new Setup();
	$statistics = new Statistics();
	$statistics->execution_start(); // Start statistics
	$read_comments = new ReadComments($setup);
	$display_comments = new DisplayComments($read_comments, $setup);

	// Display error if the API is disabled
	if ($setup->api_status('latest') == 'disabled') {
		exit($setup->escape_output('<b>HashOver</b>: This API is not enabled.', 'single'));
	}

	$latest = array();
	$hashover = array();
	$output_key = 0;

	if (!empty($_GET['global']) and $_GET['global'] == 'yes') {
		$file = './pages/';
		$metadata = json_decode(file_get_contents($file . '.metadata'), true);
	} else {
		$file = $setup->dir . '/';
		$metadata = $setup->metadata;
	}

	if (!empty($metadata)) {
		for ($i = 0, $il = count($metadata['latest']); $i < $il; $i++) {
			$tryfile = $file . $metadata['latest'][$i] . '.' . $setup->data_format;

			if (file_exists($tryfile) and is_readable($tryfile)) {
				$latest[basename($metadata['latest'][$i])] = $tryfile;
			}
		}

		if (!empty($_GET['global']) and $_GET['global'] == 'yes') {
			foreach ($latest as $key => $comment) {
				if (empty($metadata_files[dirname($comment)])) {
					$metadata_files[dirname($comment)] = json_decode(file_get_contents(dirname($comment) . '/.metadata'), true);
				}

				$comment_data = $read_comments->data->read($comment, true);

				if ($setup->latest_trimwidth > 0) {
					if (mb_strwidth($comment_data['body']) > $setup->latest_trimwidth) {
						$comment_data['body'] = mb_strimwidth($comment_data['body'], 0, $setup->latest_trimwidth, '...');
					}
				}

				$hashover[$output_key] = $display_comments->parse($comment_data, $key, false);
				$hashover[$output_key]['thread_title'] = $metadata_files[dirname($comment)]['title'];
				$hashover[$output_key]['thread_url'] = $metadata_files[dirname($comment)]['url'];
				$output_key++;
			}
		} else {
			foreach ($read_comments->data->query($latest, false) as $key => $comment) {
				$comment_data = $read_comments->data->read($comment);

				if ($setup->latest_trimwidth > 0) {
					$comment_data['body'] = rtrim(mb_strimwidth($comment_data['body'], 0, $setup->latest_trimwidth, '...'));
				}

				$hashover[$output_key] = $display_comments->parse($comment_data, $key, false);
				$hashover[$output_key]['thread_title'] = $setup->metadata['title'];
				$hashover[$output_key]['thread_url'] = $setup->metadata['url'];
				$output_key++;
			}
		}
	}

	// Tell browser this is JavaScript
	header('Content-Type: text/javascript');

	if (!include('./scripts/widget-output.php')) {
		exit($setup->escape_output('<b>HashOver - Error:</b> file "widget-output.php" could not be included!', 'single'));
	}

	$statistics->execution_end();

?>
