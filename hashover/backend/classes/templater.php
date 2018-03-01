<?php namespace HashOver;

// Copyright (C) 2015-2018 Jacob Barkdull
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
	public $mode;
	public $setup;
	public $template;

	protected $curlyRegex = '/\{([a-z]+):([a-z_-]+)\}/i';
	protected $newlineSearch = array ("\r\n", "\r", "\n");
	protected $newlineReplace = array ("\n", "\n", PHP_EOL);

	public function __construct ($mode = 'php', Setup $setup)
	{
		$this->mode = $mode;
		$this->setup = $setup;
	}

	public function loadFile ($file)
	{
		// Attempt to read template HTML file
		$theme_html = @file_get_contents ($file);

		// Check if template file read successfully
		if ($theme_html !== false) {
			// If so, return trimmed HTML template
			return trim ($theme_html);
		} else {
			// If not, throw exception
			throw new \Exception ('Failed to load template file.');
		}
	}

	protected function curlyVariable ($key)
	{
		return '{{' . $key . '}}';
	}

	protected function fromTemplate ($key)
	{
		if ($this->mode !== 'php') {
			return $this->curlyVariable ($key);
		}

		if (!empty ($this->template[$key])) {
			return $this->template[$key];
		}

		return '';
	}

	protected function placeholder ($id)
	{
		$span_id  = 'hashover-placeholder-' . $id;
		$span_id .= '-' . $this->fromTemplate ('permalink');

		$placeholder = new HTMLTag ('span', array (
			'id' => $span_id
		), false);

		if (!empty ($this->template[$id])) {
			$placeholder->innerHTML ($this->template[$id]);
		}

		return $placeholder->asHTML ();
	}

	protected function parser ($var)
	{
		if (empty ($var[1]) or empty ($var[2])) {
			return '';
		}

		switch ($var[1]) {
			case 'hashover': {
				return $this->fromTemplate ($var[2]);
			}

			case 'placeholder': {
				return $this->placeholder ($var[2]);
			}
		}
	}

	public function parseTemplate ($file, array $template = array ())
	{
		$this->template = $template;
		$data = $this->loadFile ($file);
		$template = preg_replace_callback ($this->curlyRegex, 'self::parser', $data);
		$template = str_replace ($this->newlineSearch, $this->newlineReplace, $template);

		return $template;
	}

	public function parseTheme ($file, array $template = array ())
	{
		// Get the file path for the configured theme
		$path = $this->setup->getThemePath ($file, false);
		$path = $this->setup->getAbsolutePath ($path);

		// Parse the theme HTML as template
		$theme = $this->parseTemplate ($path, $template);

		return $theme;
	}
}
