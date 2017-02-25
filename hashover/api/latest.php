<?php

// Copyright (C) 2010-2017 Jacob Barkdull
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
include ('standard-setup.php');
include ('javascript-setup.php');
include ('oop-setup.php');

try {
	// Instantiate HashOver class
	$hashover = new HashOver ('javascript', 'api');
	$hashover->setup->setPageURL ('request');
	$hashover->initiate ();

	// Display error if the API is disabled
	if ($hashover->setup->APIStatus ('latest') === 'disabled') {
		throw new Exception ('This API is not enabled.');
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
			$tryfile = $file . $metadata['latest'][$i] . '.' . $hashover->setup->dataFormat;

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

				if (!empty ($comment_data['status']) and $comment_data['status'] !== 'approved') {
					continue;
				}

				if ($hashover->setup->latestTrimWidth > 0) {
					if (mb_strwidth ($comment_data['body']) > $hashover->setup->latestTrimWidth) {
						$comment_data['body'] = mb_strimwidth ($comment_data['body'], 0, $hashover->setup->latestTrimWidth, '...');
					}
				}

				$comments[$output_key] = $hashover->commentParser->parse ($comment_data, $key, false);
				$comments[$output_key]['thread-url'] = $metadata_files[dirname ($comment)]['url'];

				if (!empty ($metadata_files[dirname ($comment)]['title'])) {
					$comments[$output_key]['thread-title'] = $metadata_files[dirname ($comment)]['title'];
				} else {
					$comments[$output_key]['thread-title'] = $hashover->locales->locale['untitled'];
				}

				$output_key++;
			}
		} else {
			$comment_list = $hashover->readComments->data->query ($latest, false);

			if ($comment_list !== false) {
				foreach ($comment_list as $key => $comment) {
					$comment_data = $hashover->readComments->data->read ($comment);

					if (!empty ($comment_data['status']) and $comment_data['status'] !== 'approved') {
						continue;
					}

					if ($hashover->setup->latestTrimWidth > 0) {
						$comment_data['body'] = rtrim (mb_strimwidth ($comment_data['body'], 0, $hashover->setup->latestTrimWidth, '...'));
					}

					$comments[$output_key] = $hashover->commentParser->parse ($comment_data, $key, false);
					$comments[$output_key]['thread-url'] = $hashover->setup->metadata['url'];

					if (!empty ($hashover->setup->metadata['title'])) {
						$comments[$output_key]['thread-title'] = $hashover->setup->metadata['title'];
					} else {
						$comments[$output_key]['thread-title'] = $hashover->locales->locale['untitled'];
					}

					$output_key++;
				}
			}
		}
	}

	if (!include ($hashover->setup->rootDirectory . '/scripts/widget-output.php')) {
		exit ('document.write (\'<b>HashOver</b>: Error! File "widget-output.php" could not be included!\');');
	}

	// End statistics and add them as code comment
	echo $hashover->statistics->executionEnd ();

} catch (Exception $error) {
	$misc = new Misc ('javascript');
	$misc->displayError ($error->getMessage ());
}
