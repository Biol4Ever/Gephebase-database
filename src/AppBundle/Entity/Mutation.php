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
 * Mutation
 *
 * @ORM\Table(name="mutation")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MutationRepository")
 */
class Mutation
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
     * @ORM\Column(name="presumptiveNull", type="string", length=255, nullable=true)
     */
    private $presumptiveNull;

    /**
     * @var string
     *
     * @ORM\Column(name="molecularType", type="string", length=255, nullable=true)
     */
    private $molecularType;

    /**
     * @var string
     *
     * @ORM\Column(name="aberrationType", type="string", length=255, nullable=true)
     */
    private $aberrationType;

    /**
     * @var string
     *
     * @ORM\Column(name="snp", type="string", length=255, nullable=true)
     */
    private $snp;

    /**
     * @var string
     *
     * @ORM\Column(name="aberrationSize", type="string", length=255, nullable=true)
     */
    private $aberrationSize;

    /**
     * @var string
     *
     * @ORM\Column(name="codonTaxonA", type="string", length=10, nullable=true)
     */
    private $codonTaxonA;

    /**
     * @var string
     *
     * @ORM\Column(name="codonTaxonB", type="string", length=10, nullable=true)
     */
    private $codonTaxonB;

    /**
     * @var int
     *
     * @ORM\Column(name="codonPosition", type="string", length=255, nullable=true)
     */
    private $codonPosition;

    /**
     * @var int
     *
     * @ORM\Column(name="aaPosition", type="string", length=255, nullable=true)
     */
    private $aaPosition;

    /**
     * @var string
     *
     * @ORM\Column(name="aminoAcidTaxonA", type="string", length=255, nullable=true)
     */
    private $aminoAcidTaxonA;

    /**
     * @var string
     *
     * @ORM\Column(name="aminoAcidTaxonB", type="string", length=255, nullable=true)
     */
    private $aminoAcidTaxonB;

    /**
     * @var string
     *
     * @ORM\Column(name="molecularDetails", type="text", nullable=true)
     */
    private $molecularDetails;

    /**
     * @var string
     *
     * @ORM\Column(name="experimentalEvidence", type="string", length=255, nullable=true)
     */
    private $experimentalEvidence;

    /**
     * @var int
     *
     * @ORM\Column(name="import_main_reference", type="string", length=255, nullable=true)
     */
    private $importMainReference;

    /**
     * @var int
     *
     * @ORM\Column(name="import_other_references", type="string", length=255, nullable=true)
     */
    private $importOtherReferences;

    /**
     * @var string
     *
     * @ORM\Column(name="validationComment", type="text", nullable=true)
     */
    private $validationComment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="temp_date_validation", type="datetime", nullable=true)
     */
    private $tempDateValidation;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateEmail", type="datetime", nullable=true)
     */
    private $dateEmail;

    /**
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="mutations")
     * @ORM\JoinColumn(name="entry_id", referencedColumnName="id")
     */
    private $entry;

    /**
     * @ORM\ManyToOne(targetEntity="Reference")
     * @ORM\JoinColumn(name="main_reference", referencedColumnName="id")
     */
    private $mainReference;

    /**
     * @ORM\OneToMany(targetEntity="MutationReference", mappedBy="mutation", cascade={"remove", "persist"})
     */
    private $otherReferences;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="validator_id", referencedColumnName="id")
     
    private $validator;*/


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
     * Set presumptiveNull
     *
     * @param string $presumptiveNull
     * @return Mutation
     */
    public function setPresumptiveNull($presumptiveNull)
    {
        $this->presumptiveNull = $presumptiveNull;

        return $this;
    }

    /**
     * Get presumptiveNull
     *
     * @return string 
     */
    public function getPresumptiveNull()
    {
        return $this->presumptiveNull;
    }

    /**
     * Set molecularType
     *
     * @param string $molecularType
     * @return Mutation
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
     * Set aberrationType
     *
     * @param string $aberrationType
     * @return Mutation
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
     * Set snp
     *
     * @param string $snp
     * @return Mutation
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
     * Set aberrationSize
     *
     * @param string $aberrationSize
     * @return Mutation
     */
    public function setAberrationSize($aberrationSize)
    {
        $this->aberrationSize = $aberrationSize;

        return $this;
    }

    /**
     * Get aberrationSize
     *
     * @return string 
     */
    public function getAberrationSize()
    {
        return $this->aberrationSize;
    }

    /**
     * Set codonTaxonA
     *
     * @param string $codonTaxonA
     * @return Mutation
     */
    public function setCodonTaxonA($codonTaxonA)
    {
        $this->codonTaxonA = $codonTaxonA;

        return $this;
    }

    /**
     * Get codonTaxonA
     *
     * @return string 
     */
    public function getCodonTaxonA()
    {
        return $this->codonTaxonA;
    }

    /**
     * Set codonTaxonB
     *
     * @param string $codonTaxonB
     * @return Mutation
     */
    public function setCodonTaxonB($codonTaxonB)
    {
        $this->codonTaxonB = $codonTaxonB;

        return $this;
    }

    /**
     * Get codonTaxonB
     *
     * @return string 
     */
    public function getCodonTaxonB()
    {
        return $this->codonTaxonB;
    }

    /**
     * Set codonPosition
     *
     * @param integer $codonPosition
     * @return Mutation
     */
    public function setCodonPosition($codonPosition)
    {
        $this->codonPosition = $codonPosition;

        return $this;
    }

    /**
     * Get codonPosition
     *
     * @return integer 
     */
    public function getCodonPosition()
    {
        return $this->codonPosition;
    }

    /**
     * Set aaPosition
     *
     * @param integer $aaPosition
     * @return Mutation
     */
    public function setAaPosition($aaPosition)
    {
        $this->aaPosition = $aaPosition;

        return $this;
    }

    /**
     * Get aaPosition
     *
     * @return integer 
     */
    public function getAaPosition()
    {
        return $this->aaPosition;
    }

    /**
     * Set aminoAcidTaxonA
     *
     * @param string $aminoAcidTaxonA
     * @return Mutation
     */
    public function setAminoAcidTaxonA($aminoAcidTaxonA)
    {
        $this->aminoAcidTaxonA = $aminoAcidTaxonA;

        return $this;
    }

    /**
     * Get aminoAcidTaxonA
     *
     * @return string 
     */
    public function getAminoAcidTaxonA()
    {
        return $this->aminoAcidTaxonA;
    }

    /**
     * Set aminoAcidTaxonB
     *
     * @param string $aminoAcidTaxonB
     * @return Mutation
     */
    public function setAminoAcidTaxonB($aminoAcidTaxonB)
    {
        $this->aminoAcidTaxonB = $aminoAcidTaxonB;

        return $this;
    }

    /**
     * Get aminoAcidTaxonB
     *
     * @return string 
     */
    public function getAminoAcidTaxonB()
    {
        return $this->aminoAcidTaxonB;
    }

    /**
     * Set molecularDetails
     *
     * @param string $molecularDetails
     * @return Mutation
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
     * Set experimentalEvidence
     *
     * @param string $experimentalEvidence
     * @return Mutation
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
     * Set entry
     *
     * @param \AppBundle\Entity\Entry $entry
     * @return Mutation
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

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->otherReferences = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set mainReference
     *
     * @param \AppBundle\Entity\Reference $mainReference
     * @return Mutation
     */
    public function setMainReference(\AppBundle\Entity\Reference $mainReference = null)
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
     * Set validator
     *
     * @param \AppBundle\Entity\User $validator
     * @return Mutation
     
    public function setValidator(\AppBundle\Entity\User $validator = null)
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Get validator
     *
     * @return \AppBundle\Entity\User 
     *
    public function getValidator()
    {
        return $this->validator;
    }*/

    /**
     * Add otherReferences
     *
     * @param \AppBundle\Entity\MutationReference $otherReferences
     * @return Mutation
     */
    public function addOtherReference(\AppBundle\Entity\MutationReference $otherReferences)
    {
        $this->otherReferences[] = $otherReferences;
        $otherReferences->setMutation($this);

        return $this;
    }

    /**
     * Remove otherReferences
     *
     * @param \AppBundle\Entity\MutationReference $otherReferences
     */
    public function removeOtherReference(\AppBundle\Entity\MutationReference $otherReferences)
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
     * Set validationComment
     *
     * @param string $validationComment
     * @return Mutation
     */
    public function setValidationComment($validationComment)
    {
        $this->validationComment = $validationComment;

        return $this;
    }

    /**
     * Get validationComment
     *
     * @return string 
     */
    public function getValidationComment()
    {
        return $this->validationComment;
    }

    /**
     * Set importMainReference
     *
     * @param string $importMainReference
     * @return Mutation
     */
    public function setImportMainReference($importMainReference)
    {
        $this->importMainReference = $importMainReference;

        return $this;
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
     * Set tempDateValidation
     *
     * @param \DateTime $tempDateValidation
     * @return Mutation
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
     * Get importMainReference
     *
     * @return string 
     */
    public function getImportMainReference()
    {
        return $this->importMainReference;
    }

    /**
     * Set importOtherReferences
     *
     * @param string $importOtherReferences
     * @return Mutation
     */
    public function setImportOtherReferences($importOtherReferences)
    {
        $this->importOtherReferences = $importOtherReferences;

        return $this;
    }

    /**
     * Get importOtherReferences
     *
     * @return string 
     */
    public function getImportOtherReferences()
    {
        return $this->importOtherReferences;
    }

    /**
     * Checks whether a mutation is coding or not
     */
    public function isCoding()
    {
        if ($this->getMolecularType() == 'Coding' && $this->getAberrationType() == 'SNP') {
            return true;
        } else {
            return false;
        }
    }
}
