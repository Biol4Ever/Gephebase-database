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
 * SuggestedArticle
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class SuggestedArticle
{
    const DOI = 'doi';
    const PMID = 'pmid';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="articleId", type="string", length=255)
     */
    private $articleId;

    /**
     * @var string
     *
     * @ORM\Column(name="idType", type="string", length=255)
     */
    private $idType;

    /**
     * @var string
     *
     * @ORM\Column(name="comments", type="text", nullable=true)
     */
    private $comments;

    /**
     * @ORM\ManyToOne(targetEntity="Reference")
     * @ORM\JoinColumn(name="reference_id", referencedColumnName="id")
     */
    private $reference;

    /**
     * @ORM\Column(name="date", type="datetime")  
     */
    private $submissionDate;

    /**
     * @ORM\Column(name="curationDate", type="datetime", nullable=true)  
     */
    private $curationDate;

    /**
     * @ORM\Column(name="rejectedDate", type="datetime", nullable=true)  
     */
    private $rejectedDate;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="curator_id", referencedColumnName="id")
     */
    private $curator;

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
     * Set articleId
     *
     * @param string $articleId
     * @return SuggestedArticle
     */
    public function setArticleId($articleId)
    {
        $this->articleId = $articleId;

        return $this;
    }

    /**
     * Get articleId
     *
     * @return string 
     */
    public function getArticleId()
    {
        return $this->articleId;
    }

    /**
     * Set idType
     *
     * @param string $idType
     * @return SuggestedArticle
     */
    public function setIdType($idType)
    {
        $this->idType = $idType;

        return $this;
    }

    /**
     * Get idType
     *
     * @return string 
     */
    public function getIdType()
    {
        return $this->idType;
    }

    /**
     * Set comments
     *
     * @param string $comments
     * @return SuggestedArticle
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
     * Set reference
     *
     * @param \AppBundle\Entity\Reference $reference
     * @return SuggestedArticle
     */
    public function setReference(\AppBundle\Entity\Reference $reference = null)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get reference
     *
     * @return \AppBundle\Entity\Reference 
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set submissionDate
     *
     * @param \DateTime $submissionDate
     * @return SuggestedArticle
     */
    public function setSubmissionDate($submissionDate)
    {
        $this->submissionDate = $submissionDate;

        return $this;
    }

    /**
     * Get submissionDate
     *
     * @return \DateTime 
     */
    public function getSubmissionDate()
    {
        return $this->submissionDate;
    }

    /**
     * Set curationDate
     *
     * @param \DateTime $curationDate
     * @return SuggestedArticle
     */
    public function setCurationDate($curationDate)
    {
        $this->curationDate = $curationDate;

        return $this;
    }

    /**
     * Get curationDate
     *
     * @return \DateTime 
     */
    public function getCurationDate()
    {
        return $this->curationDate;
    }

    /**
     * Set rejectedDate
     *
     * @param \DateTime $rejectedDate
     * @return SuggestedArticle
     */
    public function setRejectedDate($rejectedDate)
    {
        $this->rejectedDate = $rejectedDate;

        return $this;
    }

    /**
     * Get rejectedDate
     *
     * @return \DateTime 
     */
    public function getRejectedDate()
    {
        return $this->rejectedDate;
    }

    /**
     * Set curator
     *
     * @param \AppBundle\Entity\User $curator
     * @return SuggestedArticle
     */
    public function setCurator(\AppBundle\Entity\User $curator = null)
    {
        $this->curator = $curator;

        return $this;
    }

    /**
     * Get curator
     *
     * @return \AppBundle\Entity\User 
     */
    public function getCurator()
    {
        return $this->curator;
    }
}
