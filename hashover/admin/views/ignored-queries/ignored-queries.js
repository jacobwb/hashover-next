document.addEventListener ('DOMContentLoaded', function () {
	// Get the "New Query Pair" and "Save" buttons
	var newButton = document.getElementById ('new-button');
	var queriesList = document.getElementById ('queries-list');
	var saveButton = document.getElementById ('save-button');

	newButton.onclick = function ()
	{
		// Create inputs and indentation
		var div = document.createElement ('div');
		var nameInput = document.createElement ('input');
		var valueInput = document.createElement ('input');
		var indentation = document.createTextNode ('\n\t\t\t\t\t');

		// Add query name input attributes
		nameInput.type = 'text';
		nameInput.name = 'names[]';
		nameInput.size = '15';
		nameInput.placeholder = 'Name';
		nameInput.title = 'Query name or blank to remove';

		// Add query value input attributes
		valueInput.type = 'text';
		valueInput.name = 'values[]';
		valueInput.size = '25';
		valueInput.placeholder = 'Value';
		valueInput.title = 'Query value or blank for any value';

		// Append indentation and input to URL Query Pair list
		div.appendChild (nameInput);
		div.appendChild (indentation);
		div.appendChild (valueInput);
		queriesList.appendChild (div);
	};

	// Disable the "Save" button when clicked
	saveButton.onclick = function ()
	{
		this.disabled = true;
	}
}, false);
