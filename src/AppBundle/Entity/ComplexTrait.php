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
 * ComplexTrait
 *
 * @ORM\Table(name="complex_trait")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ComplexTraitRepository")
 */
class ComplexTrait
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
     * @var string
     *
     * @ORM\Column(name="stateInTaxonA", type="text", nullable=true)
     */
    private $stateInTaxonA;

    /**
     * @var string
     *
     * @ORM\Column(name="stateInTaxonB", type="text", nullable=true)
     */
    private $stateInTaxonB;

    /**
     * @ORM\ManyToOne(targetEntity="PhenotypeTrait")
     * @ORM\JoinColumn(name="phenotype_trait_id", referencedColumnName="id")
     */
    private $phenotypeTrait;

    /**
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="traits", cascade={"persist"})
     * @ORM\JoinColumn(name="entry_id", referencedColumnName="id")
     */
    private $entry;


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
     * Set stateInTaxonA
     *
     * @param string $stateInTaxonA
     * @return ComplexTrait
     */
    public function setStateInTaxonA($stateInTaxonA)
    {
        $this->stateInTaxonA = $stateInTaxonA;

        return $this;
    }

    /**
     * Get stateInTaxonA
     *
     * @return string 
     */
    public function getStateInTaxonA()
    {
        return $this->stateInTaxonA;
    }

    /**
     * Set stateInTaxonB
     *
     * @param string $stateInTaxonB
     * @return ComplexTrait
     */
    public function setStateInTaxonB($stateInTaxonB)
    {
        $this->stateInTaxonB = $stateInTaxonB;

        return $this;
    }

    /**
     * Get stateInTaxonB
     *
     * @return string 
     */
    public function getStateInTaxonB()
    {
        return $this->stateInTaxonB;
    }

    /**
     * Set phenotypeTrait
     *
     * @param \AppBundle\Entity\PhenotypeTrait $phenotypeTrait
     * @return ComplexTrait
     */
    public function setPhenotypeTrait(\AppBundle\Entity\PhenotypeTrait $phenotypeTrait = null)
    {
        $this->phenotypeTrait = $phenotypeTrait;

        return $this;
    }

    /**
     * Get phenotypeTrait
     *
     * @return \AppBundle\Entity\PhenotypeTrait 
     */
    public function getPhenotypeTrait()
    {
        return $this->phenotypeTrait;
    }

    /**
     * Set entry
     *
     * @param \AppBundle\Entity\Entry $entry
     * @return ComplexTrait
     */
    public function setEntry(\AppBundle\Entity\Entry $entry = null)
    {
        $this->entry = $entry;

        return $this;
    }

    /**
     * Get entry
     *
     * @return \AppBundle\Entity\Entry 
     */
    public function getEntry()
    {
        return $this->entry;
    }
}
