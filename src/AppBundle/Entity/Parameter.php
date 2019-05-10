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
 * Parameter
 *
 * @ORM\Table(name="parameter")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ParameterRepository")
 */
class Parameter
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
     * @ORM\Column(name="contact", type="string", length=255)
     */
    private $contact;

    /**
     * @var string
     *
     * @ORM\Column(name="contact_validator", type="string", length=255)
     */
    private $contactValidator;

    /**
     * @var string
     *
     * @ORM\Column(name="homepage_description", type="string", length=5000)
     */
    private $homepageDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="whats_new", type="string", length=5000)
     */
    private $whatsNew;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255)
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="fromMail", type="string", length=255)
     */
    private $fromMail;

    /**
     * @var string
     *
     * @ORM\Column(name="loginMail", type="string", length=255)
     */
    private $loginMail;

    /**
     * @var string
     *
     * @ORM\Column(name="mail", type="string", length=5000)
     */
    private $mail;

    /**
     * @var string
     *
     * @ORM\Column(name="greetings", type="string", length=5000)
     */
    private $greetings;

    /**
     * @var string
     *
     * @ORM\Column(name="base", type="string", length=255)
     */
    private $base;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastEntry", type="datetime", nullable=true)
     */
    private $lastEntry;

    /**
     * @var string
     *
     * @ORM\Column(name="lastReference", type="string", length=255, nullable = true)
     */
    private $lastReference;

    /**
     * @var int
     *
     * @ORM\Column(name="importedNumber", type="integer")
     */
    private $importedNumber = 0;

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
     * Set id
     *
     * @param string $id
     * @return Parameters
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }


    /**
     * Set contact
     *
     * @param string $contact
     * @return Parameters
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return string 
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set contactValidator
     *
     * @param string $contactValidator
     * @return Parameters
     */
    public function setContactValidator($contactValidator)
    {
        $this->contactValidator = $contactValidator;

        return $this;
    }

    /**
     * Get contactValidator
     *
     * @return string 
     */
    public function getContactValidator()
    {
        return $this->contactValidator;
    }

    /**
     * Set homepageDescription
     *
     * @param string $homepageDescription
     * @return Parameters
     */
    public function setHomepageDescription($homepageDescription)
    {
        $this->homepageDescription = $homepageDescription;

        return $this;
    }

    /**
     * Get homepageDescription
     *
     * @return string 
     */
    public function getHomepageDescription()
    {
        return $this->homepageDescription;
    }

    /**
     * Get mail
     *
     * @return string 
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Set mail
     *
     * @param string $mail
     * @return Parameters
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get fromMail
     *
     * @return string 
     */
    public function getFromMail()
    {
        return $this->fromMail;
    }

    /**
     * Set fromMail
     *
     * @param string $fromMail
     * @return Parameters
     */
    public function setFromMail($fromMail)
    {
        $this->fromMail = $fromMail;

        return $this;
    }

    /**
     * Get loginMail
     *
     * @return string 
     */
    public function getLoginMail()
    {
        return $this->loginMail;
    }

    /**
     * Set loginMail
     *
     * @param string $loginMail
     * @return Parameters
     */
    public function setLoginMail($loginMail)
    {
        $this->loginMail = $loginMail;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set subject
     *
     * @param string $subject
     * @return Parameters
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Set greetings
     *
     * @param string $greetings
     * @return Parameters
     */
    public function setGreetings($greetings)
    {
        $this->greetings = $greetings;

        return $this;
    }

    /**
     * Get greetings
     *
     * @return string 
     */
    public function getGreetings()
    {
        return $this->greetings;
    }

    /**
     * Set whatsNew
     *
     * @param string $whatsNew
     * @return Parameters
     */
    public function setWhatsNew($whatsNew)
    {
        $this->whatsNew = $whatsNew;

        return $this;
    }

    /**
     * Get whatsNew
     *
     * @return string 
     */
    public function getWhatsNew()
    {
        return $this->whatsNew;
    }

    /**
     * Set base
     *
     * @param string $base
     * @return Parameters
     */
    public function setBase($base)
    {
        $this->base = $base;

        return $this;
    }

    /**
     * Get base
     *
     * @return string 
     */
    public function getBase()
    {
        return $this->base;
    }


    /**
     * Set lastEntry
     *
     * @param \DateTime $lastEntry
     * @return Parameters
     */
    public function setLastEntry($lastEntry)
    {
        $this->lastEntry = $lastEntry;

        return $this;
    }


    /**
     * Get lastEntry
     *
     * @return \DateTime 
     */
    public function getLastEntry()
    {
        return $this->lastEntry;
    }

    /**
     * Set lastReference
     *
     * @param string $lastReference
     * @return Parameters
     */
    public function setLastReference($lastReference)
    {
        $this->lastReference = $lastReference;

        return $this;
    }
    /**
     * Get lastReference
     *
     * @return string 
     */
    public function getLastReference()
    {
        return $this->lastReference;
    }

    /**
     * Set importedNumber
     *
     * @param boolean $importedNumber
     * @return Parameter
     */
    public function setImportedNumber($importedNumber)
    {
        $this->importedNumber = $importedNumber;

        return $this;
    }

    /**
     * Get importedNumber
     *
     * @return boolean 
     */
    public function getImportedNumber()
    {
        return $this->importedNumber;
    }
}
