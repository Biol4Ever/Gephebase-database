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
use AppBundle\Entity\Mutation;
use AppBundle\Entity\MutationReference;

/**
 * This command creates a mutation for every entry already existing in database.
 * To be run ONLY ONCE when migrating to Gephebase V3
 */
class InitialiseMutationsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gephebase:initialise-mutations')
            ->setDescription('Creates a mutation for every entry in the database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting mutation initialisation...</info>');

        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $entries = $em->getRepository('AppBundle:Entry')->findAll();

        foreach ($entries as $entry) {
            $mutation = new Mutation();
            $mutation->setPresumptiveNull($entry->getPresumptiveNull());
            $mutation->setMolecularType($entry->getMolecularType());
            $mutation->setAberrationType($entry->getAberrationType());
            $mutation->setSnp($entry->getSnp());
            $mutation->setAberrationSize($entry->getAberrationSize());
            $mutation->setCodonTaxonA($entry->getCodonTaxonA());
            $mutation->setCodonTaxonB($entry->getCodonTaxonB());
            $mutation->setAaPosition($entry->getAaPosition());
            $mutation->setCodonPosition($entry->getCodonPosition());
            $mutation->setAminoAcidTaxonA($entry->getAminoAcidTaxonA());
            $mutation->setAminoAcidTaxonB($entry->getAminoAcidTaxonB());
            $mutation->setMolecularDetails($entry->getMolecularDetails());
            $mutation->setExperimentalEvidence($entry->getExperimentalEvidence());
            $mutation->setMainReference($entry->getMainReference());

            foreach($entry->getOtherReferences() as $reference) {
                $mutationReference = new MutationReference();
                $mutationReference->setMutation($mutation);
                $mutationReference->setReference($reference);
                $em->persist($mutationReference);
            }

            $entry->addMutation($mutation);
            $em->persist($mutation);
        }

        $em->flush();

        $output->writeln('<info>Successfully initialised '. count($entries) .' mutations.</info>');
    }
}
