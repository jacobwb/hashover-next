<?php namespace HashOver;

// Copyright (C) 2015-2017 Jacob Barkdull
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

class Templater
{
	public $mode;
	public $setup;
	public $theme;
	public $template;

	protected $curlyRegex = '/\{([a-z]+):([a-z_-]+)\}/i';
	protected $newlineSearch = array ("\r\n", "\r", "\n");
	protected $newlineReplace = array ("\n", "\n", PHP_EOL);

	public function __construct ($mode = 'php', Setup $setup)
	{
		$this->mode = $mode;
		$this->setup = $setup;
		$relative_path = 'themes/' . $this->setup->theme . '/layout.html';
		$theme = $this->setup->getAbsolutePath ($relative_path);

		// Use default theme if theme in settings doesn't exist
		if (!file_exists ($theme)) {
			$relative_path = 'themes/default/layout.html';
			$theme = $this->setup->getAbsolutePath ($relative_path);
		}

		// Attempt to read template HTML file
		$theme_html = @file_get_contents ($theme);

		// Check if template file read successfully
		if ($theme_html !== false) {
			// If so, load and escape HTML template
			$this->theme = addcslashes (trim ($theme_html), "\\'");
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

	public function parseTemplate (array $template = array ())
	{
		$this->template = $template;
		$template = preg_replace_callback ($this->curlyRegex, 'self::parser', $this->theme);
		$template = str_replace ($this->newlineSearch, $this->newlineReplace, $template);

		return $template;
	}
}
