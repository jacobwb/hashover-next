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


// Get the "+" and "Save" buttons
var newButton = document.getElementById ('new-button');
var ipList = document.getElementById ('ip-list');
var saveButton = document.getElementById ('save-button');

// Add click event handler to "Add" button
newButton.onclick = function ()
{
	// Create input and indentation
	var addresses = document.getElementsByClassName ('addresses');
	var indentation = document.createTextNode ('\n\t\t\t\t');

	// Clone the first address field
	var input = addresses[0].cloneNode (true);

	// Remove its value
	input.value = '';

	// Append indentation and input to IP address list
	ipList.appendChild (indentation);
	ipList.appendChild (input);
};

// Disable the "Save" button when clicked
saveButton.onclick = function () {
	this.disabled = true;
};
