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

	// Get current website
	$current_website = $hashover->setup->website;

	// Attempt to get website from GET data
	$website = $hashover->setup->getRequest ('website', $current_website);

	// Set website if GET website is different
	if ($website !== $current_website) {
		$hashover->setup->setWebsite ($website);
	}

	// Attempt to get array of comment threads
	$threads = $hashover->thread->queryThreads ();

	// Create comment thread table
	$threads_table = new HTMLTag ('table', array (
		'id' => 'threads',
		'class' => 'striped-rows-odd',
		'cellspacing' => '0',
		'cellpadding' => '8'
	));

	// Add first row as header if multiple website support is enabled
	if ($hashover->setup->supportsMultisites === true) {
		add_table_row ($threads_table, new HTMLTag ('b', $website, false));
	}

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
				'website=' . urlencode ($website),
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
		'left-id'	=> 'threads-column',
		'threads'	=> $threads_table->asHTML ("\t\t\t\t\t"),
	);

	// Check if multiple website support is enabled
	if ($hashover->setup->supportsMultisites === true) {
		// If so, attempt to get array of websites
		$websites = $hashover->thread->queryWebsites ();

		// Check if other websites exist
		if (count ($websites) > 1) {
			// If so, create comment thread table
			$websites_table = new HTMLTag ('table', array (
				'id' => 'websites',
				'class' => 'striped-rows-odd',
				'cellspacing' => '0',
				'cellpadding' => '8'
			));

			// Add first row as header
			add_table_row ($websites_table, new HTMLTag ('b', array (
				'innerHTML' => $hashover->locale->text['website'][1],
			), false));

			// Sort the websites
			sort ($websites, SORT_NATURAL);

			// Run through website directories
			foreach ($websites as $name) {
				// Skip current website
				if ($name === $website) {
					continue;
				}

				// Create website hyperlink
				add_table_row ($websites_table, new HTMLTag ('a', array (
					'href' => '?website=' . urlencode ($name),
					'innerHTML' => $name
				)));
			}

			// And add other websites to template
			$template = array_merge ($template, array (
				'right-id'	=> 'websites-column',
				'websites'	=> $websites_table->asHTML ("\t\t\t\t\t")
			));
		}
	}

	// Load and parse HTML template
	echo $hashover->templater->parseTemplate ('moderation.html', $template);

} catch (\Exception $error) {
	echo Misc::displayError ($error->getMessage ());
}
