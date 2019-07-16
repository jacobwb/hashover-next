// @licstart  The following is the entire license notice for the
//  JavaScript code in this page.
//
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
//
// @licend  The above is the entire license notice for the
//  JavaScript code in this page.

"use strict";

// Latest comments API frontend constructor (constructor.js)
function HashOverLatest (options)
{
	// Reference to this HashOver object
	var hashover = this;

	// Get current date and time
	var datetime = new Date ();

	// Start queries with current client time
	var queries = [
		'time=' + HashOverLatest.getClientTime ()
	];

	// Check if options is an object
	if (options && options.constructor === Object) {
		// If so, add website to queries if present
		if (options.url !== undefined) {
			queries.push ('url=' + encodeURIComponent (options.url));
		}

		// Add website to queries if present
		if (options.website !== undefined) {
			queries.push ('website=' + encodeURIComponent (options.website));
		}

		// And add thread to queries if present
		if (options.thread !== undefined) {
			queries.push ('thread=' + encodeURIComponent (options.thread));
		}
	}

	// Backend request path
	var requestPath = HashOverLatest.backendPath + '/latest-ajax.php';

	// Handle backend request
	this.ajax ('POST', requestPath, queries, function (json) {
		// Handle error messages
		if (json.message !== undefined) {
			hashover.displayError (json, 'hashover-latest');
			return;
		}

		// Locales from HashOver backend
		HashOverLatest.prototype.locale = json.locale;

		// Setup information from HashOver back-end
		HashOverLatest.prototype.setup = json.setup;

		// Templatify UI HTML from backend
		HashOverLatest.prototype.ui = hashover.strings.templatify (json.ui);

		// Thread information from HashOver back-end
		hashover.instance = json.instance;

		// Backend execution time and memory usage statistics
		hashover.statistics = json.statistics;

		// Initiate HashOver latest comments
		hashover.init ();
	}, true);
};

// Constructor to add shared methods to (constructor.js)
var HashOverConstructor = HashOverLatest;
