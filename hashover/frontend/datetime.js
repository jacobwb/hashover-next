// Add date and time regular expressions (datetime.js)
HashOverConstructor.prototype.regex.dateTime = {
	offset: /[0-9]{2}/g,
	dashes: /-/g
};

// Simple PHP date function port (datetime.js)
HashOverConstructor.prototype.getDateTime = function (format, date)
{
	// Format parameter or default
	format = format || 'DATE_ISO8601';

	// Date parameter or default
	date = date || new Date ();

	// Get hours from date, in 24 hour format
	var hours = date.getHours ();

	// AM/PM based if 24 hour time is above 12 hours
	var ampm = (hours >= 12) ? 'pm' : 'am';

	// Get day from date, as a number
	var day = date.getDate ();

	// Get weekday from date, as a number
	var weekDay = date.getDay ();

	// Get localized weekday, Sunday through Saturday
	var dayName = this.parent.locale['day-names'][weekDay];

	// Get month from date, as an index 0-11
	var monthIndex = date.getMonth ();

	// Get localized month, January through December
	var monthName = this.parent.locale['month-names'][monthIndex];

	// Convert 24 hour time to 12 hour
	var hours12 = (hours % 12) ? hours % 12 : 12;

	// Get minutes from date
	var minutes = date.getMinutes ();

	// Get month from date, as a number 1-12
	var month = monthIndex + 1;

	// Get timezone offset in hours from date
	var offsetHours = (date.getTimezoneOffset() / 60) * 100;

	// Add leading zero to timezone offset
	var offset = ((offsetHours < 1000) ? '0' : '') + offsetHours;

	// Convert timezone offset to time format, -07:00
	var offsetColon = offset.match (this.regex.dateTime.offset).join (':');

	// Timezone offset positivity
	var offsetPositivity = (offsetHours > 0) ? '-' : '+';

	// Get seconds from date
	var seconds = date.getSeconds ();

	// Get year from date
	var year = date.getFullYear ();

	// Format characters, see: http://php.net/manual/en/function.date.php
	var characters = {
		a: ampm,
		A: ampm.toUpperCase (),
		d: (day < 10) ? '0' + day : day,
		D: dayName.substr (0, 3),
		F: monthName,
		g: hours12,
		G: hours,
		h: (hours12 < 10) ? '0' + hours12 : hours12,
		H: (hours < 10) ? '0' + hours : hours,
		i: (minutes < 10) ? '0' + minutes : minutes,
		j: day,
		l: dayName,
		m: (month < 10) ? '0' + month : month,
		M: monthName.substr (0, 3),
		n: month,
		N: weekDay + 1,
		O: offsetPositivity + offset,
		P: offsetPositivity + offsetColon,
		s: (seconds < 10) ? '0' + seconds : seconds,
		w: weekDay,
		y: ('' + year).substr (2),
		Y: year
	};

	// Convert dashes to underscores
	var dateConstant = format.replace (this.regex.dateTime.dashes, '_');

	// Convert constant to uppercase
	dateConstant = dateConstant.toUpperCase ();

	// Pre-defined constants, see: http://php.net/manual/en/class.datetime.php
	switch (dateConstant) {
		case 'DATE_ATOM':
		case 'DATE_RFC3339':
		case 'DATE_W3C': {
			format = 'Y-m-d\\TH:i:sP';
			break;
		}

		case 'DATE_COOKIE': {
			format = 'l, d-M-Y H:i:s';
			break;
		}

		case 'DATE_ISO8601': {
			format = 'Y-m-d\\TH:i:sO';
			break;
		}

		case 'DATE_RFC822':
		case 'DATE_RFC1036': {
			format = 'D, d M y H:i:s O';
			break;
		}

		case 'DATE_RFC850': {
			format = 'l, d-M-y H:i:s';
			break;
		}

		case 'DATE_RFC1123':
		case 'DATE_RFC2822':
		case 'DATE_RSS': {
			format = 'D, d M Y H:i:s O';
			break;
		}

		case 'GNOME_DATE': {
			format = 'D M d, g:i A';
			break;
		}

		case 'US_DATE': {
			format = 'm/d/Y';
			break;
		}

		case 'STANDARD_DATE': {
			format = 'Y-m-d';
			break;
		}

		case '12H_TIME': {
			format = 'g:ia';
			break;
		}

		case '24H_TIME': {
			format = 'H:i';
			break;
		}
	}

	// Split format into individual characters
	var formatParts = format.split ('');

	// Run through format characters
	for (var i = 0, il = formatParts.length; i < il; i++) {
		// Current format character
		var c = formatParts[i];

		// Skip escaped characters
		if (i > 0 && formatParts[i - 1] === '\\') {
			formatParts[i - 1] = '';
			continue;
		}

		// Replace characters with date/time
		formatParts[i] = characters[c] || c;
	}

	return formatParts.join ('');
};
