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
use AppBundle\Entity\Entry;

class NewEntryListener
{
    private $entries = array();
    private $requireFlush = false;

    const GEPHE_PREFIX = 'GP';

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // only act on some "Entry" entity
        if (!$entity instanceof Entry) {
            return;
        }

        $entity = $args->GetEntity();
        $this->entries[] = $entity;
        $this->requireFlush = true;
    }

    public function postFlush(PostFlushEventArgs $args)
    {
        if(!empty($this->entries) && $this->requireFlush)
        {
            $em = $args->getEntityManager();

            foreach ($this->entries as $entry) 
            {
                $gepheId = self::generateGepheId($entry->getId());
                $entry->setGepheId($gepheId);
                $em->persist($entry);
            }
            $this->entries = array();
            $this->requiredFlush = false;
            $em->flush();
        }
    }

    public static function generateGepheId($id)
    {
        $gepheId = self::GEPHE_PREFIX . str_pad($id, 8, '0', STR_PAD_LEFT);

        return $gepheId;
    }
}