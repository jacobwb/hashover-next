<?php

	// Copyright (C) 2010-2015 Jacob Barkdull
	//
	//	This file is part of HashOver.
	//
	//	HashOver is free software: you can redistribute it and/or modify
	//	it under the terms of the GNU Affero General Public License as
	//	published by the Free Software Foundation, either version 3 of the
	//	License, or (at your option) any later version.
	//
	//	HashOver is distributed in the hope that it will be useful,
	//	but WITHOUT ANY WARRANTY; without even the implied warranty of
	//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	//	GNU Affero General Public License for more details.
	//
	//	You should have received a copy of the GNU Affero General Public License
	//	along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


	// Display source code
	if (basename ($_SERVER['PHP_SELF']) === basename (__FILE__)) {
		if (isset ($_GET['source'])) {
			header ('Content-type: text/plain; charset=UTF-8');
			exit (file_get_contents (basename (__FILE__)));
		}
	}

	// Autoload class files
	spl_autoload_register (function ($classname) {
		$classname = strtolower ($classname);

		if (!@include ('./' . $classname . '.php')) {
			exit ('<b>HashOver</b>: "' . $classname . '.php" file could not be included!');
		}
	});

	// Instantiate settings class
	$settings = new Settings ();

	// Get icon size from settings
	$icon_size = ($settings->isMobile) ? 256 : $settings->iconSize;

	// Use HTTPS if this file is requested with HTTPS, default to HTTP
	$http = !empty ($_SERVER['HTTPS']) ? 'https' : 'http';

	// Default avatar
	$avatar = $http . '://' . $_SERVER['HTTP_HOST'] . '/hashover/images/' . $settings->imageFormat . 's/avatar.' . $settings->imageFormat;

	// Attempt to get Gravatar avatar image
	if (!empty ($_GET['hash'])) {
		$gravatar = $http . '://gravatar.com/avatar/' . $_GET['hash'] . '.png?r=pg';
		$gravatar .= '&s=' . $icon_size;

		// If set to custom direct 404s to local avatar image
		if ($settings->gravatarDefault === 'custom') {
			$gravatar .= '&d=' . $avatar;
		} else {
			// If not direct to a themed default
			$gravatar .= '&d=' . $settings->gravatarDefault;
		}

		// Force Gravatar default avatar if enabled
		if ($settings->gravatarForce) {
			$gravatar .= '&f=y';
		}

		// Redirect Gravatar image
		exit (header ('Location: ' . $gravatar));
	}

	// Redirect to default avatar image if no hash was given
	exit (header ('Location: ' . $avatar));

?>
