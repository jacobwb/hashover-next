<?php namespace HashOver;

// Copyright (C) 2015-2019 Jacob Barkdull
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


class Markdown
{
	// Matches a markdown code block
	protected $blockCodeRegex = '/```([\s\S]+?)```/S';

	// Matches a paragraph/double line break
	protected $paragraphRegex = '/(?:\r\n|\r|\n){2}/S';

	// Matches markdown inline code
	protected $inlineCodeRegex = '/(^|[^a-z0-9`])`((?!`)[\s\S]+?)`([^a-z0-9`]|$)/iS';

	// Array for inline code and code block markers
	protected $codeMarkers = array (
		'block' => array ('marks' => array (), 'count' => 0),
		'inline' => array ('marks' => array (), 'count' => 0)
	);

	// Markdown patterns to search for
	protected $search = array (
		// Matches **bold** text
		'/\*\*([^ *])([\s\S]+?)([^ *])\*\*/S',

		// Matches *italic* text
		'/\*([^ *])([\s\S]+?)([^ *])\*/S',

		// Matches _underlined_ text
		'/(^|\W)_((?!_)[\s\S]+?)_(\W|$)/S',

		// Matches forced __underlined__ text
		'/__([^ _])([\s\S]+?)([^ _])__/S',

		// Matches ~~strikethrough~~ text
		'/~~([^ ~])([\s\S]+?)([^ ~])~~/S'
	);

	// HTML replacements for markdown patterns
	protected $replace = array (
		'<strong>\\1\\2\\3</strong>',
		'<em>\\1\\2\\3</em>',
		'\\1<u>\\2</u>\\3',
		'<u>\\1\\2\\3</u>',
		'<s>\\1\\2\\3</s>'
	);

	// Replaces markdown for inline code with a marker
	protected function codeReplace ($grp, $display)
	{
		$markName = 'CODE_' . strtoupper ($display);
		$markCount = $this->codeMarkers[$display]['count']++;

		if ($display !== 'block') {
			$codeMarker = $grp[1] . $markName . '[' . $markCount . ']' . $grp[3];
			$this->codeMarkers[$display]['marks'][$markCount] = trim ($grp[2], "\r\n");
		} else {
			$codeMarker = $markName . '[' . $markCount . ']';
			$this->codeMarkers[$display]['marks'][$markCount] = trim ($grp[1], "\r\n");
		}

		return $codeMarker;
	}

	// Replaces markdown for code block with a marker
	protected function blockCodeReplace ($grp)
	{
		return $this->codeReplace ($grp, 'block');
	}

	// Replaces markdown for inline code with a marker
	protected function inlineCodeReplace ($grp)
	{
		return $this->codeReplace ($grp, 'inline');
	}

	// Returns the original inline markdown code with HTML replacement
	protected function inlineCodeReturn ($grp)
	{
		return '<code class="hashover-inline">' . $this->codeMarkers['inline']['marks'][($grp[1])] . '</code>';
	}

	// Returns the original markdown code block with HTML replacement
	protected function blockCodeReturn ($grp)
	{
		return '<code>' . $this->codeMarkers['block']['marks'][($grp[1])] . '</code>';
	}

	// Parses a string as markdown
	public function parseMarkdown ($string)
	{
		// Reset marker arrays
		$this->codeMarkers = array (
			'block' => array ('marks' => array (), 'count' => 0),
			'inline' => array ('marks' => array (), 'count' => 0)
		);

		// Replace code blocks with markers
		$string = preg_replace_callback ($this->blockCodeRegex, 'self::blockCodeReplace', $string);

		// Break string into paragraphs
		$paragraphs = preg_split ($this->paragraphRegex, $string);

		// Run through each paragraph
		for ($i = 0, $il = count ($paragraphs); $i < $il; $i++) {
			// Replace inline code with markers
			$paragraphs[$i] = preg_replace_callback ($this->inlineCodeRegex, 'self::inlineCodeReplace', $paragraphs[$i]);

			// Replace markdown patterns
			$paragraphs[$i] = preg_replace ($this->search, $this->replace, $paragraphs[$i]);

			// Replace markers with original markdown code
			$paragraphs[$i] = preg_replace_callback ('/CODE_INLINE\[([0-9]+)\]/S', 'self::inlineCodeReturn', $paragraphs[$i]);
		}

		// Join paragraphs
		$string = implode (PHP_EOL . PHP_EOL, $paragraphs);

		// Replace code block markers with original markdown code
		$string = preg_replace_callback ('/CODE_BLOCK\[([0-9]+)\]/S', 'self::blockCodeReturn', $string);

		return $string;
	}
}
