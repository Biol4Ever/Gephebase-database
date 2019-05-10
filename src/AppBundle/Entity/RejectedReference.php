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
 * RejectedReference
 *
 * @ORM\Table(name="rejected_reference")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RejectedReferenceRepository")
 */
class RejectedReference
{
    const MESSAGE_DUPLICATE_REFERENCE = 'Error: This paper has already been added to the rejected list.';
    const INVALID_REFERENCE = 'Error: Reference identifier not found in Gephebase.';
    const REJECTED_REFERENCE_ADDED = 'Rejected paper successfully added to Gephebase.';
    const REFERENCE_DELETED = 'Reference successfully deleted from rejected paper list.';

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
     * @ORM\Column(name="reason", type="text", nullable=true)
     */
    private $reason;

    /**
     * @var string
     *
     * @ORM\Column(name="reference_identifier", type="string", length=255, nullable=true)
     */
    private $referenceIdentifier;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $curator;

    /**
     * @ORM\OneToOne(targetEntity="Reference")
     * @ORM\JoinColumn(nullable=true)
     */
    private $reference;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set reason
     *
     * @param string $reason
     *
     * @return RejectedReference
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get reason
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set referenceIdentifier
     *
     * @param string $referenceIdentifier
     *
     * @return RejectedReference
     */
    public function setReferenceIdentifier($referenceIdentifier)
    {
        $this->referenceIdentifier = $referenceIdentifier;

        return $this;
    }

    /**
     * Get referenceIdentifier
     *
     * @return string
     */
    public function getReferenceIdentifier()
    {
        return $this->referenceIdentifier;
    }

    /**
     * Set curator
     *
     * @param \AppBundle\Entity\User $curator
     *
     * @return RejectedReference
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

    /**
     * Set reference
     *
     * @param \AppBundle\Entity\Reference $reference
     *
     * @return RejectedReference
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
}
