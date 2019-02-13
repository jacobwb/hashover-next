// Returns instantiated pseudo-namespaced ID (prefix.js)
HashOverConstructor.prototype.prefix = function (id)
{
	// Initial prefix
	var prefix = 'hashover';

	// Append instance number to prefix
	if (this.instanceNumber > 1) {
		prefix += '-' + this.instanceNumber;
	}

	// Return prefixed ID if one is given
	if (id) {
		return prefix + '-' + id;
	}

	// Otherwise, return prefix by itself
	return prefix;
};
