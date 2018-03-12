<?php namespace HashOver;

// Copyright (C) 2010-2018 Jacob Barkdull
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


// Encryption methods
class Encryption
{
	protected $prefix;
	protected $cost = '$10$';
	protected $encryptionHash;
	protected $ivSize;
	protected $cipher = 'aes-128-cbc';
	protected $options;
	protected $alphabet = 'aAbBcCdDeEfFgGhHiIjJkKlLmM.nNoOpPqQrRsStTuUvVwWxXyYzZ/0123456789';

	public function __construct ($encryption_key)
	{
		// Throw exception if encryption key isn't at least 8 characters long
		if (mb_strlen ($encryption_key, '8bit') < 8) {
			throw new Exception ('Encryption key must by at least 8 characters long.');
		}

		// Blowfish prefix
		$this->prefix = (version_compare (PHP_VERSION, '5.3.7') < 0) ? '$2a' : '$2y';

		// OpenSSL raw output/options
		$this->options = defined ('OPENSSL_RAW_DATA') ? OPENSSL_RAW_DATA : true;

		// SHA-256 hash array
		$this->encryptionHash = str_split (hash ('sha256', $encryption_key));

		// OpenSSL cipher IV
		$this->ivSize = openssl_cipher_iv_length ($this->cipher);
	}

	// Creates Blowfish hash for passwords
	public function createHash ($string)
	{
		// Alphanumeric character array
		$alphabet = str_split ($this->alphabet);

		// Shuffle alphanumeric character array as to randomize it
		shuffle ($alphabet);

		// Initial salt
		$salt = '';

		// Generate random 20 character alphanumeric string
		foreach (array_rand ($alphabet, 20) as $character) {
			$salt .= $alphabet[$character];
		}

		// Blowfish hash
		$hash = crypt ($string, $this->prefix . $this->cost . $salt . '$$');

		// Return hashed string
		return $hash;
	}

	// Creates Blowfish hash with salt from supplied hash
	public function verifyHash ($string, $compare)
	{
		// Split string by dollar sign
		$parts = explode ('$', $compare);

		// Hash salt
		$salt = !empty ($parts[3]) ? $parts[3] : '';

		// Encryption string as Blowfish hash
		$hash = crypt ($string, $this->prefix . $this->cost . $salt . '$$');

		// Returns true if both match
		return ($hash === $compare);
	}

	// Generates a random encryption key
	public function createKey ($string)
	{
		// Shuffle alphanumeric character array as to randomize it
		shuffle ($string);

		// Initial array keys
		$keys = array ();

		// Initial key
		$key = '';

		// Generate random string from encryption key SHA-256 hash
		for ($k = 0; $k < 16; $k++) {
			// Add encryption key character index to keys array
			$keys[] = array_search ($string[$k], $this->encryptionHash);

			// Add encryption key character to key
			$key .= $string[$k];
		}

		// Return random string and list of encryption hash array keys
		return array (
			'key' => $key,
			'keys' => join (',', $keys)
		);
	}

	// OpenSSL encrypt with random key from SHA-256 hash for e-mails
	public function encrypt ($string)
	{
		// Get a random encryption key
		$key_pair = $this->createKey ($this->encryptionHash);

		// Get pseudo-random bytes for OpenSSL IV
		$iv = openssl_random_pseudo_bytes ($this->ivSize);

		// OpenSSL encrypt using random encryption key
		$ciphertext = openssl_encrypt (
			$string,
			$this->cipher,
			$key_pair['key'],
			$this->options,
			$iv
		);

		// Return encrypted value and list of encryption hash array keys
		return array (
			'encrypted' => base64_encode ($iv . $ciphertext),
			'keys' => $key_pair['keys']
		);
	}

	// Decrypt OpenSSL encrypted string
	public function decrypt ($string, $keys)
	{
		// Return false if string or keys is empty
		if (empty ($string) or empty ($keys)) {
			return false;
		}

		// Initial key
		$key = '';

		// Split keys string into array
		$keys = explode (',', $keys);

		// Retrieve random key from array
		foreach ($keys as $value) {
			// Cast key to integer
			$hash_key = (int)($value);

			// Give up if any array value isn't valid
			if (!isset ($this->encryptionHash[$hash_key])) {
				return '';
			}

			// Add character to decryption key
			$key .= $this->encryptionHash[$hash_key];
		}

		// Decode base64 encoded string
		$decode = base64_decode ($string, true);

		// Setup OpenSSL IV
		$iv = mb_substr ($decode, 0, $this->ivSize, '8bit');

		// Setup OpenSSL cipher text
		$full_length = mb_strlen ($decode, '8bit');
		$deciphertext = mb_substr ($decode, $this->ivSize, $full_length, '8bit');

		// Return OpenSSL decrypted string
		return openssl_decrypt (
			$deciphertext,
			$this->cipher,
			$key,
			$this->options,
			$iv
		);
	}
}
