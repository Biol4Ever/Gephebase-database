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

/**
 * This command updates all of the data fetched from UniprotKb
 */
class UpdateFetchedDataCommand extends ContainerAwareCommand
{
    private $allowedTypes = array('reference','gene');

    protected function configure()
    {
        $this
            ->setName('gephebase:database:update-fetched-data')
            ->setDescription('Updates the data fetched from UniprotKb.')
            ->addOption(
                'type',
                't',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Limit the type of data that should be retrieved, allowed types are ['.implode(',', $this->allowedTypes).']',
                array()
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $retriever = $container->get('entry.data.retriever');

        $types = $this->allowedTypes;
        $inputTypes = $input->getOption('type');
        if (!empty($inputTypes)) {
            $types = $inputTypes;
        }

        foreach ($types as $type) {
            // if the type is not in our list of types to update, show error message and ignore it
            if (!in_array($type, $this->allowedTypes)) {
                $output->writeln('<error>Invalid type value "'.$type.'". Only values ['.implode(',',$this->allowedTypes).'] may be passed to the type option</error>');

                continue;
            }

            $function = 'update'.ucfirst($type).'Data';
            $this->$function($em, $retriever, $output);
        }

        $em->flush();
        $output->writeln('<info>Updated data persisted to database...</info>');
        $output->writeln('<info>Update complete.</info>');
    }

    /**
     * Updates all gene data in our database by querying uniprot with a list of uniprotIds
     */
    private function updateGeneData($em, $retriever, $output)
    {
        $genes = $em->getRepository('AppBundle:Gene')->findAllNonNull();
        $output->writeln('<info>Starting Uniprot data update...</info>');
        $count = 1;
        foreach($genes as $gene) {
            $id = $gene->getUniProtKbId();

            // remove all synonyms
            foreach($gene->getSynonyms() as $synonym) {
                $gene->removeSynonym($synonym);
            }

            // remove all Molecular Go
            foreach($gene->getGoMolecular() as $go) {
                $gene->removeGoMolecular($go);
            }

            // remove all Biological Go
            foreach($gene->getGoBiological() as $go) {
                $gene->removeGoBiological($go);
            }

            // remove all Cellular Go
            foreach($gene->getGoCellular() as $go) {
                $gene->removeGoCellular($go);
            }

            $gene = $retriever->updateGeneData($gene);

            if ($gene) {
                $em->persist($gene);
                $output->writeln('Successfully updated gene data for UniprotId: '.$id);

            } else {
                $output->writeln('<error>Failed to update gene data for UniprotId: '.$id.'</error>');
            }

            // flush every 50 entries
            if ($count%50 == 0) {
                $em->flush();
                $output->writeln('<info>Updated Uniprot data persisted to database...</info>');
            }
            $count++;
        }
    }

    /**
     * Updates all reference data in our database by querying ncbi with a list of pmids
     */
    private function updateReferenceData($em, $retriever, $output)
    {
        $references = $em->getRepository('AppBundle:Reference')->findAll();
        $output->writeln('<info>Starting Reference data update...</info>');
        $count = 1;
        foreach($references as $reference) {
            $pmid = $reference->getPmid();
            try {
                $reference = $retriever->retrieveReferenceEntityFromId($pmid);
            } catch (\Exception $e) {
                $reference = null;
            }

            if ($reference) {
                $em->persist($reference);
                $output->writeln('Successfully updated reference data for PMID: '.$pmid);
            } else {
                $output->writeln('<error>Failed to update reference data for PMID: '.$pmid.'</error>');
            }

            // flush every 50 entries
            if ($count%50 == 0) {
                $em->flush();
                $output->writeln('<info>Updated Reference data persisted to database...</info>');
            }
            $count++;
        }
    }
}
