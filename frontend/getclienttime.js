// Returns current client 24-hour time (getclienttime.js)
HashOverConstructor.getClientTime = function ()
{
	// Get current date and time
	var datetime = new Date ();

	// Get 24-hour current time
	var hours = datetime.getHours ();
	var minutes = datetime.getMinutes ();
	var time = hours + ':' + minutes;

	return time;
}
