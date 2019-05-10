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


class AcceptAllCommand extends ContainerAwareCommand
{
    private $printErrors = '';

    protected function configure()
    {
        $this
            ->setName('accept:all')
            ->setDescription('Validate all entries')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $entries = $em->getRepository('AppBundle:Entry')->findAllImportedEntries();
        $countValidate = 0;
        $countUnvalidated = 0;
        $count = 0;

        foreach($entries as $entry) {

            $dataRetriever = $this->getContainer()->get('entry.data.retriever');
            $entry = $dataRetriever->retrieveData($entry->getId());
            $validator = $this->getContainer()->get('entry.validator');
            $v = $validator->validate($entry);
            if (!$v) {
                $countUnvalidated += 1;
                $errors = $validator->getErrorMessages();
                $string = null;
                $output->writeln('Entry unvalidated '.$entry->getId()." : ".$string);
                $this->allErrors = '';

                foreach($errors as $entity => $fields) {
                    foreach($fields as $key => $field) {

                        if (is_array($field)) {
                            foreach ($field as $subField) {

                                if (is_array($subField)) {
                                    foreach ($subField as $subSubField) {
                                        $this->printError($entity, $key, $subSubField, $output);
                                    }
                                } else {
                                    $this->printError($entity, $key, $subField, $output);
                                }
                            }
                        } else {
                            $this->printError($entity, $key, $field, $output);
                        }
                    }
                }
                $this->getContainer()->get('log.creator')->createLogCommand($entry, "Imported Entry validate", $this->printErrors);
            } else {
                $acceptedCurator = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::ACCEPTED_CURATOR);
                $entry->setStatus($acceptedCurator);
                $em->persist($entry);
                $em->flush();
                $countValidate += 1;
                $this->getContainer()->get('log.creator')->createLogCommand($entry, "Imported Entry validate", $acceptedCurator->getName());
                $output->writeln('Entry validate '.$entry->getId());
            }
            $count = $countValidate + $countUnvalidated;
        }
    }

    /**
     * Prints an error to output and adds it to list of errors to be logged
     */
    private function printError($entity, $key, $field, $output)
    {
        $printError = $entity." - ".$key." : ".$field;
        $output->writeln('    '.$printError);
        $this->printErrors .= $printError ."\n";
    }
}
