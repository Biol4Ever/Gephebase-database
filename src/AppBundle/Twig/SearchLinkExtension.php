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

namespace AppBundle\Twig;

use AppBundle\Model\Search\SearchBuilder;
use AppBundle\Repository\EntryRepository;

/**
 * Creates various searchs links using the advanced search form
 */
class SearchLinkExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            // --------------- SEARCH --------------------- //
            new \Twig_SimpleFilter('searchTaxonLink', array($this, 'searchTaxonLink')),
            new \Twig_SimpleFilter('searchTaxonIdLink', array($this, 'searchTaxonIdLink')),
            new \Twig_SimpleFilter('searchTraitCategoryLink', array($this, 'searchTraitCategoryLink')),
            new \Twig_SimpleFilter('searchTraitDescriptionLink', array($this, 'searchTraitDescriptionLink')),
            new \Twig_SimpleFilter('searchTaxonomicStatusLink', array($this, 'searchTaxonomicStatusLink')),
            new \Twig_SimpleFilter('searchPresumptiveLink', array($this, 'searchPresumptiveLink')),
            new \Twig_SimpleFilter('searchMolecularLink', array($this, 'searchMolecularLink')),
            new \Twig_SimpleFilter('searchAberrationTypeLink', array($this, 'searchAberrationTypeLink')),
            new \Twig_SimpleFilter('searchMappingLink', array($this, 'searchMappingLink')),
            new \Twig_SimpleFilter('searchGeneGephe', array($this, 'searchGeneGephe')),

            // --------------- EXACT SEARCH --------------------- //
            new \Twig_SimpleFilter('exactSearchTaxonLink', array($this, 'exactSearchTaxonLink')),
            new \Twig_SimpleFilter('exactSearchTaxonIdLink', array($this, 'exactSearchTaxonIdLink')),
            new \Twig_SimpleFilter('exactSearchTraitCategoryLink', array($this, 'exactSearchTraitCategoryLink')),
            new \Twig_SimpleFilter('exactSearchTraitDescriptionLink', array($this, 'exactSearchTraitDescriptionLink')),
            new \Twig_SimpleFilter('exactSearchTaxonomicStatusLink', array($this, 'exactSearchTaxonomicStatusLink')),
            new \Twig_SimpleFilter('exactSearchPresumptiveLink', array($this, 'exactSearchPresumptiveLink')),
            new \Twig_SimpleFilter('exactSearchMolecularLink', array($this, 'exactSearchMolecularLink')),
            new \Twig_SimpleFilter('exactSearchAberrationTypeLink', array($this, 'exactSearchAberrationTypeLink')),
            new \Twig_SimpleFilter('exactSearchMappingLink', array($this, 'exactSearchMappingLink')),
            new \Twig_SimpleFilter('exactSearchGeneGephe', array($this, 'exactSearchGeneGephe')),
        );
    }

    // --------------- SEARCH --------------------- //
    public function searchTaxonLink($term)
    {
        $result = '?/and+Taxon and Synonyms='.trim($term).SearchBuilder::SEARCH_TABLE_ANCHOR;

        return $result;
    }

    public function searchTaxonIdLink($term)
    {
        $result = '?/and+Taxon ID='.trim($term).SearchBuilder::SEARCH_TABLE_ANCHOR;

        return $result;
    }

    public function searchTraitCategoryLink($category)
    {
        $result = '?';

        if (!$category || $category === '') {
            return null;
        }

        // split the database string  
        $categories = preg_split('/(?=[A-Z])/',$category);

        foreach ($categories as $category) {
            if ($category != '') {
                $result .= '/and+Trait Category='.$category;
            }
        }

        $result .= SearchBuilder::SEARCH_TABLE_ANCHOR;

        return $result;
    }

    public function searchTraitDescriptionLink($description)
    {
        $result = '?/and+Trait='.trim($description).SearchBuilder::SEARCH_TABLE_ANCHOR;

        return $result;
    }

    public function searchTaxonomicStatusLink($status)
    {
        $result = '?/and+Taxonomic Status='.trim($status).SearchBuilder::SEARCH_TABLE_ANCHOR;

        return $result;
    }

    public function searchPresumptiveLink($value)
    {
        $result = '?/and+Presumptive Null='.trim($value).SearchBuilder::SEARCH_TABLE_ANCHOR;

        return $result;
    }

    public function searchMolecularLink($value)
    {
        $result = '?/and+Molecular Type='.trim($value).SearchBuilder::SEARCH_TABLE_ANCHOR;

        return $result;
    }

    public function searchAberrationTypeLink($value)
    {
        $result = '?/and+Aberration Type='.trim($value).SearchBuilder::SEARCH_TABLE_ANCHOR;

        return $result;
    }

    public function searchMappingLink($value)
    {
        $result = '?/and+Experimental Evidence='.trim($value).SearchBuilder::SEARCH_TABLE_ANCHOR;

        return $result;
    }

    public function searchGeneGephe($value)
    {
        $result = '?/and+Gene Gephebase='.trim($value).SearchBuilder::SEARCH_TABLE_ANCHOR;

        return $result;
    }

    // --------------- EXACT SEARCH --------------------- //
    public function exactSearchTaxonLink($term)
    {
        $result = '?/and+Taxon and Synonyms='. EntryRepository::EXACT_SEARCH_CHAR . trim($term) . EntryRepository::EXACT_SEARCH_CHAR . SearchBuilder::SEARCH_TABLE_ANCHOR;

        return $result;
    }

    public function exactSearchTaxonIdLink($term)
    {
        $result = '?/and+Taxon ID='. EntryRepository::EXACT_SEARCH_CHAR . trim($term) . EntryRepository::EXACT_SEARCH_CHAR . SearchBuilder::SEARCH_TABLE_ANCHOR;

        return $result;
    }

    public function exactSearchTraitCategoryLink($category)
    {
        $result = '?';

        if (!$category || $category === '') {
            return null;
        }

        // split the database string
        $categories = preg_split('/(?=[A-Z])/',$category);

        foreach ($categories as $category) {
            if ($category != '') {
                $result .= '/and+Trait Category='. EntryRepository::EXACT_SEARCH_CHAR . $category . EntryRepository::EXACT_SEARCH_CHAR;
            }
        }

        $result .= SearchBuilder::SEARCH_TABLE_ANCHOR;

        return $result;
    }

    public function exactSearchTraitDescriptionLink($description)
    {
        $result = '?/and+Trait='. EntryRepository::EXACT_SEARCH_CHAR . trim($description) . EntryRepository::EXACT_SEARCH_CHAR . SearchBuilder::SEARCH_TABLE_ANCHOR;

        return $result;
    }

    public function exactSearchTaxonomicStatusLink($status)
    {
        $result = '?/and+Taxonomic Status='. EntryRepository::EXACT_SEARCH_CHAR . trim($status) . EntryRepository::EXACT_SEARCH_CHAR . SearchBuilder::SEARCH_TABLE_ANCHOR;

        return $result;
    }

    public function exactSearchPresumptiveLink($value)
    {
        $result = '?/and+Presumptive Null='. EntryRepository::EXACT_SEARCH_CHAR . trim($value) . EntryRepository::EXACT_SEARCH_CHAR . SearchBuilder::SEARCH_TABLE_ANCHOR;

        return $result;
    }

    public function exactSearchMolecularLink($value)
    {
        $result = '?/and+Molecular Type='. EntryRepository::EXACT_SEARCH_CHAR . trim($value) . EntryRepository::EXACT_SEARCH_CHAR . SearchBuilder::SEARCH_TABLE_ANCHOR;

        return $result;
    }

    public function exactSearchAberrationTypeLink($value)
    {
        $result = '?/and+Aberration Type='. EntryRepository::EXACT_SEARCH_CHAR . trim($value) . EntryRepository::EXACT_SEARCH_CHAR . SearchBuilder::SEARCH_TABLE_ANCHOR;

        return $result;
    }

    public function exactSearchMappingLink($value)
    {
        $result = '?/and+Experimental Evidence='. EntryRepository::EXACT_SEARCH_CHAR . trim($value) . EntryRepository::EXACT_SEARCH_CHAR . SearchBuilder::SEARCH_TABLE_ANCHOR;

        return $result;
    }

    public function exactSearchGeneGephe($value)
    {
        $result = '?/and+Gene Gephebase='. EntryRepository::EXACT_SEARCH_CHAR.trim($value) . EntryRepository::EXACT_SEARCH_CHAR . SearchBuilder::SEARCH_TABLE_ANCHOR;

        return $result;
    }

    public function getName()
    {
        return 'searchLinkExtension';
    }
}