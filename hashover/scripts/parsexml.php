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

	// Functions for reading and writing XML files
	class ParseXML extends ReadFiles
	{
		public
		function __construct ($setup)
		{
			parent::__construct ($setup);
			libxml_use_internal_errors (true);
		}

		public
		function query (array $files = array (), $auto = true)
		{
			return $this->loadFiles ('xml', $files, $auto);
		}

		public
		function read ($file, $fullpath = false)
		{
			if ($fullpath === false) {
				$file = $this->setup->dir . '/' . $file . '.xml';
			}

			$xml = @simplexml_load_string (file_get_contents ($file), 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_NOCDATA);

			if ($xml !== false) {
				$xml->body = preg_replace ('/^\t{0,2}/m', '', trim ($xml->body, "\r\n\t"));

				return (array) $xml;
			}

			return false;
		}

		public
		function save (array $contents, $file, $editing = false, $fullpath = false)
		{
			if ($fullpath === false) {
				$file = $this->setup->dir . '/' . $file . '.xml';
			}

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

		public
		function delete ($file, $hardUnlink = false)
		{
			if ($hardUnlink) {
				return unlink ($this->setup->dir . '/' . $file . '.xml');
			}

			$xml = $this->read ($file);

			if ($xml) {
				$xml['status'] = 'deleted';

				if ($this->save ($xml, $file, true)) {
					return true;
				}
			}

			return false;
		}
	}

?>
