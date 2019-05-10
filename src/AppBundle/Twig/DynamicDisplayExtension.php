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

use AppBundle\Entity\Entry;

/**
 * Twig extensions for References
 */
class DynamicDisplayExtension extends \Twig_Extension
{
    private $entryManager;

    public function __construct($entryManager)
    {
        $this->entryManager = $entryManager;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('toggleHide', array($this, 'toggleHide')),
            new \Twig_SimpleFunction('toggleShow', array($this, 'toggleShow')),
            new \Twig_SimpleFunction('getHiddenSearchFields', array($this, 'getHiddenSearchFields')),
            new \Twig_SimpleFunction('isComplexEntry', array($this, 'isComplexEntry')),
        );
    }

    /**
     * Returns bootstrap "hidden" class if element is not set
     */
    public function toggleShow($element)
    {
        if ($element) {
            return '';
        }

        return 'hidden';
    }

    /**
     * Returns bootstrap "hidden" class if element is set
     */
    public function toggleHide($element)
    {
        if ($element) {
            return 'hidden';
        }

        return '';
    }

    /**
     * Returns an array of all advanced search fields that should be hidden on page load (custom query fields)
     */
    public function getHiddenSearchFields()
    {
        $hiddenCriterias = Entry::getHiddenCriteriaList();

        return array_keys($hiddenCriterias);
    }

    /**
     * Checks if the entry is a complex case (multiple traits, taxons or mutations)
     */
    public function isComplexEntry($entry)
    {
        if ($this->entryManager->isComplex($entry)) {
            return true;
        }

        return false;
    }

    public function getName()
    {
        return 'dynamicDisplayExtension';
    }
}