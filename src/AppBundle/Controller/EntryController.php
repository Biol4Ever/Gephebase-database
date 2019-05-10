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


namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Reference;
use AppBundle\Entity\Author;
use AppBundle\Entity\Gene;
use AppBundle\Entity\Entry as Entry;

class EntryController extends Controller
{
   	/**
     * @Route("/curator/entry/load-uniprot-data", name="load_uniprot_data")
     */
    public function loadUniprotAction(Request $request)
    {
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $dataRetriever = $this->get('entry.data.retriever');

        $form = $this->createForm('entry');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $entry = $form->getData();
            $newGene = $entry->getGene();
            $newUniProtId = $newGene->getUniProtKbId();

            $gene = $em->getRepository('AppBundle:Gene')->findOneByUniProtKbId($newUniProtId);
            if ($gene) {
                $entry->setGene($gene);
            } else {
                // try to fetch gene from UniProt database
                $current_entry = $em->getRepository('AppBundle:Entry')->findBy(array('gepheId' => $entry->getGepheId()));
                if($current_entry == null) {
                    $entry_status = $em->getRepository('AppBundle:EntryStatus')->find(1);
                } else {
                    $entry_status = $current_entry[0]->getStatus();
                }
                $entry->setStatus($entry_status);
                $entry->setTempUniprotId($newUniProtId);

                
                $entry = $dataRetriever->retrieveGeneData($entry);
                if($entry == null) {
                    $response->setData(array('error' => true));
                    return $response;
                }
            }

            $form = $this->createForm('entry', $entry);
            $geneNameView = $this->render('curator/entry/geneName.html.twig', array('form' => $form->createView()));
            $geneSynonymsView = $this->render('curator/entry/geneSynonyms.html.twig', array('form' => $form->createView()));
            $goView = $this->render('curator/entry/geneGo.html.twig', array('form' => $form->createView()));
            $uniprotView = $this->render('curator/entry/geneUniprot.html.twig', array('form' => $form->createView()));

            $response->setData(array(
                'geneNameView' => $geneNameView->getContent(),
                'geneSynonymsView' => $geneSynonymsView->getContent(),
                'geneGoView' => $goView->getContent(),
                'geneUniprotView' => $uniprotView->getContent(),
            ));
        }

        return $response;
    }

    /**
     * @Route("/curator/entry/load-genebank-data", name="load_genebank_data")
     */
    public function loadGenebankData(Request $request)
    {
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $dataRetriever = $this->get('entry.data.retriever');

        $form = $this->createForm('entry');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $entry = $form->getData();
            $geneBankId = $entry->getGenbankId();

            $gene = $em->getRepository('AppBundle:Gene')->findOneByUniProtKbId($geneBankId);
            if (!$gene && $geneBankId) {
                $gene = $dataRetriever->retrieveGeneEntityFromId($geneBankId);
            }

            // if we found gene, check if it matches with taxon A or B, and update that field accordingly.
            if($gene) {
                $entry->setGenBankOrganism($gene->getOrganism());
                $taxonIdentifier = $gene->getTaxonomicIdentifier();
                $matchesA = $matchesB = false;
                foreach($entry->getTaxonAList() as $complexTaxon) {
                    $taxon = $complexTaxon->getTaxon();
                    if (!$taxon) {
                        continue;
                    }

                    if ($taxonIdentifier == $taxon->getTaxId()) {
                        $matchesA = true;
                        break;
                    }
                }

                foreach($entry->getTaxonBList() as $complexTaxon) {
                    $taxon = $complexTaxon->getTaxon();
                    if (!$taxon) {
                        continue;
                    }

                    if ($taxonIdentifier == $taxon->getTaxId()) {
                        $matchesB = true;
                        break;
                    }
                }

                if($matchesA && $matchesB) {
                    $entry->setGenbankTaxonAOrB(Gene::TAXON_ID_MATCHES_A_AND_B);
                } elseif ($matchesA) {
                    $entry->setGenbankTaxonAOrB(Gene::TAXON_ID_MATCHES_A);
                } elseif ($matchesB) {
                    $entry->setGenbankTaxonAOrB(Gene::TAXON_ID_MATCHES_B);
                } else {
                    $entry->setGenbankTaxonAOrB(Gene::TAXON_ID_MATCHES_NONE);
                }

            } else {
                $entry->setGenbankTaxonAOrB(Gene::TAXON_ID_MATCHES_NONE);
                $entry->setGenBankOrganism('');
            }

            // otherwise it's not a uniprotkbID and do nothing.

            $form = $this->createForm('entry', $entry);
            $view = $this->render('curator/entry/geneBank.html.twig', array('form' => $form->createView()));

            $response->setData(array(
                'view' => $view->getContent(),
            ));
        }

        return $response;
    }

    /**
     * @Route("/curator/entry/load-taxon-data/{taxonType}", name="load_taxon_data")
     */
    public function loadTaxonAction(Request $request, $taxonType)
    {
        $response = new JsonResponse();
        $dataRetriever = $this->get('entry.data.retriever');
        $em = $this->getDoctrine()->getManager();

        $getter = 'getTaxon' . $taxonType;
        $setter = 'setTaxon' . $taxonType;
        $tempSetter = 'setTempTaxon'.$taxonType.'Id';

        $form = $this->createForm('entry');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $entry = $form->getData();
            $data = $request->request->get('entry');
            $newTaxId = $data['taxon'.$taxonType]['taxId'];

            $taxon = $em->getRepository('AppBundle:Taxon')->findOneByTaxId($newTaxId);
            if ($taxon) {
                $entry->$setter($taxon);
                $entry->$tempSetter($newTaxId);
            } else {
                // try to fetch taxon from ncbi database
                $current_entry = $em->getRepository('AppBundle:Entry')->findBy(array('gepheId' => $entry->getGepheId()));
                if($current_entry == null) {
                    $entry_status = $em->getRepository('AppBundle:EntryStatus')->find(1);
                } else {
                    $entry_status = $current_entry[0]->getStatus();
                }
                $entry->setStatus($entry_status);
                $entry->$tempSetter($newTaxId);
                $entry = $dataRetriever->retrieveTaxonData($entry);
                if($entry == null || !$newTaxId) {
                    $response->setData(array('error' => true, 'taxonType' => $taxonType));
                    return $response;
                }
            }

            $form = $this->createForm('entry', $entry);
            $view = $this->render('curator/entry/taxonData.html.twig', array(
                'taxon' => $form->get('taxon'.$taxonType)->createView(),
                'taxonType' => $taxonType,
            ));

            $response->setData(array(
                'view' => $view->getContent(),
            ));
        }

        return $response;
    }

    /**
     * @Route("/curator/entry/load-complex-taxon-data/{taxonType}/{taxonId}", name="load_complex_taxon_data")
     */
    public function loadComplexTaxonAction(Request $request, $taxonType, $taxonId = null)
    {
        $response = new JsonResponse();
        $responseData = array();
        $dataRetriever = $this->get('entry.data.retriever');
        $em = $this->getDoctrine()->getManager();

        // look for taxon in our database
        $taxon = $em->getRepository('AppBundle:Taxon')->findOneByTaxId($taxonId);

        // if we couldn't find it, query ncbi to fetch taxon data
        if (!$taxon) {
            $taxon = $dataRetriever->retrieveTaxonEntityFromId($taxonId);
        }

        // ncbi could not find a taxon matching the taxon id argument, return an error
        if (!$taxon) {
            $responseData['error'] = true;
        } else {
            $responseData['taxId'] = $taxon->getTaxId();
            $responseData['commonName'] = $taxon->getCommonName();
            $responseData['latinName'] = $taxon->getLatinName();
        }

        $response->setData($responseData);

        return $response;
    }

    /**
     * @Route("/curator/entry/load-reviewer/{email}", name="load_reviewer")
     */
    public function loadReviewer(Request $request, $email = null)
    {
        $response = new JsonResponse();
        $responseData = array();

        $em = $this->getDoctrine()->getManager();

        // look for reference in our database
        $reviewer = $em->getRepository('AppBundle:User')->findByEmail($email);

        if (!$reviewer) {
            //  create a reviewer
            $manager = $this->get('validator.manager');
            $validator = $manager->createValidator($email);
        }

        $responseData['reviewer'] = $email;

        $response->setData($responseData);

        return $response;
    }

    /**
     * @Route("/curator/entry/load-reference/{referenceId}", name="load_reference")
     */
    public function loadReference(Request $request, $referenceId = null)
    {
        $response = new JsonResponse();
        $responseData = array();
        $dataRetriever = $this->get('entry.data.retriever');
        $em = $this->getDoctrine()->getManager();

        // look for reference in our database
        $reference = $em->getRepository('AppBundle:Reference')->findByIdentifier($referenceId);

        // if we couldn't find it, query pubmed to fetch reference data
        if (!$reference) {
            $reference = $dataRetriever->retrieveReferenceEntityFromId($referenceId);
        }

        // ncbi could not find a reference matching the pubmed id argument, return an error
        if (!$reference) {
            $responseData['error'] = true;
        } else {
            // check if it belonds to rejected references
            $rejectedReferences = $em->getRepository('AppBundle:RejectedReference')->findAllAssoc();

            // check if main reference belongs to rejected papers, and if it is, add an alert
            $rejected = false;
            if(array_key_exists($reference->getPmId(), $rejectedReferences)) {
                $rejected = true;
            }

            $responseData['rejected'] = $rejected;
            $responseData['referenceId'] = $reference->getPmId();
            $responseData['articleTitle'] = $reference->getArticleTitle();
            $responseData['authors'] = $reference->getFullAuthors();
        }

        $response->setData($responseData);

        return $response;
    }

    /**
     * @Route("/curator/entry/load-go-data", name="load_go_data")
     */
    public function loadGoDataAction(Request $request)
    {
        $response = new JsonResponse();
        $dataRetriever = $this->get('entry.data.retriever');
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('entry');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $goId = $form->get('goId')->getData();

            $goReference = $em->getRepository('AppBundle:Go')->findOneByGoId($goId);
            if (!$goReference) {
                // try to fetch reference from QuickGO database
                $goReference = $dataRetriever->retrieveGoData($goId);
                if($goReference == null) {
                    $response->setData(array('error' => true));
                    return $response;
                }
            }

            if ($goReference) {
                $goForm = $this->createForm('go_validation', $goReference);
                $view = $this->render('curator/entry/goValidation.html.twig', array('form' => $goForm->createView()));

                $response->setData(array(
                    'view' => $view->getContent(),
                ));
            }
        }

        return $response;
    }

    /**
     * @Route("/curator/import-ris", name="import_ris")
     */

    public function importRisAction(Request $request)
    {
        $response = new JsonResponse();

        $file = $request->files->get('importRis');
        if (null === $file) {
            $file = $request->files->get('importRisOther');
        }

        if ( null === $file) {
            $response->setData(array('error' => true));
            return $response;
        }

        $importRisManager = $this->get('import.ris');
        $references = $importRisManager->import($file, false);
        $em = $this->getDoctrine()->getManager();
        $lastPmId = $em->getRepository('AppBundle:Reference')->findLastPmIdRis();
        foreach($references as $reference) {
            $pubType = ($reference['TY']) ? $reference['TY'] : null;
            if(isset($reference['AU']) === true) { 
                $count = 0;
                foreach($reference['AU'] as $auths) {
                    $authors[$count]['lastname'] = substr($auths, 0, strpos($auths, ','));
                    $after = strpos($auths, ',') + 1;
                    $authors[$count]['initials'] = substr($auths, $after);
                    $count++;
                } 
            }
            $journal = (isset($reference['JO'])) ? $reference['JO'] : null;
            $volume = (isset($reference['VL'])) ? $reference['VL'] : null;
            $year = (isset($reference['Y1'])) ? $reference['Y1'] : null;
            $abstract = (isset($reference['AB'])) ? $reference['AB'] : null;
            $do = (isset($reference['DO'])) ? $reference['DO'] : null;
            $articleTitle = (isset($reference['T1'])) ? $reference['T1'] : null;
            $sp = (isset($reference['SP'])) ? $reference['SP'] : null;
            $ep = (isset($reference['EP'])) ? $reference['EP'] : null;
            $pagination = $sp.' - '.$ep;
            $url = (isset($reference['UR'])) ? $reference['UR'] : null;
            $reference = new Reference();
            if(empty($lastPmId) === true) {
                $endPmId = "000001";
            } else {
                $endPmId = substr($lastPmId[0]['pmId'], -6);
                $endPmId = $endPmId + 1;
                $endPmId = str_pad($endPmId, 6, "0", STR_PAD_LEFT);
            }
            $reference->setPmId('00000000.'.$endPmId);
            $reference->setArticleTitle($articleTitle);
            $reference->setJournalTitle($journal);
            $reference->setJournalVolume($volume);
            $reference->setPublicationType($pubType);
            $reference->setAbstract($abstract);
            $reference->setDoi($do);
            $reference->setJournalYear($year);
            $reference->setJournalPagination($pagination);
            $reference->setUrl($url);
            foreach($authors as $auth) {
                $author = new Author();
                $author->setLastname(trim($auth['lastname']));
                $author->setInitials(trim($auth['initials']));
                $identifier = $auth['lastname'].$auth['initials'];
                $identifier = str_replace(' ', '', $identifier);
                $author->setIdentifier($identifier);
                $em->persist($author);
                $reference->addAuthor($author);
            }
            $em->persist($reference);
        }
        $em->flush();

        // check if it belonds to rejected references
        $rejectedReferences = $em->getRepository('AppBundle:RejectedReference')->findAllAssoc();

        // check if main reference belongs to rejected papers, and if it is, add an alert
        $rejected = false;
        if(array_key_exists($reference->getPmId(), $rejectedReferences)) {
            $rejected = true;
        }

        $responseData['rejected'] = $rejected;
        $responseData['referenceId'] = $reference->getPmId();
        $responseData['articleTitle'] = $reference->getArticleTitle();
        $responseData['authors'] = $reference->getFullAuthors();
        $response->setData($responseData);

        return $response;
    }

    /**
     * @Route("/curator/import-ris-other", name="import_ris_other")
     */

    public function importRisOtherAction(Request $request) {
        $response = new JsonResponse();
        $file = $request->files->get('importRisOther');
        $pmIds = json_decode($request->get('pmIds'));
        $entryId = $request->get('entryId');
        $importRisManager = $this->get('import.ris');
        $references = $importRisManager->import($file, false);
        $em = $this->getDoctrine()->getManager();

        // can be empty for new Entry
        if (empty($entryId) === true) {
            $entry = new Entry();
        } else {
            $entry = $em->getRepository('AppBundle:Entry')->find($entryId);
        }

        $lastPmId = $em->getRepository('AppBundle:Reference')->findLastPmIdRis();

        foreach($pmIds as $pmId) {
            $reference = $em->getRepository('AppBundle:Reference')->findByIdentifier($pmId);
            if($reference) {
                if(!$entry->getOtherReferences()->contains($reference)) {
                    $entry->addOtherReference($reference);
                }
            } 
        }
        foreach($references as $reference) {
            $pubType = ($reference['TY']) ? $reference['TY'] : null;
            if(isset($reference['AU']) === true) {
                $count = 0;
                foreach($reference['AU'] as $auths) {
                    $authors[$count]['lastname'] = substr($auths, 0, strpos($auths, ','));
                    $after = strpos($auths, ',') + 1;
                    $authors[$count]['initials'] = substr($auths, $after);
                    $count++;
                } 
            }
            $journal = (isset($reference['JO'])) ? $reference['JO'] : null;
            $volume = (isset($reference['VL'])) ? $reference['VL'] : null;
            $year = (isset($reference['Y1'])) ? $reference['Y1'] : null;
            $abstract = (isset($reference['AB'])) ? $reference['AB'] : null;
            $do = (isset($reference['DO'])) ? $reference['DO'] : null;
            $articleTitle = (isset($reference['T1'])) ? $reference['T1'] : null;
            $sp = (isset($reference['SP'])) ? $reference['SP'] : null;
            $ep = (isset($reference['EP'])) ? $reference['EP'] : null;
            $pagination = $sp.' - '.$ep;
            $url = (isset($reference['UR'])) ? $reference['UR'] : null;
            $reference = new Reference();
            if($lastPmId[0]['pmId']) {
                $endPmId = substr($lastPmId[0]['pmId'], -6);
                $endPmId = $endPmId + 1;
                $lastPmId[0]['pmId'] = $endPmId;
                $endPmId = str_pad($endPmId, 6, "0", STR_PAD_LEFT);
            } else {
                $endPmId = "000001";
            }
            $reference->setPmId('00000000.'.$endPmId);
            $reference->setArticleTitle($articleTitle);
            $reference->setJournalTitle($journal);
            $reference->setJournalVolume($volume);
            $reference->setPublicationType($pubType);
            $reference->setAbstract($abstract);
            $reference->setDoi($do);
            $reference->setJournalYear($year);
            $reference->setJournalPagination($pagination);
            $reference->setUrl($url);
            foreach($authors as $auth) {
                $author = new Author();
                $author->setLastname(trim($auth['lastname']));
                $author->setInitials(trim($auth['initials']));
                $identifier = $auth['lastname'].$auth['initials'];
                $identifier = str_replace(' ', '', $identifier);
                $author->setIdentifier($identifier);
                $em->persist($author);
                $reference->addAuthor($author);
            }
            $em->persist($reference);
            $refs[] = $reference;
        }
        $em->flush();
        foreach ($refs as $ref) {
            $entry->addOtherReference($ref);
        }
        $form = $this->createForm('entry', $entry);
        $view = $this->render('curator/entry/otherReferences.html.twig', array('form' => $form->createView()));

        $response->setData(array(
            'view' => $view->getContent(),
        ));

        return $response;
    }
}