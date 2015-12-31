<?php

// Copyright (C) 2015 Jacob Barkdull
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

class Markdown
{
	protected $codeTags = array ();
	protected $codeTagCount = 0;

	// Markdown patterns to search for
	public $search = array (
		'/__([\s\S]*?)__/',
		'/\*\*([^ ])([\s\S]*?)([^ ])\*\*/',
		'/\*([^ \*])([\s\S]*?)([^ \*])\*/',
		'/([^a-z0-9])_([\s\S]*?)_([^a-z0-9])/i',
		'/~~([^ ])([\s\S]*?)([^ ])~~/'
	);

	// HTML replacements for markdown patterns
	public $replace = array (
		'<u>\\1</u>',
		'<strong>\\1\\2\\3</strong>',
		'<em>\\1\\2\\3</em>',
		'\\1<u>\\2</u>\\3',
		'<s>\\1\\2\\3</s>'
	);

	// Replaces markdown for code with a placeholder
	protected function codeReplace ($grp)
	{
		$codePlaceholder = 'CODE_MARKDOWN[' . $this->codeTagCount . ']';
		$this->codeTags[$this->codeTagCount] = trim ($grp[1], PHP_EOL);
		$this->codeTagCount++;

		return $codePlaceholder;
	}

	// Returns the original markdown code with HTML replacement
	protected function codeReturn ($grp) {
		return '<code class="hashover-inline">' . $this->codeTags[($grp[1])] . '</code>';
	}

	// Parses a string as markdown
	public function parseMarkdown ($string)
	{
		$this->codeTagCount = 0;
		$this->codeTags = array ();

		// Break string into paragraphs
		$paragraphs = explode (PHP_EOL . PHP_EOL, $string);

		// Run through each paragraph replacing markdown patterns
		for ($i = 0, $il = count ($paragraphs); $i < $il; $i++) {
			$paragraphs[$i] = preg_replace_callback ('/`([\s\S]*?)`/', 'self::codeReplace', $paragraphs[$i]);
			$paragraphs[$i] = preg_replace($this->search, $this->replace, $paragraphs[$i]);
			$paragraphs[$i] = preg_replace_callback ('/CODE_MARKDOWN\[([0-9]+)\]/', 'self::codeReturn', $paragraphs[$i]);
		}

		// Return joined paragraphs
		return implode (PHP_EOL . PHP_EOL, $paragraphs);
	}
}
