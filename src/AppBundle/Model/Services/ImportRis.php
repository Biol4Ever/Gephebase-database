<?php

/*

 Copyright 2019 CNRS.

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License,
 or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

namespace AppBundle\Model\Services;

class ImportRis
{
	function import($file, $form = true) {
		if($form) {
			if($file->getData()) {
				$file = $file->getData()->getRealPath();
			} else {
				return array('error' => 'First parameter is not from a form. Please set second parameter to false and set real path to first parameter');
			}
		}
		// Open the file 
		$array = $this->fileReader($file);
		if(!$array) {
			return array('error' => 'Cannot open the file.');
		}

		return $array;
	}

	function fileReader($file) {
		if (($handle = fopen($file, "r")) !== FALSE) {
			$current = 0;
			while(($line = fgets($handle, 4096)) !== false ) {		
				$argument = substr($line,0 ,2);
				if($argument == 'TY') {
					$current++;
				}
				if($argument == 'AU' || $argument == 'A1' || $argument == 'A2' || $argument == 'A3' || $argument == 'A4') {
					$element = substr($line,6);
					$array[$current]['AU'][] = $element;
				} elseif($argument != 'ER' || $argument != '' || $argument != null) {
					$element = substr($line,6);
					$array[$current][$argument] = $element;
				}
			}
			fclose($handle);
			return $array;
		} else {
			return null;
		}
	}
}