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

use AppBundle\Entity\Go;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class GoToListTransformer implements DataTransformerInterface
{
    private $separator = "\n";
    private $em;
    private $gene;
    private $category;
    private $descriptions = array();
    private $goFunctions = array(
        Go::CATEGORY_MOLECULAR => 'getGoMolecular',
        Go::CATEGORY_BIOLOGICAL => 'getGoBiological',
        Go::CATEGORY_CELLULAR => 'getGoCellular',
    );

    public function __construct(ObjectManager $em, $gene, $category)
    {
        $this->em = $em;
        $this->gene = $gene;
        $this->category = $category;
    }

    /**
     * Transforms a collection of objects (go) to a string (list of go).
     *
     * @param  PersistentCollection|null $gos
     * @return string
     */
    public function transform($gos)
    {
        $list = '';

        if (!$gos || $gos->isEmpty()) {
            return $list;
        }

        foreach ($gos as $key => $go) {
            $list .= $go->getGoId().' : '.$go->getDescription().$this->separator;
        }

        return $list;
    }

    /**
     * Transforms a string (semi-colon seperated list of gos) to a collection of objects (go).
     *
     * @param  string $list
     * @return PersistentCollection|null
     */
    public function reverseTransform($list)
    {
        if (!$this->gene || $list === '') {
            return array();
        }

        $getter = $this->goFunctions[$this->category];
        $collection =  $this->gene->$getter();

        // clean input string
        $gos = array_filter(array_map('trim', explode($this->separator, $list)));

        $goIds = array_map(array($this, "filterId"), $gos);

        // remove from collection all go that were deleted in form
        foreach ($collection as $go) {
            if (!in_array($go->getGoId(), $goIds)) {
                $collection->removeElement($go);
            } else {
                if(($key = array_search($go->getGoId(), $goIds)) !== false) {
                    unset($goIds[$key]);
                }
            }
        }

        // attach existing go entities that were added in form
        $goEntities = $this->em->getRepository('AppBundle:Go')->findByGoId($goIds);
        foreach ($goEntities as $existingGo) {
            $collection->add($existingGo);
            if(($key = array_search($existingGo->getGoId(), $goIds)) !== false) {
                unset($goIds[$key]);
            }
        }

        // create new goIds that were added in form and didn't exist in our database
        foreach ($goIds as $id) {
            $newGo = new Go();
            $newGo->setGoId($id);
            $newGo->setCategory($this->category);
            $newGo->setDescription($this->descriptions[$id]);
            $this->em->persist($newGo);
            $collection->add($newGo);
        }

        return $collection;
    }

    private function filterId($goString)
    {
        $pattern = '/^(GO:[0-9]+)(?= :) : (.*)/';

        $matches = array();
        $result = preg_match($pattern, $goString, $matches);

        if (!$result) {
            throw new TransformationFailedException(sprintf(
                'Invalid Go Id format.'
            ));
        }

        $this->descriptions[$matches[1]] = $matches[2];

        return $matches[1];
    }
}