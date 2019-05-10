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
 * MutationReference
 *
 * @ORM\Table(name="mutation_reference")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MutationReferenceRepository")
 */
class MutationReference
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
     * @ORM\ManyToOne(targetEntity="Reference")
     * @ORM\JoinColumn(name="reference_id", referencedColumnName="id")
     */
    private $reference;

    /**
     * @ORM\ManyToOne(targetEntity="Mutation", inversedBy="otherReferences")
     * @ORM\JoinColumn(name="mutation_id", referencedColumnName="id")
     */
    private $mutation;


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
     * Set reference
     *
     * @param \AppBundle\Entity\Reference $reference
     * @return MutationReference
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
     * Set mutation
     *
     * @param \AppBundle\Entity\Mutation $mutation
     * @return MutationReference
     */
    public function setMutation(\AppBundle\Entity\Mutation $mutation = null)
    {
        $this->mutation = $mutation;

        return $this;
    }

    /**
     * Get mutation
     *
     * @return \AppBundle\Entity\Mutation 
     */
    public function getMutation()
    {
        return $this->mutation;
    }
}
