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

	// Default URL Query Pair array
	$ignored_queries = array ();

	// Ignored URL queries JSON file location
	$queries_file = $hashover->setup->getAbsolutePath ('config/ignored-queries.json');

	// Check if the form has been submitted
	if (!empty ($_POST['names']) and is_array ($_POST['names'])
	    and !empty ($_POST['values']) and is_array ($_POST['values']))
	{
		// If so, run through submitted queries
		for ($i = 0, $il = count ($_POST['names']); $i < $il; $i++) {
			// Add each non-empty query to the pair array
			if (!empty ($_POST['names'][$i])) {
				// Start the query pair with the query name
				$query_pair = $_POST['names'][$i];

				// Check if the query has a value
				if (!empty ($_POST['values'][$i])) {
					// If so, add it to the pair
					$query_pair .= '=' . $_POST['values'][$i];
				}

				// Add the query pair to the URL Query Pair array
				$ignored_queries[] = $query_pair;
			}
		}

		// Save the JSON data to the URL Query Pairs file
		if ($hashover->login->verifyAdmin ()
		    and $data_files->saveJSON ($queries_file, $ignored_queries))
		{
			// Redirect with success indicator
			header ('Location: index.php?status=success');
		} else {
			// Redirect with failure indicator
			header ('Location: index.php?status=failure');
		}

		// Exit after redirect
		exit;
	}

	// Otherwise, load and parse URL Query Pairs file
	$json = $data_files->readJSON ($queries_file);

	// Check for JSON parse error
	if (is_array ($json)) {
		$ignored_queries = $json;
	}

	// URL Query Pair inputs
	$inputs = new HTMLTag ('span');

	// Create URL Query Pair inputs
	for ($i = 0, $il = max (3, count ($ignored_queries)); $i < $il; $i++) {
		// Use URL query pairs from file or blank
		$query = !empty ($ignored_queries[$i]) ? $ignored_queries[$i] : '';

		// Split query pair into name and value
		$query_parts = explode ('=', $query);
		$query_name = $query_parts[0];
		$query_value = !empty ($query_parts[1]) ? $query_parts[1] : '';

		// Create input tag
		$input = new HTMLTag ('div', array (
			'children' => array (
				new HTMLTag ('input', array (
					'class' => 'name',
					'type' => 'text',
					'name' => 'names[]',
					'value' => $query_name,
					'size' => '15',
					'placeholder' => $hashover->locale->text['name'],
					'title' => $hashover->locale->text['url-queries-name-tip']
				), false, true),

				new HTMLTag ('input', array (
					'class' => 'value',
					'type' => 'text',
					'name' => 'values[]',
					'value' => $query_value,
					'size' => '25',
					'placeholder' => $hashover->locale->text['value'],
					'title' => $hashover->locale->text['url-queries-value-tip']
				), false, true)
			)
		));

		// Add input to inputs container
		$inputs->appendChild ($input);
	}

	// Template data
	$template = array (
		'title'		=> $hashover->locale->text['url-queries-title'],
		'logout'	=> $logout->asHTML ("\t\t\t"),
		'sub-title'	=> $hashover->locale->text['url-queries-sub'],
		'message'	=> $form_message,
		'inputs'	=> $inputs->getInnerHTML ("\t\t\t\t"),
		'save-button'	=> $hashover->locale->text['save']
	);

	// Load and parse HTML template
	echo $hashover->templater->parseTemplate ('url-queries.html', $template);

} catch (\Exception $error) {
	$misc = new Misc ('php');
	$message = $error->getMessage ();
	$misc->displayError ($message);
}
