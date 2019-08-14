<?php namespace HashOver;

// Copyright (C) 2018-2019 Jacob Barkdull
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


// Change to the HashOver directory
chdir (realpath ('../../'));

// Check if request is for JSONP
if (isset ($_GET['jsonp'])) {
	// If so, setup HashOver for JavaScript
	require ('backend/javascript-setup.php');
} else {
	// If not, setup HashOver for JSON
	require ('backend/json-setup.php');
}

try {
	// Instantiate HashOver class
	$hashover = new \HashOver ('json');

	// Throw exception if API is disabled
	$hashover->setup->apiCheck ('latest');

	// Enable remote access
	$hashover->setup->setupRemoteAccess ();

	// Check if a page URL was given
	if ($hashover->setup->getRequest ('url') !== false) {
		// If so, set page URL from POST/GET data
		$hashover->setup->setPageURL ('request');
	} else {
		// If not, set thread name from POST/GET data
		$hashover->setup->setThreadName ('request');

		// And set website from POST/GET data
		$hashover->setup->setWebsite ('request');
	}

	// Initiate and finalize comment processing
	$hashover->initiate ();
	$hashover->finalize ();

	// Comments and statistics response array
	$data = array ();

	// Add locales to data
	$data['locale'] = array (
		'dislike'		=> $hashover->locale->text['dislike'],
		'dislikes'		=> $hashover->locale->text['dislikes'],
		'external-image-tip'	=> $hashover->locale->text['external-image-tip'],
		'like'			=> $hashover->locale->text['like'],
		'likes'			=> $hashover->locale->text['likes'],
		'today'			=> $hashover->locale->text['date-today'],
		'commenter-tip'		=> $hashover->locale->text['commenter-tip'],
		'subscribed-tip'	=> $hashover->locale->text['subscribed-tip'],
		'unsubscribed-tip'	=> $hashover->locale->text['unsubscribed-tip'],
		'replies'		=> $hashover->locale->text['replies'],
		'reply'			=> $hashover->locale->text['reply'],
		'loading'		=> $hashover->locale->text['loading'],
		'click-to-close'	=> $hashover->locale->text['click-to-close']
	);

	// Add setup information to data
	$data['setup'] = array (
		'server-eol'		=> PHP_EOL,
		'default-name'		=> $hashover->setup->defaultName,
		'user-is-logged-in'	=> $hashover->login->userIsLoggedIn,
		'image-extensions'	=> $hashover->setup->imageTypes,
		'image-placeholder'	=> $hashover->setup->getImagePath ('place-holder'),
		'theme-css'		=> $hashover->setup->getThemePath ('latest.css'),
		'image-format'		=> $hashover->setup->imageFormat,
		'device-type'		=> ($hashover->setup->isMobile === true) ? 'mobile' : 'desktop',
		'allows-images'		=> $hashover->setup->allowsImages,
		'uses-markdown'		=> $hashover->setup->usesMarkdown
	);

	// Add UI HTML to data
	$data['ui'] = array (
		'user-avatar'		=> $hashover->ui->userAvatar (),
		'name-link'		=> $hashover->ui->nameElement ('a'),
		'name-span'		=> $hashover->ui->nameElement ('span'),
		'thread-link'		=> $hashover->ui->threadLink (),
		'reply-link'		=> $hashover->ui->formLink ('{href}', 'reply'),
		'like-count'		=> $hashover->ui->likeCount ('likes'),
		'dislike-count'		=> $hashover->ui->likeCount ('dislikes'),
		'name-wrapper'		=> $hashover->ui->nameWrapper (),
		'date-link'		=> $hashover->ui->dateLink (),
		'theme'			=> $hashover->templater->parseTheme ('latest.html'),
		'comment-wrapper'	=> $hashover->ui->commentWrapper ()
	);

	// Attempt to get comment thread from GET/POST data
	$get_thread = $hashover->setup->threadName ?: 'auto';

	// Check if we're getting metadata for a specific thread
	if ($get_thread !== 'auto') {
		// If so, attempt to read thread-specific latest comments metadata
		$latest = $hashover->thread->data->readMeta ('latest-comments', $get_thread);
	} else {
		// If not, attempt to read global latest comments metadata
		$latest = $hashover->thread->data->readMeta ('latest-comments', 'auto', true);
	}

	// Check if the latest comments read successfully
	if ($latest !== false) {
		// If so, reduce number of latest comments to configured limit
		$latest = array_slice ($latest, 0, $hashover->setup->latestMax);
	} else {
		// If not, set to empty array
		$latest = array ();
	}

	// Latest comments
	$comments = array ();

	// Run through the latest comments
	foreach ($latest as $raw) {
		// Get comment key
		$key = $raw['comment'];

		// Split comment key by dashes
		$key_parts = explode ('-', $key);

		// Attempt to read page information metadata
		$page_info = $hashover->thread->data->readMeta ('page-info', $raw['thread']);

		// Skip if we're missing metadata
		if ($page_info === false) {
			continue;
		}

		// Otherwise, parse comment
		$comment = $hashover->commentParser->parse ($raw, $key, $key_parts);

		// Merge comment with page metadata
		$comment = array_merge ($page_info, $comment);

		// Add comment to response array
		$comments[] = $comment;
	}

	// HashOver instance information
	$data['instance'] = array (
		'comments' => array ('primary' => $comments),
		'total-count' => count ($comments)
	);

	// Generate statistics
	$hashover->statistics->executionEnd ();

	// HashOver statistics
	$data['statistics'] = array (
		'execution-time'	=> $hashover->statistics->executionTime,
		'script-memory'		=> $hashover->statistics->scriptMemory,
		'system-memory'		=> $hashover->statistics->systemMemory
	);

	// Encode JSON data
	echo Misc::jsonData ($data);

} catch (\Exception $error) {
	echo Misc::displayException ($error, 'json');
}
