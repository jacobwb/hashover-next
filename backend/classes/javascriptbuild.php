<?php namespace HashOver;

// Copyright (C) 2018-2019 Jacob Barkdull
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


class JavaScriptBuild
{
	protected $directory;
	protected $files = array ();

	public function __construct ($directory = '.')
	{
		$this->changeDirectory ($directory);
	}

	public function changeDirectory ($directory = '.')
	{
		$this->directory = trim ($directory, '/') . '/';
	}

	protected function addFile ($file)
	{
		// Add file to files array if it isn't already present
		if (!in_array ($file, $this->files, true)) {
			$this->files[] = $file;
		}
	}

	protected function addDependencies ($file, array $dependencies)
	{
		// Add each dependency to files array
		foreach ($dependencies as $dependency) {
			$dependency = $this->directory . $dependency;

			// Check if the file exists
			if (file_exists ($file)) {
				// If so, add file to files array
				$this->addFile ($dependency);
			} else {
				// If not, throw exception on failure
				throw new \Exception (sprintf (
					'"%s" depends on "%s" but it does not exist.',
					$file, $dependency
				));
			}
		}

		return true;
	}

	protected function includeFile ($file)
	{
		// Attempt to read JavaScript file
		$file = @file_get_contents ($file);

		// Check if the file read successfully
		if ($file !== false) {
			// If so, return the contents
			return trim ($file);
		}

		// Otherwise throw exception
		throw new \Exception (
			sprintf ('Unable to include "%s"', $file)
		);
	}

	public function registerFile ($file, array $options = array ())
	{
		$file = $this->directory . $file;

		if (!empty ($options)) {
			// Check if there is an include condition
			if (isset ($options['include'])) {
				// If so, return void if include is false
				if ($options['include'] === false) {
					return;
				}
			}

			// Add optional dependencies to files array
			if (!empty ($options['dependencies'])) {
				$dependencies = $options['dependencies'];
				$this->addDependencies ($file, $dependencies);
			}
		}

		// Check if the file exists
		if (file_exists ($file)) {
			// If so, add file to files array
			$this->addFile ($file);
		} else {
			// If not, throw exception
			throw new \Exception (
				sprintf ('"%s" does not exist.', $file)
			);
		}

		return true;
	}

	public function build ($minify = false, $minify_level = 0)
	{
		// Array for included JavaScript files
		$files = array ();

		// Attempt to include registered JavaScript files
		foreach ($this->files as $file) {
			$files[] = $this->includeFile ($file);
		}

		// Join the included JavaScript files
		$javascript = implode (PHP_EOL . PHP_EOL, $files);

		// Minify the JavaScript if told to
		if (!isset ($_GET['unminified'])) {
			if ($minify === true and $minify_level > 0) {
				// Instantiate JavaScript minification class
				$minifier = new JavaScriptMinifier ();

				// Minify JavaScript build result
				$minified = $minifier->minify ($javascript, $minify_level);

				// Set minified result as JavaScript output
				$javascript = $minified;
			}
		}

		// Return normal JavaScript code
		return $javascript;
	}
}
