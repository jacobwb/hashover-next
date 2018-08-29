<?php namespace HashOver;

// Copyright (C) 2018 Jacob Barkdull
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


try {
	// View setup
	require (realpath ('../view-setup.php'));

	// Load and parse HTML template
	echo $hashover->templater->parseTemplate ('threads.html', array (
		'title' => $hashover->locale->text['moderation'],
		'back' => $hashover->locale->text['back'],
		'logout' => $logout->asHTML ("\t\t\t")
	));
} catch (\Exception $error) {
	echo Misc::displayError ($error->getMessage ());
}
