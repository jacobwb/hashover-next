<?php

// Copyright (C) 2015-2017 Jacob Barkdull
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

class PostData
{
	public $postData = array ();
	public $remoteAccess = false;
	public $file;
	public $replyTo;
	public $viaAJAX = false;

	public function __construct ()
	{
		// Set status
		if (isset ($_POST['status'])) {
			$this->postData['status'] = $this->ForceUTF8 ($_POST['status']);
		}

		// Set name
		if (isset ($_POST['name'])) {
			$this->postData['name'] = $this->ForceUTF8 ($_POST['name']);
		}

		// Set password
		if (isset ($_POST['password'])) {
			$this->postData['password'] = $this->ForceUTF8 ($_POST['password']);
		}

		// Set e-mail address
		if (isset ($_POST['email'])) {
			$this->postData['email'] = $this->ForceUTF8 ($_POST['email']);
		}

		// Set website URL
		if (isset ($_POST['website'])) {
			$this->postData['website'] = $this->ForceUTF8 ($_POST['website']);
		}

		// Set comment
		if (isset ($_POST['comment'])) {
			$this->postData['comment'] = $this->ForceUTF8 ($_POST['comment']);
		}

		// Set indicator of remote access
		if (isset ($_POST['remote-access'])) {
			$this->remoteAccess = true;
		}

		// Get comment file
		if (isset ($_POST['file'])) {
			$this->file = $_POST['file'];
		}

		// Get reply comment file
		if (isset ($_POST['reply-to'])) {
			$this->replyTo = $_POST['reply-to'];
		}

		// Set indicator of AJAX requests
		if (isset ($_POST['ajax'])) {
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
