// Storage for JSONP callbacks (ajax.js)
HashOverConstructor.jsonp = [];

// Push error handler function into JSONP callbacks array (ajax.js)
HashOverConstructor.jsonp.push (function (json) {
	alert (json.message);
});

// Send HTTP requests using either XMLHttpRequest or JSONP (ajax.js)
HashOverConstructor.prototype.ajax = function (method, path, data, callback, async)
{
	// Workaround for IE 11
	if (window.location.origin) {
		var origin = window.location.origin;
	} else {
		var protocol = window.location.protocol;
		var hostname = window.location.hostname;
		var port = window.location.port;

		// Final origin
		var origin = protocol + '//' + hostname + (port ? ':' + port : '');
	}

	// Create origin regular expression
	var originRegex = new RegExp ('^' + origin + '/', 'i');

	// Check if script is being remotely accessed
	if (originRegex.test (this.constructor.script.src) === false) {
		// If so, use JSONP
		this.constructor.jsonp.push (callback);

		// Add JSONP indicator to request path
		data.push ('jsonp=' + (this.constructor.jsonp.length - 1));

		// Create request script
		var request = this.elements.create ('script', {
			src: path + '?' + data.join ('&'),
			async: async
		});

		// Append request script to page
		document.body.appendChild (request);
	} else {
		// Create request
		var request = new XMLHttpRequest ();

		// Set callback as ready state change handler
		request.onreadystatechange = function ()
		{
			// Do nothing if request wasn't successful in a meaningful way
			if (this.readyState !== 4 || this.status !== 200) {
				return;
			}

			// Parse response as JSON
			var json = JSON.parse (this.responseText);

			// Execute callback
			callback.apply (this, [ json ]);
		};

		// Send request
		request.open (method, path, async);
		request.setRequestHeader ('Content-type', 'application/x-www-form-urlencoded');
		request.send (data.join ('&'));
	}
};
