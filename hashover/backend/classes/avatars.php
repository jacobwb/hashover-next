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
		$this->isHTTPS = $setup->isHTTPS ();

		// Get icon size from settings
		$this->iconSize = ($setup->isMobile === true) ? 256 : $setup->iconSize;

		// Default avatar
		$avatar = $setup->httpImages . '/avatar';
		$extension = ($setup->isMobile === true) ? 'svg' : 'png';
		$this->avatar = $avatar . '.' . $extension;

		// Use HTTPS if this file is requested with HTTPS
		$this->http = ($this->isHTTPS ? 'https' : 'http') . '://';
		$this->subdomain = $this->isHTTPS ? 'secure' : 'www';

		// If set to custom, direct 404s to local avatar image
		if ($setup->gravatarDefault === 'custom') {
			// The fallback image uses the PNG image
			$fallback = $avatar . '.png';

			// Check if HashOver is being remotely accessed
			if ($setup->remoteAccess === false) {
				// If so, make avatar path absolute
				$fallback = $setup->absolutePath . $fallback;
			}

			// URL encode fallback URL
			$this->fallback = urlencode ($fallback);
		} else {
			// If not direct to a themed default
			$this->fallback = $setup->gravatarDefault;
		}

		// Gravatar URL
		$this->gravatar  = $this->http . $this->subdomain;
		$this->gravatar .= '.gravatar.com/avatar/';
	}

	// Attempt to get Gravatar avatar image
	public function getGravatar ($hash)
	{
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
