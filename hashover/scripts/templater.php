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
	public $comment;

	protected $newline_search = array ("\r\n", "\r", "\n");
	protected $newline_replace = array ("\n", "\n", PHP_EOL);

	public function __construct ($mode = 'php', Setup $setup)
	{
		$this->mode = $mode;
		$this->setup = $setup;
		$theme = $this->setup->getAbsolutePath ('themes/' . $this->setup->theme . '/layout.html');

		// Use default theme if theme in settings doesn't exist
		if (!file_exists ($theme)) {
			$theme = $this->setup->getAbsolutePath ('themes/default/layout.html');
		}

		// Load and escape HTML template
		$this->theme = addcslashes (trim (file_get_contents ($theme)), "\\'");
	}

	protected function fromComment ($key)
	{
		if ($this->mode !== 'php') {
			return '\' + (comment[\'' . $key . '\'] || \'\') + \'';
		}

		if (!empty ($this->comment[$key])) {
			return $this->comment[$key];
		}

		return '';
	}

	protected function fromVariable ($key = '')
	{
		if ($this->mode !== 'php') {
			return '\' + (template[\'' . $key . '\'] || \'\') + \'';
		}

		return $this->fromComment ($key);
	}

	protected function placeholder ($id)
	{
		$span_id  = 'hashover-placeholder-' . $id;
		$span_id .= '-' . $this->fromVariable ('permalink');

		$placeholder = new HTMLTag ('span', array (
			'id' => $span_id
		), false);

		if (!empty ($this->comment[$id])) {
			$placeholder->innerHTML ($this->comment[$id]);
		}

		return $placeholder->asHTML ();
	}

	protected function mainCallback ($var)
	{
		if (empty ($var[1]) or empty ($var[2])) {
			return '';
		}

		switch ($var[1]) {
			case 'hashover': {
				return $this->fromVariable ($var[2]);
			}

			case 'comment': {
				return $this->fromComment ($var[2]);
			}

			case 'placeholder': {
				return $this->placeholder ($var[2]);
			}
		}
	}

	public function parseTemplate (array $comment = array ())
	{
		$this->comment = $comment;
		$template = preg_replace_callback ('/{([a-z]+):([a-z_-]+)}/i', 'self::mainCallback', $this->theme);
		$template = str_replace ($this->newline_search, $this->newline_replace, $template);

		return str_replace ("+ '' +", '+', $template);
	}
}
