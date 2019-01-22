// Self-executing function to prevent pollution
(function () {
	// Default global instantiation target
	var instance = 'hashover';

	// Check if we have a loader instance
	if (HashOver.loaderInstance !== undefined) {
		// If so, run through global scope
		for (var variable in window) {
			// Check if current property is loader instance
			if (window[variable] === HashOver.loaderInstance) {
				// If so, set it as instantiation target
				instance = variable;
				break;
			}
		}
	}

	// Instantiate globally
	window[instance] = new HashOver ();
}) ();
