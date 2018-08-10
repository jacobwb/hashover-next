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
	// Create comments script
	var ui = document.createElement ('script');

	// This script
	var script = HashOver.script;

	// Comments script path
	var path = HashOver.rootPath + '/comments.php';

	// Check if the options are an object
	if (options && options.constructor === Object) {
		// If so, add settings object to request if they exist
		if (options.settings.constructor === Object) {
			path += '?settings=' + encodeURIComponent (
				JSON.stringify (options.settings)
			);
		}

		// And store options globally for later use
		window.hashoverOptions = options;
	}

	// Set HashOver script attributes
	ui.async = true;
	ui.src = path;

	// Add script to page
	script.parentNode.insertBefore (ui, script.nextSibling);
};

// Constructor to add methods to
var HashOverConstructor = HashOver;
