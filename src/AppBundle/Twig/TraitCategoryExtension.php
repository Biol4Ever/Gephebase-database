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
 * Split a string of concatnated phenotype traits based on capital letters
 */
class TraitCategoryExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('splitCategory', array($this, 'splitCategory')),
        );
    }

    public function splitCategory($category)
    {
        $result = '';

        if (!$category || $category === '') {
            return $result;
        }

        // split the database string  
        $categories = preg_split('/(?=[A-Z])/',$category);

        foreach ($categories as $category) {
            if (!($result === '')) {
                $result .= ', ';
            }
            $result .= $category;
        }

        return $result;
    }

    public function getName()
    {
        return 'traitCategoryExtension';
    }
}