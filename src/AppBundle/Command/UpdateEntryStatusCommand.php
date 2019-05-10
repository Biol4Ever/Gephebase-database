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

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use AppBundle\Entity\EntryStatus;
use AppBundle\Entity\EntryStatusGroup;

/**
 * This command updates all of the data fetched from UniprotKb
 */
class UpdateEntryStatusCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gephebase:database:update-entry-status')
            ->setDescription('Version 3 upgrade only: Update the entry status names and assign them to the new status groups')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting status update...</info>');

        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        // list of persisted status in database
        $persistedList = $em->getRepository('AppBundle:EntryStatus')->findAll();
        $persistedListArray = array();
        foreach ($persistedList as $status) {
            $persistedListArray[$status->getId()] = $status;
        }

        // list of new status groups
        $statusGroupList = array(
            array("id" => EntryStatusGroup::DRAFT, "name" => "Draft"),
            array("id" => EntryStatusGroup::PUBLISHED, "name" => "Published"),
            array("id" => EntryStatusGroup::REVIEWED, "name" => "Reviewed"),
        );
        $persistedGroups = $em->getRepository('AppBundle:EntryStatusGroup')->findAll();
        $persistedGroupsArray = array();
        foreach ($persistedGroups as $group) {
            $persistedGroupsArray[$group->getId()] = $group;
        }

        // list of updated status
        $statusList = array(
            EntryStatus::TEMPORARY => array("name" => "Draft-Temporary", "group" => EntryStatusGroup::DRAFT),
            EntryStatus::IMPORTED => array("name" => "Draft-Imported", "group" => EntryStatusGroup::DRAFT),
            EntryStatus::DELETED => array("name" => "Draft-Deleted", "group" => EntryStatusGroup::DRAFT),
            EntryStatus::ACCEPTED_CURATOR => array("name" => "Published - Accepted by Curator", "group" => EntryStatusGroup::PUBLISHED),
            EntryStatus::ACCEPTED_VALIDATOR => array("name" => "Reviewed", "group" => EntryStatusGroup::REVIEWED),
            EntryStatus::REFUSED_VALIDATOR => array("name" => "Published - Reviewer comment", "group" => EntryStatusGroup::PUBLISHED),
        );

        // Create all status groups
        foreach ($statusGroupList as $group) {

            if (!array_key_exists($group["id"], $persistedGroupsArray)) {        
                $entity = new EntryStatusGroup();
                $entity->setName($group["name"]);
                $entity->setId($group["id"]);
                $em->persist($entity);

                // temporarily change strategy to allow setId
                $metadata = $em->getClassMetaData(get_class($entity));
                $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
            }
        }

        $em->flush();

        $persistedGroups = $em->getRepository('AppBundle:EntryStatusGroup')->findAll();
        $persistedGroupsArray = array();
        foreach ($persistedGroups as $group) {
            $persistedGroupsArray[$group->getId()] = $group;
        }

        // iterate over each new status, update name, then assign it to a group
        foreach ($statusList as $id => $status) {
            if (array_key_exists($id,$persistedListArray)) {
                $entity = $persistedListArray[$id];
            } else {
                $entity = new EntryStatus();
                $entity->setId($id);
            }

            // set the new name
            $entity->setName($status["name"]);
            // set the new group
            $entity->setStatusGroup($persistedGroupsArray[$status["group"]]);
            $em->persist($entity);

        }

        $metadata = $em->getClassMetaData(get_class($entity));
        $metadata->setIdGeneratorType(\Doctrine\ORM\Mapping\ClassMetadata::GENERATOR_TYPE_NONE);
        $em->flush();

        $output->writeln('<info>Update complete.</info>');
    }
}
