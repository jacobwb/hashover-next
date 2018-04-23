// Get the full list of URL queries
var fullQueries = window.location.search.substr (1);

// Split the list into individual name and value pairs
var queries = fullQueries.split ('&');

// Initial HashOver options object
var options = {};

// Parse URL queries as HashOver options
for (var i = 0, il = queries.length; i < il; i++) {
	// Split current URL query pair into name and value array
	var queryParts = queries[i].split ('=');

	// URL query name part
	var queryName = queryParts[0];

	// URL query value part
	var queryValue = queryParts[1];

	// Check if URL query has both a name and value
	if (queryName && queryValue) {
		// If so, decode the value as a URI component
		queryValue = queryValue.replace (/\+/g, '%20');
		queryValue = decodeURIComponent (queryValue);

		// And set the option
		options[queryName] = queryValue;
	}
}

// And instantiate HashOver
var hashover = new HashOver (options);
