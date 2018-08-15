<?php namespace HashOver;

// Copyright (C) 2015-2018 Jacob Barkdull
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


class Avatars
{
	public $setup;
	public $isHTTPS = false;
	public $http;
	public $subdomain;
	public $iconSize;
	public $avatar;
	public $fallback;

	public function __construct (Setup $setup)
	{
		$this->setup = $setup;
		$this->isVector = ($setup->imageFormat === 'svg');
		$this->isHTTPS = $setup->isHTTPS ();

		// Setup
		$this->iconSetup ();
	}

	// Sets up avatar
	protected function iconSetup ($force_size = false)
	{
		// Decide desired size of icon
		$size = $force_size ? $force_size : $this->setup->iconSize;

		// Deside whether icon is vector
		$size = $this->isVector ? 256 : $size;

		// Set icon size to the closest supported size
		$this->iconSize = $this->closestSize ($size);

		// Default avatar file names
		$avatar = $this->setup->httpImages . '/avatar';
		$png_avatar = $avatar . '-' . $this->iconSize . '.png';
		$svg_avatar = $avatar . '.svg';

		// Set avatar property to appropriate type
		$this->avatar = $this->isVector ? $svg_avatar : $png_avatar;

		// Use HTTPS if this file is requested with HTTPS
		$this->http = ($this->isHTTPS ? 'https' : 'http') . '://';
		$this->subdomain = $this->isHTTPS ? 'secure' : 'www';

		// If set to custom, direct 404s to local avatar image
		if ($this->setup->gravatarDefault === 'custom') {
			// The fallback image uses the PNG image
			$fallback = $png_avatar;

			// Make avatar path absolute if being accessed remotely
			if ($this->setup->remoteAccess === false) {
				$fallback = $this->setup->absolutePath . $fallback;
			}

			// URL encode fallback URL
			$this->fallback = urlencode ($fallback);
		} else {
			// If not direct to a themed default
			$this->fallback = $this->setup->gravatarDefault;
		}

		// Gravatar URL
		$this->gravatar  = $this->http . $this->subdomain;
		$this->gravatar .= '.gravatar.com/avatar/';
	}

	// Gets default avatar size closest to the given size
	protected function closestSize ($size = 45)
	{
		// Supported sizes
		$sizes = array (45, 64, 128, 256, 512);

		// Current supported size
		$closest = $sizes[0];

		// Find the closest size
		for ($i = 0, $il = count ($sizes); $i < $il; $i++) {
			// Check if the size is too small
			if ($size > $closest) {
				// If so, increase closest size
				$closest = $sizes[$i];
			} else {
				// If not, end the loop
				break;
			}
		}

		return $closest;
	}

	// Attempt to get Gravatar avatar image
	public function getGravatar ($hash, $force_size = false)
	{
		// Perform setup again if the size doesn't match
		if ($force_size !== false and $force_size !== $this->iconSize) {
			$this->iconSetup ($force_size);
		}

		// If no hash is given, return the default avatar
		if (empty ($hash)) {
			return $this->avatar;
		}

		// Gravatar URL
		$gravatar  = $this->gravatar . $hash . '.png?r=pg';
		$gravatar .= '&amp;s=' . $this->iconSize;
		$gravatar .= '&amp;d=' . $this->fallback;

		// Force Gravatar default avatar if enabled
		if ($this->setup->gravatarForce === true) {
			$gravatar .= '&amp;f=y';
		}

		// Redirect Gravatar image
		return $gravatar;
	}
}
