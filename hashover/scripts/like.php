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
//
//--------------------
//
// Script Description:
//
//	This script reads a given comment file, retrieves the like count,
//	increases the count by one, then writes the file. Assuming the
//	visitor hasn't already liked the given comment before and the
//	visitor isn't the comment's original poster.


// Display source code
if (basename ($_SERVER['PHP_SELF']) === basename (__FILE__)) {
	if (isset ($_GET['source'])) {
		header ('Content-type: text/plain; charset=UTF-8');
		exit (file_get_contents (basename (__FILE__)));
	}
}

// Do some standard HashOver setup work
include ('standard-setup.php');

// Autoload class files
spl_autoload_register (function ($classname) {
	$classname = strtolower ($classname);

	if (!@include ('./' . $classname . '.php')) {
		echo json_encode (array (
			'error' => $classname . '.php" file could not be included!'
		));

		exit;
	}
});

// Sets cookie indicating what comment was liked
function set_like (&$hashover, $like_cookie, $set, &$likes)
{
	$hashover->cookies->set ($like_cookie, $set, mktime (0, 0, 0, 11, 26, 3468));
	$likes = $likes + 1;
}

// Decreases a like/dislike count
function like_decrease (&$likes)
{
	if ($likes > 0) {
		$likes = $likes - 1;
	}
}

// Likes or dislikes a comment
function liker ($action, $like_cookie, &$hashover, &$comment)
{
	// Get the comment array key based on given action
	$key = ($action === 'like') ? 'likes' : 'dislikes';
	$set = ($action === 'like') ? 'liked' : 'disliked';

	// Check that a like/dislike cookie is not already set
	if (empty ($_COOKIE[$like_cookie])) {
		// If so, set the cookie and increase the like/dislike count
		set_like ($hashover, $like_cookie, $set, $comment[$key]);
	} else {
		// If not, we're unliking/un-disliking the comment
		$opposite_key = ($action === 'like') ? 'dislikes' : 'likes';
		$opposite_set = ($action === 'like') ? 'disliked' : 'liked';

		// Check if the user has liked the comment
		if ($_COOKIE[$like_cookie] === $set) {
			// If so, expire the like cookie
			$hashover->cookies->expireCookie ($like_cookie);

			// And decrease the like count
			like_decrease ($comment[$key]);
		}

		// Check if the user has disliked the comment
		if ($_COOKIE[$like_cookie] === $opposite_set) {
			// If so, expire the dislike cookie
			set_like ($hashover, $like_cookie, $set, $comment[$key]);

			// And decrease the dislike count
			like_decrease ($comment[$opposite_key]);
		}
	}
}

function get_json_response ()
{
	// Get required POST data
	$url = !empty ($_POST['url']) ? $_POST['url'] : null;
	$key = !empty ($_POST['comment']) ? $_POST['comment'] : null;
	$action = !empty ($_POST['action']) ? $_POST['action'] : null;

	// Return error if we're missing necessary post data
	if (($url and $key and $action) === null) {
		return array ('error' => 'No action.');
	}

	try {
		// Instanciate HashOver class
		$hashover = new HashOver ('api');
		$hashover->setup->setPageURL ($url);
		$hashover->initiate ();

		// JSON data
		$json = array ();

		// Store references to some long variables
		$storageMode =& $hashover->readComments->data->storageMode;
		$thread = $hashover->setup->threadDirectory;

		// Sanitize file path
		$file = str_replace ('../', '', $key);

		// Return error message if file doesn't exist
		if ($storageMode === 'flat-file') {
			$file = $thread . '/' . $file;
			$file = '../pages/' . $file . '.' . $hashover->setup->dataFormat;

			if (!file_exists ($file)) {
				return array ('error' => 'File: "' . $file . '" non-existent!');
			}
		}

		// Read comment
		$comment = $hashover->readComments->data->read ($file, true);

		// Return error message if failed to read comment
		if ($comment === false) {
			return array ('error' => 'Failed to read file: "' . $file . '"');
		}

		// Check if liker isn't poster via login ID comparision
		if ($hashover->login->userIsLoggedIn and !empty ($comment['login_id'])) {
			if ($_COOKIE['hashover-login'] === $comment['login_id']) {
				// Return error message if liker posted the comment
				return array ('message' => 'Practice altruism!');
			}
		}

		// Name of the cookie used to indicate liked comments
		$like_cookie = md5 ($hashover->setup->domain . $thread . '/' . $key);

		// Action: like or dislike
		$action = ($action !== 'dislike') ? 'like' : 'dislike';

		// Like or dislike the comment
		liker ($action, $like_cookie, $hashover, $comment);

		// Attempt to save file with updated like count
		if ($hashover->readComments->data->save ($comment, $file, true, true)) {
			// If successful, add number of likes to JSON
			if (isset ($comment['likes'])) {
				$json['likes'] = $comment['likes'];
			}

			// And add dislikes to JSON as well
			if (isset ($comment['dislikes'])) {
				$json['dislikes'] = $comment['dislikes'];
			}
		} else {
			// If failed, add error message to JSON
			$json['error'] = 'Failed to save comment file!';
		}
	} catch (Exception $error) {
		$json['error'] = $error->getMessage ();
	}

	return $json;
}

// Display JSON
$json = get_json_response ();
echo json_encode ($json);
