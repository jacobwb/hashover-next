<?php namespace HashOver;

// Copyright (C) 2018-2019 Jacob Barkdull
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


// Verify that certain setup prerequisites are met
class SetupChecks extends Secrets
{
	public function __construct (Setup $setup)
	{
		// Check if PHP version is too old
		if (version_compare (PHP_VERSION, '5.3.3') < 0) {
			// If so, split the PHP version by dashes
			$version_parts = explode ('-', PHP_VERSION);

			// And throw exception
			throw new \Exception (sprintf (
				// The exception message
				'PHP %s is too old. Must be at least PHP 5.3.3.',

				// The first part of the version
				$version_parts[0]
			));
		}

		// Throw exception if for Blowfish hashing support isn't detected
		if ((defined ('CRYPT_BLOWFISH') and CRYPT_BLOWFISH) === false) {
			throw new \Exception (
				'Failed to find CRYPT_BLOWFISH. Blowfish hashing support is required.'
			);
		}

		// Throw exception if administrative password is set to the default
		if ($this->adminPassword === 'password') {
			throw new \Exception (sprintf (
				'You must use an admin password other than "password" for `$adminPassword` in %s',
				$setup->getBackendPath ('classes/secrets.php')
			));
		}

		// Throw exception if encryption key is set to the default
		if ($this->encryptionKey === '8CharKey') {
			throw new \Exception (sprintf (
				'You must use an encryption key other than "8CharKey" for `$encryptionKey` in %s',
				$setup->getBackendPath ('classes/secrets.php')
			));
		}

		// Throw exception if notification e-mail address is set to the default
		if ($this->notificationEmail === 'example@example.com') {
			throw new \Exception (sprintf (
				'You must use an e-mail address other than "example@example.com" for `$notificationEmail` in %s',
				$setup->getBackendPath ('classes/secrets.php')
			));
		}

		// Throw exception if noreply e-mail address is set to the default
		if ($this->noreplyEmail === 'noreply@example.com') {
			throw new \Exception (sprintf (
				'You must use an e-mail address other than "noreply@example.com" for `$noreplyEmail` in %s',
				$setup->getBackendPath ('classes/secrets.php')
			));
		}

		// Check if the database is set to an SQL other than SQLite
		if ($setup->dataFormat === 'sql' and $this->databaseType !== 'sqlite') {
			// If so, throw exception if database user is set to the default
			if ($this->databaseUser === 'user') {
				throw new \Exception (sprintf (
					'You must use a database user name other than "user" for `$databaseUser` in %s',
					$setup->getBackendPath ('classes/secrets.php')
				));
			}

			// Throw exception if database password is set to the default
			if ($this->databasePassword === 'password') {
				throw new \Exception (sprintf (
					'You must use a database password other than "password" for `$databasePassword` in %s',
					$setup->getBackendPath ('classes/secrets.php')
				));
			}
		}

		// Check if we're sending notification e-mails through SMTP
		if ($setup->mailer === 'smtp') {
			// If so, throw exception if SMTP user is set to the default
			if ($this->smtpUser === 'user') {
				throw new \Exception (sprintf (
					'You must use an SMTP user name other than "user" for `$smtpUser` in %s',
					$setup->getBackendPath ('classes/secrets.php')
				));
			}

			// Throw exception if SMTP password is set to the default
			if ($this->smtpPassword === 'password') {
				throw new \Exception (sprintf (
					'You must use an SMTP password other than "password" for `$smtpPassword` in %s',
					$setup->getBackendPath ('classes/secrets.php')
				));
			}
		}
	}
}
