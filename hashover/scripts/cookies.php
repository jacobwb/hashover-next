<?php

	// Copyright (C) 2014 Jacob Barkdull
	//
	//	This program is free software: you can redistribute it and/or modify
	//	it under the terms of the GNU Affero General Public License as
	//	published by the Free Software Foundation, either version 3 of the
	//	License, or (at your option) any later version.
	//
	//	This program is distributed in the hope that it will be useful,
	//	but WITHOUT ANY WARRANTY; without even the implied warranty of
	//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	//	GNU Affero General Public License for more details.
	//
	//	You should have received a copy of the GNU Affero General Public License
	//	along with this program.  If not, see <http://www.gnu.org/licenses/>.


	// Display source code
	if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		if (isset($_GET['source'])) {
			header('Content-type: text/plain; charset=UTF-8');
			exit(file_get_contents(basename(__FILE__)));
		}
	}

	class Cookies
	{
		public $domain,
		       $expire;

		public function __construct($domain, $expire)
		{
			$this->domain = $domain;
			$this->expire = $expire;
		}

		public function set($name, $value, $date = false)
		{
			setcookie($name, $value, (($date != false) ? $date : $this->expire), '/', $this->domain, isset($_SERVER['HTTPS']), true);
		}

		public function expire_cookie($cookie)
		{
			if (isset($_COOKIE[$cookie])) {
				setcookie($cookie, '', 1, '/', $this->domain, isset($_SERVER['HTTPS']), true);
			}
		}

		public function clear()
		{
			// Expire message cookie
			if (isset($_COOKIE['message'])) {
				setcookie('message', '', 1, '/', $this->domain, isset($_SERVER['HTTPS']), true);
			}

			// Expire comment and reply failure cookie(s)
			if (isset($_COOKIE['success']) and $_COOKIE['success'] == 'no') {
				setcookie('success', '', 1, '/', $this->domain, isset($_SERVER['HTTPS']), true);

				if (!empty($_COOKIE['replied'])) {
					setcookie('replied', '', 1, '/', $this->domain, isset($_SERVER['HTTPS']), true);
				}
			}
		}
	}

?>
