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


// Wait for the page HTML to be parsed
document.addEventListener ('DOMContentLoaded', function () {
	// Get message element
	var message = document.getElementById ('message');

	// Hide message after 5 seconds
	if (message !== null) {
		setTimeout (function () {
			message.className = message.className.replace ('success', 'hide');
		}, 1000 * 5);
	}
}, false);
