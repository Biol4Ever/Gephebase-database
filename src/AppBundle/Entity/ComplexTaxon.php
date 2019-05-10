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

/**
 * ComplexTaxon
 *
 * @ORM\Table(name="complex_taxon")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ComplexTaxonRepository")
 */
class ComplexTaxon
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var bool
     *
     * @ORM\Column(name="isInfraspecies", type="boolean", nullable=true)
     */
    private $isInfraspecies;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="import_taxon_id", type="string", length=255, nullable=true)
     */
    private $importTaxonId;

    /**
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="taxonAList")
     * @ORM\JoinColumn(name="entry_as_taxon_a_id", referencedColumnName="id")
     */
    private $entryAsTaxonA;

    /**
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="taxonBList")
     * @ORM\JoinColumn(name="entry_as_taxon_b_id", referencedColumnName="id")
     */
    private $entryAsTaxonB;

    /**
     * @ORM\ManyToOne(targetEntity="Taxon")
     * @ORM\JoinColumn(name="taxon_id", referencedColumnName="id")
     */
    private $taxon;


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
     * Set isInfraspecies
     *
     * @param boolean $isInfraspecies
     * @return ComplexTaxon
     */
    public function setIsInfraspecies($isInfraspecies)
    {
        $this->isInfraspecies = $isInfraspecies;

        return $this;
    }

    /**
     * Get isInfraspecies
     *
     * @return boolean 
     */
    public function getIsInfraspecies()
    {
        return $this->isInfraspecies;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return ComplexTaxon
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set taxon
     *
     * @param \AppBundle\Entity\Taxon $taxon
     * @return ComplexTaxon
     */
    public function setTaxon(\AppBundle\Entity\Taxon $taxon = null)
    {
        $this->taxon = $taxon;

        return $this;
    }

    /**
     * Get taxon
     *
     * @return \AppBundle\Entity\Taxon 
     */
    public function getTaxon()
    {
        return $this->taxon;
    }

    /**
     * Set entryAsTaxonA
     *
     * @param \AppBundle\Entity\Entry $entryAsTaxonA
     * @return ComplexTaxon
     */
    public function setEntryAsTaxonA(\AppBundle\Entity\Entry $entryAsTaxonA = null)
    {
        $this->entryAsTaxonA = $entryAsTaxonA;

        return $this;
    }

    /**
     * Get entryAsTaxonA
     *
     * @return \AppBundle\Entity\Entry 
     */
    public function getEntryAsTaxonA()
    {
        return $this->entryAsTaxonA;
    }

    /**
     * Set entryAsTaxonB
     *
     * @param \AppBundle\Entity\Entry $entryAsTaxonB
     * @return ComplexTaxon
     */
    public function setEntryAsTaxonB(\AppBundle\Entity\Entry $entryAsTaxonB = null)
    {
        $this->entryAsTaxonB = $entryAsTaxonB;

        return $this;
    }

    /**
     * Get entryAsTaxonB
     *
     * @return \AppBundle\Entity\Entry 
     */
    public function getEntryAsTaxonB()
    {
        return $this->entryAsTaxonB;
    }

    /**
     * Set importTaxonId
     *
     * @param string $importTaxonId
     * @return ComplexTaxon
     */
    public function setImportTaxonId($importTaxonId)
    {
        $this->importTaxonId = $importTaxonId;

        return $this;
    }

    /**
     * Get importTaxonId
     *
     * @return string 
     */
    public function getImportTaxonId()
    {
        return $this->importTaxonId;
    }
}
