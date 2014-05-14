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
	if (isset($_GET['source']) and basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		header('Content-type: text/plain; charset=UTF-8');
		exit(file_get_contents(basename(__FILE__)));
	}

	class SpamCheck {
		protected $remote_addr;

		public function __construct($ip) {
			$this->remote_addr = $ip;
		}

		public function remote() {
			$url = 'http://www.stopforumspam.com/api?ip=' . $this->remote_addr . '&f=json';

			if (function_exists('curl_init')) {
				$ch = curl_init();
				$options = array(CURLOPT_URL => $url, CURLOPT_RETURNTRANSFER => true);
				curl_setopt_array($ch, $options);
				$output = curl_exec($ch);
				curl_close($ch);
				$read_json = @json_decode($output, true);

				if ($read_json !== false) {
					return (!empty($read_json->appears) and $read_json->appears == 'yes') ? true : false;
				} else {
					echo $this->escape_output('<b>HashOver:</b> Invalid JSON!', 'single');
				}
			} else {
				if (ini_get('allow_url_fopen')) {
					$read_json = @json_decode(file_get_contents($url), true);

					if ($read_json !== false) {
						return (!empty($read_json->appears) and $read_json->appears == 'yes') ? true : false;
					} else {
						echo $this->escape_output('<b>HashOver:</b> Invalid JSON!', 'single');
					}
				} else {
					return false;
				}
			}
		}

		public function local() {
			if (file_exists('../spam_database.csv')) {
				$ips = explode(',', file_get_contents('../spam_database.csv'));

				for ($ip = count($ips) - 1; $ip >= 0; $ip--) {
					if ($ip[$ips] === $this->remote_addr) {
						return false;
					}
				}
			} else {
				echo $this->escape_output('<b>HashOver:</b> No local database found!', 'single');
				return false;
			}
		}
	}

?>
