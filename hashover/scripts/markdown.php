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
	public    $codeRegex = '/(^|[^a-z0-9`])`([\s\S]+?)`([^a-z0-9`]|$)/i';
	protected $codePlaceholders = array ();
	protected $codeCount = 0;

	// Markdown patterns to search for
	public $search = array (
		'/\*\*([^ *])([\s\S]+?)([^ *])\*\*/',
		'/\*([^ *])([\s\S]+?)([^ *])\*/',
		'/(^|[^a-z0-9_])_([^_]+?)_([^a-z0-9_]|$)/i',
		'/__([^ _])([\s\S]+?)([^ _])__/',
		'/~~([^ ~])([\s\S]+?)([^ ~])~~/'
	);

	// HTML replacements for markdown patterns
	public $replace = array (
		'<strong>\\1\\2\\3</strong>',
		'<em>\\1\\2\\3</em>',
		'\\1<u>\\2</u>\\3',
		'<u>\\1\\2\\3</u>',
		'<s>\\1\\2\\3</s>'
	);

	// Replaces markdown for code with a placeholder
	protected function codeReplace ($grp)
	{
		$codePlaceholder = $grp[1] . 'CODE_MARKDOWN[' . $this->codeCount . ']' . $grp[3];
		$this->codePlaceholders[$this->codeCount] = trim ($grp[2], PHP_EOL);
		$this->codeCount++;

		return $codePlaceholder;
	}

	// Returns the original markdown code with HTML replacement
	protected function codeReturn ($grp) {
		return '<code class="hashover-inline">' . $this->codePlaceholders[($grp[1])] . '</code>';
	}

	// Parses a string as markdown
	public function parseMarkdown ($string)
	{
		$this->codeCount = 0;
		$this->codePlaceholders = array ();

		// Break string into paragraphs
		$paragraphs = explode (PHP_EOL . PHP_EOL, $string);

		// Run through each paragraph replacing markdown patterns
		for ($i = 0, $il = count ($paragraphs); $i < $il; $i++) {
			$paragraphs[$i] = preg_replace_callback ($this->codeRegex, 'self::codeReplace', $paragraphs[$i]);
			$paragraphs[$i] = preg_replace($this->search, $this->replace, $paragraphs[$i]);
			$paragraphs[$i] = preg_replace_callback ('/CODE_MARKDOWN\[([0-9]+)\]/', 'self::codeReturn', $paragraphs[$i]);
		}

		// Return joined paragraphs
		return implode (PHP_EOL . PHP_EOL, $paragraphs);
	}
}
