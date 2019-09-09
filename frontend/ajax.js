// Array of JSONP callbacks, starting with default error handler (ajax.js)
HashOverConstructor.jsonp = [
	function (json) { alert (json.message); }
];

// Send HTTP requests using JSONP as a fallback (ajax.js)
HashOverConstructor.prototype.jsonp = function (method, path, data, callback, async)
{
	// Get constructor name
	var source = this.constructor.toString ();
	var constructor = source.match (/function (\w+)/)[1];

	// Push callback into JSONP array
	this.constructor.jsonp.push (callback);

	// Add JSONP callback index and constructor to request data
	data.push ('jsonp=' + (this.constructor.jsonp.length - 1));
	data.push ('jsonp_object=' + constructor || 'HashOver');

	// Create request script
	var script = document.createElement ('script');

	// Set request script path
	script.src = path + '?' + data.join ('&');

	// Set request script to load type
	script.async = async;

	// Append request script to page
	document.body.appendChild (script);
};

// Send HTTP requests using either XMLHttpRequest or JSONP (ajax.js)
HashOverConstructor.prototype.ajax = function (method, path, data, callback, async)
{
	// Reference to this object
	var hashover = this;

	// Arguments to this method
	var args = arguments;

	// Successful request handler
	var onSuccess = function ()
	{
		// Parse JSON response
		var json = JSON.parse (this.responseText);

		// And execute callback
		callback.apply (this, [ json ]);
	};

	// CORS error handler
	var onError = function ()
	{
		// Call JSONP fallback
		hashover.jsonp.apply (hashover, args);

		// And set AJAX to use JSONP
		hashover.ajax = hashover.jsonp;
	};

	// Check for XHR with credentials support
	if ('withCredentials' in new XMLHttpRequest ()) {
		// If supported, create XHR request
		var xhr = new XMLHttpRequest ();

		// Set ready state change handler
		xhr.onreadystatechange = function ()
		{
			// Do nothing if request isn't ready
			if (this.readyState !== 4) {
				return;
			}

			// Handle successful request response
			if (this.status === 200) {
				return onSuccess.apply (this);
			}

			// Handle failed request response, likely CORS error
			if (this.status === 0) {
				return onError ();
			}
		};

		// Open XHR request
		xhr.open (method, path, async);

		// Set request headers
		xhr.setRequestHeader ('Content-type', 'application/x-www-form-urlencoded');

		// Set request to include credentials, mostly cookies
		xhr.withCredentials = true;

		// Send XHR request
		xhr.send (data.join ('&'));

		// And do nothing else
		return;
	}

	// Try to fallback to XDomainRequest if supported
	if (typeof (XDomainRequest) !== 'undefined') {
		// If so, create XDR request
		var xdr = new XDomainRequest ();

		// Open request
		xdr.open (method, path);

		// Set successful request response handler
		xdr.onload = onSuccess;

		// Set failed request response handler
		xdr.onerror = onError;

		// Send XDR request
		setTimeout (xdr.send, 0);

		// And do nothing else
		return;
	}

	// If all else fails fallback to JSONP
	onError ();
};
