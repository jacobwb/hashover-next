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
var queriesList = document.getElementById ('queries-list');
var saveButton = document.getElementById ('save-button');

// Add click event handler to "Add" button
newButton.onclick = function ()
{
	// Create inputs and indentation
	var div = document.createElement ('div');
	var names = document.getElementsByClassName ('name');
	var values = document.getElementsByClassName ('value');
	var indentation = document.createTextNode ('\n\t\t\t\t\t');

	// Clone the first name and value fields
	var nameInput = names[0].cloneNode (true);
	var valueInput = values[0].cloneNode (true);

	// Remove their values
	nameInput.value = '';
	valueInput.value = '';

	// Append indentation and input to URL Query Pair list
	div.appendChild (nameInput);
	div.appendChild (indentation);
	div.appendChild (valueInput);
	queriesList.appendChild (div);
};

// Disable the "Save" button when clicked
saveButton.onclick = function () {
	this.disabled = true;
};
