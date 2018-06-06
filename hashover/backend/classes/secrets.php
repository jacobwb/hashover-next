<?php namespace HashOver;

// Copyright (C) 2010-2018 Jacob Barkdull
// This file is part of HashOver.
//
// I, Jacob Barkdull, hereby release this work into the public domain.
// This applies worldwide. If this is not legally possible, I grant any
// entity the right to use this work for any purpose, without any
// conditions, unless such conditions are required by law.


class Secrets
{
	// E-mail for notification of new comments
	public $notificationEmail = 'example@example.com';

	// Unique encryption key (case-sensitive)
	protected $encryptionKey = '8CharKey';

	// Login name to gain admin rights (case-sensitive)
	protected $adminName = 'admin';

	// Login password to gain admin rights (case-sensitive)
	protected $adminPassword = 'passwd';

	// HTTP root directory. This is usually auto-detected correctly,
	// so it does not need to be set in most circumstances.
	protected $httpRootDirectory = NULL;

	protected function getSecretConfigPath() {
		return dirname(dirname(__DIR__)) . '/config/secrets.ini';
	}

	function __construct() {
		$config_file_name = $this->getSecretConfigPath();
		if (!file_exists($config_file_name)) {
			throw new \Exception (sprintf (
				'Please create the file %s (using secrets.ini.sample as a template)',
				$config_file_name
			));
		}

		$arr = parse_ini_file($config_file_name);
		$this->notificationEmail = $arr['notification-email'];
		$this->encryptionKey = $arr['encryption-key'];
		$this->adminName = $arr['admin-name'];
		$this->adminPassword = $arr['admin-password'];
		if (isset($arr['http-root-directory']))
			$this->httpRootDirectory = $arr['http-root-directory'];
	}
}
