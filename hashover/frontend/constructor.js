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

// Initial HashOver constructor (constructor.js)
function HashOver (options)
{
	// Reference to this HashOver object
	var hashover = this;

	// Self-executing backend wait loop
	(function backendWait () {
		// Check if we're on the first instance or if HashOver is prepared
		if (HashOver.prepared === true || HashOver.instanceCount === 0) {
			// If so, execute the real constructor
			HashOver.instantiator.call (hashover, options);
		} else {
			// If not, check again in 100 milliseconds
			setTimeout (backendWait, 100);
		}
	}) ();
};

// Constructor to add HashOver methods to
var HashOverConstructor = HashOver;

// Indicator that backend information has been received (constructor.js)
HashOver.prepared = false;

// Initial HashOver instance count (constructor.js)
HashOver.instanceCount = 0;
