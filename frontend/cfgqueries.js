// Converts an object in a series of URL queries (cfgqueries.js)
HashOverConstructor.cfgQueries = function (value, name, queries)
{
	// Current URL query matrix
	name = name || [];

	// All settings URL queries to return
	queries = queries || [];

	// Check if value is an object
	if (typeof (value) !== 'object') {
		// If so, get query matrix as string
		var matrix = '[' + name.join ('][') + ']';

		// Encode current URL query value
		var value = encodeURIComponent (value);

		// Add current URL query to return array
		queries.push ('cfg' + matrix + '=' + value);

		// And do nothing else
		return;
	}

	// Otherwise, descend in setting object
	for (var key in value) {
		HashOverConstructor.cfgQueries (value[key], name.concat (key), queries);
	}

	// And return settings URL queries
	return queries;
};
