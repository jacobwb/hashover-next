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

// Count link API frontend constructor (constructor.js)
function HashOverCountLink ()
{
	// Get count link class elements
	var countLinks = document.getElementsByClassName ('hashover-count-link');

	// Run through the count links
	for (var i = 0, il = countLinks.length; i < il; i++) {
		var link = countLinks[i];

		// Instantiate new count link object
		this.getCommentCount (link, {
			url: link.href
		});
	}
};

// Constructor to add shared methods to (constructor.js)
var HashOverConstructor = HashOverCountLink;
