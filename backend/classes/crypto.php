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


// Encryption methods
class Crypto extends Secrets
{
	protected $prefix;
	protected $cost = '$10$';
	protected $encryptionHash;
	protected $ivSize;
	protected $cipher = 'aes-128-cbc';
	protected $options;
	protected $alphabet = 'aAbBcCdDeEfFgGhHiIjJkKlLmM.nNoOpPqQrRsStTuUvVwWxXyYzZ/0123456789';

	public function __construct ()
	{
		// Throw exception if encryption key isn't at least 8 characters long
		if (mb_strlen ($this->encryptionKey, '8bit') < 8) {
			throw new \Exception (
				'Encryption key must by at least 8 characters long.'
			);
		}

		// Blowfish prefix
		$this->prefix = (version_compare (PHP_VERSION, '5.3.7') < 0) ? '$2a' : '$2y';

		// OpenSSL raw output/options
		$this->options = defined ('OPENSSL_RAW_DATA') ? OPENSSL_RAW_DATA : true;

		// SHA-256 hash array
		$this->encryptionHash = str_split (hash ('sha256', $this->encryptionKey));

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
	protected function createKey ($string)
	{
		// Shuffle alphanumeric character array as to randomize it
		shuffle ($string);

		// Initial array keys
		$keys = array ();

		// Initial key
		$encryption_key = '';

		// Generate random string from encryption key SHA-256 hash
		for ($k = 0; $k < 16; $k++) {
			// Add encryption key character index to keys array
			$keys[] = array_search ($string[$k], $this->encryptionHash);

			// Add encryption key character to key
			$encryption_key .= $string[$k];
		}

		// Convert encryption hash array keys to string
		$list = join (',', $keys);

		// Return encryption key info
		return array (
			// Randomly generated encryption key
			'key' => $encryption_key,

			// List of encryption hash array keys
			'keys' => $list
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
		$encrypted = openssl_encrypt (
			// String being encrypted
			$string,

			// Encryption cipher method
			$this->cipher,

			// Generated encryption key
			$key_pair['key'],

			// OpenSSL options
			$this->options,

			// Initialization vector
			$iv
		);

		// Encode encrypted text as base64
		$encoded = base64_encode ($iv . $encrypted);

		// Return encryption info
		return array (
			// Encrypted string
			'encrypted' => $encoded,

			// List of encryption hash array keys
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
		$decryption_key = '';

		// Split keys string into array
		$keys = explode (',', $keys);

		// Retrieve random key from array
		foreach ($keys as $value) {
			// Cast key to integer
			$hash_key = (int)($value);

			// Check if encryption hash key exists
			if (isset ($this->encryptionHash[$hash_key])) {
				// If so, add character to decryption key
				$decryption_key .= $this->encryptionHash[$hash_key];
			} else {
				// If not, give up and return false
				return false;
			}
		}

		// Decode base64 encoded string
		$decoded = base64_decode ($string, true);

		// Get length of decoded string
		$length = mb_strlen ($decoded, '8bit');

		// Get decipher text from decoded string
		$decrypted = mb_substr ($decoded, $this->ivSize, $length, '8bit');

		// Setup OpenSSL IV
		$iv = mb_substr ($decoded, 0, $this->ivSize, '8bit');

		// Return OpenSSL decrypted string
		return openssl_decrypt (
			// String being decrypted
			$decrypted,

			// Encryption decipher method
			$this->cipher,

			// Retrieved decryption key
			$decryption_key,

			// OpenSSL options
			$this->options,

			// Initialization vector
			$iv
		);
	}
}
