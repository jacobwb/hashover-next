// Execute a callback when the page HTML is parsed and ready (onready.js)
HashOverConstructor.onReady = function (callback)
{
	// Check if document HTML has been parsed
	if (document.readyState === 'interactive') {
		// If so, execute callback immediately
		callback ();
	} else {
		// If not, execute callback after the DOM is parsed
		document.addEventListener ('DOMContentLoaded', function () {
			callback ();
		}, false);
	}
};
