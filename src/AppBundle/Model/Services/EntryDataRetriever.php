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

use AppBundle\Entity\MutationReference;
use AppBundle\Model\Services;

class EntryDataRetriever
{
	const UNIPROT_QUERY = 'https://www.uniprot.org/uniprot/?format=xml&query=accession%3A';
	const NCBI_TAXON_QUERY = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=taxonomy&format=xml&id=';
	const NCBI_PUBMED_QUERY = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&format=xml&id=';
	const QUICKGO_QUERY = 'http://www.ebi.ac.uk/QuickGO/GTerm?id=';
	const NCBI_ID_CONVERTER_QUERY = 'https://www.ncbi.nlm.nih.gov/pmc/utils/idconv/v1.0/';

	private $httpClient;
	private $em;
	private $parser;
	private $logger;
	private $appName;
	private $appContact;

    /**
     * EntryDataRetriever constructor.
     * @param $httpClient
     * @param $em
     * @param EntryDataParser $parser
     * @param $logger
     * @param $appName
     * @param $appContact
     */
	public function __construct($httpClient, $em, EntryDataParser $parser, $logger, $appName, $appContact)
	{
		$this->httpClient = $httpClient;
		$this->em = $em;
		$this->parser = $parser;
		$this->logger = $logger;
		$this->appName = $appName;
		$this->appContact = $appContact;
	}

	public function retrieveData($id)
	{
        $entry = $this->em->getRepository('AppBundle:Entry')->findOneBy(array('id' => $id));
        $entry_persist = $entry;
		if (!$entry_persist->getImportedGeneData()) {
			$entry = $this->retrieveGeneData($entry_persist);
			if($entry != null) {
				$entry_persist = $entry;
				$entry_persist->setImportedGeneData(true);
			}
		}

		foreach ($entry_persist->getTaxonAList() as $complexTaxon) {
			if ($complexTaxon->getImportTaxonId()) {

				$taxon = $this->retrieveTaxonEntityFromId($complexTaxon->getImportTaxonId());
				if ($taxon) {
					$complexTaxon->setTaxon($taxon);
					$complexTaxon->setImportTaxonId(null);
				}
			}
		}

		foreach ($entry_persist->getTaxonBList() as $complexTaxon) {
			if ($complexTaxon->getImportTaxonId()) {

				$taxon = $this->retrieveTaxonEntityFromId($complexTaxon->getImportTaxonId());

				if ($taxon) {
					$complexTaxon->setTaxon($taxon);
					$complexTaxon->setImportTaxonId(null);
				}
			}
		}

		foreach ($entry_persist->getMutations() as $mutation) {
			if ($mutation->getImportMainReference()) {
				$reference = $this->retrieveReferenceEntityFromId($mutation->getImportMainReference());

				if ($reference) {
					$mutation->setMainReference($reference);
					$mutation->setImportMainReference(null);
				}
			}

			if ($mutation->getImportOtherReferences()) {
				$references = explode(';', $mutation->getImportOtherReferences());

				foreach ($references as $referenceId) {
					$reference = $this->retrieveReferenceEntityFromId($referenceId);

					if ($reference) {
						$mutationReference = new MutationReference();
						$mutationReference->setReference($reference);
						$mutationReference->setMutation($mutation);
						$this->em->persist($mutationReference);
						$mutation->setImportOtherReferences(null);
					}
				}
			}
		}

		return $entry_persist;
	}

	public function retrieveGeneData($entry)
	{
		// exit if uniprot id is empty
		$uniprotId = $entry->getTempUniprotId();
		if (!$uniprotId || $uniprotId === '') {
			return $entry;
		}

		// build query to retrieve gene data from uniprot database
		$query = self::UNIPROT_QUERY . $uniprotId;
		$uniProtXml = $this->httpClient->get($query);
		
		// parse xml data sent by uni prot and inject it into entity
		if($uniProtXml != "") { 
			$e = $this->parser->parseGeneXml($entry, $uniProtXml);
			if($e == null) {
				return $e;
			}
			$entry->setTempUniprotId(null);
			return $entry;
		} else {
        	$this->logger->createGenericErrorLog("Gene data cannot be imported for UniProtKB ID: " . $uniprotId);
			return null;
		}
	}

 	/**
 	 * Update gene data by fetching from uniprotKb
 	 * If the update fails, an error will be logged containing the gene's id.
 	 */
	public function updateGeneData($gene)
	{
		$uniprotId = $gene->getUniProtKbId();

		// build query to retrieve gene data from uniprot database
		$query = self::UNIPROT_QUERY . $uniprotId;
		$uniProtXml = $this->httpClient->get($query);
		
		// parse xml data sent by uni prot and inject it into entity
		if($uniProtXml != "") { 
			$gene = $this->parser->hydrateGeneFromXml($gene, $uniProtXml);
		} else {
        	$this->logger->createGenericErrorLog("Gene data could not be updated for UniprotId: ".$uniprotId);
			return null;
		}

		return $gene;
	}

	/**
	 * Fetch gene data based on uniprotKB ID.
 	 * Similar to retrieveGeneData() function but does not link gene to an entry entity.
 	 */
	public function retrieveGeneEntityFromId($id)
	{
		// build query to retrieve gene data from uniprot database
		$query = self::UNIPROT_QUERY . $id;
		$uniProtXml = $this->httpClient->get($query);
		
		// parse xml data sent by uni prot and inject it into entity
		if($uniProtXml != "") { 
			$gene = $this->parser->hydrateGeneFromXml($gene, $uniProtXml);
		} else {
			return null;
		}

		return $gene;
	}

	/**
	 * Fetch taxon data based on Taxonomy ID.
 	 */
	public function retrieveTaxonEntityFromId($id)
	{
		// build query to retrieve taxon data from NCBI
		$query = self::NCBI_TAXON_QUERY . $id;
		$taxonXml = $this->httpClient->get($query);
		
		// parse xml data sent by NCBI and inject it into entity
		if($taxonXml != "") {
			$taxonsNode = new \SimpleXMLElement($taxonXml);
			if(!$taxonsNode->Taxon) {
				return null;
			}

			foreach ($taxonsNode->Taxon as $taxonXml) {
				if ($taxonXml->TaxId == $id) {
					$taxon = $this->parser->hydrateTaxonFromXml($taxonXml);
					break;
				}
			}
		} else {
			return null;
		}

		return $taxon;
	}

	/**
	 * Fetch Reference data based on Pubmed ID.
 	 */
	public function retrieveReferenceEntityFromId($id)
	{
		// looking for DOI value
		if (strpos($id, '/') !== false) {
			$id = $this->retrievePmidFromDoi($id);

			if (!$id) {
				return null;
			}
		}

		// remove the version before querying ncbi
		if(substr($id, -2) == ".1") {
            $id = substr($id, 0, -2);
        }

		$query = self::NCBI_PUBMED_QUERY . $id;
		$pubmedXml = $this->httpClient->get($query);

		$reference = $this->parser->hydrateReferenceFromXml($pubmedXml);

		return $reference;
	}

	public function retrieveTaxonData($entry)
	{
		$taxonAId = $entry->getTempTaxonAId();
		$taxonBId = $entry->getTempTaxonBId();

		// build query to retrieve taxon data from ncbi taxonomy database
		$query = self::NCBI_TAXON_QUERY;

		if (!$taxonAId && !$taxonBId) {
			return $entry;
		} elseif (!$taxonBId || $taxonAId == $taxonBId) {
			$query .= $taxonAId;
		} elseif (!$taxonAId) {
			$query .= $taxonBId;
		} else {
			$query .= $taxonAId.','.$taxonBId;
		}

		$ncbiXml = $this->httpClient->get($query);
		$httpClient = $this->httpClient;
		if($ncbiXml != "") {
			$e = $this->parser->parseTaxonXml($entry, $ncbiXml, $httpClient);
			if($e == null) {
				return $e;
			}
		} else {
      		$this->logger->createGenericErrorLog("Taxon data cannot be imported. Failed NCBI query: " . $query);
			return null;
		}
		
		$entry->setTempTaxonAId(null);
		$entry->setTempTaxonBId(null);

		return $entry;
	}

	public function retrieveMainReferenceData($entry)
	{
		$pmid = $entry->getTempMainPmid();

		// looking for DOI value
		if (strpos($pmid, '/') !== false) {
			$pmid = $this->retrievePmidFromDoi($pmid);

			if (!$pmid) {
				return null;
			}
		}

		if ($pmid && $pmid !== '' && $pmid !== 'No PMID') {

			$query = self::NCBI_PUBMED_QUERY . $pmid;
			$pubmedXml = $this->httpClient->get($query);

			// check error message

			if($pubmedXml != "") { 
				$e = $this->parser->parseMainReferenceXml($entry, $pubmedXml);
				if($e == null) {
					return $e;
				}
			} else {
  	       		$this->logger->createLogCommand($entry, "Main reference cannot be imported", "Main reference cannot be imported");
				return null;
			}
		}

		$entry->setTempMainPmid(null);

		return $entry;
	}

	public function retrieveOtherReferenceData($entry)
	{
		$pmids = $entry->getTempOtherPmid();

		if (!$pmids) {
			return $entry;
		}
		$query = self::NCBI_PUBMED_QUERY;


		$count = 0;
		foreach($pmids as $pmid) {
			// backwards compatibility: remove unwanted newlines 
			$pmid = str_replace("\r", "", $pmid);
			// remove version from request pmid
			$pmid = str_replace('.1', "", $pmid);
			// remove strangely formatted version generated by french excel programs?
			$pmid = str_replace(',1', "", $pmid);
			if(!$count == 0) {
				$query .= '&id=';
			}
			$query .= $pmid;
			$count++;
		}

		$pubmedXml = $this->httpClient->get($query);

		if($pubmedXml != "") {
			$e = $this->parser->parseOtherReferenceXml($entry, $pubmedXml);
			if($e == null) {
				return $e;
			}
		} else {
            $this->logger->createLogCommand($entry, "Other reference cannot be imported", "Other reference cannot be imported");
			return null;
		}

		$entry->setTempOtherPmid(null);

		return $entry;
	}

	public function retrieveGoData($goId, $format = "oboxml")
	{
		$query = self::QUICKGO_QUERY . $goId . "&format=". $format;

		$quickGoXml = $this->httpClient->get($query);
		if($quickGoXml != "") {
			$goReference = $this->parser->parseGoXml($quickGoXml);
		}  else {
            return null;
		}

		return $goReference;
	}

	/**
	 * Receives a DOI as input, executes a request to NCBI ID converter.
	 * Returns the PMID equivalent on success, or the DOI if fails.
	 */
	public function retrievePmidFromDoi($doi)
	{
		// build the query to ID converter tool
		$query = self::NCBI_ID_CONVERTER_QUERY . '?tool='.$this->appName.'&email='.$this->appContact.'&ids='.$doi;
		$xml = $this->httpClient->get($query);

		if ($xml && $xml != "") {
			$pmid = $this->parser->parseIdConverterXml($xml);
			if ($pmid) {
				return $pmid;
			}
		} else {
            $this->logger->createGenericErrorLog("Could not retrieve PMID from input DOI: ".$doi, "Could not retrieve PMID from input DOI: ".$doi);
		}

		// if request failed, return input
		return null;
	}
}