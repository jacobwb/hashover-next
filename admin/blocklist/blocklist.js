// Wait for the page HTML to be parsed
document.addEventListener ('DOMContentLoaded', function () {
	// Get the "New Address" and "Save" buttons
	var newButton = document.getElementById ('new-button');
	var ipList = document.getElementById ('ip-list');
	var saveButton = document.getElementById ('save-button');

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
}, false);
