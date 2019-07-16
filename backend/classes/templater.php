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


class Templater
{
	// Setup information
	protected $setup;

	// Store arguments passed during instantiation
	public function __construct (Setup $setup)
	{
		$this->setup = $setup;
	}

	// Reads a file
	public function loadFile ($file)
	{
		// Attempt to read template HTML file
		$content = @file_get_contents ($file);

		// Check if template file read successfully
		if ($content !== false) {
			// If so, return trimmed HTML template
			return trim ($content);
		} else {
			// If not, throw exception
			throw new \Exception (
				'Failed to load template file.'
			);
		}
	}

	// Parses file from any location
	public function parseTemplate ($file, array $template = array ())
	{
		// Read template file
		$data = $this->loadFile ($file);

		// Local callback for preg_replace
		$parser = function ($grp) use (&$template)
		{
			// Store key for pretty code
			$key = $grp[2];

			// Store whitespace for pretty code
			$whitespace = $grp[1];

			// Return data from template if it exists
			if (!empty ($template[$key])) {
				return $whitespace . $template[$key];
			}

			// Otherwise, return nothing
			return '';
		};

		// Curly brace variable regular expression
		$curly_regex = '/(\s*)\{([a-z_-]+)\}/i';

		// Convert string to OS-specific line endings
		$template = preg_replace ('/\r\n|\r|\n/', PHP_EOL, $template);

		// Replace curly brace variable with data from template
		$template = preg_replace_callback ($curly_regex, $parser, $data);

		// Remove blank lines
		$template = preg_replace ('/^[\s\n]+$/m', '', $template);

		return $template;
	}

	// Parses file from the theme directory
	public function parseTheme ($file, array $template = array ())
	{
		// Get the file path for the configured theme
		$path = $this->setup->getThemePath ($file, false);
		$path = $this->setup->getAbsolutePath ($path);

		// Parse the theme file as template
		if (!empty ($template)) {
			return $this->parseTemplate ($path, $template);
		}

		// Otherwise, return theme file as-is
		return $this->loadFile ($path);
	}
}
