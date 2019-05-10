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

namespace AppBundle\Form\DataTransformer;

use AppBundle\Entity\TraitCategory;
use AppBundle\Entity\Entry;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class TraitCategoryTransformer implements DataTransformerInterface
{
    private $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    /**
     * Transforms a string of categories to an array (list of categories).
     *
     * @param  string|null $category
     * @return array
     */
    public function transform($category)
    {
        // Initialize the list of categories
        $categoryArray = array();
        $traitList = Entry::getTraitCategoryList();

        // Check if $category is empty
        if ($category == null) {
            return $categoryArray;
        }

        // Create an array from the string
        foreach($traitList as $trait) {
            if(strpos($category, $trait) !== false) {
                $categoryArray[] = $trait;
            }
        }

        return $categoryArray;
    }

    /**
     * Transforms an array (list of categories) to a string.
     *
     * @param  array $categoryArray
     * @return string|null
     * @throws TransformationFailedException if object (categoryArray) is not found.
     */
    public function reverseTransform($categoryArray)
    {
        // Category is not required
        if (!$categoryArray) {
            return;
        }

        // Implode the array
        $category = implode('', $categoryArray);

        return $category;
    }
}