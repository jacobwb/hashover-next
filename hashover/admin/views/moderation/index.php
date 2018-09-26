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


// Adds a row to a table
function add_table_row (HTMLTag $table, HTMLTag $child)
{
	// Create table row and column
	$tr = new HTMLTag ('tr');
	$td = new HTMLTag ('td');

	// Append child element to column
	$td->appendChild ($child);

	// Append column to row
	$tr->appendChild ($td);

	// Append row to table
	$table->appendChild ($tr);
}

try {
	// View setup
	require (realpath ('../view-setup.php'));

	// Attempt to get array of comment threads
	$threads = $hashover->thread->queryThreads ();

	// Create comment thread table
	$threads_table = new HTMLTag ('table', array (
		'id' => 'threads',
		'class' => 'striped-rows-odd',
		'cellspacing' => '0',
		'cellpadding' => '4'
	));

	// Run through comment threads
	foreach ($threads as $thread) {
		// Read and parse JSON metadata file
		$data = $hashover->thread->data->readMeta ('page-info', $thread);

		// Check if metadata was read successfully
		if ($data === false or empty ($data['url']) or empty ($data['title'])) {
			continue;
		}

		// Create row div
		$div = new HTMLTag ('div');

		// Add thread hyperlink to row div
		$div->appendChild (new HTMLTag ('a', array (
			'href' => 'threads.php?' . implode ('&', array (
				'thread=' . urlencode ($thread),
				'title=' . urlencode ($data['title']),
				'url=' . urlencode ($data['url'])
			)),

			'innerHTML' => $data['title']
		)));

		// Add thread URL line to row div
		$div->appendChild (new HTMLTag ('p', array (
			'children' => array (new HTMLTag ('small', $data['url'], false))
		)));

		// And row div to table
		add_table_row ($threads_table, $div);
	}

	// Template data
	$template = array (
		'title'		=> $hashover->locale->text['moderation'],
		'logout'	=> $logout->asHTML ("\t\t\t"),
		'sub-title'	=> $hashover->locale->text['moderation-sub'],
		'threads'	=> $threads_table->asHTML ("\t\t")
	);

	// Load and parse HTML template
	echo $hashover->templater->parseTemplate ('moderation.html', $template);

} catch (\Exception $error) {
	echo Misc::displayError ($error->getMessage ());
}
