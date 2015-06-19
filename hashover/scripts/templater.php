<?php

	// Copyright (C) 2015 Jacob Barkdull
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

	class Templater
	{
		public $mode;
		public $input;
		public $comment;

		public
		function __construct ($input, $mode = 'javascript')
		{
			$this->mode = $mode;
			$this->input = addcslashes (trim ($input), "'");
		}

		protected
		function fromComment ($key)
		{
			if ($this->mode != 'php') {
				return '\' + (json.' . $key . ' || \'\') + \'';
			}

			if (!empty ($key)) {
				if (!empty ($this->comment[$key])) {
					return $this->comment[$key];
				}
			}

			return '';
		}

		protected
		function fromVariable ($key = '')
		{
			if ($this->mode != 'php') {
				return '\' + (template.' . $key . ' || \'\') + \'';
			}

			return $this->fromComment ($key);
		}

		protected
		function placeholder ($id)
		{
			$placeholder = new HTMLTag ('span', false, false);
			$placeholder->createAttribute ('id', 'hashover-placeholder-' . $id);
			$placeholder->appendAttribute ('id', '-' . $this->fromVariable ('permalink'), false);

			if (!empty ($this->comment[$id])) {
				$placeholder->innerHTML ($this->comment[$id]);
			}

			return $placeholder->asHTML ();
		}

		protected
		function mainCallback ($var)
		{
			if (empty ($var[1])) {
				return '';
			}

			switch ($var[1]) {
				case 'hashover': {
					return $this->fromVariable ($var[2]);
					break;
				}

				case 'comment': {
					return $this->fromComment ($var[2]);
					break;
				}

				case 'placeholder': {
					return $this->placeholder ($var[2]);
					break;
				}
			}
		}

		public
		function parseTemplate (array $comment = array ())
		{
			$this->comment = $comment;
			$template = preg_replace_callback ('/{([a-z]+):([a-z_-]+)}/i', 'self::mainCallback', $this->input);

			return str_replace ("+ '' +", '+', $template);
		}
	}

?>
