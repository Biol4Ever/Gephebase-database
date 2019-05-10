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

use AppBundle\Entity\Author;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class AuthorsToListTransformer implements DataTransformerInterface
{
    private $separator = ';';
    private $em;
    private $reference;

    public function __construct(ObjectManager $em, $reference)
    {
        $this->em = $em;
        $this->reference = $reference;
    }

    /**
     * Transforms a collection of objects (authors) to a string (list of authors).
     *
     * @param  PersistentCollection|null $authors
     * @return string
     */
    public function transform($authors)
    {
        $list = '';

        if (!$authors || $authors->isEmpty()) {
            return $list;
        }

        foreach ($authors as $key => $author) {
            if ($key !== 0) {
                $list .= $this->separator . ' ';
            }

            $list .= $author->getLastName() . ', ' .$author->getInitials();
        }

        return $list;
    }

    /**
     * No reverse transformation required, authors cannot be modified.
     *
     * @param  string $list
     * @return PersistentCollection|null
     */
    public function reverseTransform($list)
    {
        if (!$this->reference || $list === "") {
            return array();
        } 

        $collection = $this->reference->getAuthors();

        return $collection;
    }
}