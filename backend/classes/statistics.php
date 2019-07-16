<?php namespace HashOver;

// Copyright (C) 2010-2019 Jacob Barkdull
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


class Statistics
{
	protected $executionStart;
	protected $executionEnd;
	protected $executionMicroTime;

	public $executionTime;
	public $scriptMemory;
	public $systemMemory;

	// Starts script execution time in seconds
	public function executionStart ()
	{
		$this->executionStart = microtime (true);
	}

	// Script execution ending time
	public function executionEnd ($mode = 'javascript')
	{
		// End time in seconds
		$this->executionEnd = microtime (true);

		// Difference between start time and end time in seconds
		$this->executionMicroTime = $this->executionEnd - $this->executionStart;

		// Unit to divided memory bytes by (1,024² for mibibytes)
		$division_unit = pow (1024, 2);

		// Memory the script consumed divided by division unit
		$script_memory = round (memory_get_peak_usage () / $division_unit, 2);
		$this->scriptMemory = $script_memory . ' MiB';

		// Memory the system consumed divided by division unit
		$system_memory = round (memory_get_peak_usage (true) / $division_unit, 2);
		$this->systemMemory = $system_memory . ' MiB';

		// Display execution time in millisecond(s)
		if ($this->executionMicroTime < 1) {
			$this->executionTime = round ($this->executionMicroTime * 1000, 5) . ' ms';
		} else {
			// Display execution time in seconds
			$this->executionTime = round ($this->executionMicroTime, 5) . ' Second';

			// Add plural to any execution time other than one
			if ($this->executionMicroTime !== 1) {
				$this->executionTime .= 's';
			}
		}

		// Statistics inner-comment
		$statistics  = PHP_EOL . PHP_EOL;
		$statistics .= "\t" . 'HashOver Statistics' . PHP_EOL . PHP_EOL;
		$statistics .= "\t" . 'Execution Time     : ' . $this->executionTime . PHP_EOL;
		$statistics .= "\t" . 'Script Memory Peak : ' . $this->scriptMemory . PHP_EOL;
		$statistics .= "\t" . 'System Memory Peak : ' . $this->systemMemory;
		$statistics .= PHP_EOL . PHP_EOL;

		// Return statistics as JavaScript comment
		if ($mode !== 'php') {
			return PHP_EOL . '/*' . $statistics . '*/';
		}

		// Return statistics as HTML comment by default
		return PHP_EOL . '<!--' . $statistics . '-->';
	}
}
