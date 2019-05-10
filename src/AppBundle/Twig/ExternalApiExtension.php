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

/**
 * Twig extensions for References
 */
class ExternalApiExtension extends \Twig_Extension
{
    const STRING_DB_URL = 'http://string-db.org/newstring_cgi/show_network_section.pl?identifier=';
    const PUBMED_URL = 'https://www.ncbi.nlm.nih.gov/pubmed/';
    const UNIPROT_URL = 'http://www.uniprot.org/uniprot/';
    const GO_URL = 'https://www.ebi.ac.uk/QuickGO/term/';
    const TAXON_URL = 'https://www.ncbi.nlm.nih.gov/Taxonomy/Browser/wwwtax.cgi?id=';
    const GENBANK_URL = 'https://www.ncbi.nlm.nih.gov/nuccore/';

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('stringUrl', array($this, 'stringUrl')),
            new \Twig_SimpleFunction('pubmedUrl', array($this, 'pubmedUrl')),
            new \Twig_SimpleFunction('uniprotUrl', array($this, 'uniprotUrl')),
            new \Twig_SimpleFunction('goUrl', array($this, 'goUrl')),
            new \Twig_SimpleFunction('taxonUrl', array($this, 'taxonUrl')),
            new \Twig_SimpleFunction('genbankUrl', array($this, 'genbankUrl')),
        );
    }

    /**
     * Returns a string database http url based on identifier
     */
    public function stringUrl($id)
    {
        if ($id) {
            return self::STRING_DB_URL . $id; 
        }

        return null;
    }

    /**
     * Returns a pubmed url based on identifier
     */
    public function pubmedUrl($id)
    {
        if ($id) {
            return self::PUBMED_URL . $id; 
        }

        return null;
    }

    /**
     * Returns a UniprotKB url based on identifier
     */
    public function uniprotUrl($id)
    {
        if ($id) {
            return self::UNIPROT_URL . $id; 
        }

        return null;
    }

    /**
     * Returns a QuickGO url based on identifier
     */
    public function goUrl($id)
    {
        if ($id) {
            return self::GO_URL . $id; 
        }

        return null;
    }

    /**
     * Returns an NCBI Taxonomy url based on identifier
     */
    public function taxonUrl($id)
    {
        if ($id) {
            return self::TAXON_URL . $id; 
        }

        return null;
    }

    /**
     * Returns a GenBank url based on identifier
     */
    public function genbankUrl($id)
    {
        if ($id) {
            return self::GENBANK_URL . $id; 
        }

        return null;
    }

    public function getName()
    {
        return 'externalApiExtension';
    }
}