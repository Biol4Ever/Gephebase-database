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
use AppBundle\Entity\ComplexTaxon;

/**
 * This command creates a complex taxon for every simple taxon existing in database.
 * To be run ONLY ONCE when migrating to Gephebase V3
 */
class InitialiseComplexTaxonCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gephebase:initialise-complex-taxons')
            ->setDescription('Creates a complex taxon for every simple taxon attached to a gephe')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting complex taxon initialisation...</info>');

        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $entries = $em->getRepository('AppBundle:Entry')->findAll();

        foreach ($entries as $entry) {
            $taxonA = new ComplexTaxon();
            $taxonA->setIsInfraspecies($entry->getIsTaxonAInfraspecies());
            $taxonA->setDescription($entry->getTaxonADescription());
            $taxonA->setTaxon($entry->getTaxonA());

            $taxonB = new ComplexTaxon();
            $taxonB->setIsInfraspecies($entry->getIsTaxonBInfraspecies());
            $taxonB->setDescription($entry->getTaxonBDescription());
            $taxonB->setTaxon($entry->getTaxonB());

            $entry->addTaxonAList($taxonA);
            $entry->addTaxonBList($taxonB);
            $em->persist($taxonA);
            $em->persist($taxonB);
        }

        $em->flush();

        $output->writeln('<info>Successfully initialised '. count($entries)*2 .' complex taxons.</info>');
    }
}
