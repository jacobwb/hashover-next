// Get supported HashOver backend queries from options (getbackendqueries.js)
HashOver.getBackendQueries = function (options, instance)
{
	// Ensure options is an object
	options = options || {};

	// URL query data object
	var data = {};

	// Options passed to loader constructor
	var loaderOptions = HashOver.loaderOptions;

	// URL queries array
	var queries = [];

	// Check for carryover options from loader constructor
	if (loaderOptions && loaderOptions.constructor === Object) {
		// If present, merge them into given options
		for (var key in loaderOptions) {
			if (options[key] === undefined) {
				options[key] = loaderOptions[key];
			}
		}
	}

	// Add instance number to data
	data.instance = instance;

	// Use URL and title options if available
	data.url = options.url || this.getURL (options.canonical);
	data.title = options.title || this.getTitle ();

	// Add website to request if told to
	if (typeof (options.website) === 'string') {
		data.website = options.website;
	}

	// Add thread to request if told to
	if (typeof (options.thread) === 'string') {
		data.thread = options.thread;
	}

	// Convert URL query data into query strings array
	for (var name in data) {
		if (data.hasOwnProperty (name) === true) {
			queries.push (name + '=' + encodeURIComponent (data[name]));
		}
	}

	// Add loader settings object to request if they exist
	if (options.settings && options.settings.constructor === Object) {
		// Get cfg URL queries array
		var cfgQueries = HashOver.cfgQueries (loaderOptions.settings);

		// And add cfg queries
		queries = queries.concat (cfgQueries);
	}

	// Set request backend information for the first instance
	if (HashOver.backendReady !== true) {
		queries.push ('prepare=true');
	}

	return queries;
};
