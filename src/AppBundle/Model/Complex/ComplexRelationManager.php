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

namespace AppBundle\Model\Complex;

use Doctrine\Common\Collections\ArrayCollection;

class ComplexRelationManager
{
	private $originalReferences;

	/** 
	 * Retrieve all original traits and store them in an ArrayCollection
	 */
	public function getOriginalTraits($entry)
	{
		$originalTraits = new ArrayCollection();
        foreach ($entry->getTraits() as $trait) {
            $originalTraits->add($trait);
        }

        return $originalTraits;
	}

	/** 
	 * Retrieve all original taxon A and store them in an ArrayCollection
	 */
	public function getOriginalTaxonAs($entry)
	{
		$originalTaxonAs = new ArrayCollection();
        foreach ($entry->getTaxonAList() as $taxon) {
            $originalTaxonAs->add($taxon);
        }

        return $originalTaxonAs;
	}

	/** 
	 * Retrieve all original taxon B and store them in an ArrayCollection
	 */
	public function getOriginalTaxonBs($entry)
	{
		$originalTaxonBs = new ArrayCollection();
        foreach ($entry->getTaxonBList() as $taxon) {
            $originalTaxonBs->add($taxon);
        }

        return $originalTaxonBs;
	}

	/** 
	 * Retrieve all original mutations and store them in an ArrayCollection
	 */
	public function getOriginalMutations($entry)
	{
		$originalMutations = new ArrayCollection();
        foreach ($entry->getMutations() as $mutation) {
            $originalMutations->add($mutation);
        }

        return $originalMutations;
	}

	/** 
	 * Retrieve all original mutation other references and store them in an ArrayCollection
	 */
	public function setOriginalOtherReferences($entry)
	{
		// prepare an array in which all other references will be stored
		$originalReferencesArray = array();

		// iterate over each mutation
		foreach ($entry->getMutations() as $mutation) {
			$id = $mutation->getId();

			// for each mutation, we will create an array collection of original other references
			$originalOtherReferences = new ArrayCollection();
			foreach ($mutation->getOtherReferences() as $reference) {
				$originalOtherReferences->add($reference);
			}

			// add the collection to array indexed by current mutation
			$originalReferencesArray[$id] = $originalOtherReferences;
		}

		$this->originalReferences = $originalReferencesArray;
	}

	/**
	 * Delete all traits that were in original traits (persisted) but not in submitted form
	 */
	public function removeDeletedTraits($entry, $originalTraits, $em)
	{
		foreach ($originalTraits as $trait) {
            if (false === $entry->getTraits()->contains($trait)) {
                $em->remove($trait);
            }
        }
	}

	/**
	 * Delete all taxon A that were in original taxon A (persisted) but not in submitted form
	 */
	public function removeDeletedTaxonAs($entry, $originalTaxonAs, $em)
	{
		foreach ($originalTaxonAs as $taxon) {
            if (false === $entry->getTaxonAList()->contains($taxon)) {
                $em->remove($taxon);
            }
        }
	}

	/**
	 * Delete all taxon B that were in original taxon B (persisted) but not in submitted form
	 */
	public function removeDeletedTaxonBs($entry, $originalTaxonBs, $em)
	{
		foreach ($originalTaxonBs as $taxon) {
            if (false === $entry->getTaxonBList()->contains($taxon)) {
                $em->remove($taxon);
            }
        }
	}

	/**
	 * Delete all mutations that were in original mutations (persisted) but not in submitted form
	 */
	public function removeDeletedMutations($entry, $originalMutations, $em)
	{
		foreach ($originalMutations as $mutation) {
            if (false === $entry->getMutations()->contains($mutation)) {
                $em->remove($mutation);
            }
        }
	}

	/**
	 * Delete all mutation references that were in original mutations (persisted) but not in submitted form
	 */
	public function removeDeletedReferences($entry, $em)
	{
		foreach($entry->getMutations() as $mutation) {
			$id = $mutation->getId();

			// check to see if mutation exists in persisted mutations, if it doesnt skip this mutation
			if (!array_key_exists($id, $this->originalReferences)) {
				continue;
			}

			$originalMutationReferences = $this->originalReferences[$id];
			foreach ($originalMutationReferences as $reference) {
				if (false === $mutation->getOtherReferences()->contains($reference)) {
					$em->remove($reference);
				}
			}
		}
	}

	/**
	 * Return array of original references
	 */
	public function getOriginalReferences()
	{
		return $this->originalReferences;
	}
}