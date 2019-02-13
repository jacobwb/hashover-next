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


class PostData
{
	public $data = array ();
	public $remoteAccess = false;
	public $file;
	public $replyTo;
	public $viaAJAX = false;

	public function __construct ()
	{
		// Use POST or GET based on whether request is for JSONP
		$postget = isset ($_GET['jsonp']) ? $_GET : $_POST;

		// Set status
		if (isset ($postget['status'])) {
			$this->data['status'] = $this->ForceUTF8 ($postget['status']);
		}

		// Set name
		if (isset ($postget['name'])) {
			$this->data['name'] = $this->ForceUTF8 ($postget['name']);
		}

		// Set password
		if (isset ($postget['password'])) {
			$this->data['password'] = $this->ForceUTF8 ($postget['password']);
		}

		// Set e-mail address
		if (isset ($postget['email'])) {
			$this->data['email'] = $this->ForceUTF8 ($postget['email']);
		}

		// Set website URL
		if (isset ($postget['website'])) {
			$this->data['website'] = $this->ForceUTF8 ($postget['website']);
		}

		// Set comment
		if (isset ($postget['comment'])) {
			$this->data['comment'] = $this->ForceUTF8 ($postget['comment']);
		}

		// Set indicator of remote access
		if (isset ($postget['remote-access'])) {
			$this->remoteAccess = true;
		}

		// Get comment file
		if (isset ($postget['file'])) {
			$this->file = $postget['file'];
		}

		// Get reply comment file
		if (isset ($postget['reply-to'])) {
			$this->replyTo = $postget['reply-to'];
		}

		// Set indicator of AJAX requests
		if (isset ($postget['ajax'])) {
			$this->viaAJAX = true;
		}
	}

	// Force a string to UTF-8 encoding and acceptable character range
	protected function ForceUTF8 ($string)
	{
		$string = mb_convert_encoding ($string, 'UTF-16', 'UTF-8');
		$string = mb_convert_encoding ($string, 'UTF-8', 'UTF-16');
		$string = preg_replace ('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '?', $string);

		return trim ($string, " \r\n\t");
	}
}
