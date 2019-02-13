// Get main HashOver UI element (displayerror.js)
HashOverConstructor.prototype.displayError = function (json, id)
{
	// Get main HashOver element
	var mainElement = this.getMainElement (id);

	// Error message HTML code
	var messageHTML = '<b>HashOver</b>: ' + json.message;

	// Display error in main HashOver element
	mainElement.innerHTML = messageHTML;
};
