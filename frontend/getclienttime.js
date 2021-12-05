// Returns current client time zone (getclienttime.js)
HashOverConstructor.getClientTimeZone = function ()
{
	return (new Date()).getTimezoneOffset();
};
