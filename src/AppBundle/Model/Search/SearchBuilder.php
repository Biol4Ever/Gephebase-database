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

use AppBundle\Entity\Entry;
use AppBundle\Model\Search\SearchCriteria;
use AppBundle\Model\Search\AdvancedSearch;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Repository\EntryRepository;

class SearchBuilder
{
    const SEARCH_TABLE_ANCHOR = '#gephebase-summary-title';

    private $em;
    private $securityContext;
    private $router;

    public function __construct($em, $securityContext, $router)
    {
        $this->securityContext = $securityContext;
        $this->em = $em;
        $this->router = $router;
    }

    /**
     * Find the number and names of all gephe entries that have same taxon and trait as argument
     */
    public function findRelatedGenes($entry)
    {
        $search = new AdvancedSearch();
        $search->setGroupHaplotypes(true);
        // array_unique eliminates the same tax_id duplicates existing in A & B taxons
        $taxons = array_unique($this->getTaxons($entry));

        $traits = [];
        foreach ($entry->getTraits() as $complexTrait) {
            if (null !== $complexTrait->getPhenotypeTrait()) {
                 $withoutParenthesis = $complexTrait->getPhenotypeTrait()->getDescription();
                 $withoutParenthesis = trim(preg_replace('/(\(.*?\))/', '', $withoutParenthesis));

                 $traits[] = $withoutParenthesis;
            }
        }

        foreach ($taxons as $taxon) {
            foreach ($traits as $trait) {
                $searchTaxon = new SearchCriteria();
                $searchTaxon->setOperator("or")
                    ->setField(8)
                    ->setTerm($this->addExactSearchCharacters($taxon));
                $search->addSearchCriteria($searchTaxon);

                $searchTrait = new SearchCriteria();
                $searchTrait->setOperator("and")
                    ->setField(6)
                    ->setTerm($trait);
                $search->addSearchCriteria($searchTrait);
            }
        }

        $results = $this->em->getRepository('AppBundle:Entry')->advancedSearch($search, $this->securityContext);

        if (empty($results)) {
            $entries = array();
        } else {
            $entries = $this->em->getRepository('AppBundle:Entry')->retrieveAllData($results, $search, $this);
        }

        $relatedGeneNames = $this->filterRelatedGenes($entry, $entries);
        $relatedGenes["count"] = count($relatedGeneNames);
        $relatedGenes["label"] = count($relatedGeneNames) . " (" . implode(", ",$relatedGeneNames) . ")";
        $relatedGenes["link"] = $this->buildSearchUrl($search);

        return $relatedGenes;
    }


    public function findRelatedHaplotypes($entry)
    {
        $search = new AdvancedSearch();

        $mergedTaxons = $this->getTaxons($entry);

        foreach ($mergedTaxons as $taxon) {

            $searchGene = new SearchCriteria();
            $searchGene->setOperator("or");
            $searchGene->setField(25);
            $searchGene->setTerm($this->addExactSearchCharacters($entry->getGeneGephebase()));
            $search->addSearchCriteria($searchGene);

            $searchTaxon = new SearchCriteria();
            $searchTaxon->setOperator("and");
            $searchTaxon->setField(8);
            $searchTaxon->setTerm($this->addExactSearchCharacters($taxon));
            $search->addSearchCriteria($searchTaxon);
        }

        $results = $this->em->getRepository('AppBundle:Entry')->advancedSearch($search, $this->securityContext);
        $relatedEntries = $this->filterRelatedEntries($entry, $results);

        $relatedHaplotypes["count"] = count($relatedEntries);
        $relatedHaplotypes["link"] = $this->buildSearchUrl($search);

        return $relatedHaplotypes;
    }


    private function getTaxons($entry)
    {
        //merging A and B taxons
        $mergedTaxons = [];
        foreach ($entry->getTaxonAList() as $complexTaxon) {
            if (null !== $complexTaxon->getTaxon()) {
                $mergedTaxons[] =  $complexTaxon->getTaxon()->getTaxId();
            }
        }

        foreach ($entry->getTaxonBList() as $complexTaxon) {
            if (null !== $complexTaxon->getTaxon()) {
                $mergedTaxons[] = $complexTaxon->getTaxon()->getTaxId();
            }
        }

        return $mergedTaxons;
    }


    /**
     * Returns a search url that will open an advanced search to match all genes in a grouped Genes search result.
     */
    public function buildGroupGenesSearch($entry, $search, $groupHaplotypes = false)
    {
        $groupedSearch = new AdvancedSearch();

        // search for all taxon A
        foreach ($entry['taxonA'] as $taxon) {
            $searchTaxonA = new SearchCriteria();
            $searchTaxonA->setOperator("and");
            $searchTaxonA->setField(27);
            $searchTaxonA->setTerm($taxon['taxId']);
            $groupedSearch->addSearchCriteria($searchTaxonA);
        }

        // search for all taxon B
        foreach ($entry['taxonB'] as $taxon) {
            $searchTaxonA = new SearchCriteria();
            $searchTaxonA->setOperator("and");
            $searchTaxonA->setField(28);
            $searchTaxonA->setTerm($taxon['taxId']);
            $groupedSearch->addSearchCriteria($searchTaxonA);
        }

        foreach ($entry['traitGroup'] as $trait) {
            $searchTrait = new SearchCriteria();
            $searchTrait->setOperator("and");
            $searchTrait->setField(6);
            $searchTrait->setTerm($trait);
            $groupedSearch->addSearchCriteria($searchTrait);
        }
        
        // if the groupHaplotypes flag is set, we will also group genes by Gene-Gephebase name
        if ($groupHaplotypes) {
            $searchGeneGephebase = new SearchCriteria();
            $searchGeneGephebase->setOperator("and");
            $searchGeneGephebase->setField(25);
            $searchGeneGephebase->setTerm($entry['geneGephebase']);
            $groupedSearch->addSearchCriteria($searchGeneGephebase);
        }

        $groupedSearch->setGroupGenes(false);
        $groupedSearch->setGroupHaplotypes(false);

        return $this->buildSearchUrl($groupedSearch);
    }

    /**
     * Creates an AdvancedSearch instance from the query string of a search url 
     */
    public function buildSearchfromQueryString($query)
    {
        $matches = array();
        preg_match_all('/\/((and|or|not)\+(.*?))(?=($|\/((and|or|not)|$)))/',$query,$matches);

        if (isset($matches[1])) {
            $queryExplode = $matches[1];
        }
        
        $datas = array();
        $checkboxes = array('splitMutations', 'groupHaplotypes', 'groupGenes');

        $criterias = array_flip(Entry::getAllCriteriaList());
        $search = new AdvancedSearch();

        foreach ($queryExplode as $q) {
            // first is the index, second the field and third the value
            if ($q != "") {
                $operator = substr($q, 0, strpos($q, '+'));
                $fieldTerm = substr($q, strlen($operator)+1);
                $indexCriteria = urldecode(substr($fieldTerm, 0, strpos($fieldTerm, '=')));
                if($fieldTerm != "") {


                    if (in_array($indexCriteria, $checkboxes)) {
                        $setter = 'set'.ucfirst($indexCriteria);
                        $search->$setter(true);
                    } elseif (array_key_exists($indexCriteria, $criterias)) {
                        $field = $criterias[$indexCriteria];
                        $term = urldecode(substr(strstr($q, '='), 1));

                        //if ($field == 30 && $term === "") {
                        if ($term === "") {
                            continue;
                        }

                        $searchCriteria = new SearchCriteria();
                        $searchCriteria->setTerm($term);
                        $searchCriteria->setField($field);
                        $searchCriteria->setOperator($operator);
                        
                        $search->addSearchCriteria($searchCriteria);
                    }
                }
            }    
        }

        return $search;
    }

    /**
     * Builds a search url corresponding to an AdvancedSearch 
     */
    public function buildSearchUrl($search)
    {
        $criteriaList = Entry::getAllCriteriaList();

        $url = $this->router->generate('search_criteria',array(),true).'?';
        foreach ($search->getSearchCriterias() as $criteria) {
            $url.='/'.$criteria->getOperator().'+'.$criteriaList[$criteria->getField()].'='.$criteria->getTerm();
        }

        if ($search->getGroupGenes()) {
            $url.='/and+groupGenes=true';
        }

        if ($search->getGroupHaplotypes()) {
            $url.='/and+groupHaplotypes=true';
        }

        if ($search->getSplitMutations()) {
            $url.='/and+splitMutations=true';
        }

        $url .= self::SEARCH_TABLE_ANCHOR;

        return $url;
    }

    /**
     * Filters the results from an advanced search and returns all related entries to the entry passed as argument excluding that one
     */
    private function filterRelatedEntries($entry, $results)
    {
        // foreach entries, rearrange them and check that they are'nt the current entry
        $relatedEntryIds = array();
        foreach ($results as $result) {
            //  ignore current entry
            if ($result == $entry->getId()) {
                continue;
            }

            $relatedEntryIds[] = $result;
        }

        return $this->em->getRepository('AppBundle:Entry')->findById($relatedEntryIds);
    }

    /**
     * Filters the results from an advanced search and removes duplicate gene gephe
     */
    private function filterRelatedGenes($entry, $results)
    {
        // foreach entries, rearrange them and check that they are'nt the current entry
        $relatedGeneNames = array();
        foreach ($results as $result) {
            //  ignore current gene or duplicate genes
            if ($result['geneGephebase'] == $entry->getGeneGephebase() || in_array($result['geneGephebase'], $relatedGeneNames)) {
                continue;
            }

            $relatedGeneNames[] = $result['geneGephebase'];
        }

        return $relatedGeneNames;
    }

    /**
     * Adds the exact search characters to start and end of given parameter
     */
    private function addExactSearchCharacters($value)
    {
        return EntryRepository::EXACT_SEARCH_CHAR . $value . EntryRepository::EXACT_SEARCH_CHAR;
    }

    private function filterTheSameGeneGepheBaseNames($relatedEntries, $entry)
    {
        for ($i = 0; $i<count($relatedEntries); $i++) {
            if ($relatedEntries[$i]->getGeneGephebase() === $entry->getGeneGephebase()) {
                unset($relatedEntries[$i]);
            }
        }

        return $relatedEntries;
    }
}