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
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportEntry
{
    public function exportCsv($entries)
    {
        // first we need to extract nested array information and add to first level index
        $entries = $this->flattenComplexRelations($entries);
    	$response = new StreamedResponse();
        $response->setCallback(function() use($entries){
            $handle = fopen('php://output', 'w+');
            fputcsv($handle, array(
                'Gephe ID',
                'Gene-Gephebase',
                'Username',
                'Generic Gene Name',
                'UniProtKB_ID',
                'GenBankID',
                'Trait Category',
                'Trait',
                'State A',
                'State B',
                'Taxon A ID',
                'Latin Name A',
                'Common Name A',
                'Rank A',
                'A=Infraspecies',
                'Taxon A Description',
                'Taxon B ID',
                'Latin Name B',
                'Common Name B',
                'Rank B',
                'B=Infraspecies',
                'Taxon B Description',
                'Ancestral State',
                'Taxonomic Status',
                'Empirical Evidence',
                'Molecular Details',
                'Molecular Type',
                'Presumptive Null',
                'SNP Coding Change',
                'Codon-Taxon-A',
                'Codon-Position',
                'Codon-TaxonB',
                'AminoAcid-Taxon A',
                'AA-Position',
                'AminoAcid-Taxon B',
                'Aberration Type',
                'Aberration Size',
                'Main PMID',
                'Additional PMID',
                'Comments',
                'User Feedback',
            ),',');

            foreach($entries as $row) {
                foreach($row as $r) {
                    $r = str_replace(',', ';', $r);
                }
                if (!array_key_exists('feedback', $row)) {
                    $row['feedback'] = "";
                }

                fputcsv($handle,array(
                    $row['gepheId'],
                    str_replace(',',';',$row['geneGephebase']),
                    str_replace(',',';',$row['username']),
                    str_replace(',',';',$row['geneName']),
                    str_replace(',',';',$row['uniProtKbId']),
                    str_replace(',',';',$row['genbankId']),
                    str_replace(',',';',$row['category']),
                    str_replace(',',';',$row['description']),
                    str_replace(',',';',$row['stateInTaxonA']),
                    str_replace(',',';',$row['stateInTaxonB']),
                    str_replace(',',';',$row['taxIdA']),
                    str_replace(',',';',$row['latinNameA']),
                    str_replace(',',';',$row['commonNameA']),
                    str_replace(',',';',$row['rankA']),
                    str_replace(',',';',$row['isInfraspeciesA'] ? 1 : 0 ),
                    str_replace(',',';',$row['descriptionA']),
                    str_replace(',',';',$row['taxIdB']),
                    str_replace(',',';',$row['latinNameB']),
                    str_replace(',',';',$row['commonNameB']),
                    str_replace(',',';',$row['rankB']),
                    str_replace(',',';',$row['isInfraspeciesB'] ? 1 : 0 ),
                    str_replace(',',';',$row['descriptionB']),
                    str_replace(',',';',$row['ancestralState']),
                    str_replace(',',';',$row['taxonomicStatus']),
                    str_replace(',',';',$row['experimentalEvidence']),
                    str_replace(',',';',$row['molecularDetails']),
                    str_replace(',',';',$row['molecularType']),
                    str_replace(',',';',$row['presumptiveNull']),
                    str_replace(',',';',$row['snp']),
                    str_replace(',',';',$row['codonTaxonA']),
                    str_replace(',',';',$row['codonPosition']),
                    str_replace(',',';',$row['codonTaxonB']),
                    str_replace(',',';',$row['aminoAcidTaxonA']),
                    str_replace(',',';',$row['aaPosition']),
                    str_replace(',',';',$row['aminoAcidTaxonB']),
                    str_replace(',',';',$row['aberrationType']),
                    str_replace(',',';',$row['aberrationSize']),
                    str_replace(',',';',$row['pmId']),
                    str_replace(',',';',$row['otherPmid']),
                    str_replace(',',';',$row['comments']),
                    str_replace(',',';',$row['feedback']),
                ),',');
            }
	
    	    fclose($handle);
    	});
    	$response->setStatusCode(200);
    	$response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $date = new \DateTime();
    	$response->headers->set('Content-Disposition','attachment; filename="Gephebase-entries-'.$date->format('Y-m-d').'.csv"');
    	return $response;
    }

    public function exportCsvComplete($entries)
    {
        // first we need to extract nested array information and add to first level index
        $entries = $this->flattenComplexRelations($entries);

        $response = new StreamedResponse();
        $response->setCallback(function() use($entries){
            $handle = fopen('php://output', 'w+');
            fputcsv($handle, array(
                'Gephe ID',
                'Gene-Gephebase',
                'Username',
                'Generic Gene Name',
                'UniProtKB_ID',
                'UniProtKB_Species',
                'String',
                'Sequence Similarities',
                'Synonyms',
                'GO Molecular',
                'GO Cellular',
                'GO Biological',
                'GenBankID',
                'Trait Category',
                'Trait',
                'State A',
                'State B',
                'Taxon A ID',
                'Latin Name A',
                'Common Name A',
                'Rank A',
                'Taxon A Lineage',
                'A=Infraspecies',
                'Taxon A Description',
                'Taxon B ID',
                'Latin Name B',
                'Common Name B',
                'Rank B',
                'Taxon B Lineage',
                'B=Infraspecies',
                'Taxon B Description',
                'Ancestral State',
                'Taxonomic Status',
                'Empirical Evidence',
                'Molecular Details',
                'Molecular Type',
                'Presumptive Null',
                'SNP Coding Change',
                'Codon-Taxon-A',
                'Codon-Position',
                'Codon-TaxonB',
                'AminoAcid-Taxon A',
                'AA-Position',
                'AminoAcid-Taxon B',
                'Aberration Type',
                'Aberration Size',
                'Reference Title',
                'Reference Abstract',
                'Publication Year',
                'Main PMID',
                'Additional PMID',
                'Comments',
                'User Feedback',
            ),',');

            foreach($entries as $row) {
                foreach($row as $r) {
                    $r = str_replace(',', ';', $r);
                }
                if (!array_key_exists('feedback', $row)) {
                    $row['feedback'] = "";
                }
                $synonyms = "";
                if($row['geneSynonyms']) {
                    $synonyms = implode(';', $row['geneSynonyms']);
                }
                $molecular = "";
                if($row['goMoleculars']) {
                    $molecular = implode(';',$row['goMoleculars']);
                }
                $cellular = "";
                if($row['goCellulars']) {
                    $cellular = implode(';',$row['goCellulars']);
                }
                $biological = "";
                if($row['goBiologicals']) {
                    $biological = implode(';',$row['goBiologicals']);
                }

                fputcsv($handle,array(
                    $row['gepheId'],
                    str_replace(',',';',$row['geneGephebase']),
                    str_replace(',',';',$row['username']),
                    str_replace(',',';',$row['geneName']),
                    str_replace(',',';',$row['uniProtKbId']),
                    str_replace(',',';',$row['organism']),
                    str_replace(',',';',$row['string']),
                    str_replace(',',';',$row['similarities']),
                    str_replace(',',';',$synonyms),
                    str_replace(',',';',$molecular),
                    str_replace(',',';',$cellular),
                    str_replace(',',';',$biological),
                    str_replace(',',';',$row['genbankId']),
                    str_replace(',',';',$row['category']),
                    str_replace(',',';',$row['description']),
                    str_replace(',',';',$row['stateInTaxonA']),
                    str_replace(',',';',$row['stateInTaxonB']),
                    str_replace(',',';',$row['taxIdA']),
                    str_replace(',',';',$row['latinNameA']),
                    str_replace(',',';',$row['commonNameA']),
                    str_replace(',',';',$row['rankA']),
                    str_replace(',',';',$row['lineageA']),
                    str_replace(',',';',$row['isInfraspeciesA'] ? 1 : 0 ),
                    str_replace(',',';',$row['descriptionA']),
                    str_replace(',',';',$row['taxIdB']),
                    str_replace(',',';',$row['latinNameB']),
                    str_replace(',',';',$row['commonNameB']),
                    str_replace(',',';',$row['rankB']),
                    str_replace(',',';',$row['lineageB']),
                    str_replace(',',';',$row['isInfraspeciesB'] ? 1 : 0 ),
                    str_replace(',',';',$row['descriptionB']),
                    str_replace(',',';',$row['ancestralState']),
                    str_replace(',',';',$row['taxonomicStatus']),
                    str_replace(',',';',$row['experimentalEvidence']),
                    str_replace(',',';',$row['molecularDetails']),
                    str_replace(',',';',$row['molecularType']),
                    str_replace(',',';',$row['presumptiveNull']),
                    str_replace(',',';',$row['snp']),
                    str_replace(',',';',$row['codonTaxonA']),
                    str_replace(',',';',$row['codonPosition']),
                    str_replace(',',';',$row['codonTaxonB']),
                    str_replace(',',';',$row['aminoAcidTaxonA']),
                    str_replace(',',';',$row['aaPosition']),
                    str_replace(',',';',$row['aminoAcidTaxonB']),
                    str_replace(',',';',$row['aberrationType']),
                    str_replace(',',';',$row['aberrationSize']),
                    str_replace(',',';',$row['articleTitle']),
                    str_replace(',',';',$row['abstract']),
                    str_replace(',',';',$row['journalYear']),
                    str_replace(',',';',$row['pmId']),
                    str_replace(',',';',$row['otherPmid']),
                    str_replace(',',';',$row['comments']),
                    str_replace(',',';',$row['feedback']),
                ),',');
            }
    
            fclose($handle);
        });
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $date = new \DateTime();
        $response->headers->set('Content-Disposition','attachment; filename="Gephebase-entries-'.$date->format('Y-m-d').'.csv"');
        return $response;
    }

    /**
     * Takes an entries array and flattens all its complex relations by concatenating collections by the &N separator, where N is an incremental digit
     */
    private function flattenComplexRelations($entries)
    {
        foreach ($entries as $key => $entry) {
            $entry = $this->flatten('complexTraits', $entry);
            $entry = $this->flatten('complexTaxonAs', $entry, 'A');
            $entry = $this->flatten('complexTaxonBs', $entry, 'B');
            $entry = $this->flatten('mutations', $entry);
            $entry = $this->flatten('feedbacks', $entry);

            $entries[$key] = $entry;
        }

        return $entries;
    }

    /**
     * Transform an array of relations into single fields concatenated by the characters '&N', where N is an incremental digit
     */
    private function flatten($collection, $entry, $keySuffix = '')
    {
        $elements = $entry[$collection];

        // if elements array does not have a 0 indexed subArray, then it must be non existent and a "placeholder" which contains the field names only.
        // Add all these fields as a first level rows of the entry array.
        if (!isset($elements[0])) {
            foreach ($elements as $key => $value) {
                $entry[$key . $keySuffix] = $value;
            }

            return $entry;
        }

        $keys = array_keys($elements[0]);

        // We will use PHP variable variables to store the different values that need to be flattened
        foreach ($keys as $key) {
            $$key = '';
        }

        $count = count($elements);
        for ($i=0;$i<$count;$i++) {
            if ($i > 0) {
                $separator = ' &'. ($i+1) .' ';
                foreach ($keys as $key) {
                    $$key .= $separator;
                }
            }

            foreach ($keys as $key) {
                $$key .= $elements[$i][$key];
            }
        }

        foreach ($keys as $key) {
            $entry[$key.$keySuffix] = $$key;
        }

        return $entry;   
    }
}