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
class FixTaxonIdDuplicatesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gephebase:database:fix-taxon-duplicates')
            ->setDescription('Changes all references to duplicate taxons to a single one, then deletes the duplicates.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting fix...</info>');
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        // group all taxon duplicates
        $duplicates = $em->getRepository('AppBundle:Taxon')->findDuplicateTaxons();

        foreach ($duplicates as $taxId => $taxons) {
            if (sizeof($taxons) < 2) {
                // If there are no duplicates, do nothing
                continue;
            }

            if ($taxId == "") {
                continue;
            }

            $output->writeln('<info>fixing taxon ID '.$taxId.'</info>');

            // set the correct taxon as the first that was entered
            $correctTaxon = $taxons[0];

            // remove the correct taxon from the list of duplicates
            array_shift($taxons);

            // find all complex taxons that reference a duplicate
            $incorrectComplexTaxons = $em->getRepository('AppBundle:ComplexTaxon')->findByTaxon($taxons);

            // attach the correct taxon
            foreach ($incorrectComplexTaxons as $ict) {
                $ict->setTaxon($correctTaxon);
            }

            // sets the correctTaxon as the parent of all duplicate taxon children.
            $children = $em->getRepository('AppBundle:Taxon')->findByParentId($taxons);
            foreach ($children as $child) {
                $child->setParentId($correctTaxon);
            }

            $em->flush();

            // delete all duplicates
            foreach ($taxons as $taxon) {
                $em->remove($taxon);
            }

            $em->flush();
        }

        $output->writeln('<info>fix complete.</info>');
    }
}
