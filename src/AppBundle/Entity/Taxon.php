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
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * Taxon
 *
 * @ORM\Table(name="taxon")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TaxonRepository")
 */
class Taxon
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
     * @ORM\Column(name="tax_id", type="string", length=255, nullable=true)
     */
    private $taxId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="latinName", type="string", length=255, nullable=true)
     */
    private $latinName;

    /**
     * @var string
     *
     * @ORM\Column(name="commonName", type="string", length=255, nullable=true)
     */
    private $commonName;

    /**
     * @var string
     *
     * @ORM\Column(name="rank", type="string", length=255, nullable=true)
     */
    private $rank;

    /**
     * @var string
     *
     * @ORM\Column(name="lineage", type="text", nullable=true)
     */
    private $lineage;

    /**
     * @ORM\ManyToOne(targetEntity="Taxon", cascade={"persist"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parentId;

    /**
     * @ORM\ManyToMany(targetEntity="Synonym")
     */
    private $synonyms;

    public function __construct()
    {
        $this->synonyms = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return Taxon
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get Name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set latinName
     *
     * @param string $latinName
     * @return Taxon
     */
    public function setLatinName($latinName)
    {
        $this->latinName = $latinName;

        return $this;
    }

    /**
     * Get latinName
     *
     * @return string 
     */
    public function getLatinName()
    {
        return $this->latinName;
    }

    /**
     * Set commonName
     *
     * @param string $commonName
     * @return Taxon
     */
    public function setCommonName($commonName)
    {
        $this->commonName = $commonName;

        return $this;
    }

    /**
     * Get commonName
     *
     * @return string 
     */
    public function getCommonName()
    {
        return $this->commonName;
    }

    /**
     * Set rank
     *
     * @param string $rank
     * @return Taxon
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Get rank
     *
     * @return string 
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set taxId
     *
     * @param string $taxId
     * @return Taxon
     */
    public function setTaxId($taxId)
    {
        $this->taxId = $taxId;

        return $this;
    }

    /**
     * Get taxId
     *
     * @return string 
     */
    public function getTaxId()
    {
        return $this->taxId;
    }

    /**
     * Set lineage
     *
     * @param string $lineage
     * @return Taxon
     */
    public function setLineage($lineage)
    {
        $this->lineage = $lineage;

        return $this;
    }

    /**
     * Get lineage
     *
     * @return string 
     */
    public function getLineage()
    {
        return $this->lineage;
    }

    /**
     * Set parentId
     *
     * @param \AppBundle\Entity\Taxon $parentId
     * @return Taxon
     */
    public function setParentId(\AppBundle\Entity\Taxon $parentId = null)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * Get parentId
     *
     * @return \AppBundle\Entity\parentId 
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Add synonyms
     *
     * @param \AppBundle\Entity\Synonym $synonyms
     * @return Taxon
     */
    public function addSynonym(\AppBundle\Entity\Synonym $synonym)
    {
        if (!$this->synonyms->contains($synonym)) {
            $this->synonyms[] = $synonym;
        }

        return $this;
    }

    /**
     * Remove synonyms
     *
     * @param \AppBundle\Entity\Synonym $synonyms
     */
    public function removeSynonym(\AppBundle\Entity\Synonym $synonyms)
    {
        $this->synonyms->removeElement($synonyms);
    }

    /**
     * Get synonyms
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSynonyms()
    {
        return $this->synonyms;
    }

    /**
     * Get a list of readable synonyms
     */
    public function getReadableSynonyms()
    {
        $result = '';

        foreach($this->synonyms as $synonym) {
            if ($result !== '') {
                $result .= '; ';
            }

            $result .= $synonym->getName();
        }

        return $result;
    }

    /**
     * Get the name of the parent
     */
    public function getReadableParent()
    {
        $result = '';

        $parent = $this->getParentId();

        if ($parent) {
            $result = $parent->getName();
        }

        return $result;
    }

    /**
     * Get the ID of the parent
     */
    public function getParentTaxId()
    {
        $result = '';

        $parent = $this->getParentId();

        if ($parent) {
            $result = $parent->getTaxId();
        }

        return $result;
    }
}
