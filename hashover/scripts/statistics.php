<?php namespace HashOver;

// Copyright (C) 2010-2017 Jacob Barkdull
// This file is part of HashOver.
//
// HashOver is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// HashOver is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


// Display source code
if (basename ($_SERVER['PHP_SELF']) === basename (__FILE__)) {
	if (isset ($_GET['source'])) {
		header ('Content-type: text/plain; charset=UTF-8');
		exit (file_get_contents (basename (__FILE__)));
	} else {
		exit ('<b>HashOver</b>: This is a class file.');
	}
}

class Statistics
{
	public $mode;
	public $executionStart;
	public $executionEnd;
	public $executionTime;
	public $scriptMemory;
	public $systemMemory;

	public function __construct ($mode = 'php')
	{
		$this->mode = $mode;
	}

	// Script execution starting time
	public function executionStart ()
	{
		// Start time in seconds
		$this->executionStart = microtime (true);
	}

	// Script execution ending time
	public function executionEnd ()
	{
		// End time in seconds
		$this->executionEnd = microtime (true);

		// Difference between start time and end time in seconds
		$this->executionTime = $this->executionEnd - $this->executionStart;

		// Unit to divided memory bytes by (1,024² for mibibytes)
		$division_unit = pow (1024, 2);

		// Memory the script consumed divided by division unit
		$this->scriptMemory = round (memory_get_peak_usage () / $division_unit, 2);

		// Memory the system consumed divided by division unit
		$this->systemMemory = round (memory_get_peak_usage (true) / $division_unit, 2);

		// Display execution time in millisecond(s)
		if ($this->executionTime < 1) {
			$execution_stat = round ($this->executionTime * 1000, 5) . ' ms';
		} else {
			// Display execution time in seconds
			$execution_stat = round ($this->executionTime, 5) . ' Second';

			// Add plural to any execution time other than one
			if ($this->executionTime !== 1) {
				$execution_stat .= 's';
			}
		}

		// Statistics inner-comment
		$statistics = PHP_EOL . PHP_EOL;
		$statistics .= "\t" . 'HashOver Statistics:' . PHP_EOL . PHP_EOL;
		$statistics .= "\t\t" . 'Execution Time     : ' . $execution_stat . PHP_EOL;
		$statistics .= "\t\t" . 'Script Memory Peak : ' . $this->scriptMemory . ' MiB' . PHP_EOL;
		$statistics .= "\t\t" . 'System Memory Peak : ' . $this->systemMemory . ' MiB';
		$statistics .= PHP_EOL . PHP_EOL;

		// Return statistics as JavaScript comment
		if ($this->mode === 'javascript') {
			return PHP_EOL . '/*' . $statistics . '*/';
		}

		// Return statistics as HTML comment by default
		return PHP_EOL . '<!--' . $statistics . '-->';
	}
}
