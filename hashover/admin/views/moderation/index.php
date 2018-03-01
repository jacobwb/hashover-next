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


try {
	// View setup
	require (realpath ('../view-setup.php'));

	// Create comment thread table
	$table = new HTMLTag ('table', array (
		'id' => 'threads',
		'class' => 'striped-rows-odd',
		'cellspacing' => '0',
		'cellpadding' => '4'
	));

	// Get comment threads
	$threads = $hashover->thread->queryThreads ();

	// Run through comment threads
	foreach ($threads as $thread) {
		// Create table row and cell
		$tr = new HTMLTag ('tr');
		$td = new HTMLTag ('td');

		// Read and parse JSON metadata file
		$data = $hashover->thread->data->readMeta ('page-info', $thread);

		// Check if metadata was read successfully
		if ($data === false or empty ($data['url']) or empty ($data['title'])) {
			continue;
		}

		// Create thread hyperlink
		$thread_link = new HTMLTag ('a', array (
			'href' => 'threads.php?' . implode ('&', array (
				'thread=' . urlencode ($thread),
				'title=' . urlencode ($data['title']),
				'url=' . urlencode ($data['url'])
			)),

			'innerHTML' => $data['title']
		));

		// Append thread hyperlink to cell
		$td->appendChild ($thread_link);

		// Append page URL to row
		$td->appendChild (new HTMLTag ('p', new HTMLTag ('small', $data['url'])));

		// Append cell to row
		$tr->appendChild ($td);

		// Append row to table
		$table->appendChild ($tr);
	}

	// Template data
	$template = array (
		'title'		=> 'Moderation',
		'logout'	=> $logout->asHTML ("\t\t\t"),
		'sub-title'	=> 'Post, edit, approve, and delete comments',
		'threads'	=> $table->asHTML ("\t\t")
	);

	// Load and parse HTML template
	echo $hashover->templater->parseTemplate ('moderation.html', $template);

} catch (\Exception $error) {
	$misc = new Misc ('php');
	$message = $error->getMessage ();
	$misc->displayError ($message);
}
