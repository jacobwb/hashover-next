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


	// Display source code
	if (isset($_GET['source']) and basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		header('Content-type: text/plain; charset=UTF-8');
		exit(file_get_contents(basename(__FILE__)));
	}

	$format = (isset($_GET['format'])) ? $_GET['format'] : 'png';
	$icon_size = (isset($_GET['size'])) ? (($format == 'svg') ? 256 : $_GET['size']) : '45';

	if (!empty($_GET['email'])) {
		$avatar = 'http://gravatar.com/avatar/' . $_GET['email'] . '.png?d=http://' . $_SERVER['HTTP_HOST'] . '/hashover/images/' . $format . 's/avatar.' . $format . '&s=' . $icon_size . '&r=pg';

		// Get Twitter avatar image headers
		if (!empty($_GET['username'])) {
			$username = preg_replace('/^@([a-zA-Z0-9_@]{1,29}$)/', '\\1', $_GET['username']);
			$headers = get_headers('http://twitter.com/api/users/profile_image/' . $username, 1);
			$code = (isset($headers[1])) ? next(explode(' ', $headers[1])) : next(explode(' ', $headers[0]));

			// Check if the file exists and there are no errors
			if ($code != 404 and $code != 403 and $code != 400 and $code != 500) {
				if (!preg_match('/default_profile_(.*?)_normal.png/i', end($headers['location']))) {
					exit(header('Location: ' . str_replace('_normal', (($format == 'svg') ? '_200x200' : '_bigger'), end($headers['location']))));
				}
			}

			header('Location: ' . $avatar);
		} else {
			$headers = get_headers($avatar, 1);
			$code = (isset($headers[1])) ? next(explode(' ', $headers[1])) : next(explode(' ', $headers[0]));

			if ($code != '400' and $code != '404') {
				header('Location: ' . $avatar);
			} else {
				header('Location: http://' . $_SERVER['HTTP_HOST'] . '/hashover/images/' . $format . 's/avatar.' . $format);
			}
		}
	} else {
		header('Location: http://' . $_SERVER['HTTP_HOST'] . '/hashover/images/' . $format . 's/avatar.' . $format);
	}

?>
