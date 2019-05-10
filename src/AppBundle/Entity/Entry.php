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


namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Entry
 *
 * @ORM\Table(name="entry")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EntryRepository")
 */
class Entry
{
    const URL_UNIPROT = "http://www.uniprot.org/uniprot/";
    const URL_PUBMED = "http://www.ncbi.nlm.nih.gov/pubmed/";
    const URL_GO = "https://www.ebi.ac.uk/QuickGO/GTerm?id=";
    const URL_GENBANK = "http://www.ncbi.nlm.nih.gov/nuccore/";
    const URL_TAXONOMY = "http://www.ncbi.nlm.nih.gov/Taxonomy/Browser/wwwtax.cgi?id=";
    const MESSAGE_VALID = "Entry Validated and Saved";
    const MESSAGE_VALID_LAST = "Entry Validated and Saved - This was the last entry on the list.";
    const MESSAGE_TEMPORARY = "Entry Saved as Draft";
    const MESSAGE_TEMPORARY_ERROR = "Validation error(s) - Entry Saved as Draft";
    const MESSAGE_ENTRY_PUBLISHED = "Entry Published";
    const MESSAGE_ADMIN_TO_DELETE = "Only admins may permanently delete an entry.";

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="gephe_id", type="string", length=255, nullable=true)
     */
    private $gepheId;

    /**
     * @var string
     *
     * @ORM\Column(name="gene_gephebase", type="string", length=255, nullable=true)
     */
    private $geneGephebase;

    /**
     * @var string
     *
     * @ORM\Column(name="genbank_id", type="string", length=255, nullable=true)
     */
    private $genbankId;

    /**
     * @var string
     *
     * @ORM\Column(name="genbank_taxon_a_or_b", type="string", length=255, nullable=true)
     */
    private $genbankTaxonAOrB;

    /**
     * @var string
     *
     * @ORM\Column(name="genbank_organism", type="string", length=255, nullable=true)
     */
    private $genbankOrganism;

    /**
     * @var string
     *
     * @ORM\Column(name="ancestral_state", type="string", length=255, nullable = true)
     */
    private $ancestralState;

    /**
     * @var string
     *
     * @ORM\Column(name="taxonomic_status", type="string", length=255, nullable=true)
     */
    private $taxonomicStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="comments", type="text", nullable=true)
     */
    private $comments;

    /**
     * @var string
     *
     * @ORM\Column(name="commentsValidator", type="text", nullable=true)
     */
    private $commentsValidator;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateEmail", type="datetime", nullable=true)
     */
    private $dateEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="temp_uniprot_id", type="string", length=255, nullable=true)
     */
    private $tempUniprotId;

    /**
     * @var string
     *
     * @ORM\Column(name="temp_taxon_a_id", type="string", length=255, nullable=true)
     */
    private $tempTaxonAId;

    /**
     * @var string
     *
     * @ORM\Column(name="temp_taxon_b_id", type="string", length=255, nullable=true)
     */
    private $tempTaxonBId;

    /**
     * @var string
     *
     * @ORM\Column(name="temp_main_pmid", type="string", length=255, nullable=true)
     */
    private $tempMainPmid;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="temp_date_validation", type="datetime", nullable=true)
     */
    private $tempDateValidation;

    /**
     * @var int
     *
     * @ORM\Column(name="imported_number", type="integer", nullable=true)
     */
    private $importedNumber;

    /**
     * @var boolean
     *
     * @ORM\Column(name="imported_gene_data", type="boolean", nullable=true)
     */
    private $importedGeneData;

    /**
     * @var boolean
     *
     * @ORM\Column(name="imported_taxon_data", type="boolean", nullable=true)
     */
    private $importedTaxonData;

    /**
     * @var boolean
     *
     * @ORM\Column(name="imported_main_ref_data", type="boolean", nullable=true)
     */
    private $importedMainRefData;

    /**
     * @var boolean
     *
     * @ORM\Column(name="imported_other_ref_data", type="boolean", nullable=true)
     */
    private $importedOtherRefData;

    /**
     * @var array
     *
     * @ORM\Column(name="temp_other_pmid", type="array", nullable=true)
     */
    private $tempOtherPmid;

    /**
     * @ORM\ManyToOne(targetEntity="Gene", cascade={"persist"})
     * @ORM\JoinColumn(name="gene_id", referencedColumnName="id")
     */
    private $gene;

    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist"})
     * @ORM\JoinColumn(name="main_curator", referencedColumnName="id", nullable=true)
     */
    private $mainCurator;

    /**
     * @ORM\ManyToOne(targetEntity="Reference", cascade={"persist"})
     * @ORM\JoinColumn(name="main_reference", referencedColumnName="id")
     */
    //private $mainReference;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="entries")
     */
    private $otherCurators;

    /**
     * @ORM\ManytoOne(targetEntity="EntryStatus", cascade={"persist"})
     */
    private $status;

    /**
     * @ORM\OneToMany(targetEntity="Feedback", mappedBy="entry")
     */
    private $feedbacks;

    /**
     * @ORM\OneToMany(targetEntity="ComplexTrait", mappedBy="entry", cascade={"remove", "persist"})
     */
    private $traits;

    /**
     * @ORM\OneToMany(targetEntity="Mutation", mappedBy="entry", cascade={"remove", "persist"})
     */
    private $mutations;

    /**
     * @ORM\OneToMany(targetEntity="ComplexTaxon", mappedBy="entryAsTaxonA", cascade={"remove", "persist"})
     */
    private $taxonAList;

    /**
     * @ORM\OneToMany(targetEntity="ComplexTaxon", mappedBy="entryAsTaxonB", cascade={"remove", "persist"})
     */
    private $taxonBList;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="entriesValidators")
     */
    private $validators;

    public function __construct() {
        $this->otherCurators = new \Doctrine\Common\Collections\ArrayCollection();
        $this->otherReferences = new \Doctrine\Common\Collections\ArrayCollection();
        $this->goCellular = new \Doctrine\Common\Collections\ArrayCollection();
        $this->goBiological = new \Doctrine\Common\Collections\ArrayCollection();
        $this->goMolecular = new \Doctrine\Common\Collections\ArrayCollection();
        $this->goOther = new \Doctrine\Common\Collections\ArrayCollection();
        $this->validators = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString() {
        return (string) $this->id;
    }

    /**
     * Returns a list of all search criteria available in advanced search form
     */
    public static function getCriteriaList() {
        return array(
            0 => 'All',
            15 => 'Aberration Type',
            31 => 'Aberration Size',
            19 => 'Abstract',
            17 => 'Authors',
            32 => 'Comments',
            11 => 'Experimental Evidence',
            23 => 'GeneBank ID',
            25 => 'Gene Gephebase',
            4 => 'Gene Name and Synonyms',
            22 => 'Gephe ID',
            20 => 'GO',
            2 => 'Main Curator',
            3 => 'Main Validator',
            12 => 'Molecular Details',
            13 => 'Molecular Type',
            26 => 'Presumptive Null',
            24 => 'Publication Date',
            16 => 'Reference',
            14 => 'SNP Coding Change',
            1 => 'Status of Gephe',
            27 => 'Taxon A',
            9 => 'Taxon and Synonyms',
            28 => 'Taxon B',
            8 => 'Taxon ID',
            10 => 'Taxonomic Status',
            6 => 'Trait',
            7 => 'Trait Category',
            21 => 'Uniprotkb ID',
        );
    }

    /**
     * Returns a list of all hidden search criterias used in backend custom queries
     */
    public static function getHiddenCriteriaList() {
        return array(
            29 => 'startYear',
            30 => 'endYear',
        );
    }

    /**
     * Returns a list of all search criteria available (those available in advanced search form and some that aren't)
     * Merges the simple criteria list with hidden criterias used for backend custom queries
     */
    public static function getAllCriteriaList() {
        return array_replace(self::getCriteriaList(), self::getHiddenCriteriaList());
    }

    public static function getStatusList() {
        return array(
            1 => "Temporary",
            2 => "Temporary (imported)",
            3 => "Accepted by curator",
            4 => "Accepted by validator",
            5 => "Refused by validator"
        );
    }

    public static function getStatusUserList() {
        return array(
            3 => "Accepted by curator",
            4 => "Accepted by validator",
            5 => "Refused by validator"
        );
    }

    public static function getTraitCategoryList() {
        return array(
            'Behavior' => 'Behavior',
            'Morphology' => 'Morphology',
            'Physiology' => 'Physiology',
        );
    }

    public static function getTraitCategoryShortList() {
        return array(
            'B' => 'Behavior',
            'M' => 'Morphology',
            'P' => 'Physiology',
            'Behaviour' => 'Behavior',
        );
    } 

    public static function getTaxonomicList() {
        return array(
            'Experimental Evolution' => 'Experimental Evolution',
            'Domesticated' => 'Domesticated',
            'Intraspecific' => 'Intraspecific',
            'Interspecific' => 'Interspecific',
            'Intergeneric or Higher' => 'Intergeneric or Higher'
        );
    }

    public static function getTaxonomicShortList() {
        return array(
            'Experimental Evolution' => 'Experimental Evolution',
            'Domesticated' => 'Domesticated',
            'Intraspecific' => 'Intraspecific',
            'Interspecific' => 'Interspecific',
            'Intergeneric or Higher' => 'Intergeneric or Higher'
        );
    }

    public static function getExperimentalList() {
        return array(
            'Linkage Mapping' => 'Linkage Mapping',
            'Association Mapping' => 'Association Mapping',
            'Candidate Gene' => 'Candidate Gene'
        );
    }

    public static function getExperimentalShortList() {
        return array(
            'M' => 'Linkage Mapping',
            'A' => 'Association Mapping',
            'C' => 'Candidate Gene'
        );
    }

    public static function getMolecularList() {
        return array(
            'Unknown' => 'Unknown',
            'Cis-regulatory' => 'Cis-regulatory',
            'Coding' => 'Coding',
            'Gene Loss' => 'Gene Loss',
            'Gene Amplification' => 'Gene Amplification',
            'Other' => 'Other' 
        );
    }

    public static function getPresumptiveNullList() {
        return array(
            'Yes' => 'Yes',
            'No' => 'No',
            'Unknown' => 'Unknown',
        );
    }

    public static function getMolecularShortList() {
        return array(
            'Unknown' => 'Unknown',
            'Cis-regulatory' => 'Cis-regulatory',
            'Coding' => 'Coding',
            'Gene Loss' => 'Gene Loss',
            'Gene Amplification' => 'Gene Amplification',
            'Other' => 'Other' 
        );
    }

    public static function getSNPList() {
        return array(
            'Nonsynonymous' => 'Nonsynonymous',
            'Nonsense' => 'Nonsense',
            'Synonymous' => 'Synonymous',
            'Unknown' => 'Unknown',
        );
    }

    public static function getSNPShortList() {
        return array(
            'Nonsynonymous' => 'Nonsynonymous',
            'Nonsense' => 'Nonsense',
            'Synonymous' => 'Synonymous',
            'Unknown' => 'Unknown',
        );
    }

    public static function getAberrationList() {
        return array(
            'SNP' => 'SNP',
            'Insertion' => 'Insertion',
            'Deletion' => 'Deletion',
            'Indel' => 'Indel',
            'Inversion' => 'Inversion',
            'Translocation' => 'Translocation',
            'Complex Change' => 'Complex Change',
            'Epigenetic Change' => 'Epigenetic Change',
            'Unknown' => 'Unknown'
        );
    }

    public static function getAberrationShortList() {
        return array(
            'SNP' => 'SNP',
            'Insertion' => 'Insertion',
            'Deletion' => 'Deletion',
            'Indel' => 'Indel',
            'Inversion' => 'Inversion',
            'Translocation' => 'Translocation',
            'Complex Change' => 'Complex Change',
            'Epigenetic Change' => 'Epigenetic Change',
            'Unknown' => 'Unknown'
        );
    }

    public static function getAberrationSizeList() {
        return array(
            'unknown' => 'unknown',
            '1-9 bp' => '1-9 bp',
            '10-99 bp' => '10-99 bp',
            '100-999 bp' => '100-999 bp',
            '1-10 kb' => '1-10 kb',
            '10-100 kb' => '10-100 kb',
            '100-1000 kb' => '100-1000 kb',
            '>1 Mb' => '>1 Mb',
        );
    }

    public static function getAberrationSizeShortList() {
        return array(
            'Unknown' => 'Unknown',
            '1-9 bp' => '1-9 bp',
            '10-99 bp' => '10-99 bp',
            '100-999 bp' => '100-999 bp',
            '1-10 kb' => '1-10 kb',
            '10-100 kb' => '10-100 kb',
            '100-1000 kb' => '100-1000 kb',
            '>1 Mb' => '>1 Mb',
            'not curated' => '',
            '(null)' => '',
            'null' => '',
            '' => '',
        );
    }

    public static function getAncestralList() {
        return array(
            'Data not curated' => 'Data Not Curated',
            'Taxon A' => 'Taxon A',
            'Unknown' => 'Unknown',
        );
    }

    public static function getYesNoList() {
        return array(
            1 => 'Yes',
            0 => 'No',
        );
    }

    public static function getAncestralShortList() {
        return array(
            null => 'Data Not Curated',
            'A' => 'Taxon A',
            'U' => 'Unknown',
            'Not Currated' => 'Data Not Curated',
            '' => 'Data Not Curated',
        );
    }

    public static function getSearchableTables() {
        return array(
          array(
            'searchGroup' => 'general',
            'type' => 'toOne',
            'table' => 'ComplexTrait',
            'shorthand' => 'cpt',
            'joinColumn' => 'cpt.entry',
            'referenceColumn' => 'e',
            'searchFields' => array(),
          ),
          array(
            'searchGroup' => 'general',
            'type' => 'toOne',
            'table' => 'PhenotypeTrait',
            'shorthand' => 'p',
            'joinColumn' => 'cpt.phenotypeTrait',
            'referenceColumn' => 'p.id',
            'searchFields' => array('description', 'category'),
          ),
          array(
            'searchGroup' => 'mutation',
            'type' => 'toOne',
            'table' => 'Mutation',
            'shorthand' => 'm',
            'joinColumn' => 'm.entry',
            'referenceColumn' => 'e',
            'searchFields' => array('snp', 'molecularType', 'molecularDetails', 'aberrationType'),
          ),
          array(
            'searchGroup' => 'taxona',
            'type' => 'toOne',
            'table' => 'ComplexTaxon',
            'shorthand' => 'cta',
            'joinColumn' => 'cta.entryAsTaxonA',
            'referenceColumn' => 'e',
            'searchFields' => array('description'),
          ),
          array(
            'searchGroup' => 'taxona',
            'type' => 'toOne',
            'table' => 'Taxon',
            'shorthand' => 'ta',
            'joinColumn' => 'cta.taxon',
            'referenceColumn' => 'ta',
            'searchFields' => array('lineage', 'latinName', 'commonName', 'rank', 'name'),
          ),
          array(
            'searchGroup' => 'taxona',
            'type' => 'toMany',
            'shorthand' => 'tas',
            'joinColumn' => 'ta.synonyms',
            'searchFields' => array('name'),
          ),
          array(
            'searchGroup' => 'taxonb',
            'type' => 'toOne',
            'table' => 'ComplexTaxon',
            'shorthand' => 'ctb',
            'joinColumn' => 'ctb.entryAsTaxonB',
            'referenceColumn' => 'e',
            'searchFields' => array('description'),
          ),
          array(
            'searchGroup' => 'taxonb',
            'type' => 'toOne',
            'table' => 'Taxon',
            'shorthand' => 'tb',
            'joinColumn' => 'ctb.taxon',
            'referenceColumn' => 'tb',
            'searchFields' => array('lineage', 'latinName', 'commonName', 'rank', 'name'),
          ),
          array(
            'searchGroup' => 'taxonb',
            'type' => 'toMany',
            'shorthand' => 'tbs',
            'joinColumn' => 'tb.synonyms',
            'searchFields' => array('name'),
          ),
          array(
            'searchGroup' => 'gene',
            'type' => 'toOne',
            'table' => 'Gene',
            'shorthand' => 'g',
            'joinColumn' => 'e.gene',
            'referenceColumn' => 'g.id',
            'searchFields' => array('name', 'uniProtKbId'),
          ),
          array(
            'searchGroup' => 'gomolecular',
            'type' => 'toOne',
            'table' => 'Gene',
            'shorthand' => 'g',
            'joinColumn' => 'e.gene',
            'referenceColumn' => 'g.id',
            'searchFields' => array(),
          ),
          array(
            'searchGroup' => 'gomolecular',
            'type' => 'toMany',
            'shorthand' => 'goM',
            'joinColumn' => 'g.goMolecular',
            'searchFields' => array('description'),
          ),
          array(
            'searchGroup' => 'gobiological',
            'type' => 'toOne',
            'table' => 'Gene',
            'shorthand' => 'g',
            'joinColumn' => 'e.gene',
            'referenceColumn' => 'g.id',
            'searchFields' => array(),
          ),
          array(
            'searchGroup' => 'gobiological',
            'type' => 'toMany',
            'shorthand' => 'goB',
            'joinColumn' => 'g.goBiological ',
            'searchFields' => array('description'),
          ),
          array(
            'searchGroup' => 'gocellular',
            'type' => 'toOne',
            'table' => 'Gene',
            'shorthand' => 'g',
            'joinColumn' => 'e.gene',
            'referenceColumn' => 'g.id',
            'searchFields' => array(),
          ),
          array(
            'searchGroup' => 'gocellular',
            'type' => 'toMany',
            'shorthand' => 'goC',
            'joinColumn' => 'g.goCellular',
            'searchFields' => array('description'),
          ),
          array(
            'searchGroup' => 'genesynonyms',
            'type' => 'toOne',
            'table' => 'Gene',
            'shorthand' => 'g',
            'joinColumn' => 'e.gene',
            'referenceColumn' => 'g.id',
            'searchFields' => array(),
          ),
          array(
            'searchGroup' => 'genesynonyms',
            'type' => 'toMany',
            'shorthand' => 's',
            'joinColumn' => 'g.synonyms',
            'searchFields' => array('name'),
          ),
          array(
            'searchGroup' => 'mainreference',
            'type' => 'toOne',
            'table' => 'Mutation',
            'shorthand' => 'm',
            'joinColumn' => 'm.entry',
            'referenceColumn' => 'e',
            'searchFields' => array(),
          ),
          array(
            'searchGroup' => 'mainreference',
            'type' => 'toOne',
            'table' => 'Reference',
            'shorthand' => 'rm',
            'joinColumn' => 'm.mainReference',
            'referenceColumn' => 'rm.id',
            'searchFields' => array('abstract', 'journalYear', 'articleTitle', 'pmId', 'doi'),
          ),
          array(
            'searchGroup' => 'mainreference',
            'type' => 'toMany',
            'shorthand' => 'rma',
            'joinColumn' => 'rm.authors',
            'searchFields' => array('lastname'),
          ),
          array(
            'searchGroup' => 'otherreference',
            'type' => 'toOne',
            'table' => 'Mutation',
            'shorthand' => 'm',
            'joinColumn' => 'm.entry',
            'referenceColumn' => 'e',
            'searchFields' => array(),
          ),
          array(
            'searchGroup' => 'otherreference',
            'type' => 'toOne',
            'table' => 'MutationReference',
            'shorthand' => 'mr',
            'joinColumn' => 'mr.mutation',
            'referenceColumn' => 'm',
            'searchFields' => array(),
          ),
          array(
            'searchGroup' => 'otherreference',
            'type' => 'toOne',
            'table' => 'Reference',
            'shorthand' => 'ro',
            'joinColumn' => 'mr.reference',
            'referenceColumn' => 'ro',
            'searchFields' => array('abstract', 'journalYear', 'articleTitle', 'pmId', 'doi'),
          ),
          array(
            'searchGroup' => 'otherreference',
            'type' => 'toMany',
            'shorthand' => 'roa',
            'joinColumn' => 'ro.authors',
            'searchFields' => array('lastname'),
          ),
        );
    }

    public static function getSearchableFields() {
        return array(
            15 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'Mutation',
                    'shorthand' => 'm',
                    'joinColumn' => 'm.entry',
                    'referenceColumn' => 'e',
                    'searchFields' => array('aberrationType'),
                ),
            ),
            26 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'Mutation',
                    'shorthand' => 'm',
                    'joinColumn' => 'm.entry',
                    'referenceColumn' => 'e',
                    'searchFields' => array('presumptiveNull'),
                ),
            ),
            11 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'Mutation',
                    'shorthand' => 'm',
                    'joinColumn' => 'm.entry',
                    'referenceColumn' => 'e',
                    'searchFields' => array('experimentalEvidence'),
                ),
            ),
            23 => array(
                array(
                    'type' => null,
                    'shorthand' => 'e',
                    'searchFields' => array('genbankId'),
                )
            ),
            22 => array(
                array(
                    'type' => null,
                    'shorthand' => 'e',
                    'searchFields' => array('gepheId'),
                )
            ),
            12 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'Mutation',
                    'shorthand' => 'm',
                    'joinColumn' => 'm.entry',
                    'referenceColumn' => 'e',
                    'searchFields' => array('molecularDetails'),
                ),
            ),
            13 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'Mutation',
                    'shorthand' => 'm',
                    'joinColumn' => 'm.entry',
                    'referenceColumn' => 'e',
                    'searchFields' => array('molecularType'),
                ),
            ),
            14 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'Mutation',
                    'shorthand' => 'm',
                    'joinColumn' => 'm.entry',
                    'referenceColumn' => 'e',
                    'searchFields' => array('snp'),
                ),
            ),
            10 => array(
                array(
                    'type' => null,
                    'shorthand' => 'e',
                    'searchFields' => array('taxonomicStatus',),
                )
            ),
            19 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'Mutation',
                    'shorthand' => 'm',
                    'joinColumn' => 'm.entry',
                    'referenceColumn' => 'e',
                    'searchFields' => array(),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'Reference',
                    'shorthand' => 'rm',
                    'joinColumn' => 'm.mainReference',
                    'referenceColumn' => 'rm.id',
                    'searchFields' => array('abstract'),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'MutationReference',
                    'shorthand' => 'mr',
                    'joinColumn' => 'mr.mutation',
                    'referenceColumn' => 'm',
                    'searchFields' => array(),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'Reference',
                    'shorthand' => 'ro',
                    'joinColumn' => 'mr.reference',
                    'referenceColumn' => 'ro',
                    'searchFields' => array('abstract'),
                ),
            ),
            17 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'Mutation',
                    'shorthand' => 'm',
                    'joinColumn' => 'm.entry',
                    'referenceColumn' => 'e',
                    'searchFields' => array(),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'Reference',
                    'shorthand' => 'rm',
                    'joinColumn' => 'm.mainReference',
                    'referenceColumn' => 'rm.id',
                    'searchFields' => array('abstract'),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'MutationReference',
                    'shorthand' => 'mr',
                    'joinColumn' => 'mr.mutation',
                    'referenceColumn' => 'm',
                    'searchFields' => array(),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'Reference',
                    'shorthand' => 'ro',
                    'joinColumn' => 'mr.reference',
                    'referenceColumn' => 'ro',
                    'searchFields' => array('abstract'),
                ),
                array(
                    'type' => 'toMany',
                    'shorthand' => 'rma',
                    'joinColumn' => 'rm.authors',
                    'searchFields' => array('lastname'),
                ),
                array(
                    'type' => 'toMany',
                    'shorthand' => 'roa',
                    'joinColumn' => 'ro.authors',
                    'searchFields' => array('lastname'),
                ),
            ),
            4 => array(
                array(
                    'type' => null,
                    'shorthand' => 'e',
                    'searchFields' => array('geneGephebase'),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'Gene',
                    'shorthand' => 'g',
                    'joinColumn' => 'e.gene',
                    'referenceColumn' => 'g.id',
                    'searchFields' => array('name'),
                ),
                array(
                    'type' => 'toMany',
                    'shorthand' => 's',
                    'joinColumn' => 'g.synonyms',
                    'searchFields' => array('name'),
                ),
            ),
            20 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'Gene',
                    'shorthand' => 'g',
                    'joinColumn' => 'e.gene',
                    'referenceColumn' => 'g.id',
                    'searchFields' => array(),
                ),
                array(
                    'type' => 'toMany',
                    'shorthand' => 'goM',
                    'joinColumn' => 'g.goMolecular',
                    'searchFields' => array('description', 'goId'),
                ),
                array(
                    'type' => 'toMany',
                    'shorthand' => 'goB',
                    'joinColumn' => 'g.goBiological ',
                    'searchFields' => array('description', 'goId'),
                ),
                array(
                    'type' => 'toMany',
                    'shorthand' => 'goC',
                    'joinColumn' => 'g.goCellular',
                    'searchFields' => array('description', 'goId'),
                ),
            ),
            2 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'User',
                    'shorthand' => 'mc',
                    'joinColumn' => 'e.mainCurator',
                    'referenceColumn' => 'mc.id',
                    'searchFields' => array('surname', 'name'),
                ),
            ),
            3 => array(
                array(
                    'type' => 'toMany',
                    'shorthand' => 'ev',
                    'joinColumn' => 'e.validators',
                    'searchFields' => array('surname', 'name'),
                ),
            ),
            24 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'Reference',
                    'shorthand' => 'rm',
                    'joinColumn' => 'e.mainReference',
                    'referenceColumn' => 'rm.id',
                    'searchFields' => array('journalYear'),
                ),
            ),
            16 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'Mutation',
                    'shorthand' => 'm',
                    'joinColumn' => 'm.entry',
                    'referenceColumn' => 'e',
                    'searchFields' => array(),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'Reference',
                    'shorthand' => 'rm',
                    'joinColumn' => 'm.mainReference',
                    'referenceColumn' => 'rm.id',
                    'searchFields' => array('abstract', 'journalYear', 'articleTitle', 'pmId', 'doi'),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'MutationReference',
                    'shorthand' => 'mr',
                    'joinColumn' => 'mr.mutation',
                    'referenceColumn' => 'm',
                    'searchFields' => array(),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'Reference',
                    'shorthand' => 'ro',
                    'joinColumn' => 'mr.reference',
                    'referenceColumn' => 'ro',
                    'searchFields' => array('abstract', 'journalYear', 'articleTitle', 'pmId', 'doi'),
                ),
                array(
                    'type' => 'toMany',
                    'shorthand' => 'rma',
                    'joinColumn' => 'rm.authors',
                    'searchFields' => array('lastname'),
                ),
                array(
                    'type' => 'toMany',
                    'shorthand' => 'roa',
                    'joinColumn' => 'ro.authors',
                    'searchFields' => array('lastname'),
                ),
            ),
            1 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'EntryStatus',
                    'shorthand' => 'es',
                    'joinColumn' => 'e.status',
                    'referenceColumn' => 'es.id',
                    'searchFields' => array('id'),
                ),
            ),
            9 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'ComplexTaxon',
                    'shorthand' => 'cta',
                    'joinColumn' => 'cta.entryAsTaxonA',
                    'referenceColumn' => 'e',
                    'searchFields' => array('description'),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'Taxon',
                    'shorthand' => 'ta',
                    'joinColumn' => 'cta.taxon',
                    'referenceColumn' => 'ta',
                    'searchFields' => array('lineage', 'latinName', 'commonName', 'rank', 'name'),
                ),
                array(
                    'type' => 'toMany',
                    'shorthand' => 'tas',
                    'joinColumn' => 'ta.synonyms',
                    'searchFields' => array('name'),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'ComplexTaxon',
                    'shorthand' => 'ctb',
                    'joinColumn' => 'ctb.entryAsTaxonB',
                    'referenceColumn' => 'e',
                    'searchFields' => array('description'),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'Taxon',
                    'shorthand' => 'tb',
                    'joinColumn' => 'ctb.taxon',
                    'referenceColumn' => 'tb',
                    'searchFields' => array('lineage', 'latinName', 'commonName', 'rank', 'name'),
                ),
                array(
                    'type' => 'toMany',
                    'shorthand' => 'tbs',
                    'joinColumn' => 'tb.synonyms',
                    'searchFields' => array('name'),
                ),
            ),
            8 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'ComplexTaxon',
                    'shorthand' => 'cta',
                    'joinColumn' => 'cta.entryAsTaxonA',
                    'referenceColumn' => 'e',
                    'searchFields' => array(),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'Taxon',
                    'shorthand' => 'ta',
                    'joinColumn' => 'cta.taxon',
                    'referenceColumn' => 'ta',
                    'searchFields' => array('taxId'),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'ComplexTaxon',
                    'shorthand' => 'ctb',
                    'joinColumn' => 'ctb.entryAsTaxonB',
                    'referenceColumn' => 'e',
                    'searchFields' => array(),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'Taxon',
                    'shorthand' => 'tb',
                    'joinColumn' => 'ctb.taxon',
                    'referenceColumn' => 'tb',
                    'searchFields' => array('taxId'),
                ),
            ),
            6 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'ComplexTrait',
                    'shorthand' => 'cpt',
                    'joinColumn' => 'cpt.entry',
                    'referenceColumn' => 'e',
                    'searchFields' => array('stateInTaxonA', 'stateInTaxonB'),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'PhenotypeTrait',
                    'shorthand' => 'p',
                    'joinColumn' => 'cpt.phenotypeTrait',
                    'referenceColumn' => 'p.id',
                    'searchFields' => array('description'),
                ),
            ),
            7 => array(
                 array(
                    'type' => 'toOne',
                    'table' => 'ComplexTrait',
                    'shorthand' => 'cpt',
                    'joinColumn' => 'cpt.entry',
                    'referenceColumn' => 'e',
                    'searchFields' => array(),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'PhenotypeTrait',
                    'shorthand' => 'p',
                    'joinColumn' => 'cpt.phenotypeTrait',
                    'referenceColumn' => 'p.id',
                    'searchFields' => array('category'),
                ),
            ),
            21 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'Gene',
                    'shorthand' => 'g',
                    'joinColumn' => 'e.gene',
                    'referenceColumn' => 'g.id',
                    'searchFields' => array('uniProtKbId'),
                ),
            ),
            25 => array(
                array(
                    'type' => null,
                    'shorthand' => 'e',
                    'searchFields' => array('geneGephebase'),
                )
            ),
            32 => array(
                array(
                    'type' => null,
                    'shorthand' => 'e',
                    'searchFields' => array('comments'),
                )
            ),
            27 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'ComplexTaxon',
                    'shorthand' => 'ctaa',
                    'joinColumn' => 'ctaa.entryAsTaxonA',
                    'referenceColumn' => 'e',
                    'searchFields' => array('description'),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'Taxon',
                    'shorthand' => 'taa',
                    'joinColumn' => 'ctaa.taxon',
                    'referenceColumn' => 'taa',
                    'searchFields' => array('taxId', 'latinName', 'commonName'),
                ),
            ),
            28 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'ComplexTaxon',
                    'shorthand' => 'ctbb',
                    'joinColumn' => 'ctbb.entryAsTaxonB',
                    'referenceColumn' => 'e',
                    'searchFields' => array('description'),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'Taxon',
                    'shorthand' => 'tbb',
                    'joinColumn' => 'ctbb.taxon',
                    'referenceColumn' => 'tbb',
                    'searchFields' => array('taxId', 'latinName', 'commonName'),
                ),
            ),
            29 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'Mutation',
                    'shorthand' => 'm',
                    'joinColumn' => 'm.entry',
                    'referenceColumn' => 'e',
                    'searchFields' => array(),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'Reference',
                    'shorthand' => 'rm',
                    'joinColumn' => 'm.mainReference',
                    'referenceColumn' => 'rm.id',
                    'searchFields' => array('journalYear'),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'MutationReference',
                    'shorthand' => 'mr',
                    'joinColumn' => 'mr.mutation',
                    'referenceColumn' => 'm',
                    'searchFields' => array(),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'Reference',
                    'shorthand' => 'ro',
                    'joinColumn' => 'mr.reference',
                    'referenceColumn' => 'ro',
                    'searchFields' => array('journalYear'),
                ),
            ),
            30 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'Mutation',
                    'shorthand' => 'm',
                    'joinColumn' => 'm.entry',
                    'referenceColumn' => 'e',
                    'searchFields' => array(),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'Reference',
                    'shorthand' => 'rm',
                    'joinColumn' => 'm.mainReference',
                    'referenceColumn' => 'rm.id',
                    'searchFields' => array('journalYear'),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'MutationReference',
                    'shorthand' => 'mr',
                    'joinColumn' => 'mr.mutation',
                    'referenceColumn' => 'm',
                    'searchFields' => array(),
                ),
                array(
                    'type' => 'toOne',
                    'table' => 'Reference',
                    'shorthand' => 'ro',
                    'joinColumn' => 'mr.reference',
                    'referenceColumn' => 'ro',
                    'searchFields' => array('journalYear'),
                ),
            ),
            31 => array(
                array(
                    'type' => 'toOne',
                    'table' => 'Mutation',
                    'shorthand' => 'm',
                    'joinColumn' => 'm.entry',
                    'referenceColumn' => 'e',
                    'searchFields' => array('aberrationSize'),
                ),
            ),
        );
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set gepheId
     *
     * @param string $gepheId
     * @return Entry
     */
    public function setGepheId($gepheId)
    {
        $this->gepheId = $gepheId;

        return $this;
    }

    /**
     * Get gepheId
     *
     * @return string 
     */
    public function getGepheId()
    {
        return $this->gepheId;
    }

    /**
     * Set geneGephebase
     *
     * @param string $geneGephebase
     * @return Entry
     */
    public function setGeneGephebase($geneGephebase)
    {
        $this->geneGephebase = $geneGephebase;

        return $this;
    }

    /**
     * Get geneGephebase
     *
     * @return string 
     */
    public function getGeneGephebase()
    {
        return $this->geneGephebase;
    }

    /**
     * Set genbankId
     *
     * @param string $genbankId
     * @return Entry
     */
    public function setGenbankId($genbankId)
    {
        $this->genbankId = $genbankId;

        return $this;
    }

    /**
     * Get genbankId
     *
     * @return string 
     */
    public function getGenbankId()
    {
        return $this->genbankId;
    }

    /**
     * Set ancestralState
     *
     * @param string $ancestralState
     * @return Entry
     */
    public function setAncestralState($ancestralState)
    {
        $this->ancestralState = $ancestralState;

        return $this;
    }

    /**
     * Get ancestralState
     *
     * @return string 
     */
    public function getAncestralState()
    {
        return $this->ancestralState;
    }

    /**
     * Set experimentalEvidence
     *
     * @param string $experimentalEvidence
     * @return Entry
     */
    public function setExperimentalEvidence($experimentalEvidence)
    {
        $this->experimentalEvidence = $experimentalEvidence;

        return $this;
    }

    /**
     * Get experimentalEvidence
     *
     * @return string 
     */
    public function getExperimentalEvidence()
    {
        return $this->experimentalEvidence;
    }

    /**
     * Set molecularDetails
     *
     * @param string $molecularDetails
     * @return Entry
     */
    public function setMolecularDetails($molecularDetails)
    {
        $this->molecularDetails = $molecularDetails;

        return $this;
    }

    /**
     * Get molecularDetails
     *
     * @return string 
     */
    public function getMolecularDetails()
    {
        return $this->molecularDetails;
    }

    /**
     * Set molecularType
     *
     * @param string $molecularType
     * @return Entry
     */
    public function setMolecularType($molecularType)
    {
        $this->molecularType = $molecularType;

        return $this;
    }

    /**
     * Get molecularType
     *
     * @return string 
     */
    public function getMolecularType()
    {
        return $this->molecularType;
    }

    /**
     * Set snp
     *
     * @param string $snp
     * @return Entry
     */
    public function setSnp($snp)
    {
        $this->snp = $snp;

        return $this;
    }

    /**
     * Get snp
     *
     * @return string 
     */
    public function getSnp()
    {
        return $this->snp;
    }

    /**
     * Set aberrationType
     *
     * @param string $aberrationType
     * @return Entry
     */
    public function setAberrationType($aberrationType)
    {
        $this->aberrationType = $aberrationType;

        return $this;
    }

    /**
     * Get aberrationType
     *
     * @return string 
     */
    public function getAberrationType()
    {
        return $this->aberrationType;
    }

    /**
     * Set comments
     *
     * @param string $comments
     * @return Entry
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments
     *
     * @return string 
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set commentsValidator
     *
     * @param string $commentsValidator
     * @return Entry
     */
    public function setCommentsValidator($commentsValidator)
    {
        $this->commentsValidator = $commentsValidator;

        return $this;
    }

    /**
     * Get commentsValidator
     *
     * @return string 
     */
    public function getCommentsValidator()
    {
        return $this->commentsValidator;
    }

    /**
     * Set gene
     *
     * @param \AppBundle\Entity\Gene $gene
     * @return Entry
     */
    public function setGene(\AppBundle\Entity\Gene $gene = null)
    {
        $this->gene = $gene;

        return $this;
    }

    /**
     * Get gene
     *
     * @return \AppBundle\Entity\Gene 
     */
    public function getGene()
    {
        return $this->gene;
    }

    /**
     * Set mainCurator
     *
     * @param \AppBundle\Entity\User $mainCurator
     * @return Entry
     */
    public function setMainCurator(\AppBundle\Entity\User $mainCurator)
    {
        $this->mainCurator = $mainCurator;

        return $this;
    }

    /**
     * Unset mainCurator
     *
     * @return Entry
     */
    public function unsetMainCurator()
    {
        $this->mainCurator = null;

        return $this;
    }

    /**
     * Get mainCurator
     *
     * @return \AppBundle\Entity\User 
     */
    public function getMainCurator()
    {
        return $this->mainCurator;
    }

    /**
     * Set mainReference
     *
     * @param \AppBundle\Entity\Reference $mainReference
     * @return Entry
     */
    public function setMainReference(\AppBundle\Entity\Reference $mainReference)
    {
        $this->mainReference = $mainReference;

        return $this;
    }

    /**
     * Get mainReference
     *
     * @return \AppBundle\Entity\Reference 
     */
    public function getMainReference()
    {
        return $this->mainReference;
    }

    /**
     * Add otherCurators
     *
     * @param \AppBundle\Entity\User $otherCurators
     * @return Entry
     */
    public function addOtherCurator(\AppBundle\Entity\User $otherCurators)
    {
        $this->otherCurators[] = $otherCurators;

        return $this;
    }

    /**
     * Remove otherCurators
     *
     * @param \AppBundle\Entity\User $otherCurators
     */
    public function removeOtherCurator(\AppBundle\Entity\User $otherCurators)
    {
        $this->otherCurators->removeElement($otherCurators);
    }

    /**
     * Get otherCurators
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOtherCurators()
    {
        return $this->otherCurators;
    }

    /**
     * Add otherReferences
     *
     * @param \AppBundle\Entity\Reference $otherReferences
     * @return Entry
     */
    public function addOtherReference(\AppBundle\Entity\Reference $otherReferences)
    {
        $this->otherReferences[] = $otherReferences;

        return $this;
    }

    /**
     * Remove otherReferences
     *
     * @param \AppBundle\Entity\Reference $otherReferences
     */
    public function removeOtherReference(\AppBundle\Entity\Reference $otherReferences)
    {
        $this->otherReferences->removeElement($otherReferences);
    }

    /**
     * Get otherReferences
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOtherReferences()
    {
        return $this->otherReferences;
    }

    /**
     * Set taxonomicStatus
     *
     * @param string $taxonomicStatus
     * @return Entry
     */
    public function setTaxonomicStatus($taxonomicStatus)
    {
        $this->taxonomicStatus = $taxonomicStatus;

        return $this;
    }

    /**
     * Get taxonomicStatus
     *
     * @return string 
     */
    public function getTaxonomicStatus()
    {
        return $this->taxonomicStatus;
    }

    /**
     * Set status
     *
     * @param \AppBundle\Entity\EntryStatus $status
     * @return Entry
     */
    public function setStatus(\AppBundle\Entity\EntryStatus $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return \AppBundle\Entity\EntryStatus 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set taxonBDescription
     *
     * @param string $taxonBDescription
     * @return Entry
     */
    public function setTaxonBDescription($taxonBDescription)
    {
        $this->taxonBDescription = $taxonBDescription;

        return $this;
    }

    /**
     * Get taxonBDescription
     *
     * @return string 
     */
    public function getTaxonBDescription()
    {
        return $this->taxonBDescription;
    }

    /**
     * Set dateEmail
     *
     * @param \DateTime $dateEmail
     * @return Entry
     */
    public function setDateEmail($dateEmail)
    {
        $this->dateEmail = $dateEmail;

        return $this;
    }


    /**
     * Get dateEmail
     *
     * @return \DateTime 
     */
    public function getDateEmail()
    {
        return $this->dateEmail;
    }

    /**
     * Set tempUniprotId
     *
     * @param string $tempUniprotId
     * @return Entry
     */
    public function setTempUniprotId($tempUniprotId)
    {
        $this->tempUniprotId = $tempUniprotId;

        return $this;
    }

    /**
     * Get tempUniprotId
     *
     * @return string 
     */
    public function getTempUniprotId()
    {
        return $this->tempUniprotId;
    }

    /**
     * Set tempTaxonAId
     *
     * @param string $tempTaxonAId
     * @return Entry
     */
    public function setTempTaxonAId($tempTaxonAId)
    {
        $this->tempTaxonAId = $tempTaxonAId;

        return $this;
    }

    /**
     * Get tempTaxonAId
     *
     * @return string 
     */
    public function getTempTaxonAId()
    {
        return $this->tempTaxonAId;
    }

    /**
     * Set tempTaxonBId
     *
     * @param string $tempTaxonBId
     * @return Entry
     */
    public function setTempTaxonBId($tempTaxonBId)
    {
        $this->tempTaxonBId = $tempTaxonBId;

        return $this;
    }

    /**
     * Get tempTaxonBId
     *
     * @return string 
     */
    public function getTempTaxonBId()
    {
        return $this->tempTaxonBId;
    }

    /**
     * Set tempMainPmid
     *
     * @param string $tempMainPmid
     * @return Entry
     */
    public function setTempMainPmid($tempMainPmid)
    {
        $this->tempMainPmid = $tempMainPmid;

        return $this;
    }

    /**
     * Get tempMainPmid
     *
     * @return string 
     */
    public function getTempMainPmid()
    {
        return $this->tempMainPmid;
    }

    /**
     * Set tempOtherPmid
     *
     * @param array $tempOtherPmid
     * @return Entry
     */
    public function setTempOtherPmid($tempOtherPmid)
    {
        $this->tempOtherPmid = $tempOtherPmid;

        return $this;
    }

    /**
     * Get tempOtherPmid
     *
     * @return array 
     */
    public function getTempOtherPmid()
    {
        return $this->tempOtherPmid;
    }

    /**
     * Set tempDateValidation
     *
     * @param \DateTime $tempDateValidation
     * @return Entry
     */
    public function setTempDateValidation($tempDateValidation)
    {
        $this->tempDateValidation = $tempDateValidation;

        return $this;
    }


    /**
     * Get tempDateValidation
     *
     * @return \DateTime 
     */
    public function getTempDateValidation()
    {
        return $this->tempDateValidation;
    }

    /**
     * Set importedNumber
     *
     * @param integer $importedNumber
     * @return Entry
     */
    public function setImportedNumber($importedNumber)
    {
        $this->importedNumber = $importedNumber;

        return $this;
    }

    /**
     * Get importedNumber
     *
     * @return integer 
     */
    public function getImportedNumber()
    {
        return $this->importedNumber;
    }

    /**
     * Set importedGeneData
     *
     * @param boolean $importedGeneData
     * @return Entry
     */
    public function setImportedGeneData($importedGeneData)
    {
        $this->importedGeneData = $importedGeneData;

        return $this;
    }

    /**
     * Get importedGeneData
     *
     * @return boolean 
     */
    public function getImportedGeneData()
    {
        return $this->importedGeneData;
    }

    /**
     * Set importedTaxonData
     *
     * @param boolean $importedTaxonData
     * @return Entry
     */
    public function setImportedTaxonData($importedTaxonData)
    {
        $this->importedTaxonData = $importedTaxonData;

        return $this;
    }

    /**
     * Get importedTaxonData
     *
     * @return boolean 
     */
    public function getImportedTaxonData()
    {
        return $this->importedTaxonData;
    }

    /**
     * Set importedMainRefData
     *
     * @param boolean $importedMainRefData
     * @return Entry
     */
    public function setImportedMainRefData($importedMainRefData)
    {
        $this->importedMainRefData = $importedMainRefData;

        return $this;
    }

    /**
     * Get importedMainRefData
     *
     * @return boolean 
     */
    public function getImportedMainRefData()
    {
        return $this->importedMainRefData;
    }

    /**
     * Set importedOtherRefData
     *
     * @param boolean $importedOtherRefData
     * @return Entry
     */
    public function setImportedOtherRefData($importedOtherRefData)
    {
        $this->importedOtherRefData = $importedOtherRefData;

        return $this;
    }

    /**
     * Get importedOtherRefData
     *
     * @return boolean 
     */
    public function getImportedOtherRefData()
    {
        return $this->importedOtherRefData;
    }

    /**
     * Set genbankTaxonAOrB
     *
     * @param string $genbankTaxonAOrB
     * @return Entry
     */
    public function setGenbankTaxonAOrB($genbankTaxonAOrB)
    {
        $this->genbankTaxonAOrB = $genbankTaxonAOrB;

        return $this;
    }

    /**
     * Get genbankTaxonAOrB
     *
     * @return string 
     */
    public function getGenbankTaxonAOrB()
    {
        return $this->genbankTaxonAOrB;
    }

    /**
     * Set genbankOrganism
     *
     * @param string $genbankOrganism
     * @return Entry
     */
    public function setGenbankOrganism($genbankOrganism)
    {
        $this->genbankOrganism = $genbankOrganism;

        return $this;
    }

    /**
     * Get genbankOrganism
     *
     * @return string 
     */
    public function getGenbankOrganism()
    {
        return $this->genbankOrganism;
    }

    /**
     * Add feedbacks
     *
     * @param \AppBundle\Entity\Feedback $feedbacks
     * @return Entry
     */
    public function addFeedback(\AppBundle\Entity\Feedback $feedbacks)
    {
        $this->feedbacks[] = $feedbacks;
        $feedbacks->setEntry($this);

        return $this;
    }

    /**
     * Remove feedbacks
     *
     * @param \AppBundle\Entity\Feedback $feedbacks
     */
    public function removeFeedback(\AppBundle\Entity\Feedback $feedbacks)
    {
        $this->feedbacks->removeElement($feedbacks);
    }

    /**
     * Get feedbacks
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getFeedbacks()
    {
        return $this->feedbacks;
    }

    /**
     * Add traits
     *
     * @param \AppBundle\Entity\ComplexTrait $traits
     * @return Entry
     */
    public function addTrait(\AppBundle\Entity\ComplexTrait $traits)
    {
        $this->traits[] = $traits;
        $traits->setEntry($this);

        return $this;
    }

    /**
     * Remove traits
     *
     * @param \AppBundle\Entity\ComplexTrait $traits
     */
    public function removeTrait(\AppBundle\Entity\ComplexTrait $traits)
    {
        $this->traits->removeElement($traits);
    }

    /**
     * Get traits
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTraits()
    {
        return $this->traits;
    }

    /**
     * Add taxonAList
     *
     * @param \AppBundle\Entity\ComplexTaxon $taxonAList
     * @return Entry
     */
    public function addTaxonAList(\AppBundle\Entity\ComplexTaxon $taxonAList)
    {
        $this->taxonAList[] = $taxonAList;
        $taxonAList->setEntryAsTaxonA($this);

        return $this;
    }

    /**
     * Remove taxonAList
     *
     * @param \AppBundle\Entity\ComplexTaxon $taxonAList
     */
    public function removeTaxonAList(\AppBundle\Entity\ComplexTaxon $taxonAList)
    {
        $this->taxonAList->removeElement($taxonAList);
    }

    /**
     * Get taxonAList
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTaxonAList()
    {
        return $this->taxonAList;
    }

    /**
     * Add taxonBList
     *
     * @param \AppBundle\Entity\ComplexTaxon $taxonBList
     * @return Entry
     */
    public function addTaxonBList(\AppBundle\Entity\ComplexTaxon $taxonBList)
    {
        $this->taxonBList[] = $taxonBList;
        $taxonBList->setEntryAsTaxonB($this);

        return $this;
    }

    /**
     * Remove taxonBList
     *
     * @param \AppBundle\Entity\ComplexTaxon $taxonBList
     */
    public function removeTaxonBList(\AppBundle\Entity\ComplexTaxon $taxonBList)
    {
        $this->taxonBList->removeElement($taxonBList);
    }

    /**
     * Get taxonBList
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTaxonBList()
    {
        return $this->taxonBList;
    }

    /**
     * Add mutations
     *
     * @param \AppBundle\Entity\Mutation $mutations
     * @return Entry
     */
    public function addMutation(\AppBundle\Entity\Mutation $mutations)
    {
        $this->mutations[] = $mutations;
        $mutations->setEntry($this);

        return $this;
    }

    /**
     * Remove mutations
     *
     * @param \AppBundle\Entity\Mutation $mutations
     */
    public function removeMutation(\AppBundle\Entity\Mutation $mutations)
    {
        $this->mutations->removeElement($mutations);
    }

    /**
     * Get mutations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMutations()
    {
        return $this->mutations;
    }

    /**
     * Add validators
     *
     * @param \AppBundle\Entity\User $validators
     * @return Entry
     */
    public function addValidator(\AppBundle\Entity\User $validator)
    {
        $this->validator[] = $validator;
        $validator->addEntriesValidator($this);

        $this->setTempDateValidation(new \DateTime());
        $this->setDateEmail(null);

        return $this;
    }

    /**
     * Remove validators
     *
     * @param \AppBundle\Entity\User $validators
     */
    public function removeValidator(\AppBundle\Entity\User $validators)
    {
        $this->validators->removeElement($validators);
    }

    /**
     * Get validators
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getValidators()
    {
        return $this->validators;
    }
}
