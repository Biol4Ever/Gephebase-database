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
use AppBundle\Entity\Entry;
use Symfony\Component\HttpFoundation\JsonResponse;


class ModalController extends Controller
{
    /**
     * @Route("/curator/entry/modal/gene-gephebase-filter", name="gene_gephebase_filter")
     */
    public function geneGephebaseFilterAction(Request $request) {
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $entries = null;
        $form = $this->createForm('geneGephebaseSeeAll');
        $form->handleRequest($request);
        if ($form->isValid()) {

            $data = $form->getData();
            $entries = $em->getRepository('AppBundle:Entry')->filterGeneGephebase($data);
            $geneGephebaseView = $this->render('curator/entry/modals/geneGephebase.html.twig', array('formGeneGephebase' => $form->createView(), 'entries' => $entries,'categoryList' => Entry::getTraitCategoryList()));

            $response->setData(array(
                'geneGephebaseView' => $geneGephebaseView->getContent(),
            ));
            return $response;
        }
        return $this->render('curator/entry/modals/geneGephebase.html.twig', array(
            'formGeneGephebase' => $form->createView(),
            'entries' => $entries,
            'categoryList' => Entry::getTraitCategoryList(),
        ));
    }

    /**
     * @Route("/curator/entry/modal/uniprot-filter", name="uniprot_filter")
     */
    public function uniprotFilterAction(Request $request) {
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $entries = null;
        $form = $this->createForm('uniprotSeeAll');
        $form->handleRequest($request);
        if ($form->isValid()) {

            $data = $form->getData();
            $entries = $em->getRepository('AppBundle:Entry')->filterUniprotkb($data);
            $uniprotView = $this->render('curator/entry/modals/uniprot.html.twig', array('formUniprot' => $form->createView(), 'entries' => $entries));

            $response->setData(array(
                'uniprotView' => $uniprotView->getContent(),
            ));
            return $response;
        }
        return $this->render('curator/entry/modals/uniprot.html.twig', array(
            'formUniprot' => $form->createView(),
            'entries' => $entries,
        ));
    }

    /**
     * @Route("/curator/entry/modal/genbank-filter", name="genbank_filter")
     */
    public function genbankFilterAction(Request $request) {
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $entries = null;
        $form = $this->createForm('genbankSeeAll');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
            $entries = $em->getRepository('AppBundle:Entry')->filterGenbank($data);
            $genbankView = $this->render('curator/entry/modals/genbank.html.twig', array('formGenbank' => $form->createView(), 'entries' => $entries));

            $response->setData(array(
                'genbankView' => $genbankView->getContent(),
            ));
            return $response;
        }
        return $this->render('curator/entry/modals/genbank.html.twig', array(
            'formGenbank' => $form->createView(),
            'entries' => $entries,
        ));
    }

    /**
     * @Route("/curator/entry/modal/trait-filter", name="trait_filter")
     */
    public function traitFilterAction(Request $request) {
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $traits = null;
        $form = $this->createForm('traitSeeAll');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
            $trait = $data['phenotypeTrait'];
            $description = $trait->getDescription();
            $category = $trait->getCategory();

            $traits = $em->getRepository('AppBundle:PhenotypeTrait')->findTraitsByFilters($description, $category);
            $traitView = $this->render('curator/entry/modals/trait.html.twig', array('formTrait' => $form->createView(), 'traits' => $traits));

            $response->setData(array(
                'traitView' => $traitView->getContent(),
            ));
            return $response;
        }

        return $this->render('curator/entry/modals/trait.html.twig', array(
            'formTrait' => $form->createView(),
            'traits' => $traits,
        ));
    }

    /**
     * @Route("/curator/entry/modal/validator_filter", name="validator_filter")
     */
    public function validatorFilterAction(Request $request) {
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm('validatorSeeAll');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $surname = $form["surname"]->getData();
            $name = $form["name"]->getData();
            $email = $form["email"]->getData();
            $validators = $em->getRepository('AppBundle:User')->validatorFilterBy($name, $surname, $email);
            $validatorView = $this->render('curator/entry/modals/validator.html.twig', array('formValidator' => $form->createView(), 'validators' => $validators ));

            $response->setData(array(
                'validatorView' => $validatorView->getContent(),
            ));
            return $response;
        }
        return $this->render('curator/entry/modals/validator.html.twig', array(
            'formValidator' => $form->createView(),
        ));
    }

    /**
     * Sort array of entries based on parameter
     */
    private function alphabeticalSort(array $entries, $key)
    {
        usort($entries, function ($item1, $item2) use ($key) {
            return strnatcasecmp($item1[$key], $item2[$key]);
        });

        return $entries;
    }
}