// @licstart  The following is the entire license notice for the
//  JavaScript code in this page.
//
// Copyright (C) 2018-2021 Jacob Barkdull
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
function HashOverLatest (id, options, instance)
{
	// Reference to this HashOver object
	var hashover = this;

	// Check if we are instantiating a specific instance
	var specific = this.rx.integer.test (instance);

	// Use given instance or instance count
	var instance = specific ? instance : HashOverLatest.instanceCount;

	// Backend request path
	var requestPath = HashOverLatest.backendPath + '/latest-ajax.php';

	// Get backend queries
	var backendQueries = this.getBackendQueries (options, instance, false);

	// Add current client time to queries
	var queries = backendQueries.concat ([
		'tz=' + HashOverLatest.getClientTimeZone ()
	]);

	// Set instance number
	this.instanceNumber = instance;

	// Store options and backend queries
	this.options = options;
	this.queries = backendQueries;

	// Handle backend request
	this.ajax ('POST', requestPath, queries, function (json) {
		// Given element ID or default
		var id = id || hashover.prefix ('latest');

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
		hashover.init (id);
	}, true);

	// And increment instance count where appropriate
	if (specific === false) {
		HashOverLatest.instanceCount++;
	}
};

// Indicator that backend information has been received (constructor.js)
HashOverLatest.backendReady = false;

// Initial HashOver instance count (constructor.js)
HashOverLatest.instanceCount = 1;

// Constructor to add shared methods to (constructor.js)
var HashOverConstructor = HashOverLatest;
