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
use AppBundle\Entity\ComplexTrait;

/**
 * This command creates a complex trait for every simple trait existing in database.
 * To be run ONLY ONCE when migrating to Gephebase V3
 */
class InitialiseComplexTraitsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gephebase:initialise-complex-traits')
            ->setDescription('Creates a complex trait for every simple trait attached to a gephe')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting complex trait initialisation...</info>');

        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $entries = $em->getRepository('AppBundle:Entry')->findAll();

        foreach ($entries as $entry) {
            $trait = new ComplexTrait();
            $trait->setPhenotypeTrait($entry->getPhenotypeTrait());
            $trait->setStateInTaxonA($entry->getStateInTaxonA());
            $trait->setStateInTaxonB($entry->getStateInTaxonB());
            $entry->addTrait($trait);
            $em->persist($trait);
        }

        $em->flush();

        $output->writeln('<info>Successfully initialised '. count($entries) .' complex taxons.</info>');
    }
}
