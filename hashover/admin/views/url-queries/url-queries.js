// Wait for the page HTML to be parsed
document.addEventListener ('DOMContentLoaded', function () {
	// Get the "New Query Pair" and "Save" buttons
	var newButton = document.getElementById ('new-button');
	var queriesList = document.getElementById ('queries-list');
	var saveButton = document.getElementById ('save-button');

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
}, false);
