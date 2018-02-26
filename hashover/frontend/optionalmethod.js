// Calls a method that may or may not exist (optionalmethod.js)
HashOverConstructor.prototype.optionalMethod = function (name, args, object)
{
	var method = object ? this[object][name] : this[name];
	var context = object ? this[object] : this;

	// Check if the method exists
	if (method && typeof (method) === 'function') {
		return method.apply (context, args);
	}
};
