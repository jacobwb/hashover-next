// Copyright (C) 2018-2019 Jacob Barkdull
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


// Get the full list of URL queries
var fullQueries = window.location.search.substring (1);

// Split the list into individual name and value pairs
var queries = fullQueries.split ('&');

// Initial HashOver options object
var options = {};

// Parse URL queries as HashOver options
for (var i = 0, il = queries.length; i < il; i++) {
	// Split current URL query pair into name and value array
	var queryParts = queries[i].split ('=');

	// URL query name part
	var queryName = queryParts[0];

	// URL query value part
	var queryValue = queryParts[1];

	// Check if URL query has both a name and value
	if (queryName && queryValue) {
		// If so, decode the value as a URI component
		queryValue = queryValue.replace (/\+/g, '%20');
		queryValue = decodeURIComponent (queryValue);

		// And set the option
		options[queryName] = queryValue;
	}
}

// Lastly override some settings
options.settings = {
	// Use default theme
	theme: 'default',

	// Sort comments newest first by default
	defaultSorting: 'by-date',

	// We always want the theme appended to the page
	appendsCss: true,

	// We don't want the comments collapsed in anyway
	collapsesInterface: false,
	collapsesComments: false,

	// Place the comment form at the bottom to deemphasize it
	formPosition: 'bottom',

	// Since passwords aren't required as admin, disable them
	passwordField: 'off'
};

// And instantiate HashOver
var hashover = new HashOver ('hashover', options);
