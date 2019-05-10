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
use Symfony\Component\Security\Core\Security;
use AppBundle\Entity\Entry;
use AppBundle\Entity\Feedback;
use AppBundle\Entity\EntryStatus;
use AppBundle\Model\Search\AdvancedSearch;
use AppBundle\Model\Search\SearchCriteria;
use AppBundle\Entity\SuggestedArticle;
use Symfony\Component\Form\FormError;

class DefaultController extends Controller
{
    /**
     * @Route("/new-view", name="change_base_view")
     */
    public function changeBaseViewAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $view = $request->request->get('submit');
        $p = $this->getDoctrine()
            ->getRepository('AppBundle:Parameter')
            ->find(1);

        if (!$p) {
            throw $this->createNotFoundException(
                'No parameters found.'
            );
        }

        $p->setBase($view);
        $em->persist($p);      
        $em->flush(); 
        return $this->redirect($request->server->get('HTTP_REFERER'));
    }

    public function renderBaseAction() {
        $parameters = $this->getDoctrine()
            ->getRepository('AppBundle:Parameter')
            ->find(1);
        return $this->render('renderBase.html.twig', array('parameters' => $parameters));
    }

    public function footerAction() {
        $parameters = $this->getDoctrine()
            ->getRepository('AppBundle:Parameter')
            ->find(1);
        return $this->render('default/footer.html.twig', array('parameters' => $parameters));
    }

    public function importAction() {
        $importForm = $this->createForm('importGephe');

        return $this->render('curator/import_entries.html.twig', array(
            'importForm' => $importForm->createView(),
        ));
    }

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $homepageParameters = $this->getDoctrine()
            ->getRepository('AppBundle:Parameter')
            ->find(1);

        if (!$homepageParameters) {
            throw $this->createNotFoundException(
                'No parameters found.'
            );
        }

        $references = $this->getDoctrine()->getRepository('AppBundle:Reference')->findLatestsReferences();

        $securityContext = $this->container->get('security.authorization_checker');
        $user = $this->getUser();
        $date = new \DateTime();
        $dateWeek = new \DateTime();
        $dateWeek->add(new \DateInterval('P30D'));
        if ( ($securityContext->isGranted('ROLE_CURATOR')) && ($user->getCredentialsExpireAt() > $date) && ($user->getCredentialsExpireAt() < $dateWeek) ) {
            $interval = $date->diff($user->getCredentialsExpireAt());
            $day = $interval->format("%d");
            $hour = $interval->format("%H");
            $minute = $interval->format("%i");
            $second = $interval->format("%s");
            $info_message = 'Your password will expires in '.$day.' day(s) and '.$hour.' hour(s) and '.$minute.' minute(s) and '.$second. ' second(s)';
        } else {
            $info_message = null;
        }
        return $this->render('default/home.html.twig', array('parameters' => $homepageParameters, 'info_message' => $info_message, 'references' => $references));
    }

    /**
     * @Route("/curator-login", name="curator_login")
     */

    public function curatorLoginAction(Request $request)
    {
         $session = $request->getSession();

        if (class_exists('\Symfony\Component\Security\Core\Security')) {
            $authErrorKey = Security::AUTHENTICATION_ERROR;
            $lastUsernameKey = Security::LAST_USERNAME;
        }

        // get the error if any
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif (null !== $session && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get($lastUsernameKey);

        if($error) {
            $this->get('log.creator')->createLogError($lastUsername, $error->getMessage(), new \DateTime());
        }


        // check if credentials have expired
        if (is_a($error, 'Symfony\Component\Security\Core\Exception\CredentialsExpiredException')) {
            $parameter = $this->getDoctrine()->getRepository('AppBundle:Parameter')->findOneById(1);
            $mail = $parameter->getLoginMail();
            $url = $this->container->get('router')->getContext()->getScheme()."://";
            $url .= $this->container->get('router')->getContext()->getHost();
            $url .= $this->container->get('router')->generate('list_user');
            $message = \Swift_Message::newInstance()
                ->setSubject('GEPHEBASE - account expired')
                ->setFrom($parameter->getFromMail())
                ->setTo($mail)
                ->setBody('<html><body><p style="font-size: 16px">Hello administrator, <br/><br/> The account <h1><strong>'.$lastUsername.'</strong></h1> tried to connect as curator but his password is expired. Can you update his password ? <br /><br /> <a href="'.$url.'">Click here to access user\'s list</a><br />'.$url.'</p></body></html>', 'text/html')
                ->addPart('Hello administrator, \n \n The account '.$lastUsername.' tried to connect as curator but his password is expired. Can you update his password ? \n \n'.$url,'text/plain')
            ;
            $this->get('mailer')->send($message);
            $info_message = "An email has been send to the administrator, who will be notified that your account has expired.";
        } else {
            $info_message = null;
        }
        $csrfToken = $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue();

        return $this->render('default/login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token' => $csrfToken,
            'info_message' => $info_message,
        ));
    }

    /**
     * @Route("/forgot-password", name="forgot_password")
     */

    public function forgotPasswordAction(Request $request)
    {
        $form = $this->createForm('forgotPassword');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $email = $request->get('forgotPassword')['email'];
            $user = $em->getRepository('AppBundle:User')->findOneByEmail($email);
            if($user) {
                $parameter = $this->getDoctrine()->getRepository('AppBundle:Parameter')->findOneById(1);
                $mail = $parameter->getLoginMail();
                $url = $this->container->get('router')->getContext()->getScheme()."://";
                $url .= $this->container->get('router')->getContext()->getHost();
                $url .= $this->container->get('router')->generate('list_user');
                $message = \Swift_Message::newInstance()
                    ->setSubject('GEPHEBASE - account forgot password')
                    ->setFrom($parameter->getFromMail())
                    ->setTo($mail)
                    ->setBody('<html><body><p style="font-size: 16px">Hello administrator, <br/><br/> The account <h1><strong>'.$user->getUsername().'</strong></h1> forgot his password. Can you update his password and send him at '.$user->getEmail(). '  and ask him to change it at the first connection. <br /><br /> <a href="'.$url.'">Click here to access user\'s list</a></p></body></html>', 'text/html')
                    ->addPart('Hello administrator, \n \n The account '.$user->getUsername().' forgot his password. Can you update his password and send him at '.$user->getEmail(). '  and ask him to change it at the first connection. \n \n ','text/plain')
                ;
                $this->get('mailer')->send($message);
                 $homepageParameters = $this->getDoctrine()
                    ->getRepository('AppBundle:Parameter')
                    ->find(1);
                 $this->get('log.creator')->createLogError($email, 'Forgot password.', new \DateTime());
                return $this->render('default/home.html.twig', array('parameters' => $homepageParameters, 'info_message' => 'Mail sent to administrator.'));
            } else {
                $form->get('email')->addError(new FormError('Address email not found.'));
            }
        }

        return $this->render('default/forgot.html.twig', array(
            'form' => $form->createView(),
        ));
    }

   	/**
     * @Route("/advanced-search", name="advanced_search")
     */
    public function advancedSearchAction(Request $request)
    {
        $firstCriteria = new SearchCriteria();
        $firstCriteria->setTerm('');
        $firstCriteria->setField(0);
        $firstCriteria->setOperator('and');
        $search = new AdvancedSearch();
        $search->addSearchCriteria($firstCriteria);

        $searchForm = $this->createForm('advancedSearch', $search, array(
            'action' => $this->generateUrl('search_criteria') . '#gephebase-summary-title',            
            'method' => 'GET',
            'attr' => array(
                'id' => 'search_id',
            )
        ));

        $securityContext = $this->container->get('security.authorization_checker');

        if($securityContext->isGranted('ROLE_CURATOR')) {
            $statusList = Entry::getStatusList();
        } else {
            $statusList = Entry::getStatusUserList();
        }

        return $this->render('default/advancedSearch.html.twig', array(
            'searchForm' => $searchForm->createView(),
            'traitList' => Entry::getTraitCategoryList(),
            'statusList' => $statusList,
            'taxonomicList' => Entry::getTaxonomicList(),
            'experimentalList' => Entry::getExperimentalList(),
            'molecularList' => Entry::getMolecularList(),
            'snpList' => Entry::getSNPList(),
            'aberrationList' => Entry::getAberrationList(),
            'aberrationSizeList' => Entry::getAberrationSizeList(),
            'presumptiveNullList' => Entry::getPresumptiveNullList(),
        ));
   	}

    /**
     * @Route("/search-criteria", name="search_criteria")
     */
    public function searchCriteriaAction(Request $request)
    {
        $entries = array();
        $query = current($request->server)['QUERY_STRING'];
        if (empty($query)) {
            $search = null;
        } else {
            $builder = $this->get('search.builder');
            $search = $builder->buildSearchfromQueryString($query);
        }

        $searchForm = $this->createForm('advancedSearch', $search, array(
            'action' => $this->generateUrl('search_criteria') . '#gephebase-summary-title',            
            'method' => 'GET',
            'attr' => array(
                'id' => 'search_id',
            )
        ));

        $securityContext = $this->container->get('security.authorization_checker');
        $searchBuilder = $this->container->get('search.builder');

        $searchForm->handleRequest($request);

        $entries = array();

        $search = $searchForm->getData();


        $array_ids = $this->getDoctrine()->getRepository('AppBundle:Entry')->advancedSearch($search, $securityContext);

        if (empty($array_ids)) {
            $entries = array();
        } else {
            $entries = $this->getDoctrine()->getRepository('AppBundle:Entry')->retrieveAllData($array_ids, $search, $searchBuilder);
        }

        if($securityContext->isGranted('ROLE_CURATOR')) {
            $statusList = Entry::getStatusList();
        } else {
            $statusList = Entry::getStatusUserList();
        }

        $criterias = $searchForm->get('searchCriterias');
        foreach($criterias as $criteria) {
            // replace the first publication date found
            $currentField = $criteria->get('field');
            $field = $currentField->getData();

            if ($field == 29) {
                $currentField->setData(24);
                $prevCriteria = $criteria;
            }
            if ($field == 30) {
                if (!isset($prevCriteria)) {
                    // there is no previous criteria, so this is the only publication date field
                    $currentField->setData(24);
                    $criteria->get('term2')->setData($criteria->get('term')->getData());
                    $criteria->get('term')->setData("");
                } else {
                    $prevCriteria->get('term2')->setData($criteria->get('term')->getData());
                    $criterias->remove($criteria->getName());
                }
            }
        }
        return $this->render('default/summaryTable.html.twig', array(
            'entries' => $entries,
            'searchForm' => $searchForm->createView(),
            'traitList' => Entry::getTraitCategoryList(),
            'statusList' => $statusList,
            'taxonomicList' => Entry::getTaxonomicList(),
            'experimentalList' => Entry::getExperimentalList(),
            'molecularList' => Entry::getMolecularList(),
            'snpList' => Entry::getSNPList(),
            'aberrationList' => Entry::getAberrationList(),
            'aberrationSizeList' => Entry::getAberrationSizeList(),
            'presumptiveNullList' => Entry::getPresumptiveNullList(),
        ));
    }

   	/**
     * @Route("/summary-table", name="summary_table")
     */
    public function summaryTableAction(Request $request)
    {
        $value = trim($request->get('value'));
        $firstCriteria = new SearchCriteria();
        $firstCriteria->setField(0);
        $firstCriteria->setOperator('and');

        if ($value) {
            $firstCriteria->setTerm($value);
            $securityContext = $this->container->get('security.authorization_checker');
            $entries = $this->getDoctrine()
                     ->getRepository('AppBundle:Entry')
                     ->simpleSearch($value, $securityContext);
            $array_ids = array();
            foreach($entries as $entry) {
                $array_ids[] = $entry['id'];
            }
            $entries = $this->getDoctrine()->getRepository('AppBundle:Entry')->retrieveAllData($array_ids);
        } else {
            $entries = array();
        }

        $search = new AdvancedSearch();
        $search->addSearchCriteria($firstCriteria);

        $searchForm = $this->createForm('advancedSearch', $search, array(
            'action' => $this->generateUrl('search_criteria') . '#gephebase-summary-title',
        ));

        $securityContext = $this->container->get('security.authorization_checker');

        if($securityContext->isGranted('ROLE_CURATOR')) {
            $statusList = Entry::getStatusList();
        } else {
            $statusList = Entry::getStatusUserList();
        }

        return $this->render('default/summaryTable.html.twig', array(
            'searchForm' => $searchForm->createView(),
            'entries' => $entries,
            'array'   => Entry::getCriteriaList(),
            'value'   => $value,
            'traitList' => Entry::getTraitCategoryList(),
            'statusList' => $statusList,
            'taxonomicList' => Entry::getTaxonomicList(),
            'experimentalList' => Entry::getExperimentalList(),
            'molecularList' => Entry::getMolecularList(),
            'snpList' => Entry::getSNPList(),
            'aberrationList' => Entry::getAberrationList(),
            'aberrationSizeList' => Entry::getAberrationSizeList(),
            'presumptiveNullList' => Entry::getPresumptiveNullList(),
        ));
   	}

   	/**
     * @Route("/view-gephe/{id}", name="view_entry")
     */
    public function previewAction(Request $request, $id)
    {
        $entry = $this->getDoctrine()->getRepository('AppBundle:Entry')->find($id);

        if (!$entry) {
            throw $this->createNotFoundException('Entry not found.');
        }
        $searchBuilder = $this->get('search.builder');
        $relatedGenes = $searchBuilder->findRelatedGenes($entry);
        $relatedHaplotypes = $searchBuilder->findRelatedHaplotypes($entry);
        $feedback = new Feedback();
        $feedback->setEntry($entry);
        $feedbackForm = $this->createForm('feedback', $feedback);
        $urlSearch = $request->server->get('HTTP_REFERER');
        return $this->render('entry/view/view.html.twig', array(
            "entry" => $entry,
            "urlSearch" => $urlSearch,
            'traitList' => Entry::getTraitCategoryList(),
            'experimentalList' => Entry::getExperimentalList(),
            'ancestralList' => Entry::getAncestralList(),
            'relatedGenes' => $relatedGenes,
            'relatedMutations' => $relatedHaplotypes,
            'feedbackForm' => $feedbackForm->createView(),
        ));
   	}

    /**
     * @Route("/feedback/submit", name="feedback_submit")
     */
    public function feedbackSubmitAction(Request $request)
    {
        $feedbackForm = $this->createForm('feedback');

        $feedbackForm->handleRequest($request);

        if ($feedbackForm->isValid()) {
            $feedback = $feedbackForm->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($feedback);
            $em->flush();

            $status = 'success';
        } else {
            $status = 'failure';
        }

        return new JsonResponse(array('status' => $status));
    }

    /**
     * @Route("/export-csv", name="export_csv")
     */
    public function exportCsvAction(Request $request)
    {
        if($request->request->get('exportMethod') == 'simple') {
            if($request->request->get('mutations') != null) {
                $entries = $this->getDoctrine()->getRepository('AppBundle:Mutation')->tableExportByMutationId($request->request->get('mutations'));



                $response = $this->get('export.entry')->ExportCsvComplete($entries);
                
                return $response;
            } else {
                return $this->redirect($request->server->get('HTTP_REFERER'));
            }
        } else {

            if($request->request->get('entries') != null) {
                $entries = $this->getDoctrine()->getRepository('AppBundle:Entry')->selectAllExportCsvComplete($request->request->get('entries'));
                $response = $this->get('export.entry')->ExportCsvComplete($entries);

                return $response;
            } else {
                return $this->redirect($request->server->get('HTTP_REFERER'));
            }
        }
    }

    /**
     *  @Route("/ajax-entries-field", name="ajax_entries_field")
     */   

    public function ajaxEntriesFieldAction(Request $request) 
    {
        $term = $request->get('query');
        if($request->get('field') != "") {
            $field = $request->get('field');
        } else {
            $field = 0;
        }
        $securityContext = $this->container->get('security.authorization_checker');
        if($field != null OR $term != null) {
            $entries = $this->getDoctrine()
            ->getRepository('AppBundle:Entry')
            ->searchWithField($field, $term, $securityContext);
        }

        $field2 = null;
        $field3 = null;
        $field4 = null;
        switch($field) {
            case 0:
                $field = 'All';
                break;
            case 1:
                $field = 'status';
                break;
            case 2:
                $field = 'username';
                $field2 = 'surname';
                break;
            case 3:
                $field = 'validatorName';
                $field2 = 'validatorSurname';
                break;
            case 4:
                $field = 'gene';
                $field2 = 'synonym';
                break;
            case 6:
                $field = 'description';
                break;
            case 7:
                $field = 'category';
                break;
            case 8:
                $field = 'taxAID';
                $field2 = 'taxBID';
                break;
            case 9:
                $field = 'latinAName';
                $field2 = 'commonAName';
                $field3 = 'latinBName';
                $field4 = 'commonBName';
                break;
            case 10:
                $field = 'taxonomic';
                break;
            case 11:
                $field = 'experimental';
                break;
            case 12:
                $field = 'details';
                break;
            case 13:
                $field = 'molecular';
                break;
            case 14:
                $field = 'SNP';
                break;
            case 15:
                $field = 'aberrationType';
                break;
            case 16:
                $field = 'article';
                $field2 = 'otherArticle';
                break;
            case 17:
                $field = 'mainAuthor';
                $field2 = 'otherAuthor';
                break;
            case 18:
                $field = 'year';
                break;
            case 19:
                $field = 'abstract';
                break;
            case 20:
                $field = 'goMID';
                $field2 = 'goCID';
                $field3 = 'goBID';
                break;
            case 21:
                $field = 'uniProtKbID';
                break;
            case 22:
                $field = 'gepheID';
                break;
            case 23:
                $field = 'genbank';
                break;
            case 25:
                $field = 'geneGephebase';
                break;
            case 27:
                $field = 'latinAName';
                $field2 = 'taxAID';
                $field3 = 'commonAName';
                break;
            case 28:
                $field = 'latinBName';
                $field2 = 'taxBID';
                $field3 = 'commonBName';
                break; 


        }
        $count = 0;
        $array = array();
        foreach($entries as $entry) {
            foreach($entry as $key => $column) {
                $bool = strpos(strtolower($column), strtolower($term));
                if($key == $field OR $key == $field2 OR $key == $field3 OR $key == $field4 OR $field == "All") {
                    if($bool !== false) {
                        // Create an array for each different value
                        switch($key) {
                            case "genbank": 
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Genbank ID";
                                $array[$count]['id']   = $key; 
                                break;
                            case "gepheID" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Gephe ID";
                                $array[$count]['id']   = $key; 
                                break;
                            case "geneGephebase" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Gene-Gephebase";
                                $array[$count]['id']   = $key; 
                                break;
                            case "uniProtKbID" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Uniprotkb ID";
                                $array[$count]['id']   = $key; 
                                break;
                            case "goMID" : 
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Go Molecular";
                                $array[$count]['id']   = $key; 
                                break;
                            case "goCID" : 
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Go Celullar";
                                $array[$count]['id']   = $key; 
                                break;
                            case "goBID" : 
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Go Biological";
                                $array[$count]['id']   = $key; 
                                break;
                            case "abstract":
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Abstract";
                                $array[$count]['id']   = $key; 
                                break;
                            case "year" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Year";
                                $array[$count]['id']   = $key; 
                                break;
                            case "mainAuthor" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Author";
                                $array[$count]['id']   = $key; 
                                break;
                            case "otherAuthor" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Author";
                                $array[$count]['id']   = $key; 
                                break;
                            case "otherArticle" : 
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Article";
                                $array[$count]['id']   = $key; 
                                break;
                            case "article" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Article";
                                $array[$count]['id']   = $key; 
                                break;
                            case "aberrationType" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Aberration Type";
                                $array[$count]['id']   = $key; 
                                break;
                            case "SNP" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "SNP coding change";
                                $array[$count]['id']   = $key; 
                                break;
                            case "molecular" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Molecular Type";
                                $array[$count]['id']   = $key; 
                                break;
                            case "details" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Molecular Details";
                                $array[$count]['id']   = $key; 
                                break;
                            case "experimental" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Experimental Evidence";
                                $array[$count]['id']   = $key; 
                                break;
                            case "taxonomic" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Taxonomic Status";
                                $array[$count]['id']   = $key; 
                                break;
                            case "latinAName" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Taxon";
                                $array[$count]['id']   = $key; 
                                break;
                            case "commonAName" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Taxon";
                                $array[$count]['id']   = $key; 
                                break;
                             case "latinBName" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Taxon";
                                $array[$count]['id']   = $key; 
                                break;
                            case "commonBName" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Taxon";
                                $array[$count]['id']   = $key; 
                                break;
                            case "taxAID" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Taxon ID";
                                $array[$count]['id']   = $key; 
                                break;
                            case "taxBID" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Taxon ID";
                                $array[$count]['id']   = $key; 
                                break;
                            case "category" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Trait Category";
                                $array[$count]['id']   = $key; 
                                break;
                            case "description" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Trait";
                                $array[$count]['id']   = $key; 
                                break;
                            case "gene" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Gene";
                                $array[$count]['id']   = $key; 
                                break;
                            case "synonym" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Synonym";
                                $array[$count]['id']   = $key; 
                                break;
                            case "validatorName" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Validator";
                                $array[$count]['id']   = $key; 
                                break;
                            case "validatorSurname" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Validator";
                                $array[$count]['id']   = $key; 
                                break;
                            case "username" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Curator";
                                $array[$count]['id']   = $key; 
                                break;
                            case "surname" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Curator";
                                $array[$count]['id']   = $key; 
                                break;
                            case "status" :
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Status";
                                $array[$count]['id']   = $key; 
                                break;
                        }
                        $count = $count + 1;
                    }
                }
            }
        }
        $uarray = array();
        foreach($array as $element) {
            $hash = strtolower($element["name"]);
            $uarray[$hash] = $element;
        }
        return new JsonResponse($uarray);
    }

    /**
     *  @Route("/ajax-entries", name="ajax_entries")
     */   
    public function ajaxEntriesAction(Request $request)
    {
        $term = $request->get('query');
        $field = 0;
        $securityContext = $this->container->get('security.authorization_checker');
        $entries = $this->getDoctrine()
            ->getRepository('AppBundle:Entry')
            ->searchWithField($field, $term, $securityContext);

        $count = 0;
        $array = array();
        // Create an array to know from where data the query has been found
        foreach($entries as $entry) {
            foreach($entry as $key => $column) {
                $bool = strpos(strtolower($column), strtolower($term));
                if($bool !== false) {
                    // Create an array for each different value
                    switch($key) {
                        case "genbank": 
                                $array[$count]['name'] = $column;
                                $array[$count]['key']  = "Genbank ID";
                                $array[$count]['id']   = $key; 
                            break;
                        case "gepheID" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Gephe ID";
                            $array[$count]['id']   = $key; 
                            break;
                        case "geneGephebase" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Gene-Gephebase";
                            $array[$count]['id']   = $key; 
                            break;
                        case "uniProtKbID" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Uniprotkb ID";
                            $array[$count]['id']   = $key; 
                            break;
                        case "goMID" : 
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Go Molecular";
                            $array[$count]['id']   = $key; 
                            break;
                        case "goCID" : 
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Go Celullar";
                            $array[$count]['id']   = $key; 
                            break;
                        case "goBID" : 
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Go Biological";
                            $array[$count]['id']   = $key; 
                            break;
                        case "abstract":
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Abstract";
                            $array[$count]['id']   = $key; 
                            break;
                        case "year" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Year";
                            $array[$count]['id']   = $key; 
                            break;
                        case "mainAuthor" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Author";
                            $array[$count]['id']   = $key; 
                            break;
                        case "otherAuthor" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Author";
                            $array[$count]['id']   = $key; 
                            break;
                        case "otherArticle" : 
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Article";
                            $array[$count]['id']   = $key; 
                            break;
                        case "article" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Article";
                            $array[$count]['id']   = $key; 
                            break;
                        case "aberrationType" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Aberration Type";
                            $array[$count]['id']   = $key; 
                            break;
                        case "SNP" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "SNP coding change";
                            $array[$count]['id']   = $key; 
                            break;
                        case "molecular" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Molecular Type";
                            $array[$count]['id']   = $key; 
                            break;
                        case "details" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Molecular Details";
                            $array[$count]['id']   = $key; 
                            break;
                        case "experimental" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Experimental Evidence";
                            $array[$count]['id']   = $key; 
                            break;
                        case "taxonomic" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Taxonomic Status";
                            $array[$count]['id']   = $key; 
                            break;
                        case "latinAName" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Taxon";
                            $array[$count]['id']   = $key; 
                            break;
                        case "commonAName" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Taxon";
                            $array[$count]['id']   = $key; 
                            break;
                         case "latinBName" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Taxon";
                            $array[$count]['id']   = $key; 
                            break;
                        case "commonBName" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Taxon";
                            $array[$count]['id']   = $key; 
                            break;
                        case "taxAID" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Taxon ID";
                            $array[$count]['id']   = $key; 
                            break;
                        case "taxBID" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Taxon ID";
                            $array[$count]['id']   = $key; 
                            break;
                        case "category" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Trait Category";
                            $array[$count]['id']   = $key; 
                            break;
                        case "description" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Trait";
                            $array[$count]['id']   = $key;
                            break;
                        case "gene" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Gene";
                            $array[$count]['id']   = $key; 
                            break;
                        case "synonym" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Synonym";
                            $array[$count]['id']   = $key; 
                            break;
                        case "validatorName" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Validator";
                            $array[$count]['id']   = $key; 
                            break;
                        case "validatorSurname" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Validator";
                            $array[$count]['id']   = $key; 
                            break;
                        case "username" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Curator";
                            $array[$count]['id']   = $key; 
                            break;
                        case "status" :
                            $array[$count]['name'] = $column;
                            $array[$count]['key']  = "Status";
                            $array[$count]['id']   = $key; 
                            break;
                    }
                    $count = $count + 1;
                }
            }
        }

        $uarray = array();
        foreach($array as $element) {
            $hash = strtolower($element["name"]);
            $uarray[$hash] = $element;
        }

        return new JsonResponse($uarray);
    }

    /**
     * Display a button and popup which allows users to suggest Articles via PMID or DOI
     */
    public function suggestArticlesAction()
    {
        $suggestArticleForm = $this->createForm('suggestArticle');

        return $this->render(
            'default/suggestArticles.html.twig',
            array(
                'suggestArticlesForm' => $suggestArticleForm->createView(),
            )
        );
    }

    /**
     * @Route("/suggest-article", name="suggest_article_submit")
     */
    public function submitSuggestedArticle(Request $request)
    {
        $responseData = array();
        $suggestionForm = $this->createForm('suggestArticle');
        $suggestionForm->handleRequest($request);

        if ($suggestionForm->isValid()) {
            $suggestion = $suggestionForm->getData();

            $referenceManager = $this->get('reference.manager');
            $retriever = $this->get('entry.data.retriever');
            $id = $suggestion->getArticleId();
            $isDoi = $referenceManager->isDoi($id);

            // First we set the article Type based on the id passed as parameter
            if ($isDoi) {
                $suggestion->setIdType(SuggestedArticle::DOI);
                $id = $this->filterDOIstring($suggestion->getArticleId());
                $suggestion->setArticleId($id);
            } else {
                $suggestion->setIdType(SuggestedArticle::PMID);
            }

            $reference = $this->getDoctrine()->getRepository('AppBundle:Reference')->findByIdentifier($id);
            if($reference) {
                $responseData['status'] = 'already'; 
            } else {
                // try to create a reference object based on passed parameter
                $reference = $retriever->retrieveReferenceEntityFromId($id);

                // if we found a reference entity, attach it to the suggestion
                if ($reference) {
                    $suggestion->setReference($reference);
                }

                if ($reference || $isDoi) {
                    $suggestion->setSubmissionDate(new \DateTime());
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($suggestion);
                    $em->flush();

                    $view = $this->render('default/suggestedArticleSuccess.html.twig', array(
                        'suggestion' => $suggestion,
                    ));


                    $responseData['status'] = 'success';
                    $responseData['view'] = $view->getContent();
                } else {
                    $responseData['status'] = 'failure';
                }
            }   
        } else {
            $responseData['status'] = 'recaptcha';
        }

        return new JsonResponse($responseData);
    }

    private function filterDOIstring($string) {
        $string = trim(trim($string), '.');
        if(stristr($string, 'https://doi.org/')) {
            return substr($string, 16);
        } else if(stristr($string, 'http://doi.org/')) {
            return substr($string, 15);
        } else if (stristr($string, 'doi: ')) {
            return substr($string, 5);
        } else if (stristr($string, 'doi:')) {
            return substr($string, 4);
        } else if (stristr($string, 'https://dx.doi.org/')) {
            return substr($string, 19);
        } else if (stristr($string, 'http://dx.doi.org/')) {
            return substr($string, 18);
        }
        return $string;
    }
}
