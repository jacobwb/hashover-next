<?php

	// Copyright (C) 2014 Jacob Barkdull
	//
	//	This program is free software: you can redistribute it and/or modify
	//	it under the terms of the GNU Affero General Public License as
	//	published by the Free Software Foundation, either version 3 of the
	//	License, or (at your option) any later version.
	//
	//	This program is distributed in the hope that it will be useful,
	//	but WITHOUT ANY WARRANTY; without even the implied warranty of
	//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	//	GNU Affero General Public License for more details.
	//
	//	You should have received a copy of the GNU Affero General Public License
	//	along with this program.  If not, see <http://www.gnu.org/licenses/>.


	// Display source code
	if (isset($_GET['source']) and basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		header('Content-type: text/plain; charset=UTF-8');
		exit(file_get_contents(basename(__FILE__)));
	}

	class Statistics extends HashOver {
		private $exec_time, $exec_start, $exec_end;

		// Script execution starting time
		public function execution_start() {
			$this->exec_time = explode(' ', microtime());
			$this->exec_start = $this->exec_time[1] + $this->exec_time[0];
		}

		// Script execution ending time
		public function execution_end() {
			$this->exec_time = explode(' ', microtime());
			$this->exec_end = $this->exec_time[1] + $this->exec_time[0];
			$this->exec_time = ($this->exec_end - $this->exec_start);

			$statistics  = "\t\t" . 'Execution Time     : ' . round($this->exec_time, 5) . ' Seconds' . PHP_EOL;
			$statistics .= "\t\t" . 'Script Memory Peak : ' . round(memory_get_peak_usage() / 1048576, 2) . 'Mb' . PHP_EOL;
			$statistics .= "\t\t" . 'System Memory Peak : ' . round(memory_get_peak_usage(true) / 1048576, 2) . 'Mb' . PHP_EOL;

			if ($this->mode != 'php') {
				echo '/*' . PHP_EOL;
				echo "\t" . 'Statistics:' . PHP_EOL . PHP_EOL;
				echo $statistics;
				echo '*/';
			} else {
				echo '<!--' . PHP_EOL;
				echo "\t" . 'HashOver Statistics:' . PHP_EOL . PHP_EOL;
				echo $statistics;
				echo '-->';
			}
		}
	}

?>
