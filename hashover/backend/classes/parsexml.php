<?php namespace HashOver;

// Copyright (C) 2010-2018 Jacob Barkdull
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


// Functions for reading and writing XML files
class ParseXML extends CommentFiles
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

	public function read ($file, $thread = 'auto')
	{
		// Get comment file path
		$file = $this->getCommentPath ($file, 'xml', $thread);

		// Read XML comment file
		$data = @file_get_contents ($file);

		// Check for file read error
		if ($data !== false) {
			// Parse XML comment file
			$xml = @simplexml_load_string ($data, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_NOCDATA);

			// Check for XML parse error
			if ($xml !== false) {
				// Remove first two levels of indentation from comment
				$xml->body = preg_replace ('/^\t{0,2}/mS', '', trim ($xml->body, "\r\n\t"));

				return (array) $xml;
			}
		}

		return false;
	}

	public function save ($file, array $contents, $editing = false, $thread = 'auto')
	{
		// Get comment file path
		$file = $this->getCommentPath ($file, 'xml', $thread);

		// Return false on attempts to override an existing file
		if (file_exists ($file) and $editing === false) {
			return false;
		}

		// Create empty XML DOM document
		$dom = new \DOMDocument ('1.0', 'UTF-8');
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;

		// Create root element "comment"
		$comment = $dom->createElement ('comment');

		// Add comment data to root "comment" element
		foreach ($contents as $key => $value) {
			$element = $dom->createElement ($key);

			if ($key === 'body') {
				$new_value = '';

				foreach (explode (PHP_EOL, trim ($value, PHP_EOL)) as $line) {
					if (!empty ($line)) {
						$new_value .= "\t\t";
					}

					$new_value .= $line . "\n";
				}

				$value = "\n" . $new_value . "\t";
			}

			$text_node = $dom->createTextNode ($value);
			$element->appendChild ($text_node);
			$comment->appendChild ($element);
		}

		// Append root element "comment"
		$dom->appendChild ($comment);

		// Replace double spaces with single tab
		$tabbed_dom = str_replace ('  ', "\t", $dom->saveXML ());

		// Convert line endings to OS specific style
		$tabbed_dom = $this->osLineEndings ($tabbed_dom);

		// Attempt to write file
		if (@file_put_contents ($file, $tabbed_dom, LOCK_EX)) {
			@chmod ($file, 0600);
			return true;
		}

		return false;
	}

	public function delete ($file, $hard_unlink = false)
	{
		// Actually delete the comment file
		if ($hard_unlink === true) {
			return unlink ($this->getCommentPath ($file, 'xml'));
		}

		// Read comment file
		$xml = $this->read ($file);

		// Check for XML parse error
		if ($xml !== false) {
			// Change status to deleted
			$xml['status'] = 'deleted';

			// Attempt to save file
			if ($this->save ($file, $xml, true)) {
				return true;
			}
		}

		return false;
	}
}
