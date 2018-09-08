// Real constructor (instantiator.js)
HashOver.instantiator = function (options)
{
	// Reference to this object
	var hashover = this;

	// Get backend queries
	var queries = HashOver.getBackendQueries (options);

	// Increment HashOver instance count
	HashOver.instanceCount++;

	// Backend request path
	var requestPath = HashOver.backendPath + '/comments-ajax.php';

	// Handle backend request
	this.ajax ('POST', requestPath, queries, function (json) {
		// Handle error messages
		if (json.message !== undefined) {
			hashover.displayError (json);
			return;
		}

		// Set the backend information
		if (HashOver.backendReady !== true) {
			// Locales from backend
			HashOver.prototype.locale = json.locale;

			// Setup information from backend
			HashOver.prototype.setup = json.setup;

			// Initial UI HTML
			HashOver.prototype.ui = {};

			// Templatify UI HTML from backend
			for (var name in json.ui) {
				// Check if property wasn't inherited
				if (json.ui.hasOwnProperty (name) === true) {
					// If so, templatify current string
					var template = hashover.strings.templatify (json.ui[name]);

					// And store the template
					HashOver.prototype.ui[name] = template;
				}
			}

			// Mark backend as ready
			HashOver.backendReady = true;
		}

		// Thread information from backend
		hashover.instance = json.instance;

		// Backend execution time and memory usage statistics
		hashover.statistics = json.statistics;

		// Initiate HashOver
		hashover.init ();
	}, true);

	// Set instance number to current instance count
	this.instanceNumber = HashOver.instanceCount;
};
