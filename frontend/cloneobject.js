// Returns a clone of an object (cloneobject.js)
HashOver.prototype.cloneObject = function (object)
{
	return JSON.parse (JSON.stringify (object));
};
