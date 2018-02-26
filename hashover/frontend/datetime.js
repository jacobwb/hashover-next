// Collection of convenient date and time functions (datetime.js)
HashOverConstructor.prototype.dateTime = {
	offsetRegex: /[0-9]{2}/g,
	dashesRegex: /-/g,

	// Simple PHP date function port
	format: function (format, date)
	{
		format = format || 'DATE_ISO8601';
		date = date || new Date ();

		var hours = date.getHours ();
		var ampm = (hours >= 12) ? 'pm' : 'am';
		var day = date.getDate ();
		var weekDay = date.getDay ();
		var dayName = this.parent.locale['day-names'][weekDay];
		var monthIndex = date.getMonth ();
		var monthName = this.parent.locale['month-names'][monthIndex];
		var hours12 = (hours % 12) ? hours % 12 : 12;
		var minutes = date.getMinutes ();
		var month = monthIndex + 1;
		var offsetHours = (date.getTimezoneOffset() / 60) * 100;
		var offset = ((offsetHours < 1000) ? '0' : '') + offsetHours;
		var offsetColon = offset.match (this.offsetRegex).join (':');
		var offsetPositivity = (offsetHours > 0) ? '-' : '+';
		var seconds = date.getSeconds ();
		var year = date.getFullYear ();
		var dateConstant;

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

		dateConstant = format.replace (this.dashesRegex, '_');
		dateConstant = dateConstant.toUpperCase ();

		switch (dateConstant) {
			case 'DATE_ATOM':
			case 'DATE_RFC3339':
			case 'DATE_W3C': {
				format = 'Y-m-d\TH:i:sP';
				break;
			}

			case 'DATE_COOKIE': {
				format = 'l, d-M-Y H:i:s';
				break;
			}

			case 'DATE_ISO8601': {
				format = 'Y-m-d\TH:i:sO';
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

		var formatParts = format.split ('');

		for (var i = 0, c, il = formatParts.length; i < il; i++) {
			if (i > 0 && formatParts[i - 1] === '\\') {
				formatParts[i - 1] = '';
				continue;
			}

			c = formatParts[i];
			formatParts[i] = characters[c] || c;
		}

		return formatParts.join ('');
	}
};
