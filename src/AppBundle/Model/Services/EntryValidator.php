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
use AppBundle\Entity\Taxon;
use Symfony\Component\Form\FormError;

class EntryValidator
{
    private $errorMessages = array();
    private $csvValidator = false;      
    private $fieldsToValidate;

    public function __construct()
    {
        $this->fieldsToValidate = array(
            array(
                'formName' => 'geneGephebase',
                'entity' => 'entry',
                'name' => 'geneGephebase',
                'validator' => 'validateGeneGephebase',
                'error' => 'Mandatory - Gene-Gephebase: cannot be empty.'
            ),
            array(
                'formName' => 'gepheId',
                'entity' => 'entry',
                'name' => 'gepheId',
                'validator' => 'validateGepheId',
                'error' => 'Mandatory - GepheID: must be in the format "GPXXXXXXXX", where X is a digit 0-9.'
            ),
            array(
                'formName' => 'uniProtKbId',
                'entity' => 'gene',
                'name' => 'gene'      ,
                'validator' => 'validateGene',
                'error' => 'UniProtKB_ID: only alphanumerical characters with no spaces are allowed'
            ),
            array(
                'formName' => 'genbankId',
                'entity' => 'entry',
                'name' => 'genbankId',
                'validator' => 'validateGenBank',
                'error' => 'GenBankID: only alphanumerical characters, "." and "_" are allowed.'
            ),
            array(
                'formName' => 'category',
                'entity' => 'traits',
                'name' => 'traits',
                'validator' => 'validateTraits',
                'error' => 'Mandatory - Trait Category: One or more value(s) must be selected.'
            ),
            array(
                'formName' => 'description',
                'entity' => 'traits',
                'name' => 'traits',
                'validator' => 'validateTraitDescription',
                'error' => 'Mandatory - Trait : Must contain text, not only numbers'
            ),
            array(
                'formName' => '',
                'entity' => 'taxonAList',
                'name' => 'taxonAList',
                'validator' => 'validateTaxonA',
                'error' => ''
            ),
            array(
                'formName' => '',
                'entity' => 'taxonBList',
                'name' => 'taxonBList',
                'validator' => 'validateTaxonB',
                'error' => ''
            ),
            array(
                'formName' => '',
                'entity' => 'mutations',
                'name' => 'mutations',
                'validator' => 'validateMutations',
                'error' => ''
            ),
            array(
                'formName' => 'comments',
                'entity' => 'entry',
                'name' => 'comments' ,
                'validator' => 'validateComments',
                'error' => 'Comments : Must contain text, not only numbers'
            ),
            array(
                'formName' => 'ancestralState',
                'entity' => 'entry',
                'name' => 'ancestralState'   ,
                'validator' => 'validateAncestralState',
                'error' => 'Ancestral State: One value must be selected.'
            ),
            array(
                'formName' => 'taxonomicStatus' ,
                'entity' => 'entry',
                'name' => 'taxonomicStatus'  ,
                'validator' => 'validateTaxonomicStatus',
                'error' => 'Mandatory - Taxonomic Status: One value must be selected.'
            ),
            array(
                'formName' => 'ancestralState',
                'entity' => 'entry',
                'name' => 'ancestralState',
                'validator' => 'validateNotEmptyList',
                'error' => 'Ancestral State: cannot be empty.'
            ),
            array(
                'formName' => 'taxonomicStatus',
                'entity' => 'entry',
                'name' => 'taxonomicStatus',
                'validator' => 'validateNotEmptyList',
                'error' => 'Taxonomic Status: cannot be empty.'
            ),
            /*array(
                'formName' => 'validator',
                'entity' => 'entry',
                'name' => 'validator',
                'validator' => 'validateValidator',
                'error' => 'Reviewer: Cannot be empty.',
            ),*/
        );
    }

    /**
     * Validates entry data. Stores an error message for each invalid field.
     */
	public function validate($entry)
	{
        $entryValidity = true;

        // complex relations which will individually set error messages on correct form element
        $validationExceptions = array(
            'traits',
            'taxonAList',
            'taxonBList',
            'mutations',
        );

        foreach ($this->fieldsToValidate as $field) {
            $getter = 'get'.ucfirst($field['name']);
            if($field['name'] == 'otherReferences') {
                if(count($entry->getTempOtherPmid()) > 0 || $entry->getImportedOtherRefData() === false) {
                    $fieldValidity = false;
                }  else {
                    $fieldValidity = true;
                }
            } else {
                $fieldValidity = $this->$field['validator']($entry->$getter());   
            }

            if ($fieldValidity === false) {
                $entryValidity = false;
                if (!in_array($field['entity'], $validationExceptions)) {
                    $this->errorMessages[$field['entity']][$field['formName']] = $field['error'];
                }
            }
        }

        return $entryValidity;
	}

    /**
     * Retrieves all error messages and adds them to form
     */
    public function addErrorsToForm($form)
    {
        $complexKeys = array("traits", "mutations", "taxonAList", "taxonBList");

        $entities = $this->errorMessages;
        foreach($entities as $key => $entity) {
            foreach($entity as $name => $error) {
                if($key == "entry") {
                    $form->get($name)->addError(new FormError($error));
                } elseif (in_array($key, $complexKeys)) {
                    if (is_array($error)) {
                        foreach ($error as $subName => $subError) {
                            if(is_array($subError)) {
                                foreach ($subError as $child => $childError) {
                                    $form->get($key)[$name]->get($subName)->get($child)->addError(new FormError($childError));
                                }
                            } else {
                                $form->get($key)[$name]->get($subName)->addError(new FormError($subError));
                            }
                        }
                    }
                } elseif ($key == "otherReferences") {
                    $form->get('addOtherReferencePmid')->addError(new FormError("PMID which have not been imported : ".implode(';', $entry->getTempOtherPmid())));
                } else {
                    $form->get($key)->get($name)->addError(new FormError($error));
                }
            }
        }
    }

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    public function setCsvValidator($bool)
    {
        $this->csvValidator = $bool;
    }

    private function validateGeneGephebase($geneGephebase)
    {
        // No checks are required on the gepheId when doing a csv import because it is auto generated after flush
        if ($this->csvValidator) {
            return true;
        }

        if( $geneGephebase != null) {
            return true;
        } else {
            return false;
        }
    }

    private function validateGepheId($gephe)
    {
        // No checks are required on the gepheId when doing a csv import because it is auto generated after flush
        if ($this->csvValidator) {
            return true;
        }

        $regex = '/^GP[0-9]{8}$/';

        if (preg_match($regex, trim($gephe))) {
            return true;
        } else {
            return false;
        }
    }

    private function validateGene($gene)
    {
        if (!$gene) {
            return true;
        }

        if($gene->getUniProtKbId() == null) {
            return true;
        }
        if($gene->getUniProtKbId() != "") {
            return ctype_alnum(trim($gene->getUniProtKbId()));
        } else {
            return false;
        }
    }

    private function validateGenBank($genbank)
    {
        if (($this->csvValidator && $genbank == '') || $genbank == '') {
            return true;
        }
        $regex = '/^[a-zA-Z0-9._]+$/';

        if (preg_match($regex, trim($genbank))) {
            return true;
        } else {
            return false;
        }
    }

    private function validateTraits($complexTraits)
    {
        if ($this->csvValidator) {
            return true;
        }

        $regex = '/^(Behavior|Morphology|Physiology){1,3}$/';

        foreach ($complexTraits as $key => $complexTrait) {
            $trait = $complexTrait->getPhenotypeTrait();

            if (!$trait) {
                return false;
            }

            if (!preg_match($regex, trim($trait->getCategory()))) {
                $this->errorMessages['traits'][$key]['phenotypeTrait']['category'] = 'Trait #'. ($key+1) .' - Mandatory - Trait Category: One or more value(s) must be selected.';
                return false;
            }
        }

        return true;
    }

    private function validateCodonTaxon($codon)
    {
        if ($this->csvValidator || trim($codon) == '') {
            return true;
        }
        $regex = '/^(A|C|G|T|W|S|M|K|R|Y|B|D|H|V|N){3}$/';

        if (preg_match($regex, trim($codon))) {
            return true;
        } else {
            return false;
        }
    }

    private function validatePosition($position)
    {
        if ($this->csvValidator || trim($position) == '') {
            return true;
        }
        $regex = '/^(?!0+$)[0-9]+$/';

        if (preg_match($regex, trim($position))) {
            return true;
        } else {
            return false;
        }
    }

    private function getValidAminoAcidInputs()
    {
        $aminoAcids = array(
            "His",
            "Gln",
            "Pro",
            "Arg",
            "Leu",
            "Asp",
            "Glu",
            "Ala",
            "Gly",
            "Val",
            "Tyr",
            "Ser",
            "Cys",
            "Trp",
            "Phe",
            "Asn",
            "Lys",
            "Thr",
            "Ile",
            "Met",
        );

        $validInputs = array_merge(array("STP"),$aminoAcids);

        return $validInputs;
    }

    private function validateAminoAcid($aa)
    {
        if ($this->csvValidator || trim($aa) == '') {
            return true;
        }
        $regex = '/^'.join('|',$this->getValidAminoAcidInputs()).'$/';

        if (preg_match($regex, trim($aa))) {
            return true;
        } else {
            return false;
        }
    }

    private function validateTraitDescription($complexTraits)
    {
        if ($this->csvValidator) {
            return true;
        }

        $regex = '/^[0-9]*$/';

        foreach ($complexTraits as $key => $complexTrait) {
            $trait = $complexTrait->getPhenotypeTrait();

            if (!$trait) {
                return false;
            }

            if(preg_match($regex, trim($trait->getDescription()))) {
                $this->errorMessages['traits'][$key]['phenotypeTrait']['description'] = 'Trait #'. ($key+1) .' - Mandatory - Trait : Must contain text, not only numbers.';

                return false;
            }
        }

        return true;
    }

    private function validateMolecularDetails($molecularDetails) {
        if ($this->csvValidator || $molecularDetails == null) {
            return true;
        }
        $regex = '/^[0-9]*$/';
        if(preg_match($regex, trim($molecularDetails))) {
            return false;
        } else {
            return true;
        }

    }

    private function validateComments($comments) {
        if ($this->csvValidator || $comments == null) {
            return true;
        }
        $regex = '/^[0-9]*$/';
        if(preg_match($regex, trim($comments))) {
            return false;
        } else {
            return true;
        }

    }

    private function validateTaxonDescription($description) {
        if ($this->csvValidator || $description == null) {
            return true;
        }
        $regex = '/^[0-9]*$/';
        if(preg_match($regex, trim($description))) {
            return false;
        } else {
            return true;
        }

    }

    private function validateTaxonBDescription($description) {
        if ($this->csvValidator || $description == null) {
            return true;
        }
        $regex = '/^[0-9]*$/';
        if(preg_match($regex, trim($description))) {
            return false;
        } else {
            return true;
        }

    }

    private function validateTaxonA($taxonAs)
    {
        $taxonValidity = true;

        foreach($taxonAs as $key => $complexTaxon) {
            $valid = $this->validateTaxonDescription($complexTaxon->getDescription());
            if (!$valid) {
                $taxonValidity = false;
                $this->errorMessages['taxonAList'][$key]['description'] = 'Taxon A #'. ($key+1)  . ' - Description : Must contain text, not only numbers';
            }

            $taxon = $complexTaxon->getTaxon();

            if($taxon) {
                $valid = $this->validateTaxId($taxon->getTaxId());
                if (!$valid) {
                    $taxonValidity = false;
                    $this->errorMessages['taxonAList'][$key]['taxon']['taxId'] = 'Taxon A #'. ($key+1)  . ' - Tax ID: mandatory - only characters "0-9" are allowed.';
                }
            }
        }

        return $taxonValidity;
    }

    private function validateTaxonB($taxonBs)
    {
        $taxonValidity = true;

        foreach($taxonBs as $key => $complexTaxon) {
            $valid = $this->validateTaxonDescription($complexTaxon->getDescription());
            if (!$valid) {
                $taxonValidity = false;
                $this->errorMessages['taxonBList'][$key]['description'] = 'Taxon B #'. ($key+1)  . ' - Description : Must contain text, not only numbers';
            }

            $taxon = $complexTaxon->getTaxon();

            if($taxon) {
                $valid = $this->validateTaxId($taxon->getTaxId());
                if (!$valid) {
                    $taxonValidity = false;
                    $this->errorMessages['taxonBList'][$key]['taxon']['taxId'] = 'Taxon B #'. ($key+1)  . ' - Tax ID: mandatory - only characters "0-9" are allowed.';
                }
            }
        }

        return $taxonValidity;
    }

    private function validateMutations($mutations)
    {
        $mutationFields = array(
            array(
                'name' => 'presumptiveNull',
                'validator' => 'validateNotEmptyList',
                'error' => 'Presumptive Null: cannot be empty.',
            ),
            array(
                'name' => 'molecularType',
                'validator' => 'validateMolecularType',
                'error' => 'Mandatory - Molecular Type: One value must be selected.',
            ),
            array(
                'name' => 'aberrationType',
                'validator' => 'validateAberrationType',
                'error' => 'Aberration Type: One value must be selected.',
            ),
            array(
                'name' => 'snp',
                'validator' => 'validateSnp',
                'error' => 'SNP coding change: One value must be selected.',
            ),
            array(
                'name' => 'codonTaxonA',
                'validator' => 'validateCodonTaxon',
                'error' => 'Invalid Codon - Taxon A: must contain exactly 3 letters among [A,C,G,T].',
            ),
            array(
                'name' => 'codonTaxonB',
                'validator' => 'validateCodonTaxon',
                'error' => 'Invalid Codon - Taxon B: must contain exactly 3 letters among [A,C,G,T].',
            ),
            array(
                'name' => 'codonPosition',
                'validator' => 'validatePosition',
                'error' => 'Invalid Codon Position: must be in the range [1-99999].',
            ),
            array(
                'name' => 'aaPosition',
                'validator' => 'validatePosition',
                'error' => 'Invalid Amino-acid Position: must be in the range [1-99999].',
            ),
            array(
                'name' => 'aminoAcidTaxonA',
                'validator' => 'validateAminoAcid',
                'error' => 'Invalid Amino-acid - Taxon A: must be one of [' . implode(',', $this->getValidAminoAcidInputs()).']',
            ),
            array(
                'name' => 'aminoAcidTaxonB',
                'validator' => 'validateAminoAcid',
                'error' => 'Invalid Amino-acid - Taxon B: must be one of ['.implode(',', $this->getValidAminoAcidInputs()).']',
            ),
            array(
                'name' => 'molecularDetails',
                'validator' => 'validateMolecularDetails',
                'error' => 'Molecular Details : Must contain text, not only numbers',
            ),
            array(
                'name' => 'experimentalEvidence',
                'validator' => 'validateExperimentalEvidence',
                'error' => 'Experimental Evidence: cannot be empty.',
            ),
            array(
                'name' => 'mainReference',
                'validator' => 'validateMainReference',
                'error' => 'Main Reference: cannot be empty.',
            ),
        );

        $mutationsValidity = true;

        foreach ($mutations as $key => $mutation) {
            foreach ($mutationFields as $field) {
                $validator = $field['validator'];
                $getter = 'get'.ucfirst($field['name']);
                $valid = $this->$validator($mutation->$getter());
                if (!$valid) {
                    $mutationsValidity = false;
                    $this->errorMessages['mutations'][$key][$field['name']] = 'Mutation #'. ($key+1)  . ' - ' . $field['error'];
                }

            }
        }

        return $mutationsValidity;
    }

    private function validateTaxId($taxId)
    {
        if ($this->csvValidator) {
            return true;
        }

        $regex = '/^[0-9]+$/';

        if($taxId == null) {
            return false;
        }

        if (preg_match($regex, trim($taxId))) {
            return true;
        } else {
            return false;
        }
    }

    private function validateAncestralState($state)
    {
        $valid = array_keys(Entry::getAncestralList());

        $array_null = array('n/a', 'na', '', 'Not  Applicable', 'not applicable');

        if(in_array(trim($state), $array_null)) {
            return true;
        }

        if (in_array(trim($state), $valid)) {
            return true;
        } else {
            return false;
        }
    }

    private function validateTaxonomicStatus($status)
    {
        $valid = array_keys(Entry::getTaxonomicList());

        $array_null = array('n/a', 'na', '', 'Not  Applicable', 'not applicable');

        if(in_array(trim($status), $array_null)) {
            return true;
        }

        if (in_array(trim($status), $valid)) {
            return true;
        } else {
            return false;
        }
    }

    private function validateExperimentalEvidence($evidence)
    {
        $valid = array_keys(Entry::getExperimentalList());

        $array_null = array('n/a', 'na', '', 'Not  Applicable', 'not applicable');

        if(in_array(trim($evidence), $array_null)) {
            return true;
        }

        if (in_array(trim($evidence), $valid)) {
            return true;
        } else {
            return false;
        }
    }

    private function validateValidator($validator)
    {
        if ($validator === null) {
            return false;
        } else {
            return true;
        }
    }

    private function validateMolecularType($type)
    {
        $valid = array_keys(Entry::getMolecularList());

        $array_null = array('n/a', 'na', '', 'Not  Applicable', 'not applicable');

        if(in_array(trim($type), $array_null)) {
            return true;
        }

        if (in_array(trim($type), $valid)) {
            return true;
        } else {
            return false;
        }
    }

    private function validateSnp($snp)
    {
        $valid = array_keys(Entry::getSNPList());

        $array_null = array('n/a', 'na', '', 'Not  Applicable', 'not applicable', 'not curated', 'Not Curated', 'Not curated');

        if(in_array(trim($snp), $array_null)) {
            return true;
        }

        if (in_array(trim($snp), $valid)) {
            return true;
        } else {
            return false;
        }
    }

    private function validateAberrationType($type)
    {
        $valid = array_keys(Entry::getAberrationList());

        $array_null = array('n/a', 'na', '', 'Not  Applicable', 'not applicable');

        if(in_array(trim($type), $array_null)) {
            return true;
        }

        if (in_array(trim($type), $valid)) {
            return true;
        } else {
            return false;
        }
    }

    private function validateMainReference($reference)
    {
        if ($this->csvValidator && $reference === null) {
            return true;
        } elseif ($reference === null) {
            return false;
        } else {
            return $this->validatePmid($reference->getPmId());
        }
    }

    private function validateOtherReferences($references)
    {
        return true;
    }

    private function validatePmid($pmid)
    {
        $regex = '/^([0-9\.]+|No PMID)$/';

        if (preg_match($regex, trim($pmid))) {
            return true;
        } else {
            return false;
        }
    }

    private function validateNotEmptyList($value)
    {
        if($value === null) {
            return false;
        } else {
            return true;
        }
    }
}