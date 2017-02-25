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

// Use UTF-8 character set
ini_set ('default_charset', 'UTF-8');

// Enable display of PHP errors
ini_set ('display_errors', true);
error_reporting (E_ALL);

// Exit if nothing is to be done
if (!empty ($_POST['url'])
    and !empty ($_POST['thread'])
    and !empty ($_POST['like'])
    and !empty ($_POST['action']))
{
	echo 'No action.'
	exit;
}

// Autoload class files
spl_autoload_register (function ($classname) {
	$classname = strtolower ($classname);

	if (!@include ('./' . $classname . '.php')) {
		exit ('<b>HashOver</b>: "' . $classname . '.php" file could not be included!');
	}
});

function set_like (&$hashover, $like_cookie, $set, &$likes)
{
	$hashover->cookies->set ($like_cookie, $set, mktime (0, 0, 0, 11, 26, 3468));
	$likes = $likes + 1;
}

function like_decrease ($key, &$likes)
{
	if ($likes > 0) {
		$likes = $likes - 1;
	}
}

function liker ($action, $like_cookie, &$hashover, &$comment)
{
	$key = ($action === 'like') ? 'likes' : 'dislikes';
	$set = ($action === 'like') ? 'liked' : 'disliked';

	if (empty ($_COOKIE[$like_cookie])) {
		set_like ($hashover, $like_cookie, $set, $comment[$key]);
	} else {
		$opposite_key = ($action === 'like') ? 'dislikes' : 'likes';
		$opposite_set = ($action === 'like') ? 'disliked' : 'liked';

		if ($_COOKIE[$like_cookie] === $set) {
			$hashover->cookies->expireCookie ($like_cookie);
			like_decrease ($key, $comment[$key]);
		}

		if ($_COOKIE[$like_cookie] === $opposite_set) {
			set_like ($hashover, $like_cookie, $set, $comment[$key]);
			like_decrease ($opposite_key, $comment[$opposite_key]);
		}
	}
}

try {
	// Instanciate HashOver class
	$hashover = new HashOver ('api');
	$hashover->setup->setPageURL ($_POST['url']);
	$hashover->initiate ();

	$storageMode =& $hashover->readComments->data->storageMode;
	$file = str_replace ('../', '', $_POST['like']);

	// Exit with error is file doesn't exist
	if ($storageMode === 'flat-file') {
		$file = $_POST['thread'] . '/' . $file;
		$file = '../pages/' . $file . '.' . $hashover->setup->dataFormat;

		if (!file_exists ($file)) {
			exit ('<b>HashOver</b>: File: "' . $file . '" non-existent!');
		}
	}

	// Read comment
	$comment = $hashover->readComments->data->read ($file, true);

	// Exit with error if failed to read comment
	if ($comment === false) {
		exit ('<b>HashOver</b>: Failed to read file: "' . $file . '"');
	}

	// Check if liker isn't poster via login ID comparision
	if ($hashover->login->userIsLoggedIn and !empty ($comment['login_id'])) {
		if ($_COOKIE['hashover-login'] === $comment['login_id']) {
			// Exit with error if liker posted the comment
			exit ('<b>HashOver</b>: Practice altruism!');
		}
	}

	// Name of the cookie used to indicate liked comments
	$like_cookie = md5 ($hashover->setup->domain . $_POST['thread'] . '/' . $_POST['like']);

	// Action: like or dislike
	$action = $_POST['action'] !== 'dislike' ? 'like' : 'dislike';

	// Like or dislike the comment
	liker ($action, $like_cookie, $hashover, $comment);

	// Attempt to save file with updated like count
	if ($hashover->readComments->data->save ($comment, $file, true, true)) {
		// If successful, display number of likes and dislikes
		if (!empty ($comment['likes'])) {
			echo $comment['likes'], ' likes.', PHP_EOL;
		}

		if (!empty ($comment['dislikes'])) {
			echo $comment['dislikes'], ' dislikes.';
		}
	} else {
		// If failed, exit with error
		echo '<b>HashOver</b>: Failed to save comment file!';
	}
} catch (Exception $error) {
	echo $error->getMessage ();
}
