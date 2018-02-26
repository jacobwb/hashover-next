var queryParts, queryName, queryValue;
var fullQueries = window.location.search.substr (1);
var queries = fullQueries.split ('&');
var options = {};

// Parse URL queries as HashOver options
for (var i = 0, il = queries.length; i < il; i++) {
	queryParts = queries[i].split ('=');
	queryName = queryParts[0];
	queryValue = queryParts[1].replace (/\+/g, '%20');
	queryValue = decodeURIComponent (queryValue);

	// Set option
	options[queryName] = queryValue;
}

// Instantiate HashOver
var hashover = new HashOver (options);
