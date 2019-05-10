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
 * This command truncates all data from Entry related tables
 */
class EmptyGephebaseDataCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('gephebase:database:empty-database')
            ->setDescription('Truncates all data from Entry related tables')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Starting database truncate...</info>');

        $em = $this->getContainer()->get('doctrine')->getManager();
        
        $sql = "
            set foreign_key_checks = 0;
            Truncate entry;
            Truncate gene;
            Truncate complex_trait;
            Truncate phenotype_trait;
            Truncate complex_taxon;
            Truncate taxon;
            Truncate taxon_synonym;
            Truncate gene_biological_go;
            Truncate gene_cellular_go;
            Truncate gene_molecular_go;
            Truncate go;
            Truncate gene_synonym;
            Truncate synonym;
            Truncate mutation;
            Truncate mutation_reference;
            Truncate rejected_reference;
            Truncate validator_entry;
            set foreign_key_checks = 1;
        ";

        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();

        $output->writeln('<info>Database truncate complete.</info>');
    }
}
