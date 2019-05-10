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

use AppBundle\Entity\Entry;

class EntryManager
{
	/**
	 * Clone input entry and return the cloned entry
	 */
	public function duplicateEntry($entry)
	{
		$newEntry = clone($entry);

		return $newEntry;
	}

	/**
	 * Checks if the entry is a complex case (multiple traits, taxons or mutations)
	 */
	public function isComplex($entry)
	{
		if (!$entry) {
			return false;
		}

		// we need to check if the entry has complex relations, if it does, redirect to complex edit
        if(sizeof($entry->getMutations()) > 1 || sizeof($entry->getTraits()) > 1 || sizeof($entry->getTaxonAList()) > 1 || sizeof($entry->getTaxonBList()) > 1) {
            return true;
        }

        return false;
	}
}