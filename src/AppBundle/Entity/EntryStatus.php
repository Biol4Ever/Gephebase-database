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
 * EntryStatus
 *
 * @ORM\Table(name="entry_status")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EntryStatusRepository")
 */
class EntryStatus
{
    const TEMPORARY = 1;
    const IMPORTED = 2;
    const ACCEPTED_CURATOR = 3;
    const ACCEPTED_VALIDATOR = 4;
    const REFUSED_VALIDATOR = 5;
    const DELETED = 6;

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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="EntryStatusGroup")
     * @ORM\JoinColumn(name="status_group_id", referencedColumnName="id")
     */
    private $statusGroup;

    /**
     * Set id - used in fixtures on database fixtures load
     *
     * @param integer $id
     * @return EntryStatus
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * @return EntryStatus
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
     * Set statusGroup
     *
     * @param \AppBundle\Entity\EntryStatusGroup $statusGroup
     * @return EntryStatus
     */
    public function setStatusGroup(\AppBundle\Entity\EntryStatusGroup $statusGroup = null)
    {
        $this->statusGroup = $statusGroup;

        return $this;
    }

    /**
     * Get statusGroup
     *
     * @return \AppBundle\Entity\EntryStatusGroup 
     */
    public function getStatusGroup()
    {
        return $this->statusGroup;
    }
}
