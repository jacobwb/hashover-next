// @licstart  The following is the entire license notice for the
//  JavaScript code in this page.
//
// Copyright (C) 2010-2018 Jacob Barkdull
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
function HashOver (options)
{
	// Check if the frontend is ready
	if (HashOver.frontendReady === true) {
		// Call frontend constructor
		this.frontendConstructor (options);

		// And do nothing else
		return;
	}

	// Do nothing is frontend is loading
	if (HashOver.frontendReady === 'loading') {
		return;
	}

	// Otherwise, create frontend script
	var frontend = document.createElement ('script');

	// Frontend script path
	var path = HashOver.rootPath + '/comments.php';

	// Some elements around this script
	var parent = HashOver.script.parentNode;
	var sibling = HashOver.script.nextSibling;

	// Store this instance as constructor property
	HashOver.loaderInstance = this;

	// Check if the options are an object
	if (options && options.constructor === Object) {
		// If so, add settings object to request if they exist
		if (options.settings.constructor === Object) {
			path += '?settings=' + encodeURIComponent (
				JSON.stringify (options.settings)
			);
		}

		// And store the options
		HashOver.loaderOptions = options;
	}

	// Set HashOver script attributes
	frontend.async = true;
	frontend.src = path;

	// Add script to page
	parent.insertBefore (frontend, sibling);

	// And set frontend as loading
	HashOver.frontendReady = 'loading';
};

// Set frontend as not ready (loader-constructor.js)
HashOver.frontendReady = false;

// Constructor to add methods to
var HashOverConstructor = HashOver;
