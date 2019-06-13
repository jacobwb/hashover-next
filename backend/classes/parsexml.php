<?php namespace HashOver;

// Copyright (C) 2010-2019 Jacob Barkdull
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
	protected $flags;

	public function __construct (Setup $setup, Thread $thread)
	{
		// Construct parent class
		parent::__construct ($setup, $thread);

		// Enable XML user error handling
		libxml_use_internal_errors (true);

		// Set LibXML flags
		$this->flags = LIBXML_COMPACT | LIBXML_NOCDATA;

		// Throw exception if required XML extensions aren't loaded
		$setup->extensionsLoaded (array (
			'xml', 'libxml', 'SimpleXML'
		));
	}

	// Returns an array of comment files
	public function query ()
	{
		return $this->loadFiles ('xml');
	}

	// Reads and processes a comment file
	public function read ($file, $thread = 'auto')
	{
		// Get comment file path
		$file = $this->getCommentPath ($file, 'xml', $thread);

		// Read XML comment file
		$data = @file_get_contents ($file);

		// Check for file read error
		if ($data !== false) {
			// Parse XML comment file
			$xml = @simplexml_load_string ($data, 'SimpleXMLElement', $this->flags);

			// Check for XML parse error
			if ($xml !== false) {
				// If no error, trim comment
				$xml->body = trim ($xml->body, "\r\n\t");

				// And remove two levels of indentation from comment
				$xml->body = preg_replace ('/^\t{0,2}/mS', '', $xml->body);

				return (array) $xml;
			}
		}

		return false;
	}

	// Saves a comment file
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

		// Create root <comment> element
		$comment = $dom->createElement ('comment');

		// Add comment data to root <comment> element
		foreach ($contents as $key => $value) {
			// Create element by content key
			$element = $dom->createElement ($key);

			// Check if element is <body> element
			if ($key === 'body') {
				// If so, set initial new body of comment
				$new_value = '';

				// Split body into lines
				$lines = explode (PHP_EOL, trim ($value, PHP_EOL));

				// Run through lines
				foreach ($lines as $line) {
					// Add indentation if line is not empty
					if (!empty ($line)) {
						$new_value .= "\t\t";
					}

					// Add line to new body
					$new_value .= $line . "\n";
				}

				// And update body value
				$value = "\n" . $new_value . "\t";
			}

			// Create a text node for content value
			$text_node = $dom->createTextNode ($value);

			// Append content value text node to element
			$element->appendChild ($text_node);

			// And append content element
			$comment->appendChild ($element);
		}

		// Append root <comment> element
		$dom->appendChild ($comment);

		// Replace double spaces with single tab
		$tabbed_dom = str_replace ('  ', "\t", $dom->saveXML ());

		// Convert line endings to OS-specific style
		$tabbed_dom = $this->osLineEndings ($tabbed_dom);

		// Return true if file writes successfully (0 counts as failure)
		if (@file_put_contents ($file, $tabbed_dom, LOCK_EX)) {
			// If successful, change file permissions
			@chmod ($file, 0600);

			// And return true
			return true;
		}

		// Otherwise, return false
		return false;
	}

	// Deletes a comment file
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
