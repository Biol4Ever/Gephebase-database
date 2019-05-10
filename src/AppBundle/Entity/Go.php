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
use AppBundle\Twig\ExternalApiExtension;

/**
 * Go
 *
 * @ORM\Table(name="go", indexes={@ORM\Index(name="go_search_idx", columns={"description"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GoRepository")
 */
class Go
{
    const CATEGORY_MOLECULAR = 'F';
    const CATEGORY_BIOLOGICAL = 'P';
    const CATEGORY_CELLULAR = 'C';

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
     * @ORM\Column(name="description", type="string", length=500)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="category", type="text", nullable=true)
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="go_id", type="string", length=255, nullable=true)
     */
    private $goId;

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
     * Set description
     *
     * @param string $description
     * @return Go
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set category
     *
     * @param string $category
     * @return Go
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category
     *
     * @return string 
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set goId
     *
     * @param string $goId
     * @return Go
     */
    public function setGoId($goId)
    {
        $this->goId = $goId;

        return $this;
    }

    /**
     * Get goId
     *
     * @return string 
     */
    public function getGoId()
    {
        return $this->goId;
    }

    /**
     * Get both Id and Description
     */
    public function getFullTerm()
    {
        return '<a href="'. ExternalApiExtension::GO_URL . $this->goId .'" target="_blank">' . $this->goId . ' : ' . $this->description . '</a>';
    }
}
