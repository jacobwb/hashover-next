// Returns false if key event is the enter key (formevents.js)
HashOver.prototype.enterCheck = function (event)
{
	return (event.keyCode === 13) ? false : true;
};

// Prevents enter key on inputs from submitting form (formevents.js)
HashOver.prototype.preventSubmit = function (form)
{
	// Get login info inputs
	var infoInputs = form.getElementsByClassName ('hashover-input-info');

	// Set enter key press to return false
	for (var i = 0, il = infoInputs.length; i < il; i++) {
		infoInputs[i].onkeypress = this.enterCheck;
	}
};
