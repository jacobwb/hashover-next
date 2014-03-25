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
	if (isset($_GET['source']) and basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		header('Content-type: text/plain; charset=UTF-8');
		exit(file_get_contents(basename(__FILE__)));
	}

	// Decryption method for stored e-mails
	function encrypt($str) {
		global $encryption_key;

		$str = str_replace(' ', '-', $str);
		$encryption_key = str_replace(chr(32), '', $encryption_key);
		if (strlen($encryption_key) < 8) exit('<b>HashOver - Error:</b> Key error, make sure it\'s at least 8 characters long.');
		$kl = strlen($encryption_key) < 32 ? strlen($encryption_key) : 32;
		$k = array();

		for ($i2 = 0; $i2 < $kl; $i2++) {
			$k[$i2] = ord($encryption_key{$i2}) & 0x1F;
		}
		$j = 0;

		for ($i2 = 0; $i2 < strlen($str); $i2++) {
			$e = ord($str{$i2});
			$str{$i2} = $e & 0xE0 ? chr($e^$k[$j]) : chr($e);
			$j++; $j = $j == $kl ? 0 : $j;
		}

		return $str;
	}

	// Function for liking a comment
	if (isset($_SERVER['HTTP_REFERER'])) {
		if (isset($_GET['like']) and !empty($_GET['like'])) {
			require('secrets.php');
			$file = '../pages/' . str_replace('../', '', $_GET['like']) . '.xml';
			$like = (file_exists($file)) ? simplexml_load_file($file) : exit('File: "' . $file . '" non-existent!');
			if (isset($_COOKIE['email']) and encrypt($_COOKIE['email']) == $like->email) exit('Practice altruism!');
			$like_cookie = md5($_SERVER['SERVER_NAME'] . $_GET['like']);

			if (!isset($_COOKIE[$like_cookie]) or (isset($_COOKIE[$like_cookie]) and $_COOKIE[$like_cookie] == 'unliked')) {
				setcookie($like_cookie, 'liked', mktime(0, 0, 0, 11, 26, 3468), '/', str_replace('www.', '', $_SERVER['SERVER_NAME']));
				$like['likes'] = $like['likes'] + 1;
				$like->asXML($file);
				exit($like['likes'] . ' likes!');
			} else {
				if ($_COOKIE[$like_cookie] != 'unliked') {
					setcookie($like_cookie, 'unliked', mktime(0, 0, 0, 11, 26, 3468), '/', str_replace('www.', '', $_SERVER['SERVER_NAME']));

					if ($like['likes'] > 0) {
						$like['likes'] = $like['likes'] - 1;
						$like->asXML($file);
					}

					exit('Unliked >;)');
				}
			}
		}
	}

?>
