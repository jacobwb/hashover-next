// @licstart  The following is the entire license notice for the
//  JavaScript code in this page.
//
// Copyright (C) 2010-2019 Jacob Barkdull
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

// Initial loader constructor (loader-constructor.js)
function HashOver (id, options, instance)
{
	// Reference to this object
	var object = this;

	// Arguments to this method
	var args = arguments;

	// Check if the frontend is ready
	if (HashOver.frontendReady === true) {
		// If so, create comment thread
		this.createThread.apply (this, args);

		// And do nothing else
		return;
	}

	// Check if frontend is loading
	if (HashOver.frontendReady === 'loading') {
		// If so, loop until frontend is loaded
		setTimeout (function () {
			object.constructor.apply (object, args);
		}, 10);

		// And do nothing else
		return;
	}

	// Otherwise, set frontend as loading
	HashOver.frontendReady = 'loading';

	// Create and add frontend script to page
	HashOver.createScript (options);

	// And try to instantiate again
	object.constructor.apply (object, args);
};

// Creates and adds HashOver frontend script to page (loader-constructor.js)
HashOver.createScript = function (options)
{
	// Create frontend script
	var frontend = document.createElement ('script');

	// Frontend script path
	var path = HashOver.rootPath + '/comments.php?nodefault';

	// Some elements around this script
	var parent = HashOver.script.parentNode;
	var sibling = HashOver.script.nextSibling;

	// Check if the options are an object
	if (options && options.constructor === Object) {
		// If so, add settings object to request if they exist
		if (options.settings && options.settings.constructor === Object) {
			// Get cfg URL queries array
			var cfgQueries = HashOver.cfgQueries (options.settings);

			// And add cfg queries to frontend script path
			path += '&' + cfgQueries.join ('&');
		}
	}

	// Set HashOver script attributes
	frontend.async = true;
	frontend.src = path;

	// Add script to page
	parent.insertBefore (frontend, sibling);
};

// Set frontend as not ready (loader-constructor.js)
HashOver.frontendReady = false;

// Constructor to add methods to (loader-constructor.js)
var HashOverConstructor = HashOver;
