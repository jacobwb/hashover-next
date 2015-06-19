<?php

	// Copyright (C) 2010-2015 Jacob Barkdull
	//
	//	This file is part of HashOver.
	//
	//	HashOver is free software: you can redistribute it and/or modify
	//	it under the terms of the GNU Affero General Public License as
	//	published by the Free Software Foundation, either version 3 of the
	//	License, or (at your option) any later version.
	//
	//	HashOver is distributed in the hope that it will be useful,
	//	but WITHOUT ANY WARRANTY; without even the implied warranty of
	//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	//	GNU Affero General Public License for more details.
	//
	//	You should have received a copy of the GNU Affero General Public License
	//	along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


	// Display source code
	if (basename ($_SERVER['PHP_SELF']) === basename (__FILE__)) {
		if (isset ($_GET['source'])) {
			header ('Content-type: text/plain; charset=UTF-8');
			exit (file_get_contents (basename (__FILE__)));
		}
	}

	class Statistics
	{
		public $mode;
		public $exec_start;
		public $exec_end;
		public $exec_time;
		public $script_memory;
		public $system_memory;

		public
		function __construct ($mode = 'javascript')
		{
			$this->mode = $mode;
		}

		// Script execution starting time
		public
		function executionStart ()
		{
			// Start time in seconds
			$this->exec_start = microtime (true);
		}

		// Script execution ending time
		public
		function executionEnd ()
		{
			// End time in seconds
			$this->exec_end = microtime (true);

			// Difference between start time and end time in seconds
			$this->exec_time = $this->exec_end - $this->exec_start;

			// Unit to divided memory bytes by (1,024² for mibibytes)
			$division_unit = pow (1024, 2);

			// Memory the script consumed divided by division unit
			$this->script_memory = round (memory_get_peak_usage () / $division_unit, 2);

			// Memory the system consumed divided by division unit
			$this->system_memory = round (memory_get_peak_usage (true) / $division_unit, 2);

			// Display execution time in millisecond(s)
			if ($this->exec_time < 1) {
				$exec_stat = round ($this->exec_time * 1000, 5) . ' ms';
			} else {
				// Display execution time in seconds
				$exec_stat = round ($this->exec_time, 5) . ' Second';

				// Add plural to any execution time other than one
				if ($this->exec_time !== 1) {
					$exec_stat .= 's';
				}
			}

			// Statistics inner-comment
			$statistics = PHP_EOL . PHP_EOL;
			$statistics .= "\t" . 'HashOver Statistics:' . PHP_EOL . PHP_EOL;
			$statistics .= "\t\t" . 'Execution Time     : ' . $exec_stat . PHP_EOL;
			$statistics .= "\t\t" . 'Script Memory Peak : ' . $this->script_memory . ' MiB' . PHP_EOL;
			$statistics .= "\t\t" . 'System Memory Peak : ' . $this->system_memory . ' MiB';
			$statistics .= PHP_EOL . PHP_EOL;

			// Return statistics as HTML comment in PHP mode
			if ($this->mode === 'php') {
				return PHP_EOL . '<!--' . $statistics . '-->';
			}

			// Return statistics as JavaScript comment by default
			return PHP_EOL . '/*' . $statistics . '*/';
		}
	}

?>
