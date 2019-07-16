<?php namespace HashOver;

// Copyright (C) 2015-2019 Jacob Barkdull
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
		$request = isset ($_GET['jsonp']) ? $_GET : $_POST;

		// Set status
		if (isset ($request['status'])) {
			$this->data['status'] = $this->ForceUTF8 ($request['status']);
		}

		// Set name
		if (isset ($request['name'])) {
			$this->data['name'] = $this->ForceUTF8 ($request['name']);
		}

		// Set password
		if (isset ($request['password'])) {
			$this->data['password'] = $this->ForceUTF8 ($request['password']);
		}

		// Set e-mail address
		if (isset ($request['email'])) {
			$this->data['email'] = $this->ForceUTF8 ($request['email']);
		}

		// Set website URL
		if (isset ($request['website'])) {
			$this->data['website'] = $this->ForceUTF8 ($request['website']);
		}

		// Set comment
		if (isset ($request['comment'])) {
			$this->data['comment'] = $this->ForceUTF8 ($request['comment']);
		}

		// Set indicator of remote access
		if (isset ($request['remote-access'])) {
			$this->remoteAccess = true;
		}

		// Get comment file
		if (isset ($request['file'])) {
			$this->file = $request['file'];
		}

		// Get reply comment file
		if (isset ($request['reply-to'])) {
			$this->replyTo = $request['reply-to'];
		}

		// Set indicator of AJAX requests
		if (isset ($request['ajax'])) {
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
