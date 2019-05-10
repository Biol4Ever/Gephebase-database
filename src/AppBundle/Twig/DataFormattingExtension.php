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
class DataFormattingExtension extends \Twig_Extension
{
    const DISPLAY_MAX_AUTHORS = 3;

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('formatTaxon', array($this, 'formatTaxon'), array(
                'is_safe' => array('html'),
                'needs_environment' => true,
            )),
            new \Twig_SimpleFunction('formatReference', array($this, 'formatReference'), array(
                'is_safe' => array('html'),
                'needs_environment' => true,
            )),
            new \Twig_SimpleFunction('formatTraitCategory', array($this, 'formatTraitCategory'), array(
                'is_safe' => array('html'),
                'needs_environment' => true,
            )),
        );
    }

    /**
     * Properlydisplay the common name, latin name and rank of a taxon
     */
    public function formatTaxon(\Twig_Environment $twig, $latinName, $commonName = null, $rank = null, $ancestralState = null)
    {
        return $twig->render('entry/list/formatTaxon.html.twig', array(
            'commonName' => $commonName,
            'latinName' => $latinName,
            'rank' => $rank,
            'ancestralState' => $ancestralState,
        ));
    }

    /**
     * Properly display a reference in entry list
     */
    public function formatReference(\Twig_Environment $twig, $pmId, $journalYear, $articleTitle, $authors, $others)
    {

        return $twig->render('entry/list/formatReference.html.twig', array(
            'pmId' => $pmId,
            'journalYear' => $journalYear,
            'articleTitle' => $articleTitle,
            'authors' => $authors,
            'maxAuthors' => self::DISPLAY_MAX_AUTHORS,
            'others' => $others,
        ));
    }

    /**
     * Splits a trait category string into all the trait categories
     */
    public function formatTraitCategory(\Twig_Environment $twig, $traitCategory)
    {
        $matches = array();

        preg_match_all('/([A-Z][a-z]+)/', $traitCategory, $matches);

        if (isset($matches[0])) {
            $categories = $matches[0];
        } else {
            $categories = null;
        }

        return $twig->render('entry/list/formatTraitCategory.html.twig', array(
            'categories' => $categories,
        ));
    }    

    public function getName()
    {
        return 'dataFormattingExtension';
    }
}