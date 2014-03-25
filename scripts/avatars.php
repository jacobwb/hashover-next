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
	//	This script redirects to the URL of a Twitter or Identi.ca avatar 
	//	icon based on a given @username. If the user's account doesn't 
	//	have an avatar or worse doesn't exist, then the script redirects 
	//	to an icon fron Gravatar if one exists there.
	//
	// NOTICE:
	//
	//	Identi.ca avatar support is temporarily disabled until I learn how to 
	//	retrive avatar images from Pump.io -- on which Identi.ca is now based.


	// Display source code
	if (isset($_GET['source']) and basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		header('Content-type: text/plain; charset=UTF-8');
		exit(file_get_contents(basename(__FILE__)));
	}

	// Function to get Avatar icons fron Twitter and Identi.ca
	if (isset($_GET['username']) and isset($_GET['email'])) {
		$avatar = 'http://gravatar.com/avatar/' . $_GET['email'] . '.png?d=http://' . $_SERVER['HTTP_HOST'] . str_replace('.', '', '/hashover/') . 'images/avatar.png&amp;s=45&amp;r=pg';
		$username = preg_replace('/^@([a-zA-Z0-9_@]{1,29}$)/', '\\1', $_GET['username']);

	//	if (!preg_match('/@identica$/i', $username)) {
			// Get Twitter avatar image headers
			$headers = get_headers('http://twitter.com/api/users/profile_image/' . $username, 1);
			$code = (isset($headers[1])) ? next(explode(' ', $headers[1])) : next(explode(' ', $headers[0]));

			// Check if the file exists and there are no errors
			if ($code != 404 and $code != 403 and $code != 400 and $code != 500) {
				if (!preg_match('/default_profile_(.*?)_normal.png/i', end($headers['location']))) {
					exit(header('Location: ' . end($headers['location'])));
				}
			}
	/*	} else {
			// Get Identi.ca avatar image headers
			$headers = get_headers('http://identi.ca/' . str_replace('@identica', '', $username) . '/avatar/48', 1);
			$code = (isset($headers[1])) ? next(explode(' ', $headers[1])) : next(explode(' ', $headers[0]));

			// Check if the file exists and there are no errors
			if ($code != 404 and $code != 403 and $code != 400 and $code != 500) {
				if (!preg_match('/default-avatar-stream.png/i', $headers['Location'])) {
					exit(header('Location: ' . $headers['Location']));
				}
			}
		}
	*/

		header('Location: ' . $avatar);
	} else {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . '/hashover/' . 'images/avatar.png');
	}

?>
