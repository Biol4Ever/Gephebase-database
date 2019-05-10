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

namespace AppBundle\Twig;

/**
 * Twig extensions for References
 */
class ReferenceExtension extends \Twig_Extension
{
    const PUBMED_LINK = 'https://www.ncbi.nlm.nih.gov/pubmed/';
    const DOI_LINK = 'https://dx.doi.org/';

    private $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('displayReference', array($this, 'displayReference'), array(
                'is_safe' => array('html'),
                'needs_environment' => true,
            )),
            new \Twig_SimpleFunction('countRejectedPapers', array($this, 'countRejectedPapers'), array(
                'is_safe' => array('html'),
                'needs_environment' => true,
            )),
            new \Twig_SimpleFunction('displayPublicationYears', array($this, 'displayPublicationYears'), array(
                'is_safe' => array('html'),
                'needs_environment' => true,
            )),
            new \Twig_SimpleFunction('displayTruncatedAuthors', array($this, 'displayTruncatedAuthors')),
            new \Twig_SimpleFunction('listRejectedReferences', array($this, 'listRejectedReferences')),
            new \Twig_SimpleFunction('pubmedLink', array($this, 'pubmedLink')),
            new \Twig_SimpleFunction('doiLink', array($this, 'doiLink')),
        );
    }

    /**
     * Display the following data of a reference: author + date + title
     */
    public function displayReference(\Twig_Environment $twig, $reference, $full = false)
    {
        return $twig->render('curator/displayReference.html.twig', array(
            'reference' => $reference,
            'full' => $full,
        ));
    }

    /**
     * Display truncated list of authors, max 3 + appended "et al." if there are more than 3.
     */
    public function displayTruncatedAuthors($reference)
    {
        if (!$reference) {
            return "";
        }

        $authors = $reference->getAuthors();
        $append = "";
        $result = "";
        $i = 0;

        foreach($authors as $author) {
            if ($i>2) {
                $result .= ' et al.';
                break;
            }

            if ($i!==0) {
                $result.= " ; ";
            }

            $result .= $author->getLastname()." ".$author->getInitials();
            $i++;
        }

        return $result;
    }

    /**
     * Display the number of rejected papers found in search results.
     * Clicking on the number opens a popup containing that list of rejected papers.
     */
    public function countRejectedPapers(\Twig_Environment $twig, $entries)
    {
        $rejectedPapers = $this->em->getRepository('AppBundle:RejectedReference')->findAllAssoc();
        $foundRejectedPapers = [];

        foreach($entries as $entry) {
            if(array_key_exists($entry['pmId'], $rejectedPapers)) {
                $foundRejectedPapers[] = $rejectedPapers[$entry['pmId']];
            }
        }

        return $twig->render('curator/countRejectedPapers.html.twig', array(
            'foundRejectedPapers' => $foundRejectedPapers,
        ));
    }

    /**
     * Displays hidden divs which contain the date of the oldest and newest publications in our database
     */
    public function displayPublicationYears(\Twig_Environment $twig)
    {
        $years = $this->em->getRepository('AppBundle:Reference')->findOldestAndNewestReferences();

        return $twig->render('curator/reference/publicationYears.html.twig', array(
            'oldest' => trim($years['oldest']),
            'newest' => trim($years['newest']),
        ));
    }

    /**
     * Returns an array containing all rejected references in the database
     */
    public function listRejectedReferences()
    {
        $rejectedPapers = $this->em->getRepository('AppBundle:RejectedReference')->findAllReferences();

        return $rejectedPapers;
    }

    /**
     * Creates a link to pubmed reference based on pubmed id
     */
    public function pubmedLink($id)
    {
        return self::PUBMED_LINK . $id;
    }

    /**
     * Creates a link to doi reference based on doi
     */
    public function doiLink($id)
    {
        return self::DOI_LINK . $id;
    }

    public function getName()
    {
        return 'referenceExtension';
    }
}