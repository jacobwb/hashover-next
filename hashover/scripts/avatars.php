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
	//	This script redirects to the URL of a Gravatar or Twitter avatar 
	//	icon image based on a given e-mail or @username. If the user's 
	//	account doesn't have an avatar or worse doesn't exist, then the 
	//	script redirects to the default avatar image.


	// Display source code
	if (isset($_GET['source']) and basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		header('Content-type: text/plain; charset=UTF-8');
		exit(file_get_contents(basename(__FILE__)));
	}

	// Get HTTP headers for avatar image; return response code
	function avatar_header($url) {
		if (function_exists('curl_init')) {
			$headers = array();
			$ch = curl_init();
			$options = array(CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true, CURLOPT_NOBODY => true, CURLOPT_HEADER => true, CURLOPT_FOLLOWLOCATION => true);
			curl_setopt_array($ch, $options);
			$get_headers = explode(PHP_EOL, curl_exec($ch));

			foreach ($get_headers as $key => $header) {
				$parts = explode(': ', $header);

				if (count($parts) > 1) {
					$headers[$parts[0]] = $parts[1];
				} else {
					$headers[$key] = $parts[0];
				}
			}

			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
		} else {
			$headers = get_headers($url, 1);
			$header = explode(' ', (isset($headers[1])) ? $headers[1] : $headers[0]);
			$code = next($header);
		}

		return array(
			'load' => ($code != 404 and $code != 403 and $code != 400 and $code != 500) ? true : false,
			'location' => isset($headers['location']) ? is_array($headers['location']) ? end($headers['location']) : $headers['location'] : ''
		);
	}

	$format = (isset($_GET['format'])) ? $_GET['format'] : 'png';
	$icon_size = (isset($_GET['size'])) ? (($format == 'svg') ? 256 : $_GET['size']) : '45';

	if (!empty($_GET['email'])) {
		$avatar = 'http://gravatar.com/avatar/' . $_GET['email'] . '.png?d=http://' . $_SERVER['HTTP_HOST'] . '/hashover/images/' . $format . 's/avatar.' . $format . '&s=' . $icon_size . '&r=pg';

		// Attempt to get Twitter avatar image
		if (!empty($_GET['username'])) {
			$username = preg_replace('/^@([a-zA-Z0-9_@]{1,29}$)/', '\\1', $_GET['username']);
			$headers = avatar_header('http://twitter.com/api/users/profile_image/' . $username);

			// Check if the file exists and there are no errors
			if ($headers['load'] and !empty($headers['location'])) {
				if (!preg_match('/default_profile_(.*?)_normal.png/i', $headers['location'])) {
					exit(header('Location: ' . str_replace('_normal', (($format == 'svg') ? '_200x200' : '_bigger'), $headers['location'])));
				}
			}

			exit(header('Location: ' . $avatar));
		} else {
			// Attempt to get Gravatar avatar image
			$headers = avatar_header($avatar);

			// Check if the file exists and there are no errors
			if ($headers['load']) {
				exit(header('Location: ' . $avatar));
			} else {
				exit(header('Location: http://' . $_SERVER['HTTP_HOST'] . '/hashover/images/' . $format . 's/avatar.' . $format));
			}
		}
	}

	// Redirect to default avatar image if both Gravatar and Twitter fail
	exit(header('Location: http://' . $_SERVER['HTTP_HOST'] . '/hashover/images/' . $format . 's/avatar.' . $format));

?>
