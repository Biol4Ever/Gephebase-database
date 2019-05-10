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
 * Gene
 *
 * @ORM\Table(name="gene", indexes={@ORM\Index(name="gene_search_idx", columns={"name", "uniProtKb_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GeneRepository")
 */
class Gene
{
    const TAXON_ID_MATCHES_A = "Taxon A";
    const TAXON_ID_MATCHES_B = "Taxon B";
    const TAXON_ID_MATCHES_A_AND_B = "Taxon A and B";
    const TAXON_ID_MATCHES_NONE = "";

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
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="uniProtKb_id", type="string", length=255, nullable=true )
     */
    private $uniProtKbId;

    /**
     * @var string
     *
     * @ORM\Column(name="string", type="string", length=255, nullable=true )
     */
    private $string;

    /**
     * @var string
     *
     * @ORM\Column(name="taxonomic_identifier", type="string", length=255, nullable=true )
     */
    private $taxonomicIdentifier;

    /**
     * @var string
     *
     * @ORM\Column(name="organism", type="string", length=255, nullable=true )
     */
    private $organism;

    /**
     * @var string
     *
     * @ORM\Column(name="sequenceSimilarities", type="string", length=255, nullable=true )
     */
    private $sequenceSimilarities;

    /**
     * @ORM\ManyToMany(targetEntity="Synonym", cascade={"persist"})
     */
    private $synonyms;

    /**
     * @ORM\ManyToMany(targetEntity="Go", cascade={"persist"})
     * @ORM\JoinTable(name="gene_molecular_go")
     */
    private $goMolecular;

    /**
     * @ORM\ManyToMany(targetEntity="Go", cascade={"persist"})
     * @ORM\JoinTable(name="gene_biological_go")
     */
    private $goBiological;

    /**
     * @ORM\ManyToMany(targetEntity="Go", cascade={"persist"})
     * @ORM\JoinTable(name="gene_cellular_go")
     */
    private $goCellular;

    /**
     * @ORM\ManyToMany(targetEntity="Go")
     * @ORM\JoinTable(name="gene_other_go")
     */
    private $goOther;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->synonyms = new \Doctrine\Common\Collections\ArrayCollection();
        $this->goMolecular = new \Doctrine\Common\Collections\ArrayCollection();
        $this->goBiological = new \Doctrine\Common\Collections\ArrayCollection();
        $this->goCellular = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Gene
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set uniProtKbId
     *
     * @param string $uniProtKbId
     * @return Gene
     */
    public function setUniProtKbId($uniProtKbId)
    {
        $this->uniProtKbId = $uniProtKbId;

        return $this;
    }

    /**
     * Get uniProtKbId
     *
     * @return string 
     */
    public function getUniProtKbId()
    {
        return $this->uniProtKbId;
    }

    /**
     * Set string
     *
     * @param string $string
     * @return Gene
     */
    public function setString($string)
    {
        $this->string = $string;

        return $this;
    }

    /**
     * Get string
     *
     * @return string 
     */
    public function getString()
    {
        return $this->string;
    }

     /**
     * Set sequenceSimilarities
     *
     * @param string $sequenceSimilarities
     * @return Gene
     */
    public function setSequenceSimilarities($sequenceSimilarities)
    {
        $this->sequenceSimilarities = $sequenceSimilarities;

        return $this;
    }

    /**
     * Get sequenceSimilarities
     *
     * @return string 
     */
    public function getSequenceSimilarities()
    {
        return $this->sequenceSimilarities;
    }

    /**
     * Add synonyms
     *
     * @param \AppBundle\Entity\Synonym $synonyms
     * @return Gene
     */
    public function addSynonym(\AppBundle\Entity\Synonym $synonyms)
    {
        $this->synonyms[] = $synonyms;

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
     * Add goMolecular
     *
     * @param \AppBundle\Entity\Go $goMolecular
     * @return Gene
     */
    public function addGoMolecular(\AppBundle\Entity\Go $goMolecular)
    {
        $this->goMolecular[] = $goMolecular;

        return $this;
    }

    /**
     * Remove goMolecular
     *
     * @param \AppBundle\Entity\Go $goMolecular
     */
    public function removeGoMolecular(\AppBundle\Entity\Go $goMolecular)
    {
        $this->goMolecular->removeElement($goMolecular);
    }

    /**
     * Get goMolecular
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGoMolecular()
    {
        return $this->goMolecular;
    }

    /**
     * Add goBiological
     *
     * @param \AppBundle\Entity\Go $goBiological
     * @return Gene
     */
    public function addGoBiological(\AppBundle\Entity\Go $goBiological)
    {
        $this->goBiological[] = $goBiological;

        return $this;
    }

    /**
     * Remove goBiological
     *
     * @param \AppBundle\Entity\Go $goBiological
     */
    public function removeGoBiological(\AppBundle\Entity\Go $goBiological)
    {
        $this->goBiological->removeElement($goBiological);
    }

    /**
     * Get goBiological
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGoBiological()
    {
        return $this->goBiological;
    }

    /**
     * Add goCellular
     *
     * @param \AppBundle\Entity\Go $goCellular
     * @return Gene
     */
    public function addGoCellular(\AppBundle\Entity\Go $goCellular)
    {
        $this->goCellular[] = $goCellular;

        return $this;
    }

    /**
     * Remove goCellular
     *
     * @param \AppBundle\Entity\Go $goCellular
     */
    public function removeGoCellular(\AppBundle\Entity\Go $goCellular)
    {
        $this->goCellular->removeElement($goCellular);
    }

    /**
     * Get goCellular
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGoCellular()
    {
        return $this->goCellular;
    }

    /**
     * Add goOther
     *
     * @param \AppBundle\Entity\Go $goOther
     * @return Gene
     */
    public function addGoOther(\AppBundle\Entity\Go $goOther)
    {
        $this->goOther[] = $goOther;

        return $this;
    }

    /**
     * Remove goOther
     *
     * @param \AppBundle\Entity\Go $goOther
     */
    public function removeGoOther(\AppBundle\Entity\Go $goOther)
    {
        $this->goOther->removeElement($goOther);
    }

    /**
     * Get goOther
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGoOther()
    {
        return $this->goOther;
    }

    public function addGo(\AppBundle\Entity\Go $go)
    {
        $category = $go->getCategory();

        switch ($category) {
            case 'F':
                $this->addGoMolecular($go);
                break;

            case 'P':
                $this->addGoBiological($go);
                break;

            case 'C':
                $this->addGoCellular($go);
                break;
        }

        return $this;
    }

    /**
     * Set taxonomicIdentifier
     *
     * @param string $taxonomicIdentifier
     *
     * @return Gene
     */
    public function setTaxonomicIdentifier($taxonomicIdentifier)
    {
        $this->taxonomicIdentifier = $taxonomicIdentifier;

        return $this;
    }

    /**
     * Get taxonomicIdentifier
     *
     * @return string
     */
    public function getTaxonomicIdentifier()
    {
        return $this->taxonomicIdentifier;
    }

    /**
     * Set organism
     *
     * @param string $organism
     *
     * @return Gene
     */
    public function setOrganism($organism)
    {
        $this->organism = $organism;

        return $this;
    }

    /**
     * Get organism
     *
     * @return string
     */
    public function getOrganism()
    {
        return $this->organism;
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
}
