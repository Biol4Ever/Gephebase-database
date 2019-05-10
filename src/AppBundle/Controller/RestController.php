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

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\Route;
use AppBundle\Entity\User;

/**
 * @RouteResource("Api")
 */

class RestController extends FOSRestController
{

    /**
     * @Route("/api/gephe/{gepheId}")
     */
	public function getGepheAction($gepheId = null)
	{
		$em = $this->getDoctrine()->getManager();
		if($gepheId == "all") {
			$data = $em->getRepository('AppBundle:Entry')->findAll();
		} else {
			if(strpos($gepheId, ',')) {
				$array = explode(',', $gepheId);
				foreach ($array as $key => $entry) {
					if(strpos($entry, 'P')) {
						$array[$key] = ltrim(substr($entry, strpos($entry, 'P')+1),"0");
					}
				}
			} elseif(strpos($gepheId, '-')) {
				$gepheIdprev = substr($gepheId, 0, strpos($gepheId, '-'));
				$gepheIdnext = substr($gepheId, strpos($gepheId, '-') + 1);
				if(strpos($gepheIdprev, 'P')) {
					$gepheIdprev = ltrim(substr($gepheIdprev, strpos($gepheIdprev, 'P')+1),"0");
				}
				if(strpos($gepheIdnext, 'P')) {
					$gepheIdnext = ltrim(substr($gepheIdnext, strpos($gepheIdnext, 'P')+1),"0");
				}
				for($i = $gepheIdprev; $i <= $gepheIdnext; $i++) {
					$array[] = $i;
				}
			} else {
				if(strpos($gepheId, 'P')) {
					$gepheId = ltrim(substr($gepheId, strpos($gepheId, 'P')+1),"0");
				}
				$array[] = $gepheId;
			}
		    $data = $em->getRepository('AppBundle:Entry')->findAllByGephe($array);
		}

	    foreach($data as $entry) {
	    	$entry->unsetMainCurator();
	    	$entry->unsetValidator();
	    }
	    $view = $this->view($data, 200);
	    return $this->handleView($view);
	}

	/**
     * @Route("/api/trait/{trait}")
     */
	public function getTraitAction($trait = null)
	{
		$em = $this->getDoctrine()->getManager();
	    $data = $em->getRepository('AppBundle:Entry')->findAllByTraits($trait);
	    foreach($data as $entry) {
	    	$entry->unsetMainCurator();
	    	$entry->unsetValidator();
	    }
	    $view = $this->view($data, 200);
	    return $this->handleView($view);
	}

	/**
     * @Route("/api/trait/category/{category}")
     */
	public function getTraitCategoryAction($category = null)
	{
		$em = $this->getDoctrine()->getManager();
	    $data = $em->getRepository('AppBundle:Entry')->findAllByTraitCategory($category);

	    foreach($data as $entry) {
	    	$entry->unsetMainCurator();
	    	$entry->unsetValidator();
	    }
	    $view = $this->view($data, 200);
	    return $this->handleView($view);
	}

	/**
     * @Route("/api/experimental/{experimental}")
     */
	public function getExperimentalAction($experimental = null)
	{
		$em = $this->getDoctrine()->getManager();
	    $data = $em->getRepository('AppBundle:Entry')->findAllByExperimental($experimental);

	    foreach($data as $entry) {
	    	$entry->unsetMainCurator();
	    	$entry->unsetValidator();
	    }
	    $view = $this->view($data, 200);
	    return $this->handleView($view);
	}

	/**
     * @Route("/api/gene/{name}")
     */
	public function getGeneAction($name = null)
	{
		$em = $this->getDoctrine()->getManager();
	    $data = $em->getRepository('AppBundle:Entry')->findAllByGeneName($name);

	    foreach($data as $entry) {
	    	$entry->unsetMainCurator();
	    	$entry->unsetValidator();
	    }
	    $view = $this->view($data, 200);
	    return $this->handleView($view);
	}

	/**
     * @Route("/api/taxon/{name}")
     */
	public function getTaxonAction($taxon = null)
	{
		$em = $this->getDoctrine()->getManager();
	    $data = $em->getRepository('AppBundle:Entry')->findAllByTaxon($taxon);

	    foreach($data as $entry) {
	    	$entry->unsetMainCurator();
	    	$entry->unsetValidator();
	    }
	    $view = $this->view($data, 200);
	    return $this->handleView($view);
	}
}