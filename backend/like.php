<?php namespace HashOver;

// Copyright (C) 2010-2019 Jacob Barkdull
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


// Check if request is for JSONP
if (isset ($_GET['jsonp'])) {
	// If so, setup HashOver for JavaScript
	require ('javascript-setup.php');
} else {
	// If not, setup HashOver for JSON
	require ('json-setup.php');
}

// Sets cookie indicating what comment was liked
function set_like (&$hashover, $like_hash, $set, &$likes)
{
	// Set cookie
	$hashover->cookies->set ($like_hash, $set, mktime (0, 0, 0, 11, 26, 3468));

	// Increase like count
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
function liker ($action, $like_hash, &$hashover, &$comment)
{
	// Get the comment array key based on given action
	$key = ($action === 'like') ? 'likes' : 'dislikes';
	$set = ($action === 'like') ? 'liked' : 'disliked';

	// Get like cookie
	$like_cookie = $hashover->cookies->getValue ($like_hash);

	// Check that a like/dislike cookie is not already set
	if ($like_cookie === null) {
		// If so, set the cookie and increase the like/dislike count
		set_like ($hashover, $like_hash, $set, $comment[$key]);
	} else {
		// If not, we're unliking/un-disliking the comment
		$opposite_key = ($action === 'like') ? 'dislikes' : 'likes';
		$opposite_set = ($action === 'like') ? 'disliked' : 'liked';

		// Check if the user has liked the comment
		if ($like_cookie === $set) {
			// If so, expire the like cookie
			$hashover->cookies->expireCookie ($like_hash);

			// And decrease the like count
			like_decrease ($comment[$key]);
		}

		// Check if the user has disliked the comment
		if ($like_cookie === $opposite_set) {
			// If so, expire the dislike cookie
			set_like ($hashover, $like_hash, $set, $comment[$key]);

			// And decrease the dislike count
			like_decrease ($comment[$opposite_key]);
		}
	}
}

// Returns comment data or error
function get_json_response ($hashover)
{
	// Initial JSON data
	$data = array ();

	// Get comment from POST/GET data
	$key = $hashover->setup->getRequest ('comment', null);

	// Get action from POST/GET data
	$action = $hashover->setup->getRequest ('action', null);

	// Return error if we're missing necessary post data
	if ($key === null or $action === null) {
		return array ('error' => 'No action.');
	}

	// Sanitize file path
	$file = str_replace ('../', '', $key);

	// Store references to some long variables
	$thread = $hashover->setup->threadName;

	// Read comment
	$comment = $hashover->thread->data->read ($file, $thread);

	// Return error message if failed to read comment
	if ($comment === false) {
		return array ('error' => 'Failed to read file: "' . $file . '"');
	}

	// Check if liker isn't poster via login ID comparision
	if ($hashover->login->userIsLoggedIn === true) {
		// Check if comment has a login ID
		if (!empty ($comment['login_id'])) {
			// If so, return error message if liker posted the comment
			if ($hashover->login->loginHash === $comment['login_id']) {
				return array ('message' => 'Practice altruism!');
			}
		}
	}

	// Name of the cookie used to indicate liked comments
	$like_hash = md5 ($hashover->setup->domain . $thread . '/' . $key);

	// Action: like or dislike
	$action = ($action !== 'dislike') ? 'like' : 'dislike';

	// Like or dislike the comment
	liker ($action, $like_hash, $hashover, $comment);

	// Attempt to save file with updated like count
	if ($hashover->thread->data->save ($file, $comment, true, $thread)) {
		// If successful, add number of likes to JSON
		if (isset ($comment['likes'])) {
			$data['likes'] = $comment['likes'];
		}

		// And add dislikes to JSON as well
		if (isset ($comment['dislikes'])) {
			$data['dislikes'] = $comment['dislikes'];
		}
	} else {
		// If failed, add error message to JSON
		$data['error'] = 'Failed to save comment file!';
	}

	return $data;
}

try {
	// Instanciate HashOver class
	$hashover = new \HashOver ('json');

	// Throw exception if requested by remote server
	$hashover->setup->refererCheck ();

	// Set page URL from POST/GET data
	$hashover->setup->setPageURL ('request');

	// Initiate comment processing
	$hashover->initiate ();

	// Get JSON response
	$data = get_json_response ($hashover);

	// Return JSON or JSONP function call
	echo Misc::jsonData ($data);

} catch (\Exception $error) {
	echo Misc::displayException ($error, 'json');
}
