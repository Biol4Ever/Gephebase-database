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
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use AppBundle\Model\Page\PageManager;

class PageController extends Controller
{
    /** 
     * @Route("/faq", name="page_faq")
     */
    public function viewFaqAction(Request $request)
    {
        $pageManager = $this->get('page.manager');
        $page = $pageManager->findOrCreatePage(PageManager::FAQ);

        return $this->render('default/miscPage.html.twig', array(
            'page' => $page,
        ));
    }

    /** 
     * @Route("/team", name="page_team")
     */
    public function viewTeamAction(Request $request)
    {
        $pageManager = $this->get('page.manager');
        $page = $pageManager->findOrCreatePage(PageManager::TEAM);

        return $this->render('default/miscPage.html.twig', array(
            'page' => $page,
        ));
    }

    /** 
     * @Route("/documentation", name="page_documentation")
     */
    public function viewDocumentationAction(Request $request)
    {
        $pageManager = $this->get('page.manager');
        $page = $pageManager->findOrCreatePage(PageManager::DOCUMENTATION);

        return $this->render('default/miscPage.html.twig', array(
            'page' => $page,
        ));
    }

    /** 
     * @Route("/events", name="page_events")
     */
    public function viewEventsAction(Request $request)
    {
        $pageManager = $this->get('page.manager');
        $page = $pageManager->findOrCreatePage(PageManager::EVENTS);

        return $this->render('default/miscPage.html.twig', array(
            'page' => $page,
        ));
    }

	/** 
     * @Route("/curator/faq/edit", name="curator_page_faq")
     */
    public function editFaqAction(Request $request)
    {
        $pageManager = $this->get('page.manager');

        $page = $pageManager->findOrCreatePage(PageManager::FAQ);
        $form = $this->createForm('page', $page);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $pageManager->persist($page);

            $session = $this->get('session');
            $session->getFlashbag()->add('success', 'Page successfully saved');
        }

        return $this->render('curator/page/editPage.html.twig', array(
            'form' => $form->createView(),
        ));
   	}

    /** 
     * @Route("/curator/team/edit", name="curator_page_team")
     */
    public function editTeamAction(Request $request)
    {
        $pageManager = $this->get('page.manager');

        $page = $pageManager->findOrCreatePage(PageManager::TEAM);
        $form = $this->createForm('page', $page);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $pageManager->persist($page);

            $session = $this->get('session');
            $session->getFlashbag()->add('success', 'Page successfully saved');
        }

        return $this->render('curator/page/editPage.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /** 
     * @Route("/curator/documentation/edit", name="curator_page_documentation")
     */
    public function editDocumentationAction(Request $request)
    {
        $pageManager = $this->get('page.manager');

        $page = $pageManager->findOrCreatePage(PageManager::DOCUMENTATION);
        $form = $this->createForm('page', $page);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $pageManager->persist($page);

            $session = $this->get('session');
            $session->getFlashbag()->add('success', 'Page successfully saved');
        }

        return $this->render('curator/page/editPage.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /** 
     * @Route("/curator/events/edit", name="curator_page_events")
     */
    public function editEventsAction(Request $request)
    {
        $pageManager = $this->get('page.manager');

        $page = $pageManager->findOrCreatePage(PageManager::EVENTS);
        $form = $this->createForm('page', $page);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $pageManager->persist($page);

            $session = $this->get('session');
            $session->getFlashbag()->add('success', 'Page successfully saved');
        }

        return $this->render('curator/page/editPage.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
