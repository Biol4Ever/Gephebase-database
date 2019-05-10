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

use AppBundle\Model\Search\AdvancedSearch;

class SearchCriteria
{
	private $advancedSearch;
    private $operator;
    private $field;
    private $term;
    private $term2;

	public function setAdvancedSearch(AdvancedSearch $advancedSearch)
    {
        $this->advancedSearch = $advancedSearch;

        return $this;
    }

    public function getAdvancedSearch()
    {
        return $this->advancedSearch;
    }

    public function getOperator()
    {
        return $this->operator;
    }

    public function setOperator($operator)
    {
        $this->operator = $operator;

        return $this;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function setTerm($term)
    {
        $this->term = $term;

        return $this;
    }

    public function getTerm2()
    {
        return $this->term2;
    }

    public function setTerm2($term2)
    {
        $this->term2 = $term2;

        return $this;
    }

    public function getField()
    {
        return $this->field;
    }

    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

}