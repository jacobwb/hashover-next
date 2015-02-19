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


	// Display source code
	if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		if (isset($_GET['source'])) {
			header('Content-type: text/plain; charset=UTF-8');
			exit(file_get_contents(basename(__FILE__)));
		}
	}

	// Encryption methods
	class Encryption
	{
		protected $cipher = MCRYPT_RIJNDAEL_128,
		          $mcrypt_mode = MCRYPT_MODE_CBC,
		          $prefix,
		          $cost = '$10$',
		          $encryption_hash;

		public function __construct($encryption_key)
		{
			$this->prefix = (version_compare(PHP_VERSION, '5.3.7') < 0) ? '$2a' : '$2y';
			$this->encryption_hash = str_split(hash('sha256', $encryption_key)); // SHA-256 hash array
		}

		// Creates Blowfish hash for passwords
		public function create_hash($str)
		{
			$alphabet = str_split('aAbBcCdDeEfFgGhHiIjJkKlLmM.nNoOpPqQrRsStTuUvVwWxXyYzZ/0123456789');
			shuffle($alphabet);
			$salt = '';

			foreach (array_rand($alphabet, 20) as $alphameric) {
				$salt .= $alphabet[$alphameric];
			}

			return crypt($str, $this->prefix . $this->cost . $salt . '$$');
		}

		// Creates Blowfish hash with salt from supplied hash; returns true if both match
		public function verify_hash($str, $compare)
		{
			$salt = explode('$', (string) $compare);
			$hash = crypt($str, $this->prefix . $this->cost . $salt[3] . '$$');

			return ($hash === (string) $compare) ? true : false;
		}

		// Generate a random mcrypt key
		public function mcrypt_create_key($str)
		{
			$key = '';
			shuffle($str);
			$keys = array();

			for ($k = 0; $k < 16; $k++) {
				$keys[] = array_search($str[$k], $this->encryption_hash);
				$key .= $str[$k];
			}

			return array(
				'key' => $key,
				'keys' => join(',', $keys)
			);
		}

		// Get mcrypt key from array
		public function mcrypt_get_key($str)
		{
			$key = '';

			foreach (explode(',', (string) $str) as $value) {
				$key .= $this->encryption_hash[$value];
			}

			return $key;
		}

		// Mcrypt with random key from SHA-256 hash for e-mails
		public function encrypt($str)
		{
			$key = $this->mcrypt_create_key($this->encryption_hash);
			$iv_size = mcrypt_get_iv_size($this->cipher, $this->mcrypt_mode);
			$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
			$encrypted = mcrypt_encrypt($this->cipher, $key['key'], $str, $this->mcrypt_mode, $iv);
			$encrypted = $iv . $encrypted;

			return array(
				'encrypted' => trim(base64_encode($encrypted)),
				'keys' => $key['keys']
			);
		}

		// Decrypt Mcrypt'd string
		public function decrypt($str, $encrypted)
		{
			$str = (string) $str;
			$encrypted = (string) $encrypted;

			if (!empty($str) and !empty($encrypted)) {
				$key = $this->mcrypt_get_key($encrypted);
				$decrypted = base64_decode($str);
				$iv_size = mcrypt_get_iv_size($this->cipher, $this->mcrypt_mode);
				$iv = substr($decrypted, 0, $iv_size);
				$decrypted = substr($decrypted, $iv_size);

				return trim(mcrypt_decrypt($this->cipher, $key, $decrypted, $this->mcrypt_mode, $iv));
			} else {
				return false;
			}
		}
	}

?>
