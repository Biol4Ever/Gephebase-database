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

namespace AppBundle\Model\Services;

use AppBundle\Entity\Entry;
use AppBundle\Entity\EntryStatus;
use AppBundle\Entity\Gene;
use AppBundle\Entity\PhenotypeTrait;
use AppBundle\Entity\RejectedReference;
use AppBundle\Entity\Taxon;
use AppBundle\Entity\ComplexTrait;
use AppBundle\Entity\ComplexTaxon;
use AppBundle\Entity\Mutation;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ImportManager
{
    private $em;
    private $tokenStorage;
    private $validator;
    private $dataRetriever;
    private $userManager;
    private $logger;
    private $logPath;


    public function __construct($em, TokenStorageInterface $tokenStorage, $validator, $dataRetriever, $userManager, $logger, $logPath)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
        $this->validator = $validator;
        $this->validator->setCsvValidator(true);
        $this->dataRetriever = $dataRetriever;
        $this->userManager = $userManager;
        $this->logger = $logger;
        $this->logPath = $logPath;
    }

    public function import($file)
    {
        // start by deleting the previous logfile
        if (file_exists($this->logPath)) {
            unlink($this->logPath);
        }

        $this->logger->info('----------------------- Starting Import -----------------------');
        $traits = $this->findTraitEntities();

        $importedStatus = $this->em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::IMPORTED);
        $parameter = $this->em->getRepository('AppBundle:Parameter')->find(1);
        $number = $parameter->getImportedNumber() + 1;
        $parameter->setImportedNumber($number);
        $this->em->persist($parameter);
        $entries = $this->get2DArrayFromCsv($file->getData()->getRealPath(), ',');

        foreach ($entries as $entry) {

            // Check all data from CSV if they match with static lists
            $taxonomic = $this->dataListTransformer($entry['Taxonomic Status'], Entry::getTaxonomicList(), Entry::getTaxonomicShortList());
            $entity = new Entry();
            $entity->setGeneGephebase($entry['Gene-Gephebase']);
            $entity->setGenbankId($entry['GenBankID']);
            $entity->setAncestralState($entry['Ancestral State']);
            $entity->setTaxonomicStatus($taxonomic);

            $this->addComplexTraits($entity, $traits, array(
                'category' => $entry['Trait Category'],
                'description' => $entry['Trait'],
                'stateA' => $entry['State A'],
                'stateB' => $entry['State B'],
            ));

            $this->addComplexTaxon($entity, 'A', array(
                'taxId' => $entry['Taxon A ID'],
                'isInfraspecies' => $entry['A=Infraspecies'],
                'description' => $entry['Taxon A Description'],
            ));

            $this->addComplexTaxon($entity, 'B', array(
                'taxId' => $entry['Taxon B ID'],
                'isInfraspecies' => $entry['B=Infraspecies'],
                'description' => $entry['Taxon B Description'],
            ));

            $this->addMutations($entity, array(
                'experimentalEvidence' => $entry['Empirical Evidence'],
                'molecularDetails' => $entry['Molecular Details'],
                'molecularType' => $entry['Molecular Type'],
                'presumptiveNull' => $entry['Presumptive Null'],
                'snp' => $entry['SNP Coding Change'],
                'codonTaxonA' => $entry['Codon-Taxon-A'],
                'codonPosition' => $entry['Codon-Position'],
                'codonTaxonB' => $entry['Codon-TaxonB'],
                'aaTaxonA' => $entry['AminoAcid-Taxon A'],
                'aaPosition' => $entry['AA-Position'],
                'aaTaxonB' => $entry['AminoAcid-Taxon B'],
                'aberrationType' => $entry['Aberration Type'],
                'aberrationSize' => $entry['Aberration Size'],
                'mainReference' => $entry['Main PMID'],
                'otherReferences' => $entry['Additional PMID'],
            ));

            $entity->setComments($entry['Comments']);
            $entity->setCommentsValidator("");
            $user = $this->addUser($entry['Username']);
            $entity->setMainCurator($user);

            $entity->setTempUniprotId($entry['UniProtKB_ID']);
            $entity->setImportedGeneData(false);
            $entity->setImportedTaxonData(false);
            $entity->setImportedMainRefData(false);
            $entity->setImportedOtherRefData(false);
            $entity->setStatus($importedStatus);
            $entity->setImportedNumber($number);


            $this->em->persist($entity);
        }
        $this->em->flush();

        $this->logger->info('----------------------- Finished Import -----------------------');
    }

    /**
     * Match User or create new one
     *
     * Return an user
     */

    public function addUser($username) {

        $userManager = $this->userManager;
        $user = $this->em->getRepository('AppBundle:User')->findOneByUsername($username);
        if(!$user) {
            $user = $userManager->createUser();
            $user->setPlainPassword(date('Y'));
            $user->setUsername($username);
            $user->setEmail($username.'@gephebaseuser.org');
            $user->setName($username);
            $user->setSurname(null);
            $user->setEnabled(true);
            $user->setToken($this->getToken(50));
            $userManager->updateUser($user,false);
            $this->em->flush();
        }

        return $user;
    }

    function getToken($length)
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        $max = strlen($codeAlphabet) - 1;
        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[$this->crypto_rand_secure(0, $max)];
        }
        return $token;
    }

    function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) return $min; 
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; 
        $bits = (int) $log + 1; 
        $filter = (int) (1 << $bits) - 1; 
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter;
        } while ($rnd >= $range);
        return $min + $rnd;
    }

    /**
     * Import rejected references from csv file
     *
     * Returns the number of rejected references successfully imported into database.
     */
    public function importRejectedReferences($file)
    {
        $references = $this->get2DArrayFromCsv($file->getData()->getRealPath(), ',');

        //$rejectedReferenceEntities = $this->findEntitiesByFilter('RejectedReference','identifier')
        $referenceEntitiesPmid = $this->findEntitiesByFilter('Reference','pmId');
        $referenceEntitiesDoi = $this->findEntitiesByFilter('Reference','doi');
        $rejectedEntities = $this->findEntitiesByFilter('RejectedReference','referenceIdentifier');
        $curatorEntities = $this->findEntitiesByFilter('User','username');
        $count = 0;

        foreach($references as $reference) {
            $referenceEntity = null;
            $identifier = $reference['Reference'];
            $reason = $reference['Reason for rejection from the database'];
            $curator = $reference['Curator'];

            if(array_key_exists($identifier, $referenceEntitiesPmid)) {
                $referenceEntity = $referenceEntitiesPmid[$identifier];
             }

            if (!$referenceEntity && array_key_exists($identifier, $referenceEntitiesDoi)) {
                $referenceEntity = $referenceEntitiesDoi[$identifier];
            }

            if (!$referenceEntity || array_key_exists($identifier, $rejectedEntities) || !array_key_exists($curator, $curatorEntities)) {
                // reference not found or reference already rejected or curator not found
                continue;
            }

            $curatorEntity = $curatorEntities[$curator];

            $rejected = new RejectedReference();
            $rejected->setReason($reason);
            $rejected->setReference($referenceEntity);
            $rejected->setCurator($curatorEntity);
            $rejected->setReferenceIdentifier($identifier);

            $this->em->persist($rejected);
            $count++;
        }

        $this->em->flush();

        return $count;
    }

    private function get2DArrayFromCsv($file, $delimiter) {
        $headers = array();

        $contents = file_get_contents($file);
        $contents = preg_replace("/\\r(?!\\n)/", "\n" , $contents);
        file_put_contents($file,$contents);

        if (($handle = fopen($file, "r")) !== FALSE) {
            $i = 0;
            while (($lineArray = fgetcsv($handle, 100000, $delimiter)) !== FALSE) {
                for ($j=0; $j<count($lineArray); $j++) {
                    if ($i == 0) {
                        $headers[] = $lineArray[$j];
                    } else {
                        $data2DArray[$i][$headers[$j]] = $this->cleanField($lineArray[$j]);
                    }


                }
                $i++;
            }
            fclose($handle);
        }

        return $data2DArray;
    }

    /**
     * Cleans the input data
     */
    private function cleanField($data)
    {
        // TODO: handle bad encoding


        return $data;
    }

    private function findEntitiesByFilter($entity, $sortField = 'id')
    {
        $getter = 'get'.ucfirst($sortField);
        $entities = $this->em->getRepository('AppBundle:'.$entity)->findAll();
        $arrayEntities = array();
        foreach ($entities as $entity) {
            $arrayEntities[$entity->$getter()] = $entity;
        }

        return $arrayEntities;
    }

    private function findTraitEntities()
    {
        $entities = $this->em->getRepository('AppBundle:PhenotypeTrait')->findAll();
        $arrayEntities = array();
        foreach ($entities as $entity) {
            $identifier = $entity->getDescription();
            $arrayEntities[$identifier] = $entity;
        }

        return $arrayEntities;
    }

    private function dataListTransformer($value, $list, $shortList = null) {

        foreach($list as $item) {
            if(strtolower($item) == strtolower($value)) {
                return $item;
            }
        }
        if ($shortList !== null) {
            foreach($shortList as $key => $item) {
                if(strtolower($value) == strtolower($key)) {
                    return $item;
                }

            }
        }

        return null;
    }


    /**
     * Creates a collection of traits for every trait in the import row and assigns them to the entry entity
     */
    private function addComplexTraits($entry, $persistedTraitEntities, $fields)
    {
        $complexTraits = $this->splitRelationsBySeparator($fields);
        foreach ($complexTraits as $complexTrait) {
            $entity = new ComplexTrait();
            if(isset($complexTrait['stateA'])) {
                $stateA = $complexTrait['stateA'];
            } else {
                $stateA = null;
            }
            if(isset($complexTrait['stateB'])) {
                $stateB = $complexTrait['stateB'];
            } else {
                $stateB = null;
            }
            $entity->setStateInTaxonA($stateA);
            $entity->setStateInTaxonB($stateB);

            // check if trait exists in our databse, otherwise create a new one

            if (!array_key_exists('description', $complexTrait)) {
                $this->logger->error($entry->getGeneGephebase() . ': invalid complex trait - description');
                continue;
            }

            if (!array_key_exists('category', $complexTrait)) {
                $this->logger->error($entry->getGeneGephebase() . ': invalid complex trait - category');
                continue;
            }

            $traitDescription = $complexTrait['description'];
            $traitCategory = $this->dataListTransformer($complexTrait['category'], Entry::getTraitCategoryList(), Entry::getTraitCategoryShortList());
            if (!$traitCategory) {
                $traitCategory = $complexTrait['category'];
            }

            if (array_key_exists($traitDescription, $persistedTraitEntities)) {
                $traitEntity = $persistedTraitEntities[$traitDescription];
            } else {
                $traitEntity = new PhenotypeTrait();
                $traitEntity->setDescription($traitDescription);
                $traitEntity->setCategory($traitCategory);
                $this->em->persist($traitEntity);
                $persistedTraitEntities[$traitDescription] = $traitEntity;
            }
            $entity->setPhenotypeTrait($traitEntity);
            $this->em->persist($entity);
            $entry->addTrait($entity);

        }
    }

    /**
     * Creates a collection of taxons for every taxon in the import row and assigns them to the entry entity
     */
    private function addComplexTaxon($entry, $type, $fields)
    {
        $adder = 'addTaxon'.$type.'List';
        $complexTaxons = $this->splitRelationsBySeparator($fields);

        foreach ($complexTaxons as $complexTaxon) {
            if (!isset($complexTaxon['description'])) {
                $this->logger->error($entry->getGeneGephebase() . ': invalid complex trait - description');
                $description = null;
            } else {
                $description = $complexTaxon['description'];
            }

            $entity = new ComplexTaxon();
            $entity->setIsInfraspecies($complexTaxon['isInfraspecies']);
            $entity->setImportTaxonId($complexTaxon['taxId']);
            $entity->setDescription($description);
            $this->em->persist($entity);
            $entry->$adder($entity);
        }
    }

    /**
     * Creates a collection of mutations for every mutation in the import row and assigns them to the entry entity
     */
    private function addMutations($entry, $fields)
    {
        // array of values in the csv file that should map to null
        $setNullValues = array('NA');


        $mutations = $this->splitRelationsBySeparator($fields);

        foreach ($mutations as $mutation) {
            $experimental = $this->dataListTransformer($mutation['experimentalEvidence'], Entry::getExperimentalList(), Entry::getExperimentalShortList());
            $snp = $this->dataListTransformer($mutation['snp'], Entry::getSNPList(), Entry::getSNPShortList());
            $snp = $this->convertExceptionsToNull($snp);
            $aberrationSize = null;
            if (array_key_exists('aberrationSize', $mutation)) {
                $aberrationSize = $this->dataListTransformer($mutation['aberrationSize'], Entry::getAberrationSizeList(), Entry::getAberrationSizeShortList());
            }
            $aberrationType = $this->dataListTransformer($mutation['aberrationType'],Entry::getAberrationList(), Entry::getAberrationShortList());
            $molecularType = $this->dataListTransformer($mutation['molecularType'], Entry::getMolecularList(), Entry::getMolecularShortList());
            $presumptiveNull = $this->dataListTransformer($mutation['presumptiveNull'], Entry::getPresumptiveNullList());

            $entity = new Mutation();
            $entity->setExperimentalEvidence($experimental);

            if (array_key_exists('molecularDetails', $mutation)) {
                $entity->setMolecularDetails($mutation['molecularDetails']);
            }

            $entity->setMolecularType($molecularType);
            $entity->setPresumptiveNull($presumptiveNull);
            $entity->setSnp($snp);
            $entity->setCodonTaxonA($mutation['codonTaxonA']);
            $entity->setCodonPosition($mutation['codonPosition']);
            $entity->setCodonTaxonB($mutation['codonTaxonB']);
            $entity->setAminoAcidTaxonA($mutation['aaTaxonA']);
            $entity->setAaPosition($mutation['aaPosition']);
            $entity->setAminoAcidTaxonB($mutation['aaTaxonB']);
            $entity->setAberrationType($aberrationType);
            $entity->setAberrationSize($aberrationSize);
            $entity->setImportMainReference($mutation['mainReference']);
            if (array_key_exists('otherReferences', $mutation)) {
                $entity->setImportOtherReferences($mutation['otherReferences']);
            }
            $this->em->persist($entity);
            $entry->addMutation($entity);
        }
    }


    /**
     * Splits a collection of elements in a single csv field seperated by &N
     */
    private function splitRelationsBySeparator($fields)
    {
        $matches = array();
        // this array will store all collection elements split into sub arrays
        $fieldArray = array();
        foreach ($fields as $fieldName => $field) {
            // prefix the field with a separator which will be used to index the first element
            $field = '&1 '.$field;

            preg_match_all('/(\&(\d+))(.*?)(?=&\d+|$)/s', $field, $matches);

            if (isset($matches[2])) {
                foreach($matches[2] as $key => $match) {
                    if ($match !== "") {
                        $index = $match;
                    } else {
                        $index = 1;
                    }

                    $fieldArray[$index][$fieldName] = trim($matches[3][$key]);
                }
            } else {
                $fieldArray[0][$fieldName] = "";
            }
        }

        return $fieldArray;
    }

    /**
     * Convert values to null if they match a specific list
     */
    private function convertExceptionsToNull($value)
    {
        $convertToNull = array(
            'na',
        );

        if (in_array(strtolower($value), $convertToNull)) {
            return null;
        }

        return $value;
    }

}