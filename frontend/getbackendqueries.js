// Get supported HashOver backend queries from options (getbackendqueries.js)
HashOverConstructor.prototype.getBackendQueries = function (options, instance, auto)
{
	// Ensure options is an object
	options = options || {};

	// URL query data object
	var data = {};

	// URL queries array
	var queries = [];

	// Add instance number to data
	data.instance = instance;

	// Check if a URL was given
	if (options.url && typeof (options.url) === 'string') {
		// If so, use it as-is
		data.url = options.url;
	} else {
		// If not, automatically detect page URL if told to
		if (auto !== false) {
			data.url = HashOverConstructor.getURL (options.canonical);
		}
	}

	// Check if a title was given
	if (options.title && typeof (options.title) === 'string') {
		// If so, use it as-is
		data.title = options.title;
	} else {
		// If not, automatically detect page title if told to
		if (auto !== false) {
			data.title = HashOverConstructor.getTitle ();
		}
	}

	// Add website to request if told to
	if (options.website && typeof (options.website) === 'string') {
		data.website = options.website;
	}

	// Add thread to request if told to
	if (options.thread && typeof (options.thread) === 'string') {
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
		var cfgQueries = HashOverConstructor.cfgQueries (options.settings);

		// And merge cfg URL queries with existing queries
		queries = queries.concat (cfgQueries);
	}

	// And return queries
	return queries;
};
