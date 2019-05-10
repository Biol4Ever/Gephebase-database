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

namespace AppBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Query\ResultSetMapping;
use AppBundle\Entity\Taxon;

class TaxonListener
{
    private $taxons = array();
    private $requireFlush = false;

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // only act on some "Taxon" entity
        if (!$entity instanceof Taxon) {
            return;
        }

        $entity = $args->GetEntity();
        $this->taxons[] = $entity;
        $this->requireFlush = true;
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        if(!empty($this->taxons) && $this->requireFlush)
        {
            $em = $args->getEntityManager();

            foreach ($this->taxons as $taxon) 
            {

                $taxonName = $this->buildTaxonName($taxon);
                $taxon->setName($taxonName);
                $em->persist($taxon);
            }
            $this->taxons = array();
            $this->requiredFlush = false;
            $em->flush();
        }
    }

    private function buildTaxonName($taxon)
    {
        $taxonName = '';

        if ($taxon->getLatinName()) {
            $taxonName .= $taxon->getLatinName();
        }

        $taxonName .= " (";

        if ($taxon->getCommonName()) {
            $taxonName .= $taxon->getCommonName();
        }

        $taxonName .= ") - (Rank: ";

        if ($taxon->getRank()) {
            $taxonName .= $taxon->getRank();
        }

        $taxonName .= ")";

        return $taxonName;
    }
}