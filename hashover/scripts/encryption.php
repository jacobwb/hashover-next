<?php

// Copyright (C) 2010-2015 Jacob Barkdull
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


// Display source code
if (basename ($_SERVER['PHP_SELF']) === basename (__FILE__)) {
	if (isset ($_GET['source'])) {
		header ('Content-type: text/plain; charset=UTF-8');
		exit (file_get_contents (basename (__FILE__)));
	} else {
		exit ('<b>HashOver</b>: This is a class file.');
	}
}

// Encryption methods
class Encryption
{
	protected $cipher = MCRYPT_RIJNDAEL_128;
	protected $mcryptMode = MCRYPT_MODE_CBC;
	protected $prefix;
	protected $cost = '$10$';
	protected $encryptionHash;
	protected $iv_size;

	public function __construct ($encryption_key)
	{
		$this->prefix = (version_compare (PHP_VERSION, '5.3.7') < 0) ? '$2a' : '$2y';
		$this->encryptionHash = str_split (hash ('sha256', $encryption_key)); // SHA-256 hash array
		$this->iv_size = mcrypt_get_iv_size ($this->cipher, $this->mcryptMode);
	}

	// Creates Blowfish hash for passwords
	public function createHash ($str)
	{
		// Generate alphameric array
		$alphabet = str_split ('aAbBcCdDeEfFgGhHiIjJkKlLmM.nNoOpPqQrRsStTuUvVwWxXyYzZ/0123456789');
		shuffle ($alphabet);
		$salt = '';

		// Generate random 20 character alphameric string
		foreach (array_rand ($alphabet, 20) as $alphameric) {
			$salt .= $alphabet[$alphameric];
		}

		// Return hashed string
		return crypt ($str, $this->prefix . $this->cost . $salt . '$$');
	}

	// Creates Blowfish hash with salt from supplied hash; returns true if both match
	public function verifyHash ($str, $compare)
	{
		$salt = explode ('$', $compare);
		$hash = crypt ($str, $this->prefix . $this->cost . $salt[3] . '$$');

		return ($hash === $compare) ? true : false;
	}

	// Generate a random mcrypt key
	public function createMcryptKey ($str)
	{
		$key = '';
		shuffle ($str);
		$keys = array ();

		// Generate random string from encryption key SHA-256 hash
		for ($k = 0; $k < 16; $k++) {
			$keys[] = array_search ($str[$k], $this->encryptionHash);
			$key .= $str[$k];
		}

		// Return random string and list of encryption hash array keys
		return array (
			'key' => $key,
			'keys' => join (',', $keys)
		);
	}

	// Mcrypt with random key from SHA-256 hash for e-mails
	public function encrypt ($str)
	{
		// Get a random encryption key
		$key = $this->createMcryptKey ($this->encryptionHash);

		// Encrypt using random encryption key
		$iv = mcrypt_create_iv ($this->iv_size, MCRYPT_RAND);
		$encrypted = mcrypt_encrypt ($this->cipher, $key['key'], $str, $this->mcryptMode, $iv);
		$encrypted = $iv . $encrypted;

		// Return encrypted value and list of encryption hash array keys
		return array (
			'encrypted' => base64_encode ($encrypted),
			'keys' => $key['keys']
		);
	}

	// Decrypt Mcrypt'd string
	public function decrypt ($str, $encrypted)
	{
		if (!empty ($str) and !empty ($encrypted)) {
			$key = '';

			// Retrieve Mcrypt key from array
			foreach (explode (',', $encrypted) as $value) {
				$hash_key =(int) $value;

				// Give up if any array value isn't valid
				if (!isset ($this->encryptionHash[$hash_key])) {
					return '';
				}

				// Add character to decryption key
				$key .= $this->encryptionHash[$hash_key];
			}

			// Decrypt using retrieved key
			$decrypted = base64_decode ($str);
			$iv = substr ($decrypted, 0, $this->iv_size);
			$decrypted = substr ($decrypted, $this->iv_size);
			$decrypted = mcrypt_decrypt ($this->cipher, $key, $decrypted, $this->mcryptMode, $iv);

			return rtrim ($decrypted, "\0");
		}

		return false;
	}
}
