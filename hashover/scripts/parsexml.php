<?php

// Copyright (C) 2010-2017 Jacob Barkdull
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

// Functions for reading and writing XML files
class ParseXML extends ReadFiles
{
	public function __construct (Setup $setup)
	{
		parent::__construct ($setup);

		// Enable XML user error handling
		libxml_use_internal_errors (true);

		// Throw exception if the XML extension isn't loaded
		$setup->extensionsLoaded (array ('xml', 'libxml'));
	}

	public function query (array $files = array (), $auto = true)
	{
		// Return array of files
		return $this->loadFiles ('xml', $files, $auto);
	}

	public function read ($file, $fullpath = false)
	{
		if ($fullpath === false) {
			$file = $this->setup->dir . '/' . $file . '.xml';
		}

		// Read XML comment file
		$data = @file_get_contents ($file);

		// Check for file read error
		if ($data !== false) {
			// Parse XML comment file
			$xml = @simplexml_load_string ($data, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_NOCDATA);

			// Check for XML parse error
			if ($xml !== false) {
				// Remove first two levels of indentation from comment
				$xml->body = preg_replace ('/^\t{0,2}/m', '', trim ($xml->body, "\r\n\t"));

				return (array) $xml;
			}
		}

		return false;
	}

	public function save (array $contents, $file, $editing = false, $fullpath = false)
	{
		if ($fullpath === false) {
			$file = $this->setup->dir . '/' . $file . '.xml';
		}

		// Return false on attempts to override an existing file
		if (file_exists ($file) and $editing === false) {
			return false;
		}

		// Create empty XML DOM document
		$dom = new DOMDocument ('1.0', 'UTF-8');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;

		// Create root element "comment"
		$comment = $dom->createElement ('comment');

		// Add comment data to root "comment" element
		foreach ($contents as $key => $value) {
			$element = $dom->createElement ($key);

			if ($key === 'body') {
				$newValue = '';

				foreach (explode (PHP_EOL, trim ($value, PHP_EOL)) as $line) {
					if (!empty ($line)) {
						$newValue .= "\t\t";
					}

					$newValue .= $line . PHP_EOL;
				}

				$value = PHP_EOL . $newValue . "\t";
			}

			$text_node = $dom->createTextNode ($value);
			$element->appendChild ($text_node);
			$comment->appendChild ($element);
		}

		// Append root element "comment"
		$dom->appendChild ($comment);

		// Replace double spaces with single tab
		$tabbed_dom = str_replace ('  ', "\t", $dom->saveXML ());

		// Attempt to write file
		if (file_put_contents ($file, $tabbed_dom, LOCK_EX)) {
			chmod ($file, 0600);

			return true;
		}

		return false;
	}

	public function delete ($file, $hardUnlink = false)
	{
		// Actually delete the comment file
		if ($hardUnlink === true) {
			return unlink ($this->setup->dir . '/' . $file . '.xml');
		}

		// Read comment file
		$xml = $this->read ($file);

		// Check for XML parse error
		if ($xml !== false) {
			// Change status to deleted
			$xml['status'] = 'deleted';

			// Attempt to save file
			if ($this->save ($xml, $file, true)) {
				return true;
			}
		}

		return false;
	}
}
