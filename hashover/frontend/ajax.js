// Array of JSONP callbacks, starting with default error handler (ajax.js)
HashOverConstructor.jsonp = [
	function (json) { alert (json.message); }
];

// Send HTTP requests using either XMLHttpRequest or JSONP (ajax.js)
HashOverConstructor.prototype.ajax = function (method, path, data, callback, async)
{
	// Check if the browser supports location origin
	if (window.location.origin) {
		// If so, use it as-is
		var origin = window.location.origin;
	} else {
		// If not, construct origin manually
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
		// If so, get constructor name
		var source = this.constructor.toString ();
		var constructor = source.match (/function (\w+)/)[1];

		// Push callback into JSONP array
		this.constructor.jsonp.push (callback);

		// Add JSONP callback index and constructor to request data
		data.push ('jsonp=' + (this.constructor.jsonp.length - 1));
		data.push ('jsonp_object=' + constructor || 'HashOver');

		// Create request script
		var request = this.createElement ('script', {
			src: path + '?' + data.join ('&'),
			async: async
		});

		// And append request script to page
		document.body.appendChild (request);
	} else {
		// If not, create AJAX request
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

		// And send request
		request.open (method, path, async);
		request.setRequestHeader ('Content-type', 'application/x-www-form-urlencoded');
		request.send (data.join ('&'));
	}
};
