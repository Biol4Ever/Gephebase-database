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

use AppBundle\Entity\Synonym;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class GeneSynonymsToListTransformer implements DataTransformerInterface
{
    private $separator = ';';
    private $em;
    private $gene;

    public function __construct(ObjectManager $em, $gene)
    {
        $this->em = $em;
        $this->gene = $gene;
    }

    /**
     * Transforms a collection of objects (synonym) to a string (list of synonyms).
     *
     * @param  PersistentCollection|null $synonyms
     * @return string
     */
    public function transform($synonyms)
    {
        $list = '';

        if (!$synonyms || $synonyms->isEmpty()) {
            return $list;
        }

        foreach ($synonyms as $key => $synonym) {
            if ($key !== 0) {
                $list .= $this->separator . ' ';
            }

            $list .= $synonym->getName();
        }

        return $list;
    }

    /**
     * Transforms a string (semi-colon seperated list of synonyms) to a collection of objects (synonym).
     *
     * @param  string $list
     * @return PersistentCollection|null
     */
    public function reverseTransform($list)
    {
        if (!$this->gene || $list === '') {
            return array();
        }

        $collection =  $this->gene->getSynonyms();

        // clean input string
        $synonyms = array_filter(array_map('trim', explode($this->separator, $list)));

        // remove from collection all synonyms that were deleted in form
        foreach ($collection as $synonym) {
            if (!in_array($synonym->getName(), $synonyms)) {
                $collection->removeElement($synonym);
            } else {
                if(($key = array_search($synonym->getName(), $synonyms)) !== false) {
                    unset($synonyms[$key]);
                }
            }
        }

        // attach existing synonyms that were added in form
        $synonymEntities = $this->em->getRepository('AppBundle:Synonym')->findByName($synonyms);
        foreach ($synonymEntities as $existingSynonym) {
            $collection->add($existingSynonym);
            if(($key = array_search($existingSynonym->getName(), $synonyms)) !== false) {
                unset($synonyms[$key]);
            }
        }

        // create new synonyms that were added in form and didn't exist in our database
        foreach ($synonyms as $name) {
            $newSynonym = new Synonym();
            $newSynonym->setName($name);
            $this->em->persist($newSynonym);
            $collection->add($newSynonym);
        }

        return $collection;
    }
}