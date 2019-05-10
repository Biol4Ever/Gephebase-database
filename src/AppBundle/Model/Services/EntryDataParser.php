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

use AppBundle\Entity\Author;
use AppBundle\Entity\Gene;
use AppBundle\Entity\Go;
use AppBundle\Entity\Reference;
use AppBundle\Entity\Synonym;
use AppBundle\Entity\Taxon;

class EntryDataParser
{
	const PUBMED_TYPE_PRIORITY = 'D016428';
	const NCBI_TAXON_QUERY = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=taxonomy&format=xml&id=';
	const NCBI_SYNONYME_XML = 'https://www.ncbi.nlm.nih.gov/gene/';

	private $httpClient;
	private $em;
	private $authorEntities;
	private $referenceEntities;

	private static $goFunctions = array(
		'F' => 'addGoMolecular',
		'P' => 'addGoBiological',
		'C' => 'addGoCellular',		
	);

	private static $goCategories = array(
		'molecular_function' => 'F',
		'biological_process' => 'P',
		'cellular_component' => 'C',
	);

	public function __construct($em, $httpClient)
	{
		$this->em = $em;
		$this->httpClient = $httpClient;
	}

	public function parseGeneXml($entry, $xml)
	{
		// retrieve all genes in our database
		$geneEntities = $this->findEntitiesByFilter('Gene', 'uniProtKbId');
		$uniprotId = $entry->getTempUniprotId();
		// check if gene exists in our database, else create a new one
        if (array_key_exists($uniprotId, $geneEntities)) {
        	$gene = $geneEntities[$uniprotId];
            $entry->setGene($gene);
        } else {
            $gene = new Gene();
            $gene->setUniProtKbId($uniprotId);
            $entry->setGene($gene);

	    	$this->hydrateGeneFromXml($gene, $xml);
        }

		$this->em->persist($gene);
		$this->em->flush($gene);

		return $entry;
	}

	/**
     * Hydrates a gene object using a Uniprot Xml response
     */
    public function hydrateGeneFromXml($gene, $xml)
    {
    	// parse XML response from uniprot web service
        $xml = $this->cleanHtmlTags($xml);
		$entries = new \SimpleXMLElement($xml);
		$entryNode = $entries->entry[0];
		$geneNode = $entryNode->gene;
		$string = '';

		// get geneId for mapping within https://www.ncbi.nlm.nih.gov/gene
		$geneId = null;

		// parse string data
		$dbReferenceNode = $entryNode->dbReference;
		foreach($dbReferenceNode as $dbReference) {
			
			if($dbReference['type'] == 'STRING') {
				$string = (string) $dbReference['id'];
				
			}

			if($dbReference['type'] == 'GeneID' && $geneId === null) {
				$geneId = (string) $dbReference['id'];
				
			}
		}

		$gene->setString($string);

		// will contain new synonyms obtained from Ncbi to avoid duplicates
		$newSynonyms = array();

		if (empty($geneId) === false) {
			$newSynonyms = $this->handleNcbi($gene, $geneId);
		}

		// parse sequence similarities data
		$similarities = "";
		$commentNode = $entryNode->comment;
		foreach($commentNode as $comment) {
			if($comment['type'] == 'similarity') {
				if($similarities == "") {
					$similarities = (string) $comment->text;
				} else {
					$similarities = (string) $similarities.";".$comment->text;
				}
			}
		}
		$gene->setSequenceSimilarities($similarities);

		// parse organism data
		$organismName = $organismIdentifier = null;
		$organismNode = $entryNode->organism;
		foreach ($organismNode->name as $name) {
			if ($name['type'] == 'scientific' || !$organismName) {
				$organismName = (string) $name;
			}
		}
		foreach ($organismNode->dbReference as $dbReference) {
			if ($dbReference['type'] == 'NCBI Taxonomy') {
				$organismIdentifier = (string) $dbReference['id'];
			}
		}

		$gene->setOrganism($organismName);
		$gene->setTaxonomicIdentifier($organismIdentifier);

		// parse synonyms
		$synonymEntities = $this->findEntitiesByFilter('Synonym', 'name');

		if ($geneNode) {

			$geneName = null;
			foreach($geneNode->name as $name) {

				if ($name['type'] == 'primary') {
					$geneName = (string) $name;
					$gene->setName($geneName);

				} elseif ($name['type'] == 'synonym' || $name['type'] == 'ordered locus' || $name['type'] == 'ORF') {

					$synonym = (string) $name;

					if ($geneName === null) {
						$geneName = $synonym;
						$gene->setName($geneName);
					}
					
					if (!in_array($synonym, $newSynonyms)) {
						if (array_key_exists($synonym, $synonymEntities)) {
							if ($gene->getSynonyms($synonym))
							$gene->addSynonym($synonymEntities[$synonym]);
						} else {
							$synonymEntity = new Synonym();
							$synonymEntity->setName($synonym);
							$this->em->persist($synonymEntity);
							$gene->addSynonym($synonymEntity);
						}
					}
				}
			}
		}

		// parse GO Ids
		$goReferences = array(); 
		$goEntities = $this->findEntitiesByFilter('Go', 'goId');
		foreach ($entryNode->dbReference as $reference) {
			if ($reference['type'] == 'GO') {
				foreach ($reference->property as $property) {
					if ($property['type'] == 'term') {

						$referenceId = (string) $reference['id'];

						if (array_key_exists($referenceId, $goEntities)) {
							$setter = self::$goFunctions[$goEntities[$referenceId]->getCategory()];
							$gene->$setter($goEntities[$referenceId]);
						} else {
							$value = (string) $property['value'];
							$category = substr($value, 0, 1);
							$description = substr($value, 2);

							$go = new Go();
							$go->setDescription($description);
							$go->setCategory($category);
							$go->setGoId($referenceId);
							$this->em->persist($go);
							if (array_key_exists($category, self::$goFunctions) && method_exists($gene, self::$goFunctions[$category])) {
								$setter = self::$goFunctions[$category];
								$gene->$setter($go);
							} else {
								$gene->addOtherGo($go);
							}
						}
					}
				}
			}
		}

		return $gene;
    }

    /**
     * Hydrates taxon object using a NCBI Xml Taxon Node response
     */
    public function hydrateTaxonFromXml($taxon)
    {
    	$synonymEntities = $this->findEntitiesByFilter('Synonym', 'name');
		$taxons = $this->findEntitiesByFilter('Taxon', 'taxId');

		// check if it exists in database, otherwise create a new one
		$taxId = (string) $taxon->TaxId;
		if (array_key_exists($taxId, $taxons)) {
			$taxonEntity = $taxons[$taxId];
		} else {
			$taxonEntity = new Taxon();
			$taxonEntity->setTaxId($taxId);
			$this->em->persist($taxonEntity);
			$taxons[$taxId] = $taxonEntity;
		}

		$nameTaxon = (string) $taxon->ScientificName;
		$lineage = (string) $taxon->Lineage;
		$parent = (string) $taxon->ParentTaxId;
		$rank = (string) $taxon->Rank;
		$synonymNames = array();
		$otherName = null;
		if ($taxon->OtherNames->count() !== 0) {
			foreach ($taxon->OtherNames->Synonym as $synonym) {
				$synonymNames[] = (string) $synonym;	
			}
			foreach($taxon->OtherNames->GenbankCommonName as $otherName) {
				$otherName = (string) $otherName;
				$synonymNames[] = $otherName;
			}
			foreach($taxon->OtherNames->CommonName as $commonName) {
				$synonymNames[] = (string) $commonName;
				 
			}
			foreach($taxon->OtherNames->Name as $name) {
				$synonymNames[] = (string) $name->DispName;
			}
		}
		$taxonEntity->setLatinName($nameTaxon);
		$taxonEntity->setCommonName($otherName);
		$taxonEntity->setRank($rank);
		$taxonEntity->setName($nameTaxon." (".$otherName.") - (Rank: ".$rank.")");

		$taxonEntity->setLineage($lineage);
		foreach ($synonymNames as $synonymName) {
			if (array_key_exists($synonymName, $synonymEntities)) {
				$taxonEntity->addSynonym($synonymEntities[$synonymName]);
			} else {
				$synonymEntity = new Synonym();
				$synonymEntity->setName($synonymName);
				$this->em->persist($synonymEntity);
				$synonymEntities[$synonymName] = $synonymEntity;
				$taxonEntity->addSynonym($synonymEntity);
			}
		}
		// Get all the parents of the taxon
		$query = self::NCBI_TAXON_QUERY;
		$count = 0;
		foreach($taxon->LineageEx->Taxon as $parentTaxon) {
			if($count == 0) {
				$query .= (string) $parentTaxon->TaxId;
				$count = 1;
			} else {
				$query .= (string) ','.$parentTaxon->TaxId;
			}
		}

		if($count != 0) {
			$ncbiParentXml = $this->httpClient->get($query);
            $ncbiParentXml = $this->cleanHtmlTags($ncbiParentXml);
			$parentsNode = new \SimpleXMLElement($ncbiParentXml);
			$reverse_array = array_reverse($parentsNode->xpath('Taxon'));
			$lastTaxon = $taxonEntity;
			foreach($reverse_array as $taxon) {
				$id = (string) $taxon->TaxId;
				if (array_key_exists($id, $taxons)) {
					$current_taxon = $taxons[$id];
					$lastTaxon->setParentId($current_taxon);
				} else {
					$current_taxon = new Taxon();
					$current_taxon->setTaxId($id);
					$nameTaxon = (string) $taxon->ScientificName;
					$lineage = (string) $taxon->Lineage;
					$parent = (string) $taxon->ParentTaxId;
					$rank = (string) $taxon->Rank;
					$synonymNames = array();
					$otherName = null;
					if ($taxon->OtherNames->count() !== 0) {
						foreach ($taxon->OtherNames->Synonym as $synonym) {
							$synonymNames[] = (string) $synonym;	
						}
						foreach($taxon->OtherNames->GenbankCommonName as $otherName) {
							$otherName = (string) $otherName;
							$synonymNames[] = $otherName;
						}
						foreach($taxon->OtherNames->CommonName as $commonName) {
							$synonymNames[] = (string) $commonName;
							 
						}
						foreach($taxon->OtherNames->Name as $name) {
							$synonymNames[] = (string) $name->DispName;
						}
					}
					$current_taxon->setName($nameTaxon." (".$otherName.") - (Rank: ".$rank.")");
					$current_taxon->setLatinName($nameTaxon);
					$current_taxon->setCommonName($otherName);
					$current_taxon->setRank($rank);
					$current_taxon->setLineage($lineage);
					$lastTaxon->setParentId($current_taxon);
					foreach ($synonymNames as $synonymName) {
						if (array_key_exists($synonymName, $synonymEntities)) {
							$current_taxon->addSynonym($synonymEntities[$synonymName]);
						} else {
							$synonymEntity = new Synonym();
							$synonymEntity->setName($synonymName);
							$this->em->persist($synonymEntity);
							$synonymEntities[$synonymName] = $synonymEntity;
							$current_taxon->addSynonym($synonymEntity);
						}
					}
				}
				$this->em->persist($lastTaxon);
				$this->em->persist($current_taxon);
				$this->em->flush($lastTaxon);
				$this->em->flush($current_taxon);

				$lastTaxon = $current_taxon;
			}
		}



		$taxonArray[] = $taxonEntity;
		$this->em->flush($taxonEntity);

		return $taxonEntity;
	}

	/**
	 * Hydrate Reference object from Pubmed Xml response
	 */
	public function hydrateReferenceFromXml($pubmedXml)
	{
		if ($pubmedXml == "") {
			return null;
		}

		$this->authorEntities = $this->findEntitiesByFilter('Author', 'identifier');
		$this->referenceEntities = $this->findEntitiesByFilter('Reference', 'pmId');

        $pubmedXml = $this->cleanHtmlTags($pubmedXml);
		$articles = new \SimpleXMLElement($pubmedXml);

        $referenceXml = $articles->PubmedArticle;
		if (!$referenceXml) {
			return null;
		}

		$reference = $this->populateReferenceFromXml($referenceXml);

		if (!$reference) {
			return null;
		}

		$this->em->persist($reference);
		$this->em->flush($reference);

		return $reference;
	}

	protected function handleNcbi($gene, $geneId)
	{
		$ncbiXml = $this->httpClient->get(self::NCBI_SYNONYME_XML . $geneId . '?report=xml&format=text');

		if(!$ncbiXml) {
			return array();
		}

		// parse synonyms
		$synonymEntities = $this->findEntitiesByFilter('Synonym', 'name');

		// character '<' encoded as '&lt;'
        $ncbiXml = $this->cleanHtmlTags($ncbiXml);
		$ncbiNode = new \SimpleXMLElement(html_entity_decode($ncbiXml));

		// check for a failed request
		if(isset($ncbiNode->GeneBeResult['status']) && ((string) $ncbiNode->GeneBeResult['status']) == 'ERROR') {
			return array();
		}

		$newSynonyms = array();

		if ($ncbiNode->Entrezgene->Entrezgene_gene->{'Gene-ref'}->{'Gene-ref_syn'}->{'Gene-ref_syn_E'}) {

			foreach($ncbiNode->Entrezgene->Entrezgene_gene->{'Gene-ref'}->{'Gene-ref_syn'}->{'Gene-ref_syn_E'} as $syn) {

				$synonym = (string) $syn;

				if (array_key_exists($synonym, $synonymEntities)) {
					if ($gene->getSynonyms($synonym))
					$gene->addSynonym($synonymEntities[$synonym]);
				} else {
					$synonymEntity = new Synonym();
					$synonymEntity->setName($synonym);
					$this->em->persist($synonymEntity);
					$gene->addSynonym($synonymEntity);
				}				
				
				$newSynonyms[] = $synonym;
			}
		}
		

		return $newSynonyms;

	}

	public function parseTaxonXml($entry, $xml, $httpClient)
	{
        $xml = $this->cleanHtmlTags($xml);
		$taxonsNode = new \SimpleXMLElement($xml);
		if(!$taxonsNode->Taxon) {
			return null;
		} 
		$synonymEntities = $this->findEntitiesByFilter('Synonym', 'name');
		$taxons = $this->findEntitiesByFilter('Taxon', 'taxId');
		$taxonAId = $entry->getTempTaxonAId();
		// check if taxon A exists, else create a new one
		if ($taxonAId) {
			if (array_key_exists($taxonAId, $taxons)) {
	        	$taxonA = $taxons[$taxonAId];
	            $entry->setTaxonA($taxonA);

	        } else {
	            $taxonA = new Taxon();
	            $taxonA->setTaxId($taxonAId);
	            // add it to array of taxons in case taxon b is the same
	            $taxons[$taxonAId] = $taxonA;
	            $entry->setTaxonA($taxonA);
	            foreach ($taxonsNode->Taxon as $taxon) {
					if ($taxonAId == $taxon->TaxId) {
						$nameTaxon = (string) $taxon->ScientificName;
						$lineage = (string) $taxon->Lineage;
						$parent = (string) $taxon->ParentTaxId;
						$rank = (string) $taxon->Rank;
						$synonymNames = array();
						$otherName = null;
						if ($taxon->OtherNames->count() !== 0) {
							foreach ($taxon->OtherNames->Synonym as $synonym) {
								$synonymNames[] = (string) $synonym;	
							}
							foreach($taxon->OtherNames->GenbankCommonName as $otherName) {
								$otherName = (string) $otherName;
								$synonymNames[] = $otherName;
							}
							foreach($taxon->OtherNames->CommonName as $commonName) {
								$synonymNames[] = (string) $commonName;
								 
							}
							foreach($taxon->OtherNames->Name as $name) {
								$synonymNames[] = (string) $name->DispName;
							}
						}
						$taxonA->setLatinName($nameTaxon);
						$taxonA->setCommonName($otherName);
						$taxonA->setRank($rank);
						$taxonA->setName($nameTaxon." (".$otherName.") - (Rank: ".$rank.")");

						$taxonA->setLineage($lineage);
						foreach ($synonymNames as $synonymName) {
							if (array_key_exists($synonymName, $synonymEntities)) {
								$taxonA->addSynonym($synonymEntities[$synonymName]);
							} else {
								$synonymEntity = new Synonym();
								$synonymEntity->setName($synonymName);
								$this->em->persist($synonymEntity);
								$synonymEntities[$synonymName] = $synonymEntity;
								$taxonA->addSynonym($synonymEntity);
							}
						}
						// Get all the parents of the taxon
						$query = self::NCBI_TAXON_QUERY;
						$count = 0;
						foreach($taxon->LineageEx->Taxon as $parentTaxon) {
							if($count == 0) {
								$query .= (string) $parentTaxon->TaxId;
								$count = 1;
							} else {
								$query .= (string) ','.$parentTaxon->TaxId;
							}
						}
						if($count != 0) {
							$ncbiParentXml = $httpClient->get($query);
                            $ncbiParentXml = $this->cleanHtmlTags($ncbiParentXml);
							$parentsNode = new \SimpleXMLElement($ncbiParentXml);
							$reverse_array = array_reverse($parentsNode->xpath('Taxon'));
							$lastTaxon = $taxonA;
							foreach($reverse_array as $taxon) {
								$id = (string) $taxon->TaxId;
								if (array_key_exists($id, $taxons)) {
	        						$current_taxon = $taxons[$id];
	        						$lastTaxon->setParentId($current_taxon);
	        					} else {
	            					$current_taxon = new Taxon();
	            					$current_taxon->setTaxId($id);
									$nameTaxon = (string) $taxon->ScientificName;
									$lineage = (string) $taxon->Lineage;
									$parent = (string) $taxon->ParentTaxId;
									$rank = (string) $taxon->Rank;
									$synonymNames = array();
									$otherName = null;
									if ($taxon->OtherNames->count() !== 0) {
										foreach ($taxon->OtherNames->Synonym as $synonym) {
											$synonymNames[] = (string) $synonym;	
										}
										foreach($taxon->OtherNames->GenbankCommonName as $otherName) {
											$otherName = (string) $otherName;
											$synonymNames[] = $otherName;
										}
										foreach($taxon->OtherNames->CommonName as $commonName) {
											$synonymNames[] = (string) $commonName;
											 
										}
										foreach($taxon->OtherNames->Name as $name) {
											$synonymNames[] = (string) $name->DispName;
										}
									}
									$current_taxon->setName($nameTaxon." (".$otherName.") - (Rank: ".$rank.")");
									$current_taxon->setLatinName($nameTaxon);
									$current_taxon->setCommonName($otherName);
									$current_taxon->setRank($rank);
									$current_taxon->setLineage($lineage);
									$lastTaxon->setParentId($current_taxon);
									foreach ($synonymNames as $synonymName) {
										if (array_key_exists($synonymName, $synonymEntities)) {
											$current_taxon->addSynonym($synonymEntities[$synonymName]);
										} else {
											$synonymEntity = new Synonym();
											$synonymEntity->setName($synonymName);
											$this->em->persist($synonymEntity);
											$synonymEntities[$synonymName] = $synonymEntity;
											$current_taxon->addSynonym($synonymEntity);
										}
									}
								}
								$this->em->persist($lastTaxon);
								$this->em->persist($current_taxon);
								$this->em->flush($lastTaxon);
								$this->em->flush($current_taxon);

								$lastTaxon = $current_taxon;
							}
						}
					}
				}
	        }
		}

		$taxonBId = $entry->getTempTaxonBId();
		if ($taxonBId) {
			 // check if taxon B exists, else create a new one
	        if (array_key_exists($taxonBId, $taxons)) {
	        	$taxonB = $taxons[$taxonBId];
	            $entry->setTaxonB($taxonB);
	        } else {
	            $taxonB = new Taxon();
	            $taxonB->setTaxId($taxonBId);
	            $entry->setTaxonB($taxonB);

	            foreach ($taxonsNode->Taxon as $taxon) {
					$nameTaxon = (string) $taxon->ScientificName;
					$lineage = (string) $taxon->Lineage;
					$parent = (string) $taxon->ParentTaxId;
					$rank = (string) $taxon->Rank;
					$synonymNames = array();
					$otherName = null;
					if ($taxon->OtherNames->count() !== 0) {
						foreach ($taxon->OtherNames->Synonym as $synonym) {
							$synonymNames[] = (string) $synonym;	
						}
						foreach($taxon->OtherNames->GenbankCommonName as $otherName) {
							$otherName = (string) $otherName;
						}
					}
					if ($taxonBId == $taxon->TaxId) {
						$taxonB->setLatinName($nameTaxon);
						$taxonB->setCommonName($otherName);
						$taxonB->setRank($rank);
						$taxonB->setName($nameTaxon." (".$otherName.") - (Rank: ".$rank.")");
						$taxonB->setLineage($lineage);
						foreach ($synonymNames as $synonymName) {
							if (array_key_exists($synonymName, $synonymEntities)) {
								$taxonB->addSynonym($synonymEntities[$synonymName]);
							} else {
								$synonymEntity = new Synonym();
								$synonymEntity->setName($synonymName);
								$this->em->persist($synonymEntity);
								$synonymEntities[$synonymName] = $synonymEntity;
								$taxonB->addSynonym($synonymEntity);
							}	
						}
						// Get all the parents of the taxon
						$query = self::NCBI_TAXON_QUERY;
						$count = 0;
						foreach($taxon->LineageEx->Taxon as $parentTaxon) {
							if($count == 0) {
								$query .= (string) $parentTaxon->TaxId;
								$count = 1;
							} else {
								$query .= (string) ','.$parentTaxon->TaxId;
							}
						}
						if($count != 0) {
							$ncbiParentXml = $httpClient->get($query);
                            $ncbiParentXml = $this->cleanHtmlTags($ncbiParentXml);
							$parentsNode = new \SimpleXMLElement($ncbiParentXml);
							$reverse_array = array_reverse($parentsNode->xpath('Taxon'));
							$lastTaxon = $taxonB;
							foreach($reverse_array as $taxon) {
								$id = (string) $taxon->TaxId;
								if (array_key_exists($id, $taxons)) {
	        						$current_taxon = $taxons[$id];
	        						$lastTaxon->setParentId($current_taxon);
	        					} else {
	            					$current_taxon = new Taxon();
	            					$current_taxon->setTaxId($id);
									$nameTaxon = (string) $taxon->ScientificName;
									$lineage = (string) $taxon->Lineage;
									$parent = (string) $taxon->ParentTaxId;
									$rank = (string) $taxon->Rank;
									$synonymNames = array();
									$otherName = null;
									if ($taxon->OtherNames->count() !== 0) {
										foreach ($taxon->OtherNames->Synonym as $synonym) {
											$synonymNames[] = (string) $synonym;	
										}
										foreach($taxon->OtherNames->GenbankCommonName as $otherName) {
											$otherName = (string) $otherName;
											$synonymNames[] = $otherName;
										}
										foreach($taxon->OtherNames->CommonName as $commonName) {
											$synonymNames[] = (string) $commonName;
											 
										}
										foreach($taxon->OtherNames->Name as $name) {
											$synonymNames[] = (string) $name->DispName;
										}
									}
									$current_taxon->setName($nameTaxon." (".$otherName.") - (Rank: ".$rank.")");
									$current_taxon->setLatinName($nameTaxon);
									$current_taxon->setCommonName($otherName);
									$current_taxon->setRank($rank);
									$current_taxon->setLineage($lineage);
									$lastTaxon->setParentId($current_taxon);
									foreach ($synonymNames as $synonymName) {
										if (array_key_exists($synonymName, $synonymEntities)) {
											$current_taxon->addSynonym($synonymEntities[$synonymName]);
										} else {
											$synonymEntity = new Synonym();
											$synonymEntity->setName($synonymName);
											$this->em->persist($synonymEntity);
											$synonymEntities[$synonymName] = $synonymEntity;
											$current_taxon->addSynonym($synonymEntity);
										}
									}
								}
								$this->em->persist($lastTaxon);
								$this->em->persist($current_taxon);
								$this->em->flush($lastTaxon);
								$this->em->flush($current_taxon);

								$lastTaxon = $current_taxon;
							}
						}
					}
				}
	        }
		}

		return $entry;
	}

	public function parseMainReferenceXml($entry, $xml)
	{
		$this->authorEntities = $this->findEntitiesByFilter('Author', 'identifier');
		$this->referenceEntities = $this->findEntitiesByFilter('Reference', 'pmId');

        $xml = $this->cleanHtmlTags($xml);
		$articles = new \SimpleXMLElement($xml);
		$referenceXml = $articles->PubmedArticle;
		if(!$referenceXml) {
			return null;
		}

		$reference = $this->populateReferenceFromXml($referenceXml);

		if (!$reference) {
			return $entry;
		}

		$this->em->persist($reference);
		$this->em->flush($reference);
		$entry->setMainReference($reference);

		return $entry;
	}

	public function parseOtherReferenceXml($entry, $xml)
	{
		$this->authorEntities = $this->findEntitiesByFilter('Author', 'identifier');
		$this->referenceEntities = $this->findEntitiesByFilter('Reference', 'pmId');

        $xml = $this->cleanHtmlTags($xml);
		$articles = new \SimpleXMLElement($xml);
		if(!$articles->PubmedArticle) {
			return null;
		}
		foreach ($articles->PubmedArticle as $referenceXml) {
			$reference = $this->populateReferenceFromXml($referenceXml);
			if (!$reference) {
				continue;
			}

			$this->em->persist($reference);
			$this->em->flush($reference);
			$this->referenceEntities[$reference->getPmid()] = $reference;
			if (!$entry->getOtherReferences()->contains($reference)) {
				$entry->addOtherReference($reference);
			}
		}

		return $entry;
	}

	public function parseGoXml($xml)
	{
        $xml = $this->cleanHtmlTags($xml);
		$obo = new \SimpleXMLElement($xml);
		$term = $obo->term;

		$go = new Go();
		$go->setDescription((string) $term->name);
		$namespace = (string) $term->namespace;
		$category = '';
		if (array_key_exists($namespace, self::$goCategories)) {
			$category = self::$goCategories[$namespace];
		}
		$go->setCategory($category);
		$go->setGoId((string) $term->id);

		return $go;
	}

	private function cleanHtmlTags($xml)
    {
        $referenceXml = $xml;
        $tags = array('i', 'b');

        foreach ( $tags as $tag ){
            $pattern= "/<" . $tag . ">(.*?(?=<\/" . $tag . ">))<\/" . $tag . ">/";
            $replacement = "$1";

            $referenceXml = preg_replace( $pattern, $replacement, $referenceXml);
        }

        return $referenceXml;
    }

	private function populateReferenceFromXml($referenceXml)
	{
		$pmids = $referenceXml->MedlineCitation->PMID;
		$highestVersion = '0';
		$versionPmidz = null;
		$noVersionPmid = null;
		foreach ($pmids as $pmid) {
			if ($pmid['Version']) {
				$version = (string) $pmid['Version'];
				if ($version > $highestVersion) {
					$highestVersion = $version;
					$versionPmid = (string) $pmid;
				}
			} else {
				$noVersionPmid = (string) $pmid;
			}
		}

		if ($versionPmid) {
			$pmid = $versionPmid . '.' . $highestVersion;
		} elseif ($noVersionPmid) {
			$pmid = $noVersionPmid;
		} else {
			$pmid = null;
		}

		if (!$pmid) {
			return null;
		}

		$foundReference = false;
		if (array_key_exists($pmid, $this->referenceEntities)) {
			$reference = $this->referenceEntities[$pmid];
			$foundReference = true;
		} else {
			$reference = new Reference();
			$reference->setPmid($pmid);
		}

		$article = $referenceXml->MedlineCitation->Article;

		// parse publication type
		$firstPublicationType = null;
		foreach ($article->PublicationTypeList->PublicationType as $type) {
			$ui = (string) $type['UI'];
			$value = (string) $type;

			if ($ui === self::PUBMED_TYPE_PRIORITY) {
				$reference->setPublicationType($value);
				break;
			}

			if ($firstPublicationType === null) {
				$firstPublicationType = $value;
			}
		}

		if ($firstPublicationType !== null) {
			$reference->setPublicationType($firstPublicationType);
			$firstPublicationType = null;
		}

		// parse authors
		if (!$foundReference) {
			foreach ($article->AuthorList->Author as $author) {
				$identifier = (string) $author->LastName.$author->Initials;
				$identifierSource = (string) $author->Identifier['Source'];
				$lastname = (string) $author->LastName;
				$initials = (string) $author->Initials;

				$authorEntity = new Author();
				$authorEntity->setLastName($lastname);
				$authorEntity->setInitials($initials);
				$authorEntity->setIdentifier($lastname.$initials);
				$authorEntity->setIdentifierSource($identifierSource);
				$this->authorEntities[$identifier] = $authorEntity;
				$this->em->persist($authorEntity);
				$reference->addAuthor($authorEntity);	
			}
		}

		// parse journal title
		$journalTitle = (string) $article->Journal->Title;
		$reference->setJournalTitle($journalTitle);

		// parse journal abbreviation
		$medline = $referenceXml->xpath('//MedlineJournalInfo')[0];
		$journalAbbreviation = (string) $medline->MedlineTA;
		$reference->setJournalAbbreviation($journalAbbreviation);

		// parse journal volume
		$journalVolume = (string) $article->Journal->JournalIssue->Volume;
		$reference->setJournalVolume($journalVolume);

		// parse journal year
		$journalYear = '';
		if ($article->Journal->JournalIssue->PubDate) {
			if ($article->Journal->JournalIssue->PubDate->Year) {
				// if the Year tag exists, use it as publication year
				$journalYear = (string) $article->Journal->JournalIssue->PubDate->Year;
			} elseif ($article->Journal->JournalIssue->PubDate->MedlineDate) {
				// otherwise use MedlineDate if it exists
				$journalYear = (string) $article->Journal->JournalIssue->PubDate->MedlineDate;
			}
		}

		$reference->setJournalYear($journalYear);

		// parse journal pagination
		$journalPagination = (string) $article->Pagination->MedlinePgn;
		$reference->setJournalPagination($journalPagination);
		
		$abstract = '';
		if ($article->Abstract->count() > 0) {
			foreach ($article->Abstract->children() as $child) {
				$abstract .= ( $abstract !== '' ) ? PHP_EOL . PHP_EOL : '';
	            $abstract .= (string) $child;
			}
		}

		$reference->setAbstract($abstract);

		// parse doi
		$doi = null;
		foreach ($article->ELocationID as $elocation) {
			$eType = (string) $elocation['EIdType'];
			if ($eType === 'doi') {
				$doi = (string) $elocation;
				break;
			}
		}

		if (!$doi) {

			foreach ($referenceXml->PubmedData->ArticleIdList->ArticleId as $articleId) {
				$type = (string) $articleId['IdType'];
				if ($type == "doi") {
					$doi = (string) $articleId;
					break;
				}
			}
		}

		$reference->setDoi($doi);

		// parse article title
		$articleTitle = (string) $article->ArticleTitle;
		$reference->setArticleTitle($articleTitle);

		return $reference;
	}

	/**
	 * Parse a response from ncbi id converter API and returns the pmid if it was found, null otherwise.
	 */
	public function parseIdConverterXml($xml)
	{
        $xml = $this->cleanHtmlTags($xml);
		$convertedId = new \SimpleXMLElement($xml);

		$record = $convertedId->record;

		if(isset($record['pmid'])) {
			return $record['pmid'];
		} else {
			return null;
		}
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
}