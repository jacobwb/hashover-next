<?php

// Copyright (C) 2010-2015 Jacob Barkdull
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

// Functions for reading and writing JSON files
class ParseJSON extends ReadFiles
{
	public function query (array $files = array (), $auto = true)
	{
		return $this->loadFiles ('json', $files, $auto);
	}

	public function read ($file, $fullpath = false)
	{
		if ($fullpath === false) {
			$file = $this->setup->dir . '/' . $file . '.json';
		}

		return @json_decode (file_get_contents ($file), true);
	}

	public function save (array $contents, $file, $editing = false, $fullpath = false)
	{
		if ($fullpath === false) {
			$file = $this->setup->dir . '/' . $file . '.json';
		}

		if (!file_exists ($file) or $editing === true) {
			if (defined ('JSON_PRETTY_PRINT')) {
				$json = str_replace ('    ', "\t", json_encode ((array) $contents, JSON_PRETTY_PRINT));
			} else {
				$json = json_encode ((array) $contents);
			}

			if (file_put_contents ($file, $json)) {
				chmod ($file, 0600);
				return true;
			}
		}

		return false;
	}

	public function delete ($file, $hardUnlink = false)
	{
		if ($hardUnlink === true) {
			return unlink ($this->setup->dir . '/' . $file . '.json');
		}

		$json = $this->read ($file);

		if ($json !== false) {
			$json['status'] = 'deleted';

			if ($this->save ($json, $file, true)) {
				return true;
			}
		}

		return false;
	}
}
