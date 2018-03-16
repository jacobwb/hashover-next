<?php namespace HashOver;

// Copyright (C) 2018 Jacob Barkdull
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


// Do some standard HashOver setup work
require ('php-setup.php');

try {
	// Instantiate SourceCode class
	$source_code = new SourceCode ();

	// Check if a file is requested
	if (isset ($_GET['file'])) {
		// Get return type
		$type = !empty ($_GET['type']) ? $_GET['type'] : 'text';

		// Display source code
		$source_code->display ($_GET['file'], $type);
	} else {
		// Instantiate HashOver class
		$hashover = new \HashOver ();
		$hashover->initiate ();
		$hashover->finalize ();

		// Create table for source code files
		$table = new HTMLTag ('table', array (
			'id' => 'threads',
			'class' => 'striped-rows-even column-borders',
			'cellspacing' => '0',
			'cellpadding' => '4'
		));

		// Append column headers row
		$table->appendChild (new HTMLTag ('tr', array (
			'children' => array (
				new HTMLTag ('td', new HTMLTag ('b', array (
					'innerHTML' => $hashover->locale->text['type']
				), false), false),

				new HTMLTag ('td', new HTMLTag ('b', array (
					'innerHTML' => $hashover->locale->text['name']
				), false), false),

				new HTMLTag ('td', new HTMLTag ('b', array (
					'innerHTML' => $hashover->locale->text['path']
				), false), false),

				new HTMLTag ('td', new HTMLTag ('b', array (
					'innerHTML' => $hashover->locale->text['view-as']
				), false), false)
			)
		)));

		// Run through HashOver files array
		foreach ($source_code->files as $file) {
			$path = $hashover->setup->getHttpPath ($file['path']);
			$name = !empty ($file['name']) ? $file['name'] : basename ($path);
			$href = '?file=' . $file['path'];

			// Create row and columns
			$tr = new HTMLTag ('tr', array (
				'children' => array (
					new HTMLTag ('td', $file['type'], false),
					new HTMLTag ('td', $name, false),
					new HTMLTag ('td', $path, false)
				)
			));

			// Append view formats column
			$tr->appendChild (new HTMLTag ('td', array (
				'class' => 'margin-right-children',

				'children' => array (
					new HTMLTag ('a', array (
						'href' => $href . '&amp;type=text',
						'innerHTML' => $hashover->locale->text['text']
					), false),

					new HTMLTag ('a', array (
						'href' => $href . '&amp;type=html',
						'innerHTML' => 'HTML'
					), false),

					new HTMLTag ('a', array (
						'href' => $href . '&amp;type=download',
						'innerHTML' => $hashover->locale->text['download']
					), false)
				)
			)));

			// Append row to table
			$table->appendChild ($tr);
		}

		// Load and parse HTML template
		echo $hashover->templater->parseTemplate ('source-viewer.html', array (
			'title' => $hashover->locale->text['source-code'],
			'sub-title' => $hashover->locale->text['source-code-sub'],
			'files' => $table->asHTML ("\t\t")
		));
	}
} catch (\Exception $error) {
	$misc = new Misc ('php');
	$message = $error->getMessage ();
	$misc->displayError ($message);
}
