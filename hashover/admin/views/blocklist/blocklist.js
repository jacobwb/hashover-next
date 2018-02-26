document.addEventListener ('DOMContentLoaded', function () {
	// Get the "New Address" and "Save" buttons
	var newButton = document.getElementById ('new-button');
	var ipList = document.getElementById ('ip-list');
	var saveButton = document.getElementById ('save-button');

	newButton.onclick = function ()
	{
		// Create input and indentation
		var input = document.createElement ('input');
		var indentation = document.createTextNode ('\n\t\t\t\t');

		// Add attributes
		input.type = 'text';
		input.name = 'addresses[]';
		input.size = '15';
		input.maxlength = '15';
		input.placeholder = '127.0.0.1';
		input.title = 'IP Address or blank to remove';

		// Append indentation and input to IP address list
		ipList.appendChild (indentation);
		ipList.appendChild (input);
	};

	// Disable the "Save" button when clicked
	saveButton.onclick = function ()
	{
		this.disabled = true;
	}
}, false);
