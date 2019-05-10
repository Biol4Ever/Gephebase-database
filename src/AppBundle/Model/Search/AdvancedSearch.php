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

namespace AppBundle\Model\Search;

use AppBundle\Model\Search\SearchCriteria;

class AdvancedSearch
{
	private $searchCriterias;
    private $groupHaplotypes;
    private $groupGenes;
    private $splitMutations;

	public function __construct()
	{
		$this->searchCriterias = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function addSearchCriteria(SearchCriteria $searchCriteria)
    {
        $this->searchCriterias[] = $searchCriteria;

        return $this;
    }

    public function removeSearchCriteria(SearchCriteria $searchCriteria)
    {
        $this->searchCriterias->removeElement($searchCriteria);
    }

    public function getSearchCriterias()
    {
        return $this->searchCriterias;
    }

    public function getGroupHaplotypes()
    {
        return $this->groupHaplotypes;
    }

    public function setGroupHaplotypes($groupHaplotypes)
    {
        $this->groupHaplotypes = $groupHaplotypes;

        return $this;
    }

    public function getGroupGenes()
    {
        return $this->groupGenes;
    }

    public function setGroupGenes($groupGenes)
    {
        $this->groupGenes = $groupGenes;

        return $this;
    }

    public function getSplitMutations()
    {
        return $this->splitMutations;
    }

    public function setSplitMutations($splitMutations)
    {
        $this->splitMutations = $splitMutations;

        return $this;
    }

    public function isMutationSearch()
    {
        if ($this->splitMutations || $this->groupHaplotypes || $this->groupGenes) {
            return true;
        }

        return false;
    }
}