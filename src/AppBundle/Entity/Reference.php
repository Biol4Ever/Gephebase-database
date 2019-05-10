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
 * Reference
 *
 * @ORM\Table(name="reference", indexes={@ORM\Index(name="reference_search_idx", columns={"journal_year", "journal_title"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ReferenceRepository")
 */
class Reference
{
    /**
     * @ORM\ManyToMany(targetEntity="Author")
     * @ORM\JoinTable(name="reference_author")
     */
    private $authors;
    
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
     * @ORM\Column(name="article_title", type="string", length=255, nullable = true)
     */
    private $articleTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="journal_abbreviation", type="string", length=255, nullable = true)
     */
    private $journalAbbreviation;

    /**
     * @var string
     *
     * @ORM\Column(name="journal_title", type="string", length=255, nullable = true)
     */
    private $journalTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="journal_volume", type="string", length=255, nullable = true)
     */
    private $journalVolume;

    /**
     * @var string
     *
     * @ORM\Column(name="journal_year", type="string", length=255, nullable = true)
     */
    private $journalYear;

    /**
     * @var string
     *
     * @ORM\Column(name="journal_pagination", type="string", length=255, nullable = true)
     */
    private $journalPagination;

    /**
     * @var string
     *
     * @ORM\Column(name="publication_type", type="string", length=255, nullable = true)
     */
    private $publicationType;

    /**
     * @var string
     *
     * @ORM\Column(name="abstract", type="text", nullable = true)
     */
    private $abstract;

    /**
     * @var string
     *
     * @ORM\Column(name="doi", type="string", length=255, nullable=true)
     */
    private $doi;

    /**
     * @var string
     *
     * @ORM\Column(name="pm_id", type="string", length=255, nullable = true)
     */
    private $pmId;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable = true)
     */
    private $url;

    public function __construct()
    {
        $this->authors = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set title
     *
     * @param string $title
     * @return Reference
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set journalTitle
     *
     * @param string $journalTitle
     * @return Reference
     */
    public function setJournalTitle($journalTitle)
    {
        $this->journalTitle = $journalTitle;

        return $this;
    }

    /**
     * Get journalTitle
     *
     * @return string 
     */
    public function getJournalTitle()
    {
        return $this->journalTitle;
    }

    /**
     * Set journalVolume
     *
     * @param string $journalVolume
     * @return Reference
     */
    public function setJournalVolume($journalVolume)
    {
        $this->journalVolume = $journalVolume;

        return $this;
    }

    /**
     * Get journalVolume
     *
     * @return string 
     */
    public function getJournalVolume()
    {
        return $this->journalVolume;
    }

    /**
     * Set publicationType
     *
     * @param string $publicationType
     * @return Reference
     */
    public function setPublicationType($publicationType)
    {
        $this->publicationType = $publicationType;

        return $this;
    }

    /**
     * Get publicationType
     *
     * @return string 
     */
    public function getPublicationType()
    {
        return $this->publicationType;
    }

    /**
     * Set abstract
     *
     * @param string $abstract
     * @return Reference
     */
    public function setAbstract($abstract)
    {
        $this->abstract = $abstract;

        return $this;
    }

    /**
     * Get abstract
     *
     * @return string 
     */
    public function getAbstract()
    {
        return $this->abstract;
    }

    /**
     * Set doi
     *
     * @param string $doi
     * @return Reference
     */
    public function setDoi($doi)
    {
        $this->doi = $doi;

        return $this;
    }

    /**
     * Get doi
     *
     * @return string 
     */
    public function getDoi()
    {
        return $this->doi;
    }

    /**
     * Set pmId
     *
     * @param string $pmId
     * @return Reference
     */
    public function setPmId($pmId)
    {
        $this->pmId = $pmId;

        return $this;
    }

    /**
     * Get pmId
     *
     * @return string 
     */
    public function getPmId()
    {
        return $this->pmId;
    }

    /**
     * Set articleTitle
     *
     * @param string $articleTitle
     * @return Reference
     */
    public function setArticleTitle($articleTitle)
    {
        $this->articleTitle = $articleTitle;

        return $this;
    }

    /**
     * Get articleTitle
     *
     * @return string 
     */
    public function getArticleTitle()
    {
        return $this->articleTitle;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Reference
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Add authors
     *
     * @param \AppBundle\Entity\Author $authors
     * @return Reference
     */
    public function addAuthor(\AppBundle\Entity\Author $authors)
    {
        $this->authors[] = $authors;

        return $this;
    }

    /**
     * Remove authors
     *
     * @param \AppBundle\Entity\Author $authors
     */
    public function removeAuthor(\AppBundle\Entity\Author $authors)
    {
        $this->authors->removeElement($authors);
    }

    /**
     * Get authors
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    /**
     * Set journalAbbreviation
     *
     * @param string $journalAbbreviation
     * @return Reference
     */
    public function setJournalAbbreviation($journalAbbreviation)
    {
        $this->journalAbbreviation = $journalAbbreviation;

        return $this;
    }

    /**
     * Get journalAbbreviation
     *
     * @return string 
     */
    public function getJournalAbbreviation()
    {
        return $this->journalAbbreviation;
    }

    /**
     * Set journalYear
     *
     * @param string $journalYear
     * @return Reference
     */
    public function setJournalYear($journalYear)
    {
        $this->journalYear = $journalYear;

        return $this;
    }

    /**
     * Get journalYear
     *
     * @return string 
     */
    public function getJournalYear()
    {
        return $this->journalYear;
    }

    /**
     * Set journalPagination
     *
     * @param string $journalPagination
     * @return Reference
     */
    public function setJournalPagination($journalPagination)
    {
        $this->journalPagination = $journalPagination;

        return $this;
    }

    /**
     * Get journalPagination
     *
     * @return string 
     */
    public function getJournalPagination()
    {
        return $this->journalPagination;
    }

    /**
     * Get a partial list of authors seperated by semi-colon, limited to 8
     */
    public function getPartialAuthors()
    {
        $result = '';

        $i=0;
        foreach($this->authors as $author) {
            if ($result !== '') {
                $result .= '; ';
            }

            // limit list to 8 authors
            if ($i==8) {
                $result .= 'et al.';
                break;
            }

            $result .= $author->getLastname()." ".$author->getInitials();
            $i++;
        }

        return $result;
    }

    /**
     * Get the full list of authors seperated by semi-colon
     */
    public function getFullAuthors()
    {
        $result = '';

        foreach($this->authors as $author) {
            if ($result !== '') {
                $result .= '; ';
            }

            $result .= $author->getLastname()." ".$author->getInitials();
        }

        return $result;
    }

    /**
     * Return the journal title of the reference appended with the date
     */
    public function getTitleWithDate()
    {
        $title = $this->articleTitle;

        // if the last character is not a period, add a period.
        if(substr($title, -1) !== '.') {
            $title .= ".";
        }

        if ($this->journalYear) {
            $title .= " (" . $this->journalYear . ")";
        }

        return $title;
    }
}
