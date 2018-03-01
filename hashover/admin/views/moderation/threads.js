var fullQueries = window.location.search.substr (1);
var queries = fullQueries.split ('&');
var options = {};

// Parse URL queries as HashOver options
for (var i = 0, il = queries.length; i < il; i++) {
	var queryParts = queries[i].split ('=');
	var queryName = queryParts[0];
	var queryValue = queryParts[1];

	if (queryName && queryValue) {
		queryValue = queryValue.replace (/\+/g, '%20');
		queryValue = decodeURIComponent (queryValue);

		// Set option
		options[queryName] = queryValue;
	}
}

// Instantiate HashOver
var hashover = new HashOver (options);
