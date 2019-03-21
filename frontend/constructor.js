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

// Initial constructor or use loader constructor (constructor.js)
var HashOver = HashOver || function HashOver (id, options, instance) {
	this.createThread.apply (this, arguments);
};

// Set frontend as ready (constructor.js)
HashOver.frontendReady = true;

// Indicator that backend information has been received (constructor.js)
HashOver.backendReady = false;

// Initial HashOver instance count (constructor.js)
HashOver.instanceCount = 1;

// Constructor to add shared methods to (constructor.js)
var HashOverConstructor = HashOver;
