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

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\EntryStatus;

class EntityStatusFixture extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $statusGroupList = array(
            array("id" => EntryStatusGroup::DRAFT, "name" => "Draft"),
            array("id" => EntryStatusGroup::PUBLISHED, "name" => "Published"),
            array("id" => EntryStatusGroup::REVIEWED, "name" => "Reviewed"),
        );

        foreach ($statusGroupList as $statusGroup) {
            $entity = new EntryStatusGroup();
            $entity->setId($statusGroup["id"]);
            $entity->setName($statusGroup["name"]);
            $manager->persist($entity);
        }

        // temporarily change strategy to allow setId
        $metadata = $manager->getClassMetaData(get_class($entity));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);

        $statusList = array(
            array("id" => EntryStatus::TEMPORARY, "name" => "Temporary", "group" => EntryStatusGroup::DRAFT),
            array("id" => EntryStatus::IMPORTED, "name" => "Imported", "group" => EntryStatusGroup::DRAFT),
            array("id" => EntryStatus::ACCEPTED_CURATOR, "name" => "Accepted by curator", "group" => EntryStatusGroup::DRAFT),
            array("id" => EntryStatus::ACCEPTED_VALIDATOR, "name" => "Accepted by validator", "group" => EntryStatusGroup::DRAFT),
            array("id" => EntryStatus::REFUSED_VALIDATOR, "name" => "Refused by validator", "group" => EntryStatusGroup::DRAFT),
        );

        foreach ($statusList as $status) {
            $entity = new EntryStatus();
            $entity->setId($status["id"]);
            $entity->setName($status["name"]);
            $manager->persist($entity);
        }
        // temporarily change strategy to allow setId
        $metadata = $manager->getClassMetaData(get_class($entity));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }
}