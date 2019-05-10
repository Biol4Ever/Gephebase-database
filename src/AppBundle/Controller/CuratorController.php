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

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use AppBundle\Entity\EntryStatus;
use AppBundle\Entity\Entry;
use AppBundle\Entity\Gene;
use AppBundle\Entity\PhenotypeTrait;
use AppBundle\Entity\ComplexTrait;
use AppBundle\Entity\ComplexTaxon;
use AppBundle\Entity\Mutation;
use AppBundle\Entity\Taxon;
use AppBundle\Entity\OldPassword;
use AppBundle\Entity\Reference;
use AppBundle\Entity\RejectedReference;
use AppBundle\Entity\User;
use Doctrine\ORM\Query\ResultSetMapping;

class CuratorController extends Controller
{
    private $originalOtherReferences;

    /**
     * @Route("/curator/entry-page", name="entry_page")
     */
    public function entryPageAction(Request $request)
    {
        $importForm = $this->createForm('importGephe');

        return $this->render('curator/entryPage.html.twig', array(
            'importForm' => $importForm->createView(),
        ));
    }

    /**
     * @Route("/curator/new-entry", name="new_entry")
     */
    public function newEntryAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entry = new Entry();
        $entry->addTrait(new ComplexTrait());
        $entry->addTaxonAList(new ComplexTaxon());
        $entry->addTaxonBList(new ComplexTaxon());
        $entry->addMutation(new Mutation());
        $persistedData = $this->fetchPersistedData($entry);

        $form = $this->createForm('entry', $entry);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $validator = $this->get('entry.validator');
            $entryForm = $request->request->get('entry');
            $entry = $this->handleRelations($form->getData(), $em, $persistedData, $entryForm);
            $this->fixHandleRequest($entryForm, $entry, $em);
            $v = $validator->validate($entry);

            if ($form->get('save_draft')->isClicked() || $form->get('save_draft_menu')->isClicked()) {
                $temporary = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::TEMPORARY);
                $entry->setStatus($temporary);
                $entry->setMainCurator($this->getUser());
                $em->persist($entry);
                $em->flush();
                $this->get('log.creator')->createLog($entry, 'New Entry : Entry Saved as Draft-Temporary', $temporary->getName());

                return $this->redirectToRoute('edit_entry', array('id' => $entry->getId(), 'info_message' => Entry::MESSAGE_TEMPORARY));
            } elseif ($form->get('review_submit')->isClicked()) {
                if (!$v) {
                    $temporary = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::TEMPORARY);
                    $entry->setStatus($temporary);
                    $entry->setMainCurator($this->getUser());
                    $em->persist($entry);
                    $em->flush();
                    $entities = $validator->getErrorMessages();
                    $form = $this->createForm('entry', $entry);
                    foreach ($entities as $key => $entity) {
                        foreach ($entity as $name => $error) {
                            if ('entry' == $key) {
                                $form->get($name)->addError(new FormError($error));
                            } elseif ('otherReferences' == $key) {
                                $form->get('addOtherReferencePmid')->addError(new FormError('PMID which have not been imported : '.implode(';', $entry->getTempOtherPmid())));
                            } else {
                                $form->get($key)->get($name)->addError(new FormError($error));
                            }
                        }
                    }
                    $this->get('log.creator')->createLog($entry, 'New Entry : Validation Failed', $temporary->getName());

                    return $this->redirectToRoute('edit_entry', array('id' => $entry->getId(), 'info_message' => Entry::MESSAGE_TEMPORARY_ERROR));
                } elseif ($form->get('delete')->isClicked()) {
                    $deleted = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::DELETED);
                    $entry->setStatus($deleted);
                    $em->persist($entry);
                    $em->flush();
                    $this->get('log.creator')->createLog($entry, 'New Entry : Entry Deleted', $deleted->getName());

                    $homepageParameters = $this->getDoctrine()->getRepository('AppBundle:Parameter')->find(1);
                    $references = $this->getDoctrine()->getRepository('AppBundle:Reference')->findLatestsReferences();

                    return $this->render('default/home.html.twig', array('parameters' => $homepageParameters, 'info_message' => 'Gephe '.$entry->getGepheId().' Saved as Draft-Deleted', 'references' => $references));
                } else {
                    $temporary = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::TEMPORARY);
                    $entry->setStatus($temporary);
                    $entry->setMainCurator($this->getUser());
                    $em->persist($entry);
                    $em->flush();
                    $this->get('log.creator')->createLog($entry, 'New Entry : Entry Validated', $temporary->getName());

                    return $this->redirectToRoute('edit_entry', array('id' => $entry->getId(), 'info_message' => Entry::MESSAGE_VALID));
                }
            } else {
                $em->remove($entry);
                $em->flush();
                $this->get('log.creator')->createLog($entry, 'New Entry Abort', "User doesn't save the entry");

                return $this->redirectToRoute('new_entry');
            }
        }

        return $this->render('curator/newEntry.html.twig', array(
            'form' => $form->createView(),
            'entry_id' => $entry->getId(),
        ));
    }

    /**
     * @Route("/curator/complex-new-entry", name="new_entry_complex")
     */
    public function newEntryComplexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entry = new Entry();
        $entry->addTrait(new ComplexTrait());
        $entry->addTaxonAList(new ComplexTaxon());
        $entry->addTaxonBList(new ComplexTaxon());
        $entry->addMutation(new Mutation());
        $persistedData = $this->fetchPersistedData($entry);

        $form = $this->createForm('entry', $entry, array('complex' => true));
        $form->handleRequest($request);
        if ($form->isValid()) {
            $validator = $this->get('entry.validator');
            $entryForm = $request->request->get('entry');
            $entry = $this->handleRelations($form->getData(), $em, $persistedData, $entryForm);
            $this->fixHandleRequest($entryForm, $entry, $em);
            $v = $validator->validate($entry);

            if ($form->get('save_draft')->isClicked() || $form->get('save_draft_menu')->isClicked()) {
                $temporary = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::TEMPORARY);
                $entry->setStatus($temporary);
                $entry->setMainCurator($this->getUser());
                $em->persist($entry);
                $em->flush();
                $this->get('log.creator')->createLog($entry, 'New Entry : Entry Saved as Draft-Temporary', $temporary->getName());

                return $this->redirectToRoute('edit_entry_complex', array('id' => $entry->getId(), 'info_message' => Entry::MESSAGE_TEMPORARY));
            } elseif ($form->get('review_submit')->isClicked()) {
                if (!$v) {
                    $temporary = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::TEMPORARY);
                    $entry->setStatus($temporary);
                    $entry->setMainCurator($this->getUser());
                    $em->persist($entry);
                    $em->flush();
                    $entities = $validator->getErrorMessages();
                    $form = $this->createForm('entry', $entry, array('complex' => true));
                    foreach ($entities as $key => $entity) {
                        foreach ($entity as $name => $error) {
                            if ('entry' == $key) {
                                $form->get($name)->addError(new FormError($error));
                            } elseif ('otherReferences' == $key) {
                                $form->get('addOtherReferencePmid')->addError(new FormError('PMID which have not been imported : '.implode(';', $entry->getTempOtherPmid())));
                            } else {
                                $form->get($key)->get($name)->addError(new FormError($error));
                            }
                        }
                    }
                    $this->get('log.creator')->createLog($entry, 'New Entry : Validation Failed', $temporary->getName());

                    return $this->redirectToRoute('edit_entry_complex', array('id' => $entry->getId(), 'info_message' => Entry::MESSAGE_TEMPORARY_ERROR));
                } elseif ($form->get('delete')->isClicked()) {
                    $deleted = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::DELETED);
                    $entry->setStatus($deleted);
                    $em->persist($entry);
                    $em->flush();
                    $this->get('log.creator')->createLog($entry, 'New Entry : Entry Deleted', $deleted->getName());

                    $homepageParameters = $this->getDoctrine()->getRepository('AppBundle:Parameter')->find(1);
                    $references = $this->getDoctrine()->getRepository('AppBundle:Reference')->findLatestsReferences();

                    return $this->render('default/home.html.twig', array('parameters' => $homepageParameters, 'info_message' => 'Gephe '.$entry->getGepheId().' Saved as Draft-Deleted', 'references' => $references));
                } else {
                    $temporary = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::TEMPORARY);
                    $entry->setStatus($temporary);
                    $entry->setMainCurator($this->getUser());
                    $em->persist($entry);
                    $em->flush();
                    $this->get('log.creator')->createLog($entry, 'New Entry : Entry Validated', $temporary->getName());

                    return $this->redirectToRoute('edit_entry_complex', array('id' => $entry->getId(), 'info_message' => Entry::MESSAGE_VALID));
                }
            } else {
                $em->remove($entry);
                $em->flush();
                $this->get('log.creator')->createLog($entry, 'New Entry Abort', "User doesn't save the entry");

                return $this->redirectToRoute('new_entry_complex');
            }
        }

        return $this->render('curator/entry/complex/newEntryComplex.html.twig', array(
            'form' => $form->createView(),
            'entry_id' => $entry->getId(),
        ));
    }

    /**
     * @Route("/curator/new-entry-remove/{id}", defaults={"id" = 0}, name="remove_new_entry")
     */
    public function removeNewEntryAction(Request $request, $id)
    {
        if (0 == $id) {
            return $this->redirectToRoute('new_entry');
        } else {
            $em = $this->getDoctrine()->getManager();
            $entry = $em->getRepository('AppBundle:Entry')->find($id);
            $em->remove($entry);
            $em->flush();

            return $this->redirectToRoute('new_entry');
        }
    }

    /**
     * @Route("/curator/edit-entry/{id}/{info_message}", defaults={"id" = 0}, name="edit_entry")
     */
    public function editEntryAction(Request $request, $id, $info_message = null)
    {
        $em = $this->getDoctrine()->getManager();
        if (0 == $id) {
            if (null != $request->request->get('id')) {
                $id = $request->request->get('id');
            }
        }
        $entry = $em->getRepository('AppBundle:Entry')->findOneById($id);
        if (null == $entry) {
            $homepageParameters = $this->getDoctrine()
                ->getRepository('AppBundle:Parameter')
                ->find(1);
            $info_message = 'This entry does not exist.';

            return $this->render('default/home.html.twig', array('parameters' => $homepageParameters, 'info_message' => $info_message));
        }

        // we need to check if the entry has complex relations, if it does, redirect to complex edit
        $manager = $this->get('entry.manager');
        if ($manager->isComplex($entry)) {
            return $this->redirectToRoute('edit_entry_complex', array('id' => $entry->getId(), 'info_message' => $info_message));
        }

        // we also need to check if it is an imported entry, if it is, retrieve data from external APIs
        if ($entry->getStatus() && EntryStatus::IMPORTED == $entry->getStatus()->getId()) {
            $dataRetriever = $this->get('entry.data.retriever');
            $entry = $dataRetriever->retrieveData($id);
            $em->flush();
        }

        $persistedData = $this->fetchPersistedData($entry);
        $validator = $this->get('entry.validator');
        $session = $this->get('session');
        $relationManager = $this->get('complex.relation.manager');
        $form = $this->createForm('entry', $entry);
        $relationManager->setOriginalOtherReferences($entry);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $entryForm = $request->request->get('entry');
            $entry = $this->handleRelations($form->getData(), $em, $persistedData, $entryForm);
            $this->fixHandleRequest($entryForm, $entry, $em);

            $relationManager->removeDeletedReferences($entry, $em);

            $v = $validator->validate($entry);

            if ($form->get('save_draft')->isClicked() || $form->get('save_draft_menu')->isClicked()) {
                $temporary = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::TEMPORARY);
                $entry->setStatus($temporary);
                $em->persist($entry);
                $em->flush();
                $this->get('log.creator')->createLog($entry, 'Edit entry : Entry draft saved', $temporary->getName());

                return $this->redirectToRoute('edit_entry', array('id' => $entry->getId(), 'info_message' => Entry::MESSAGE_TEMPORARY));
            } elseif ($form->get('review_submit')->isClicked() || $form->get('submit_and_next')->isClicked()) {
                if (!$v) {
                    $temporary = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::TEMPORARY);
                    $entry->setStatus($temporary);
                    $em->persist($entry);
                    $em->flush();
                    $form = $this->createForm('entry', $entry);
                    $entities = $validator->getErrorMessages();
                    foreach ($entities as $key => $entity) {
                        foreach ($entity as $name => $error) {
                            if ('entry' == $key) {
                                $form->get($name)->addError(new FormError($error));
                            } elseif ('otherReferences' == $key) {
                                $form->get('addOtherReferencePmid')->addError(new FormError('PMID which have not been imported : '.implode(';', $entry->getTempOtherPmid())));
                            } else {
                                $form->get($key)->get($name)->addError(new FormError($error));
                            }
                        }
                    }
                    $this->get('log.creator')->createLog($entry, 'Edit entry : Validation failed', $temporary->getName());

                    return $this->render('curator/editEntry.html.twig', array(
                        'id' => $entry->getId(),
                        'form' => $form->createView(),
                        'info_message' => Entry::MESSAGE_TEMPORARY_ERROR,
                        'formErrors' => $entities,
                    ));
                } else {
                    $nextId = $em->getRepository('AppBundle:Entry')->findIdHigherThan($entry->getId(), $entry->getStatus());
                    $published = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::ACCEPTED_CURATOR);
                    $entry->setStatus($published);
                    $em->persist($entry);
                    $em->flush();
                    $this->get('log.creator')->createLog($entry, 'Edit entry : Validate Entry', $entry->getStatus()->getName());
                    if ($form->get('submit_and_next')->isClicked()) {
                        if (null == $nextId) {
                            $session->getFlashbag()->add('success', Entry::MESSAGE_VALID_LAST);

                            return $this->redirectToRoute('preview_entry', array('id' => $entry->getId()));
                        } else {
                            return $this->redirectToRoute('edit_entry', array('id' => $nextId, 'info_message' => Entry::MESSAGE_VALID));
                        }
                    } else {
                        return $this->redirectToRoute('preview_entry', array('id' => $entry->getId(), 'info_message' => Entry::MESSAGE_VALID));
                    }
                }
            } elseif ($form->get('delete')->isClicked()) {
                $deleted = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::DELETED);
                $entry->setStatus($deleted);
                $em->persist($entry);
                $em->flush();
                $this->get('log.creator')->createLog($entry, 'Edit entry : Entry deleted', $deleted->getName());

                $homepageParameters = $this->getDoctrine()->getRepository('AppBundle:Parameter')->find(1);
                $references = $this->getDoctrine()->getRepository('AppBundle:Reference')->findLatestsReferences();

                return $this->render('default/home.html.twig', array('parameters' => $homepageParameters, 'info_message' => 'Gephe '.$entry->getGepheId().' Saved as Draft-Deleted', 'references' => $references));
            } else {
                $this->get('log.creator')->createLog($entry, 'Edit entry : Abort modify Entry', "User doesn't save the entry");

                return $this->redirectToRoute('edit_entry', array('id' => $entry->getId()));
            }
        }

        if (!$form->isSubmitted()) {
            $isValid = $validator->validate($entry);
            if (!$isValid) {
                $validator->addErrorsToForm($form);
                $formErrors = $validator->getErrorMessages();
            }
        }

        $renderOptions = array(
            'form' => $form->createView(),
            'id' => $id,
            'info_message' => $info_message,
        );

        if (isset($formErrors)) {
            $renderOptions['formErrors'] = $formErrors;
        }

        return $this->render('curator/editEntry.html.twig', $renderOptions);
    }

    /**
     * @Route("/curator/complex-edit-entry/{id}/{info_message}", defaults={"id" = 0}, name="edit_entry_complex")
     */
    public function editEntryComplexAction(Request $request, $id, $info_message = null)
    {
        $em = $this->getDoctrine()->getManager();
        if (0 == $id) {
            if (null != $request->request->get('id')) {
                $id = $request->request->get('id');
            }
        }
        $entry = $em->getRepository('AppBundle:Entry')->findOneById($id);

        if (null == $entry) {
            $homepageParameters = $this->getDoctrine()
                ->getRepository('AppBundle:Parameter')
                ->find(1);
            $info_message = 'This entry does not exist.';

            return $this->render('default/home.html.twig', array('parameters' => $homepageParameters, 'info_message' => $info_message));
        }

        // we also need to check if it is an imported entry, if it is, retrieve data from external APIs
        if ($entry->getStatus() && EntryStatus::IMPORTED == $entry->getStatus()->getId()) {
            $dataRetriever = $this->get('entry.data.retriever');
            $entry = $dataRetriever->retrieveData($id);
        }

        $persistedData = $this->fetchPersistedData($entry);
        $validator = $this->get('entry.validator');
        $relationManager = $this->get('complex.relation.manager');
        $session = $this->get('session');

        $form = $this->createForm('entry', $entry, array('complex' => true));

        $originalTraits = $relationManager->getOriginalTraits($entry);
        $originalTaxonAs = $relationManager->getOriginalTaxonAs($entry);
        $originalTaxonBs = $relationManager->getOriginalTaxonBs($entry);
        $originalMutations = $relationManager->getOriginalMutations($entry);
        $relationManager->setOriginalOtherReferences($entry);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $entryForm = $request->request->get('entry');

            $entry = $this->handleRelations($form->getData(), $em, $persistedData, $entryForm);
            $this->fixHandleRequest($entryForm, $entry, $em);

            $relationManager->removeDeletedTraits($entry, $originalTraits, $em);
            $relationManager->removeDeletedTaxonAs($entry, $originalTaxonAs, $em);
            $relationManager->removeDeletedTaxonBs($entry, $originalTaxonBs, $em);
            $relationManager->removeDeletedMutations($entry, $originalMutations, $em);
            $relationManager->removeDeletedReferences($entry, $em);

            $v = $validator->validate($entry);

            if ($form->get('save_draft')->isClicked() || $form->get('save_draft_menu')->isClicked()) {
                $temporary = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::TEMPORARY);
                $entry->setStatus($temporary);
                $em->persist($entry);
                $em->flush();
                $this->get('log.creator')->createLog($entry, 'Edit entry : Entry draft saved', $temporary->getName());

                return $this->redirectToRoute('edit_entry_complex', array('id' => $entry->getId(), 'info_message' => Entry::MESSAGE_TEMPORARY));
            } elseif ($form->get('review_submit')->isClicked() || $form->get('submit_and_next')->isClicked()) {
                if (!$v) {
                    $temporary = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::TEMPORARY);
                    $entry->setStatus($temporary);
                    $em->persist($entry);
                    $em->flush();
                    $form = $this->createForm('entry', $entry, array('complex' => true));
                    $entities = $validator->getErrorMessages();
                    foreach ($entities as $key => $entity) {
                        foreach ($entity as $name => $error) {
                            if ('entry' == $key) {
                                $form->get($name)->addError(new FormError($error));
                            } elseif ('otherReferences' == $key) {
                                $form->get('addOtherReferencePmid')->addError(new FormError('PMID which have not been imported : '.implode(';', $entry->getTempOtherPmid())));
                            } else {
                                $form->get($key)->get($name)->addError(new FormError($error));
                            }
                        }
                    }
                    $this->get('log.creator')->createLog($entry, 'Edit entry : Validation failed', $temporary->getName());

                    return $this->render('curator/editEntryComplex.html.twig', array(
                        'id' => $entry->getId(),
                        'form' => $form->createView(),
                        'info_message' => Entry::MESSAGE_TEMPORARY_ERROR,
                        'formErrors' => $entities,
                    ));
                } else {
                    $em->persist($entry);
                    $nextId = $em->getRepository('AppBundle:Entry')->findIdHigherThan($entry->getId(), $entry->getStatus());
                    $em->flush();
                    $this->get('log.creator')->createLog($entry, 'Edit entry : Validate Entry', $entry->getStatus()->getName());
                    if ($form->get('submit_and_next')->isClicked()) {
                        if (null == $nextId) {
                            $session->getFlashbag()->add('success', Entry::MESSAGE_VALID_LAST);

                            return $this->redirectToRoute('preview_entry', array('id' => $entry->getId()));
                        } else {
                            return $this->redirectToRoute('edit_entry_complex', array('id' => $nextId, 'info_message' => Entry::MESSAGE_VALID));
                        }
                    } else {
                        return $this->redirectToRoute('preview_entry', array('id' => $entry->getId(), 'info_message' => Entry::MESSAGE_VALID));
                    }
                }
            } elseif ($form->get('delete')->isClicked()) {
                $deleted = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::DELETED);
                $entry->setStatus($deleted);
                $em->persist($entry);
                $em->flush();
                $this->get('log.creator')->createLog($entry, 'Edit entry : Entry deleted', $deleted->getName());

                $homepageParameters = $this->getDoctrine()->getRepository('AppBundle:Parameter')->find(1);
                $references = $this->getDoctrine()->getRepository('AppBundle:Reference')->findLatestsReferences();

                return $this->render('default/home.html.twig', array('parameters' => $homepageParameters, 'info_message' => 'Gephe '.$entry->getGepheId().' Saved as Draft-Deleted', 'references' => $references));
            } else {
                $this->get('log.creator')->createLog($entry, 'Edit entry : Abort modify Entry', "User doesn't save the entry");

                return $this->redirectToRoute('edit_entry', array('id' => $entry->getId()));
            }
        }

        if (!$form->isSubmitted()) {
            $isValid = $validator->validate($entry);
            if (!$isValid) {
                $validator->addErrorsToForm($form);
                $formErrors = $validator->getErrorMessages();
            }
        }

        $renderOptions = array(
            'form' => $form->createView(),
            'id' => $id,
            'info_message' => $info_message,
        );

        if (isset($formErrors)) {
            $renderOptions['formErrors'] = $formErrors;
        }

        return $this->render('curator/editEntryComplex.html.twig', $renderOptions);
    }

    /**
     * @Route("/curator/edit-draft-entry/{id}/{info_message}", defaults={"id" = 0}, name="edit_draft_entry")
     */
    public function editDraftEntryAction(Request $request, $id, $info_message = null)
    {
        $em = $this->getDoctrine()->getManager();
        if (0 == $id) {
            if (null != $request->request->get('id')) {
                $id = $request->request->get('id');
            }
        }
        $entry = $em->getRepository('AppBundle:Entry')->findOneById($id);
        if (null == $entry) {
            $homepageParameters = $this->getDoctrine()->getRepository('AppBundle:Parameter')->find(1);
            $info_message = 'This entry does not exist.';

            return $this->render('default/home.html.twig', array('parameters' => $homepageParameters, 'info_message' => $info_message));
        }

        $temporary = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::TEMPORARY);
        $entry->setStatus($temporary);
        $em->persist($entry);
        $em->flush($entry);

        return $this->redirectToRoute('edit_entry', array('id' => $entry->getId()));
    }

    /**
     * @Route("/curator/duplicate-entry/{id}", defaults={"id" = 0}, name="duplicate_entry")
     */
    public function duplicateEntryAction(Request $request, $id, $info_message = null)
    {
        $em = $this->getDoctrine()->getManager();
        if (0 == $id) {
            if (null != $request->request->get('id')) {
                $id = $request->request->get('id');
            }
        }
        $entry = $em->getRepository('AppBundle:Entry')->findOneById($id);
        if (null == $entry) {
            $homepageParameters = $this->getDoctrine()->getRepository('AppBundle:Parameter')->find(1);
            $info_message = 'This entry does not exist.';

            return $this->render('default/home.html.twig', array('parameters' => $homepageParameters, 'info_message' => $info_message));
        }

        $manager = $this->get('entry.manager');
        $newEntry = $manager->duplicateEntry($entry);
        $temporary = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::TEMPORARY);
        $newEntry->setStatus($temporary);
        $newEntry->setGepheId(null);

        if ($manager->isComplex($entry)) {
            $form = $this->createForm('entry', $newEntry, array(
                'action' => $this->generateUrl('new_entry_complex'),
            ));
            $view = $this->render('curator/entry/complex/newEntryComplex.html.twig', array(
                'form' => $form->createView(),
            ));
        } else {
            $form = $this->createForm('entry', $newEntry, array(
                'action' => $this->generateUrl('new_entry'),
            ));
            $view = $this->render('curator/newEntry.html.twig', array(
                'form' => $form->createView(),
            ));
        }

        return $view;
    }

    /**
     * @Route("/curator/publish-entry/{id}", defaults={"id" = 0}, name="publish_entry")
     */
    public function publishEntryAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        if (0 == $id) {
            if (null != $request->request->get('id')) {
                $id = $request->request->get('id');
            }
        }
        $entry = $em->getRepository('AppBundle:Entry')->findOneById($id);
        if (null == $entry) {
            $homepageParameters = $this->getDoctrine()->getRepository('AppBundle:Parameter')->find(1);
            $info_message = 'This entry does not exist.';

            return $this->render('default/home.html.twig', array('parameters' => $homepageParameters, 'info_message' => $info_message));
        }

        $published = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::ACCEPTED_CURATOR);
        $entry->setStatus($published);
        $em->persist($entry);
        $em->flush($entry);

        $session = $this->get('session');
        $session->getFlashbag()->add('success', Entry::MESSAGE_ENTRY_PUBLISHED);

        return $this->redirectToRoute('view_entry', array('id' => $entry->getId()));
    }

    /**
     * Updates an entry by setting its status to Deleted.
     *
     * @Route("/curator/delete-entry/{id}/{info_message}", defaults={"id" = 0}, name="delete_entry")
     */
    public function deleteEntryAction(Request $request, $id, $info_message = null)
    {
        if (0 == $id) {
            if (null != $request->request->get('id')) {
                $id = $request->request->get('id');
            }
        }

        $em = $this->getDoctrine()->getManager();
        $entry = $em->getRepository('AppBundle:Entry')->findOneById($id);
        if (null == $entry) {
            $homepageParameters = $this->getDoctrine()->getRepository('AppBundle:Parameter')->find(1);
            $info_message = 'This entry does not exist.';

            return $this->render('default/home.html.twig', array('parameters' => $homepageParameters, 'info_message' => $info_message));
        }

        $deleted = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::DELETED);
        $entry->setStatus($deleted);
        $em->persist($entry);
        $em->flush();
        $this->get('log.creator')->createLog($entry, 'View Gephe: Entry Deleted', $deleted->getName());
        $homepageParameters = $this->getDoctrine()->getRepository('AppBundle:Parameter')->find(1);
        $references = $this->getDoctrine()->getRepository('AppBundle:Reference')->findLatestsReferences();

        return $this->render('default/home.html.twig', array(
            'parameters' => $homepageParameters,
            'info_message' => 'Gephe '.$entry->getGepheId().' Saved as Draft-Deleted',
            'references' => $references,
        ));
    }

    /**
     * Deletes an entry from the database.
     *
     * @Route("/curator/delete-entry-permanent/{id}/{info_message}", defaults={"id" = 0}, name="delete_entry_permanent")
     */
    public function deleteEntryPermanentAction(Request $request, $id, $info_message = null)
    {
        if (!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $session = $this->get('session');
            $session->getFlashbag()->add('danger', Entry::MESSAGE_ADMIN_TO_DELETE);

            return $this->redirectToRoute('deleted_entries');
        }

        if (0 == $id) {
            if (null != $request->request->get('id')) {
                $id = $request->request->get('id');
            }
        }

        $em = $this->getDoctrine()->getManager();
        $entry = $em->getRepository('AppBundle:Entry')->findOneById($id);
        if (null == $entry) {
            $homepageParameters = $this->getDoctrine()->getRepository('AppBundle:Parameter')->find(1);
            $info_message = 'This entry does not exist.';

            return $this->render('default/home.html.twig', array('parameters' => $homepageParameters, 'info_message' => $info_message));
        }

        $em->remove($entry);
        $em->flush();
        $message = 'Gephe '.$entry->getGepheId().' Permanently Deleted';
        $this->get('log.creator')->createGenericErrorLog($message);
        $homepageParameters = $this->getDoctrine()->getRepository('AppBundle:Parameter')->find(1);
        $references = $this->getDoctrine()->getRepository('AppBundle:Reference')->findLatestsReferences();

        return $this->render('default/home.html.twig', array(
            'parameters' => $homepageParameters,
            'info_message' => $message,
            'references' => $references,
        ));
    }

    /**
     * @Route("/curator/preview-entry/{id}/{info_message}", defaults={"id" = 0}, name="preview_entry")
     */
    public function previewEntryAction(Request $request, $id, $info_message = null)
    {
        $entry = $this->getDoctrine()->getRepository('AppBundle:Entry')->find($id);

        if (!$entry) {
            throw $this->createNotFoundException('Entry not found.');
        }

        $searchBuilder = $this->get('search.builder');
        $relatedGenes = $searchBuilder->findRelatedGenes($entry);
        $relatedMutations = $searchBuilder->findRelatedHaplotypes($entry);

        return $this->render('curator/previewEntry.html.twig', array(
            'entry' => $entry,
            'relatedGenes' => $relatedGenes,
            'relatedMutations' => $relatedMutations,
            'preview' => true,
        ));
    }

    /**
     * @Route("/curator/refused-entry/{id}/{info_message}", defaults={"id" = 0}, name="refused_entry")
     */
    public function refusedEntryAction(Request $request, $id, $info_message = null)
    {
        $em = $this->getDoctrine()->getManager();
        if (0 == $id) {
            if (null != $request->request->get('id')) {
                $id = $request->request->get('id');
            }
        }
        $entry = $em->getRepository('AppBundle:Entry')->findOneById($id);
        $persistedData = $this->fetchPersistedData($entry);

        $form = $this->createForm('entry', $entry);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $validator = $this->get('entry.validator');
            $entryForm = $request->request->get('entry');
            $entry = $this->handleRelations($form->getData(), $em, $persistedData, $entryForm);
            $v = $validator->validate($entry, $entryForm['taxonA']['taxId'], $entryForm['taxonB']['taxId']);

            if ($form->get('saveEntry')->isClicked()) {
                $temporary = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::REFUSED_VALIDATOR);
                $entry->setStatus($temporary);
                $em->persist($entry);
                $em->flush();

                $this->get('log.creator')->createLog($entry, 'Refused entry : Save temporary Entry', $temporary->getName());

                return $this->redirectToRoute('refused_entry', array('id' => $entry->getId(), 'info_message' => Entry::MESSAGE_TEMPORARY));
            } elseif ($form->get('submit')->isClicked()) {
                if (!$v) {
                    $temporary = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::REFUSED_VALIDATOR);
                    $entry->setStatus($temporary);
                    $em->persist($entry);
                    $em->flush();
                    $entities = $validator->getErrorMessages();
                    $form = $this->createForm('entry', $entry);
                    foreach ($entities as $key => $entity) {
                        foreach ($entity as $name => $error) {
                            if ('entry' == $key) {
                                $form->get($name)->addError(new FormError($error));
                            } elseif ('otherReferences' == $key) {
                                /*foreach($form->get($key) as $n => $e) {
                                    $form->get($key)->get($n)->get('pmId')->addError(new FormError($error));
                                }*/
                                $form->get('addOtherReferencePmid')->addError(new FormError('PMID which have not been imported : '.implode(';', $entry->getTempOtherPmid())));
                            } else {
                                $form->get($key)->get($name)->addError(new FormError($error));
                            }
                        }
                    }
                    $this->get('log.creator')->createLog($entry, 'Refused entry : Validation failed', $temporary->getName());

                    return $this->render('curator/refusedEntry.html.twig', array('id' => $entry->getId(), 'form' => $form->createView(), 'info_message' => Entry::MESSAGE_TEMPORARY_ERROR));
                } else {
                    $acceptedCurator = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::ACCEPTED_CURATOR);
                    $entry->setStatus($acceptedCurator);
                    $em->persist($entry);
                    $em->flush();
                    $this->get('log.creator')->createLog($entry, 'Refused entry : Validate Entry', $acceptedCurator->getName());

                    return $this->redirectToRoute('refused_entry', array('id' => $entry->getId(), 'info_message' => Entry::MESSAGE_VALID));
                }
            } else {
                $this->get('log.creator')->createLog($entry, 'Refused entry : Abort modify Entry', "User doesn't save the entry");

                return $this->redirectToRoute('refused_entry', array('id' => $entry->getId()));
            }
        }

        return $this->render('curator/refusedEntry.html.twig', array(
            'form' => $form->createView(),
            'id' => $id,
            'info_message' => $info_message,
            'entry' => $entry,
        ));
    }

    /**
     * @Route("/curator/list-user", name="list_user")
     */
    public function listUserAction(Request $request, $info_message = null)
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('AppBundle:User')->listAllCurators();

        return $this->render('curator/user/users.html.twig', array(
            'users' => $users,
            'info_message' => $info_message,
        ));
    }

    /**
     * @Route("/curator/create-user", name="create_user")
     */
    public function createUserAction(Request $request)
    {
        if (!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('list_user');
        }
        $em = $this->getDoctrine()->getManager();
        $userManager = $this->container->get('fos_user.user_manager');
        $form = $this->createForm('curator');
        $form->handleRequest($request);
        if ($form->isValid()) {
            $error = false;
            $password = $request->get('curator')['password'];
            if ('' == $request->get('curator')['username']) {
                $form->get('username')->addError(new FormError('Please complete username.'));
                $error = true;
            }
            if ('' == $request->get('curator')['surname']) {
                $form->get('surname')->addError(new FormError('Please complete firstname.'));
                $error = true;
            }
            if ('' == $request->get('curator')['name']) {
                $form->get('name')->addError(new FormError('Please complete lastname.'));
                $error = true;
            }
            if (!filter_var($request->get('curator')['email'], FILTER_VALIDATE_EMAIL)) {
                $form->get('email')->addError(new FormError('Invalid email address.'));
                $error = true;
            }

            $uppercase = preg_match('@[A-Z]@', $password);
            $lowercase = preg_match('@[a-z]@', $password);
            $number = preg_match('@[0-9]@', $password);
            $special = preg_match('@[^\w]@', $password);

            if (!$uppercase || !$lowercase || !$number || !$special || strlen($password) < 6) {
                $form->get('password')->addError(new Form('Your password must contains capital letters, numbers and special characters with at least 6 characters long.'));
                $error = true;
            }
            if ($password != $request->get('curator')['confirmPassword']) {
                $form->get('confirmPassword')->addError(new Form('Confirmation of your password is not the same as your password.'));
                $error = true;
            }

            // look for existing users
            $user = $userManager->findUserByEmail($request->get('curator')['email']);
            if ($user) {
                $form->get('email')->addError(new FormError('This email already exists.'));
                $error = true;
            } else {
                $user = $userManager->findUserByUsername($request->get('curator')['username']);
                if ($user) {
                    $form->get('username')->addError(new FormError('This username already exists.'));
                    $error = true;
                }
            }

            if ($error) {
                return $this->render('curator/user/createUser.html.twig', array(
                    'form' => $form->createView(),
                ));
            }

            $user = $userManager->createUser();
            if (null != $password && '' != $password) {
                $user->setPlainPassword($password);
                $newExpireDate = new \DateTime();
                $newExpireDate->add(new \DateInterval('P365D'));
                $user->setCredentialsExpireAt($newExpireDate);
            }
            $user->setUsername($request->get('curator')['username']);
            $user->setEmail($request->get('curator')['email']);
            $user->setSurname($request->get('curator')['surname']);
            $user->setName($request->get('curator')['name']);
            $user->setEnabled(true);
            if (isset($request->get('curator')['roleUser']) && '1' == $request->get('curator')['roleUser']) {
                $user->addRole('ROLE_ADMIN');
            } elseif (isset($request->get('curator')['roleUser']) && 'no' == $request->get('curator')['roleUser']) {
                $user->removeRole('ROLE_ADMIN');
            }
            $user->addRole('ROLE_CURATOR');
            $userManager->updateUser($user);

            $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
            $oldPassword = $encoder->encodePassword($password, $user->getSalt());

            $oP = new OldPassword();
            $oP->setName($oldPassword);
            $oP->setUser($user);
            $em->persist($oP);
            $em->flush();

            return $this->redirectToRoute('list_user', array(
                'info_message' => 'User created',
            ));
        }

        return $this->render('curator/user/createUser.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/curator/edit-user/{id}", name="edit_user")
     */
    public function editUserAction(Request $request, $id = null, $info_message = null)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($id);
        if (!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            if ($user != $this->getUser()) {
                return $this->redirectToRoute('list_user');
            }
        }
        if (null == $id) {
            return $this->redirectToRoute('list_user');
        }
        $form = $this->createForm('curator', $user);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $error = false;
            $password = $request->get('curator')['password'];
            if ('' == $request->get('curator')['username']) {
                $form->get('username')->addError(new FormError('Please complete username.'));
                $error = true;
            }
            if ('' == $request->get('curator')['surname']) {
                $form->get('surname')->addError(new FormError('Please complete firstname.'));
                $error = true;
            }
            if ('' == $request->get('curator')['name']) {
                $form->get('name')->addError(new FormError('Please complete lastname.'));
                $error = true;
            }
            $endEmail = substr($request->get('curator')['email'], strpos($request->get('curator')['email'], '@') + 1);
            if (substr_count($endEmail, '.') < 2) {
                if (!filter_var($request->get('curator')['email'], FILTER_VALIDATE_EMAIL)) {
                    $form->get('email')->addError(new FormError('Invalid email address.'));
                    $error = true;
                }
            }
            if (null != $password && '' != $password) {
                $uppercase = preg_match('@[A-Z]@', $password);
                $lowercase = preg_match('@[a-z]@', $password);
                $number = preg_match('@[0-9]@', $password);
                $special = preg_match('@[^\w]@', $password);

                if (!$uppercase || !$lowercase || !$number || !$special || strlen($password) < 6) {
                    $form->get('password')->addError(new Form('Your password must contains capital letters, numbers and special characters with at least 6 characters long.'));
                    $error = true;
                }
                if ($password != $request->get('curator')['confirmPassword']) {
                    $form->get('confirmPassword')->addError(new Form('Confirmation of your password is not the same as your password.'));
                    $error = true;
                }
            }

            $userManager = $this->container->get('fos_user.user_manager');
            if (null != $password && '' != $password) {
                $oldPasswords = $em->getRepository('AppBundle:OldPassword')->allOldPassword($user->getId());
                $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
                $cryptPassword = $encoder->encodePassword($password, $user->getSalt());
                foreach ($oldPasswords as $oldPassword) {
                    if ($oldPassword->getName() == $cryptPassword) {
                        $form->get('password')->addError(new FormError('You have ever used this password. Please create a new one.'));
                        $error = true;
                        break;
                    }
                }
                $user->setPlainPassword($password);
                $newExpireDate = new \DateTime();
                $newExpireDate->add(new \DateInterval('P365D'));
                $user->setCredentialsExpireAt($newExpireDate);
            }

            if ($error) {
                return $this->render('curator/user/createUser.html.twig', array(
                    'form' => $form->createView(),
                ));
            }

            $user->setEnabled(true);
            if (isset($request->get('curator')['roleUser']) && $request->get('curator')['roleUser']) {
                $user->addRole('ROLE_ADMIN');
            }
            if (isset($request->get('curator')['roleUser']) && 'no' == $request->get('curator')['roleUser']) {
                $user->removeRole('ROLE_ADMIN');
            }
            $userManager->updateUser($user);

            if (null != $password && '' != $password) {
                $oP = new OldPassword();
                $oP->setName($cryptPassword);
                $oP->setUser($user);
                $em->persist($oP);
            }
            $em->flush();

            return $this->render('curator/user/editUser.html.twig', array(
                'form' => $form->createView(),
                'info_message' => 'User saved',
            ));
        }

        return $this->render('curator/user/editUser.html.twig', array(
            'form' => $form->createView(),
            'info_message' => $info_message,
        ));
    }

    /**
     * @Route("/curator/delete-user", name="delete_user")
     */
    public function deleteUserAction(Request $request)
    {
        if (!$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return $response;
        }
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $id = $request->request->get('id');
        $user = $em->getRepository('AppBundle:User')->find($id);
        $userManager = $this->container->get('fos_user.user_manager');
        $userManager->deleteUser($user);

        return $response;
    }

    /**
     * @Route("/curator/unvalidated-entries", name="unvalidated_entries")
     */
    public function unvalidatedEntriesAction(Request $request)
    {
        $userId = $this->getUser()->getId();
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm('validator');
        $entries = $em->getRepository('AppBundle:Entry')->listUnvalidatedEntries($userId);

        if ($entries) {
            $array_ids = array();
            foreach ($entries as $entry) {
                $array_ids[] = $entry->getId();
            }
            $entries = $this->getDoctrine()->getRepository('AppBundle:Entry')->retrieveAllData($array_ids);
        } else {
            $entries = array();
        }
        $alreadyDone = array();
        $entriesTemp = $entries;
        foreach ($entries as $key => $entry) {
            foreach ($entriesTemp as $keyTemp => $entryTemp) {
                if ($key != $keyTemp && $entry['id'] == $entryTemp['id'] && !in_array($entry['id'], $alreadyDone)) {
                    unset($entries[$key]);
                    $alreadyDone[] = $entry['id'];
                }
            }
        }

        return $this->render('curator/unvalidatedEntries.html.twig', array(
            'form' => $form->createView(),
            'entries' => $entries,
            'traitList' => Entry::getTraitCategoryList(),
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
     * @Route("/curator/affect-validator", name="affect_validator")
     */
    public function affectValidatorAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $arrayIdsMutations = $request->request->get('entries');
        $userManager = $this->container->get('fos_user.user_manager');
        $validator = $userManager->findUserByEmail($request->get('validator_id'));
        if (!$validator) {

            $manager = $this->get('validator.manager');
            $validator = $manager->createValidator($request->get('validator_id'), $request->get('validator_post_name'), $request->get('validator_post_surname'));

            //$validator = $userManager->createUser();

            //$date = new \DateTime();
            //$interval = new \DateInterval('P1M');
            //$date->add($interval);

            //$validator->setPlainPassword($request->get('validator_id').$this->getToken(50));
            //$validator->setUsername($this->getToken(50));
            // $validator->addRole('ROLE_VALIDATOR');
            // $validator->setExpiresAt($date);
            // $validator->setEmail($request->get('validator_id'));
            // if ($request->get('validator_post_name')) {
            //     $validator->setName($request->get('validator_post_name'));
            // } else {
            //     $validator->setName(null);
            // }
            // if ($request->get('validator_post_surname')) {
            //     $validator->setSurname($request->get('validator_post_surname'));
            // } else {
            //     $validator->setName(null);
            // }
            // $validator->setEnabled(true);
            // $validator->setToken($this->getToken(50));
            // $userManager->updateUser($validator);
        } else {
            if (null == $validator->getToken()) {
                $validator->setToken($this->getToken(50));
            }
            $validator->addRole('ROLE_VALIDATOR');
        }
        foreach ($arrayIdsMutations as $mutationId) {
            $mutation = $em->getRepository('AppBundle:Mutation')->find($mutationId);
            $entry = $em->getRepository('AppBundle:Entry')->find($mutation->getEntry()->getId());
            $entry->addValidator($validator);
            $em->persist($entry);
        }

        $em->flush();
        if (count($arrayIdsMutations) > 1) {
            $info_message = 'Validator affected to entries';
        } else {
            $info_message = 'Validator affected to entry';
        }
        $userId = $this->getUser()->getId();
        $form = $this->createForm('validator');
        $entries = $em->getRepository('AppBundle:Entry')->listUnvalidatedEntries($userId);

        if ($entries) {
            $array_ids = array();
            foreach ($entries as $entry) {
                $array_ids[] = $entry->getId();
            }
            $entries = $this->getDoctrine()->getRepository('AppBundle:Entry')->retrieveAllData($array_ids);
        } else {
            $entries = array();
        }

        return $this->render('curator/unvalidatedEntries.html.twig', array(
            'form' => $form->createView(),
            'entries' => $entries,
            'info_message' => $info_message,
            'traitList' => Entry::getTraitCategoryList(),
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
     * @Route("/curator/fetch-validator", name="fetch_validator")
     */
    public function fetchValidatorAction(Request $request)
    {
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('validator');
        $form->handleRequest($request);
        if ($form->isValid() && filter_var($form['email']->getData(), FILTER_VALIDATE_EMAIL)) {
            $userManager = $this->container->get('fos_user.user_manager');
            $user = $userManager->findUserByEmail($form['email']->getData());
            if ($user) {
                if (null != $form['name']->getData()) {
                    $user->setName($form['name']->getData());
                }
                if (null != $form['surname']->getData()) {
                    $user->setSurname($form['surname']->getData());
                }
                $userManager->updateUser($user);
                $response->setData(array(
                    'validatorName' => $user->getName(),
                    'validatorSurname' => $user->getSurname(),
                    'validatorEmail' => $user->getEmail(),
                ));
            } else {
                $response->setData(array('error' => 'found'));
            }
        } else {
            $response->setData(array('error' => 'invalid'));
        }

        return $response;
    }

    /**
     * @Route("/curator/load-validator", name="load_validator")
     */
    public function loadValidatorAction(Request $request)
    {
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm('validator');
        $form->handleRequest($request);
        if ($form->isValid() && filter_var($form['email']->getData(), FILTER_VALIDATE_EMAIL)) {
            $userManager = $this->container->get('fos_user.user_manager');
            $user = $userManager->findUserByEmail($form['email']->getData());
            if ($user) {
                if (null != $form['name']->getData()) {
                    $user->setName($form['name']->getData());
                }
                if (null != $form['surname']->getData()) {
                    $user->setSurname($form['surname']->getData());
                }
                $userManager->updateUser($user);
                $response->setData(array(
                    'validatorName' => $user->getName(),
                    'validatorSurname' => $user->getSurname(),
                    'validatorEmail' => $user->getEmail(),
                ));
            } else {
                $user = $userManager->createUser();

                $date = new \DateTime();
                $interval = new \DateInterval('P1M');
                $date->add($interval);

                $user->setPlainPassword(ucfirst(strtolower($form['name']->getData())).date('Y'));
                $user->setUsername($form['name']->getData().$form['surname']->getData().$this->getToken(20));
                $user->addRole('ROLE_VALIDATOR');
                $user->setExpiresAt($date);
                $user->setEmail($form['email']->getData());
                $user->setName($form['name']->getData());
                $user->setSurname($form['surname']->getData());
                $user->setEnabled(true);
                $user->setToken($this->getToken(50));

                $userManager->updateUser($user);

                $response->setData(array(
                    'validatorName' => $user->getName(),
                    'validatorSurname' => $user->getSurname(),
                    'validatorEmail' => $user->getEmail(),
                ));
            }
        } else {
            $response->setData(array('error' => true));
        }

        return $response;
    }

    public function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) {
            return $min;
        }
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

    public function getToken($length)
    {
        $token = '';
        $codeAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $codeAlphabet .= 'abcdefghijklmnopqrstuvwxyz';
        $codeAlphabet .= '0123456789';
        $max = strlen($codeAlphabet) - 1;
        for ($i = 0; $i < $length; ++$i) {
            $token .= $codeAlphabet[$this->crypto_rand_secure(0, $max)];
        }

        return $token;
    }

    /**
     * @Route("/curator/validation", name="validation")
     */
    public function validationAction(Request $request)
    {
        return $this->render('curator/validation.html.twig');
    }

    /**
     * @Route("/curator/sql", name="sql")
     */
    public function sqlAction(Request $request)
    {
        return $this->render('curator/sql.html.twig');
    }

    /**
     *  @Route("/curator/send-sql", name="send_sql_request")
     */
    public function sendSqlAction(Request $request)
    {
        $query = $request->get('query');
        if ('' == $query) {
            return $this->render('curator/sql.html.twig');
        }
        $sql = $query;
        $em = $this->getDoctrine()->getManager();
        $rsm = new ResultSetMapping();
        // build rsm here
        $posU = strstr(strtolower($query), 'update');
        $posD = strstr(strtolower($query), 'delete');
        $posDr = strstr(strtolower($query), 'drop');
        $posC = strstr(strtolower($query), 'create');
        $posIi = strstr(strtolower($query), 'insert');
        $posA = strstr(strtolower($query), 'alter');
        $posT = strstr(strtolower($query), 'truncate');
        if ($posU || $posD || $posDr || $posC || $posIi || $posA || $posT) {
            return $this->render('curator/sql.html.twig', array(
                'info_message' => 'Query contains forbidden requests.',
                'sql' => $sql,
            ));
        } else {
            try {
                $stmt = $em->getConnection()->prepare($query);
                $stmt->execute();
                $result = $stmt->fetchAll();
            } catch (\Exception $e) {
                return $this->render('curator/sql.html.twig', array(
                    'info_message' => 'This query is invalid.',
                    'sql' => $sql,
                ));
            }
        }

        return $this->render('curator/sql.html.twig', array(
            'query' => $result,
            'sql' => $sql,
        ));
    }

    /**
     * @Route("/curator/parameters", name="parameters")
     */
    public function parametersAction(Request $request)
    {
        $parameter = $this->getDoctrine()
            ->getRepository('AppBundle:Parameter')
            ->find(1);
        $form = $this->createForm('parameter', $parameter);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $error = false;
            if (!filter_var($request->get('parameter')['loginMail'], FILTER_VALIDATE_EMAIL)) {
                $form->get('loginMail')->addError(new FormError('Invalid email address.'));
                $error = true;
            }
            if (!filter_var($request->get('parameter')['contact'], FILTER_VALIDATE_EMAIL)) {
                $form->get('contact')->addError(new FormError('Invalid email address.'));
                $error = true;
            }
            if (!filter_var($request->get('parameter')['contactValidator'], FILTER_VALIDATE_EMAIL)) {
                $form->get('contactValidator')->addError(new FormError('Invalid email address.'));
                $error = true;
            }
            if (!filter_var($request->get('parameter')['fromMail'], FILTER_VALIDATE_EMAIL)) {
                $form->get('fromMail')->addError(new FormError('Invalid email address.'));
                $error = true;
            }
            if ($error) {
                return $this->render('curator/parameters.html.twig', array(
                    'form' => $form->createView(),
                ));
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($parameter);
            $em->flush();
        }

        return $this->render('curator/parameters.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/curator/trafic", name="trafic")
     */
    public function traficAction(Request $request)
    {
        return $this->render('curator/trafic.html.twig');
    }

    /**
     * @Route("/curator/logs", name="logs")
     */
    public function logsAction(Request $request)
    {
        return $this->render('curator/logs.html.twig');
    }

    /**
     * @Route("/curator/preview", name="preview")
     */
    public function previewAction(Request $request)
    {
        return $this->render('curator/preview.html.twig');
    }

    /**
     * @Route("/curator/import-gephe", name="import_gephe")
     */
    public function importGepheAction(Request $request)
    {
        $importForm = $this->createForm('importGephe');

        $importForm->handleRequest($request);
        if ($importForm->isValid()) {
            $file = $importForm->get('attachment');
            $importManager = $this->get('import.manager');
            $importManager->import($file);
        }

        $em = $this->getDoctrine()->getManager();
        $entries = $em->getRepository('AppBundle:Entry')->listImportedEntries();

        return $this->render('curator/entry/importedEntries.html.twig', array(
            'entries' => $entries,
        ));
    }

    /**
     * @Route("/curator/import-rejected-references", name="import_rejected_references")
     */
    public function importRejectedReferencesAction(Request $request)
    {
        $importForm = $this->createForm('importRejectedReferences');
        $notification = null;

        $importForm->handleRequest($request);
        if ($importForm->isValid()) {
            $file = $importForm->get('attachment');
            $importManager = $this->get('import.manager');
            $count = $importManager->importRejectedReferences($file);
            $notification = $count.' Rejected references successfully imported.';
        }

        return $this->redirectToRoute('rejected_references', array('info_message' => $notification));
    }

    /**
     * @Route("/curator/imported-entries/{info_message}", name="list_imported_entries")
     */
    public function listImportedEntriesAction(Request $request, $info_message = null)
    {
        $em = $this->getDoctrine()->getManager();
        $entries = $em->getRepository('AppBundle:Entry')->listImportedEntries();

        return $this->render('curator/entry/importedEntries.html.twig', array(
            'entries' => $entries,
            'info_message' => $info_message,
        ));
    }

    /**
     * @Route("/curator/refused-entries/{info_message}", name="refused_entries")
     */
    public function listRefusedEntriesAction(Request $request, $info_message = null)
    {
        $em = $this->getDoctrine()->getManager();
        $entries = $em->getRepository('AppBundle:Entry')->listRefusedEntries();

        return $this->render('curator/refusedEntries.html.twig', array(
            'entries' => $entries,
            'info_message' => $info_message,
        ));
    }

    /**
     * @Route("/curator/suggested-articles", name="suggested_articles")
     */
    public function listSuggestedArticles(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $articles = $em->getRepository('AppBundle:SuggestedArticle')->findBy(array(), array('submissionDate' => 'DESC'));

        return $this->render('curator/suggestedArticles.html.twig', array(
            'articles' => $articles,
        ));
    }

    /**
     * @Route("/curator/accept-suggested-article/{id}", name="accept_suggested_article")
     */
    public function acceptSuggestedArticleAction(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $returnRoute = $this->redirectToRoute('suggested_articles');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $suggestedArticle = $em->getRepository('AppBundle:SuggestedArticle')->findOneById($id);

        if (!$suggestedArticle) {
            return $returnRoute;
        }

        $suggestedArticle->setRejectedDate(null);
        $suggestedArticle->setCurationDate(new \DateTime());
        $suggestedArticle->setCurator($user);
        $em->persist($suggestedArticle);
        $em->flush();

        return $returnRoute;
    }

    /**
     * @Route("/curator/reject-suggested-article", name="reject_suggested_article")
     */
    public function rejectSuggestedArticleAction(Request $request)
    {
        $form = $request->get('reject');
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $suggestedArticle = $em->getRepository('AppBundle:SuggestedArticle')->findOneById($form['id']);
        $suggestedArticle->setRejectedDate(new \DateTime());
        $suggestedArticle->setCurationDate(null);
        $suggestedArticle->setCurator($user);
        $em->persist($suggestedArticle);
        $em->flush();

        $reference = $suggestedArticle->getReference();

        // if the suggested Article has an attached reference, we will add it to the list of rejected papers
        if ($reference) {
            // check to see that it already isn't a rejected reference
            $rejectedReference = $em->getRepository('AppBundle:RejectedReference')->findOneByReference($reference);

            if (!$rejectedReference) {
                $rejectedReference = new RejectedReference();
                $rejectedReference->setReference($reference);
                $rejectedReference->setReferenceIdentifier($suggestedArticle->getArticleId());
                $rejectedReference->setCurator($this->get('security.token_storage')->getToken()->getUser());
                $reason = $form['reason'];
                if ($reason) {
                    $rejectedReference->setReason($reason);
                }

                $em->persist($rejectedReference);
                $em->flush();
            }
        }

        return $this->redirectToRoute('suggested_articles');
    }

    /**
     * @Route("/curator/delete-suggested-article/{id}", name="delete_suggested_article")
     */
    public function deleteSuggestedArticleAction(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $returnRoute = $this->redirectToRoute('suggested_articles');
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $suggestedArticle = $em->getRepository('AppBundle:SuggestedArticle')->findOneById($id);

        if (!$suggestedArticle) {
            return $returnRoute;
        }

        $em->remove($suggestedArticle);
        $em->flush();

        return $returnRoute;
    }

    /**
     * Returns a list of all entries with status group Draft.
     *
     * @Route("/curator/draft-entries", name="draft_entries")
     */
    public function listDraftEntriesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entries = $em->getRepository('AppBundle:Entry')->listDraftEntries();

        return $this->render('curator/list/draftEntries.html.twig', array(
            'entries' => $entries,
        ));
    }

    /**
     * Returns a list of all entries with status group Deleted.
     *
     * @Route("/curator/deleted-entries", name="deleted_entries")
     */
    public function listDeletedEntriesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entries = $em->getRepository('AppBundle:Entry')->listDeletedEntries();

        return $this->render('curator/list/deletedEntries.html.twig', array(
            'entries' => $entries,
        ));
    }

    /**
     * Returns a list of all entries with a validator comment or user feedback.
     *
     * @Route("/curator/to-review-entries", name="to_review_entries")
     */
    public function listToReviewEntriesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entries = $em->getRepository('AppBundle:Entry')->listToReviewEntries();

        return $this->render('curator/list/toReviewEntries.html.twig', array(
            'entries' => $entries,
        ));
    }

    /**
     * Returns a list of all entries with status Temporary.
     *
     * @Route("/curator/other-entries", name="other_entries")
     */
    public function listOtherEntriesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entries = $em->getRepository('AppBundle:Entry')->listOtherEntries();

        return $this->render('curator/list/otherEntries.html.twig', array(
            'entries' => $entries,
        ));
    }

    /**
     * @Route("/curator/rejected-papers", name="rejected_references")
     */
    public function listRejectedReferencesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm('rejectedReference');
        $notification = $request->get('info_message');

        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();
            $newIdentifier = $data->getReferenceIdentifier();
            // look for a reference that matches identifier
            $reference = $em->getRepository('AppBundle:Reference')->findByIdentifier($newIdentifier);
            if (!$reference) {
                $notification = RejectedReference::INVALID_REFERENCE;
            } else {
                // look for a rejected reference that matches identifier
                $rejectedReference = $em->getRepository('AppBundle:RejectedReference')->findByReferenceIdentifier($newIdentifier);

                if ($rejectedReference) {
                    $notification = RejectedReference::MESSAGE_DUPLICATE_REFERENCE;
                } else {
                    $rejectedReference = new RejectedReference();
                    $rejectedReference->setReference($reference);
                    $rejectedReference->setReferenceIdentifier($newIdentifier);
                    $rejectedReference->setReason($data->getReason());
                    $rejectedReference->setCurator($this->get('security.token_storage')->getToken()->getUser());

                    $em->persist($rejectedReference);
                    $em->flush();

                    $notification = RejectedReference::REJECTED_REFERENCE_ADDED;
                }
            }
        }

        $rejectedReferences = $em->getRepository('AppBundle:RejectedReference')->findAll();
        $importForm = $this->createForm('importRejectedReferences');

        return $this->render('curator/rejectedReferences.html.twig', array(
            'form' => $form->createView(),
            'rejectedReferences' => $rejectedReferences,
            'info_message' => $notification,
            'importForm' => $importForm->createView(),
        ));
    }

    /**
     * @Route("/curator/rejected-papers/remove/{id}", name="remove_rejected_article")
     */
    public function removeRejectedArticleAction(Request $request, $id = null)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Admin rights required to delete a rejected paper.');
        }

        $em = $this->getDoctrine()->getManager();
        $rejectedReferenceRepo = $em->getRepository('AppBundle:RejectedReference');
        $notification = null;

        $rejectedReference = $rejectedReferenceRepo->find($id);
        if (!$rejectedReference) {
            $notification = RejectedReference::INVALID_REFERENCE;
        } else {
            $suggestedArticle = $em->getRepository('AppBundle:SuggestedArticle')->findOneByReference($rejectedReference->getReference());
            $suggestedArticle->setRejectedDate(null);
            $suggestedArticle->setCurationDate(null);
            $suggestedArticle->setCurator(null);
            $em->persist($suggestedArticle);
            $em->remove($rejectedReference);
            $em->flush();
            $notification = RejectedReference::REFERENCE_DELETED;
        }

        return $this->redirectToRoute('rejected_references', array('info_message' => $notification));
    }

    /**
     * @Route("/curator/rejected-papers/delete/{id}", name="rejected_references_deletion")
     */
    public function deleteRejectedReference(Request $request, $id = null)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('Admin rights required to delete a rejected paper.');
        }

        $em = $this->getDoctrine()->getManager();
        $rejectedReferenceRepo = $em->getRepository('AppBundle:RejectedReference');
        $notification = null;

        $rejectedReference = $rejectedReferenceRepo->findOneById($id);
        $reference = $rejectedReference->getReference();
        $suggestedArticle = $em->getRepository('AppBundle:SuggestedArticle')->findOneByReference($reference);
        if (!$rejectedReference) {
            $notification = RejectedReference::INVALID_REFERENCE;
        } else {
            $em->remove($rejectedReference);
            $em->remove($suggestedArticle);
            $em->remove($reference);
            $em->flush();
            $notification = RejectedReference::REFERENCE_DELETED;
        }

        return $this->redirectToRoute('rejected_references', array('info_message' => $notification));
    }

    /**
     * @Route("/curator/imported-entries/validate/{id}/{info_message}", name="validate_imported_entry")
     */
    public function validateImportedEntryAction(Request $request, $id, $info_message = null)
    {
        $em = $this->getDoctrine()->getManager();
        $dataRetriever = $this->get('entry.data.retriever');
        $validator = $this->get('entry.validator');
        $entry = $dataRetriever->retrieveData($id);
        $persistedData = $this->fetchPersistedData($entry);
        $next = $em->getRepository('AppBundle:Entry')->findNextImportedEntry($id);
        $form = $this->createForm('entry', $entry);

        $em->persist($entry);
        $em->flush();

        return $this->redirectToRoute('edit_entry', array('id' => $entry->getId()));
    }

    /**
     * @Route("/curator/one-by-one-validation", name="one_by_one_validation")
     */
    public function oneByOneValidation(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entries = $em->getRepository('AppBundle:Entry')->listImportedEntries();
        $id = $entries[0]->getId();
        $next = $entries[1];
        $dataRetriever = $this->get('entry.data.retriever');
        $validator = $this->get('entry.validator');
        $entry = $dataRetriever->retrieveData($id);
        $v = $validator->validate($entry, $taxA, $taxB);
        if (!$v) {
            $refused = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::REFUSED_VALIDATOR);
            $entry->setStatus($refused);
            $em->persist($entry);
            $em->flush();
            if ($next) {
                return $this->redirectToRoute('one_by_one_validation');
            } else {
                return $this->redirectToRoute('list_imported_entries');
            }
        } else {
            $acceptedCurator = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::ACCEPTED_CURATOR);
            $entry->setStatus($acceptedCurator);
            $em->persist($entry);
            $em->flush();
        }
        if ($next) {
            return $this->redirectToRoute('one_by_one_validation');
        } else {
            return $this->redirectToRoute('list_imported_entries');
        }

        return $this->redirectToRoute('list_imported_entries');
    }

    /**
     * @Route("/curator/multiple-validation", name="multiple_validation")
     */
    public function multipleValidationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        ini_set('max_execution_time', 1000);

        $entries = $em->getRepository('AppBundle:Entry')->listImportedEntries();
        $countValidate = 0;
        $countUnvalidated = 0;
        $count = 0;
        foreach ($entries as $entry) {
            $entry = $em->getRepository('AppBundle:Entry')->findOneById($entry['id']);
            $dataRetriever = $this->get('entry.data.retriever');
            $entry = $dataRetriever->retrieveData($entry->getId());
            $validator = $this->get('entry.validator');
            $v = $validator->validate($entry);
            if (!$v) {
                ++$countUnvalidated;
                $entities = $validator->getErrorMessages();
                $string = null;
                foreach ($entities as $entity => $fields) {
                    foreach ($fields as $key => $field) {
                        if ('mutations' == $entity && null != $field) {
                            $string .= $entity.' - '.$key.' : '.array_values($field)[0];
                        } else {
                            $string .= $entity.' - '.$key.' : '.$field;
                        }
                    }
                }
                $this->get('log.creator')->createLog($entry, 'Imported Entry validate', $string);
            } else {
                $acceptedCurator = $em->getRepository('AppBundle:EntryStatus')->findOneById(EntryStatus::ACCEPTED_CURATOR);
                $entry->setStatus($acceptedCurator);
                $em->persist($entry);
                $em->flush();
                ++$countValidate;
                $this->get('log.creator')->createLog($entry, 'Imported Entry validate', $acceptedCurator->getName());
            }
            $count = $countValidate + $countUnvalidated;
        }
        if ($countValidate > 1) {
            $nameValid = 'entries';
        } else {
            $nameValid = 'entry';
        }
        if ($countUnvalidated > 1) {
            $nameUnvalid = 'entries';
        } else {
            $nameUnvalid = 'entry';
        }

        return $this->redirectToRoute('list_imported_entries');
    }

    /**
     * @Route("/curator/delete-unaccepted-entries", name="delete_unaccepted_entries")
     */
    public function deleteUnacceptedEntriesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entries = $em->getRepository('AppBundle:Entry')->listImportedEntries();
        foreach ($entries as $entry) {
            $em->remove($entry);
        }
        $em->flush();

        return $this->redirectToRoute('list_imported_entries');
    }

    /**
     * @Route("/curator/cancel-last-import", name="cancel_last_import")
     */
    public function cancelLastImportAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $parameter = $em->getRepository('AppBundle:Parameter')->find(1);
        $entries = $em->getRepository('AppBundle:Entry')->findBy(array('importedNumber' => $parameter->getImportedNumber()));
        foreach ($entries as $entry) {
            $em->remove($entry);
        }
        $em->flush();

        return $this->redirectToRoute('list_imported_entries');
    }

    /**
     * @Route("/curator/multiple-validation-info", name="multiple_validation_info")
     */
    public function multipleValidationInfoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();

        $length = $request->get('length');

        $entries = $em->getRepository('AppBundle:Entry')->listImportedEntries();

        $currentLength = count($entries);

        $nbEntries = $length - $currentLength;

        sleep(120);

        return $response->setData(array('entries' => $nbEntries));
    }

    /**
     * @Route("/curator/export-csv-logs", name="export_csv_logs")
     */
    public function exportCsvLogsAction(Request $request)
    {
        if (null != $request->request->get('date') and null != $request->request->get('dateEnd')) {
            $date = $request->request->get('date');
            $dateEnd = $request->request->get('dateEnd');

            $logs = $this->getDoctrine()
            ->getRepository('AppBundle:Log')
            ->selectAllExportCsv($date, $dateEnd);
            $response = $this->get('export.log')->ExportCsv($logs, $date, $dateEnd);

            return $response;
        } else {
            return $this->redirect($request->server->get('HTTP_REFERER'));
        }
    }

    /**
     * @Route("/curator/ajax-gene-gephebase", name="ajax_gene_gephebase")
     */
    public function ajaxGeneGephebase(Request $request)
    {
        $term = $request->get('query');
        $entries = $this->getDoctrine()
            ->getRepository('AppBundle:Entry')
            ->searchGeneGephebase($term);

        return new JsonResponse($entries);
    }

    /**
     * @Route("/curator/ajax-validator", name="ajax_validator")
     */
    public function ajaxValidator(Request $request)
    {
        $term = $request->get('query');
        $validators = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->searchValidator($term);

        return new JsonResponse($validators);
    }

    /**
     * @Route("/curator/ajax-uniprot", name="ajax_uniprot")
     */
    public function ajaxUniprot(Request $request)
    {
        $term = $request->get('query');
        $entries = $this->getDoctrine()
            ->getRepository('AppBundle:Entry')
            ->searchUniprot($term);

        return new JsonResponse($entries);
    }

    /**
     * @Route("/curator/ajax-go", name="ajax_go")
     */
    public function ajaxGo(Request $request)
    {
        $term = $request->get('query');
        $gos = $this->getDoctrine()
            ->getRepository('AppBundle:Go')
            ->searchGo($term);

        return new JsonResponse($gos);
    }

    /**
     * @Route("/curator/ajax-genbank", name="ajax_genbank")
     */
    public function ajaxGenbank(Request $request)
    {
        $term = $request->get('query');
        $entries = $this->getDoctrine()
            ->getRepository('AppBundle:Entry')
            ->searchGenbank($term);

        return new JsonResponse($entries);
    }

    /**
     * @Route("/curator/ajax-trait", name="ajax_trait")
     */
    public function ajaxTrait(Request $request)
    {
        $term = $request->get('query');
        $trait = $this->getDoctrine()
            ->getRepository('AppBundle:PhenotypeTrait')
            ->searchTrait($term);

        return new JsonResponse($trait);
    }

    /**
     * @Route("/curator/ajax-taxon", name="ajax_taxon")
     */
    public function ajaxTaxon(Request $request)
    {
        $term = $request->get('query');
        $taxon = $this->getDoctrine()
            ->getRepository('AppBundle:Taxon')
            ->searchTaxon($term);

        return new JsonResponse($taxon);
    }

    /**
     * @Route("/curator/ajax-molecular-details", name="ajax_molecular_details")
     */
    public function ajaxMolecularDetails(Request $request)
    {
        $term = $request->get('query');
        $entries = $this->getDoctrine()
            ->getRepository('AppBundle:Entry')
            ->searchMolecularDetails($term);

        return new JsonResponse($entries);
    }

    private function fetchPersistedData($entry)
    {
        $data = array();

        // get uniprotId
        $gene = $entry->getGene();
        if (!$gene || !$gene->getUniProtKbId()) {
            $data['gene'] = null;
        } else {
            $data['gene'] = $gene->getUniProtKbId();
        }

        return $data;
    }

    private function handleRelations($entry, $em, $persistedData, $entryForm)
    {
        $newGene = $entry->getGene();
        if ($newGene && $newGene->getUniProtKbId() !== $persistedData['gene']) {
            $this->detachGeneData($newGene, $em);
            $gene = $em->getRepository('AppBundle:Gene')->findOneByUniProtKbId($newGene->getUniProtKbId());
            if ($gene) {
                $entry->setGene($gene);
            } else {
                $gene = new Gene();
                $gene->setUniProtKbId($newGene->getUniProtKbId());
                $gene->setName($newGene->getName());
                foreach ($newGene->getSynonyms() as $synonym) {
                    if (!$gene->getSynonyms()->contains($synonym)) {
                        $gene->addSynonym($synonym);
                    }
                }
                foreach ($newGene->getGoMolecular() as $molecular) {
                    if (!$gene->getGoMolecular()->contains($molecular)) {
                        $gene->addGoMolecular($molecular);
                    }
                }
                foreach ($newGene->getGoBiological() as $biological) {
                    if (!$gene->getGoBiological()->contains($biological)) {
                        $gene->addGoBiological($biological);
                    }
                }
                foreach ($newGene->getGoCellular() as $cellular) {
                    if (!$gene->getGoCellular()->contains($cellular)) {
                        $gene->addGoCellular($cellular);
                    }
                }
                $em->persist($gene);
                $entry->setGene($gene);
            }
        }

        return $entry;
    }

    /**
     * Detach all related entities to gene to prevent unwanted data propagation bug.
     */
    private function detachGeneData($gene, $em)
    {
        // Detach gene itself
        $em->detach($gene);

        foreach ($gene->getSynonyms() as $synonym) {
            $em->detach($synonym);
        }

        foreach ($gene->getGoCellular() as $go) {
            $em->detach($go);
        }

        foreach ($gene->getGoBiological() as $go) {
            $em->detach($go);
        }

        foreach ($gene->getGoMolecular() as $go) {
            $em->detach($go);
        }
    }

    /**
     * Fix the handleRequest with regards to taxon, trait and gene relations.
     */
    private function fixHandleRequest($userData, $entry, $em)
    {
        $this->fixTraits($userData, $entry, $em);
        $this->fixTaxons($userData, $entry, $em);
        $this->fixMutations($userData, $entry, $em);
        $this->fixValidator($userData, $entry, $em);
    }

    /**
     * Fix the Trait persistence issue.
     */
    private function fixTraits($userData, $entry, $em)
    {
        // reset array keys so they can be matched when iterating through complex traits
        $userTraits = array_values($userData['traits']);

        // first detach all old traits
        foreach ($entry->getTraits() as $complexTrait) {
            $em->detach($complexTrait->getPhenotypeTrait());
        }

        $i = 0;
        foreach ($entry->getTraits() as $complexTrait) {
            if (!isset($userTraits[$i])) {
                continue;
            }

            $complexTrait->setPhenotypeTrait(null);
            $submittedTrait = $userTraits[$i]['phenotypeTrait'];
            $description = $submittedTrait['description'];
            if (isset($submittedTrait['category'])) {
                $category = implode($submittedTrait['category']);
            } else {
                $category = '';
            }
            $trait = $em->getRepository('AppBundle:PhenotypeTrait')->findOneBy(array('description' => $description, 'category' => $category));
            if (!$trait) {
                $trait = new PhenotypeTrait();
                $trait->setDescription($description);
                $trait->setCategory($category);
            }
            $em->persist($trait);

            $complexTrait->setPhenotypeTrait($trait);

            ++$i;
        }
    }

    /**
     * Fix the Taxon persistence issue.
     */
    private function fixTaxons($userData, $entry, $em)
    {
        // reset array keys so they can be matched when iterating through complex taxons
        $userTaxonAs = array_values($userData['taxonAList']);
        $userTaxonBs = array_values($userData['taxonBList']);

        // first detach all old taxons
        foreach ($entry->getTaxonAList() as $complexTaxon) {
            $em->detach($complexTaxon->getTaxon());
        }
        foreach ($entry->getTAxonBList() as $complexTaxon) {
            $em->detach($complexTaxon->getTaxon());
        }

        // fix Taxon A
        $i = 0;
        foreach ($entry->getTaxonAList() as $complexTaxon) {
            if (!isset($userTaxonAs[$i])) {
                continue;
            }

            $complexTaxon->setTaxon(null);
            $submittedTaxon = $userTaxonAs[$i]['taxon'];
            $taxId = $submittedTaxon['taxId'];
            $taxon = $em->getRepository('AppBundle:Taxon')->findOneBy(array('taxId' => $taxId));
            if (!$taxon) {
                $taxon = new Taxon();
                $taxon->setTaxId($taxId);
                $taxon->setLatinName($submittedTaxon['latinName']);
                $taxon->setCommonName($submittedTaxon['commonName']);
            }
            $em->persist($taxon);

            $complexTaxon->setTaxon($taxon);

            ++$i;
        }

        // fix Taxon B
        $i = 0;
        foreach ($entry->getTaxonBList() as $complexTaxon) {
            if (!isset($userTaxonBs[$i])) {
                continue;
            }

            $complexTaxon->setTaxon(null);
            $submittedTaxon = $userTaxonBs[$i]['taxon'];
            $taxId = $submittedTaxon['taxId'];
            $taxon = $em->getRepository('AppBundle:Taxon')->findOneBy(array('taxId' => $taxId));
            if (!$taxon) {
                $taxon = new Taxon();
                $taxon->setTaxId($taxId);
                $taxon->setLatinName($submittedTaxon['latinName']);
                $taxon->setCommonName($submittedTaxon['commonName']);
            }
            $em->persist($taxon);

            $complexTaxon->setTaxon($taxon);

            ++$i;
        }
    }

    /**
     * Fix the Mutation persistence issue.
     */
    private function fixMutations($userData, $entry, $em)
    {
        // reset array keys so they can be matched when iterating through complex traits
        $mutations = array_values($userData['mutations']);

        $detachedReferences = array();

        // first detach all old references and validators
        foreach ($entry->getMutations() as $mutation) {
            $mainReference = $mutation->getMainReference();
            $em->detach($mainReference);
            $detachedReferences[$mutation->getMainReference()->getId()] = $mainReference;
            foreach ($mutation->getOtherReferences() as $mutationReference) {
                $em->detach($mutationReference->getReference());
            }
        }

        $i = 0;
        foreach ($entry->getMutations() as $mutation) {
            if (!isset($mutations[$i])) {
                continue;
            }

            $submittedMainReference = $mutations[$i]['mainReference'];
            $pmId = $submittedMainReference['pmId'];
            $reference = $em->getRepository('AppBundle:Reference')->findByIdentifier($pmId);
            if (!$reference) {
                $mutation->setMainReference(null);
            } else {
                $em->persist($reference);
                $mutation->setMainReference($reference);
            }

            if (array_key_exists('otherReferences', $mutations[$i])) {
                $submittedOtherReferences = array_values($mutations[$i]['otherReferences']);
                $j = 0;
                foreach ($mutation->getOtherReferences() as $mutationReference) {
                    $submittedOtherReference = $submittedOtherReferences[$j]['reference'];
                    $pmId = $submittedOtherReference['pmId'];
                    $reference = $em->getRepository('AppBundle:Reference')->findByIdentifier($pmId);
                    if (!$reference) {
                        // maybe delete the mutationReference instead?
                        $mutationReference->setReference(null);
                    } else {
                        $mutationReference->setReference($reference);
                    }
                    ++$j;
                }
            }

            ++$i;
        }
    }

    /**
     * @param $userData
     * @param Entry                  $entry
     * @param EntityManagerInterface $em
     */
    private function fixValidator($userData, $entry, $em)
    {
        $validators = array();
        if (array_key_exists('validators', $userData)) {
            $validators = array_values($userData['validators']);
        }

        // rearrange submitted data
        $submittedValidators = array();
        foreach ($validators as $validator) {
            $submittedValidators[] = $validator['email'];
        }

        $persistedValidators = array();
        foreach ($entry->getValidators() as $validator) {
            $persistedValidators[] = $validator->getEmail();
        }

        // also look in database for validators that aren't showing up after handle request
        if ($entry->getId()) {
            $hiddenPersistedValidators = $em->getRepository('AppBundle:User')->findValidatorsByEntryId($entry->getId());

            foreach ($hiddenPersistedValidators as $validator) {
                if (!in_array($validator, $persistedValidators)) {
                    $persistedValidators[] = $validator;
                }
            }
        }

        // check for newly added validators
        foreach ($submittedValidators as $submittedValidator) {
            if (!in_array($submittedValidator, $persistedValidators)) {
                $validatorEntry = $em->getRepository('AppBundle:User')->findOneByEmail($submittedValidator);
                $entry->addValidator($validatorEntry);
            }
        }

        // remove deleted validators
        foreach ($persistedValidators as $persistedValidator) {
            if (!in_array($persistedValidator, $submittedValidators)) {
                $validatorEntry = $em->getRepository('AppBundle:User')->findOneByEmail($persistedValidator);
                $entry->removeValidator($validatorEntry);
                $validatorEntry->removeEntriesValidator($entry);
            }
        }
    }
}
