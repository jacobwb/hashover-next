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
	//	This script reads a given comment file, retrieves the like count, 
	//	increases the count by one, then writes the file. Assuming the 
	//	visitor hasn't already liked the given comment before and the 
	//	visitor isn't the comment's original poster.


	// Display source code
	if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		if (isset($_GET['source'])) {
			header('Content-type: text/plain; charset=UTF-8');
			exit(file_get_contents(basename(__FILE__)));
		}
	}

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

	// Function for liking a comment
	if (isset($_SERVER['HTTP_REFERER']) and !empty($_GET['like'])) {
		$setup = new Setup('api');
		$read_comment = new ReadComments($setup);
		$like_cookie = md5($_SERVER['SERVER_NAME'] . $_GET['like']);
		$file = './pages/' . str_replace('../', '', $_GET['like']) . '.' . $setup->data_format;

		if (file_exists($file)) {
			$like = $read_comment->data->read($file, true);
		} else {
			exit('File: "' . $file . '" non-existent!');
		}

		if ($like == false) {
			exit('Failed to read file: "' . $file . '"');
		}

		if (isset($_COOKIE['email']) and $setup->encryption->encrypt($_COOKIE['email']) === $like['email']) {
			exit('Practice altruism!');
		}

		if (!empty($_GET['action'])) {
			if ($_GET['action'] == 'like') {
				if (empty($_COOKIE[$like_cookie])) {
					setcookie($like_cookie, 'liked', mktime(0, 0, 0, 11, 26, 3468), '/', str_replace('www.', '', $_SERVER['SERVER_NAME']));
					$like['likes'] = $like['likes'] + 1;
				} else {
					if ($_COOKIE[$like_cookie] == 'liked') {
						setcookie($like_cookie, 'unliked', 0, '/', str_replace('www.', '', $_SERVER['SERVER_NAME']));

						if ($like['likes'] > 0) {
							$like['likes'] = $like['likes'] - 1;
						}
					}

					if ($_COOKIE[$like_cookie] == 'disliked') {
						setcookie($like_cookie, 'liked', mktime(0, 0, 0, 11, 26, 3468), '/', str_replace('www.', '', $_SERVER['SERVER_NAME']));
						$like['likes'] = $like['likes'] + 1;

						if ($like['dislikes'] > 0) {
							$like['dislikes'] = $like['dislikes'] - 1;
						}
					}
				}
			}

			if ($_GET['action'] == 'dislike') {
				if (empty($_COOKIE[$like_cookie])) {
					setcookie($like_cookie, 'disliked', mktime(0, 0, 0, 11, 26, 3468), '/', str_replace('www.', '', $_SERVER['SERVER_NAME']));
					$like['dislikes'] = $like['dislikes'] + 1;
				} else {
					if ($_COOKIE[$like_cookie] == 'disliked') {
						setcookie($like_cookie, 'undisliked', 0, '/', str_replace('www.', '', $_SERVER['SERVER_NAME']));

						if ($like['dislikes'] > 0) {
							$like['dislikes'] = $like['dislikes'] - 1;
						}
					}

					if ($_COOKIE[$like_cookie] == 'liked') {
						setcookie($like_cookie, 'disliked', mktime(0, 0, 0, 11, 26, 3468), '/', str_replace('www.', '', $_SERVER['SERVER_NAME']));
						$like['dislikes'] = $like['dislikes'] + 1;

						if ($like['likes'] > 0) {
							$like['likes'] = $like['likes'] - 1;
						}
					}
				}
			}

			if ($_GET['action'] == 'like' or $_GET['action'] == 'dislike') {
				if ($read_comment->data->save($like, $file, true, true)) {
					echo $like['likes'], ' likes, ', $like['dislikes'], ' dislikes';
				}
			}
		}
	}

?>
