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

// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id 
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="surname", type="string", length=255, nullable=true)
     */
    private $surname;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @ORM\ManyToMany(targetEntity="Entry", inversedBy="validators")
     * @ORM\JoinTable(name="validator_entry")
     */
    private $entriesValidators;

    /**
     * @ORM\ManyToMany(targetEntity="Entry", inversedBy="otherCurators")
     * @ORM\JoinTable(name="user_entry")
     */
    private $entries;

    public function __construct()
    {
        parent::__construct();
        $this->entries = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add entries
     *
     * @param \AppBundle\Entity\Entry $entries
     * @return User
     */
    public function addEntry(\AppBundle\Entity\Entry $entries)
    {
        $this->entries[] = $entries;

        return $this;
    }

    /**
     * Remove entries
     *
     * @param \AppBundle\Entity\Entry $entries
     */
    public function removeEntry(\AppBundle\Entity\Entry $entries)
    {
        $this->entries->removeElement($entries);
    }

    /**
     * Get entries
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * Set id
     *
     * @param string $id
     * @return User
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set surname
     *
     * @param string $surname
     * @return User
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get surname
     *
     * @return string 
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return User
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
     * Set token
     *
     * @param string $token
     * @return User
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Get credentialsExpireAt
     *
     * @return \DateTime
     */
    public function getCredentialsExpireAt()
    {
        return $this->credentialsExpireAt;
    }

    /**
     * Get User's full name (Surname + name)
     */
    public function getFullName()
    {
        return $this->surname." ".$this->name;
    }

    /**
     * Add entriesValidators
     *
     * @param \AppBundle\Entity\Entry $entriesValidators
     * @return User
     */
    public function addEntriesValidator(\AppBundle\Entity\Entry $entriesValidators)
    {
        $this->entriesValidators[] = $entriesValidators;

        return $this;
    }

    /**
     * Remove entriesValidators
     *
     * @param \AppBundle\Entity\Entry $entriesValidators
     */
    public function removeEntriesValidator(\AppBundle\Entity\Entry $entriesValidators)
    {
        $this->entriesValidators->removeElement($entriesValidators);
    }

    /**
     * Get entriesValidators
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEntriesValidators()
    {
        return $this->entriesValidators;
    }
}
