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
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportLog
{
    public function exportCsv($logs, $start = null, $end = null) {

        $date = new \DateTime($start);
        $dateEnd = new \Datetime($end);
    	$response = new StreamedResponse();
        $response->setCallback(function() use($logs){
            $handle = fopen('php://output', 'w+');

            //fputcsv($handle,chr(255) . chr(254));
            fputcsv($handle, array(
                'Log title',
                'Entry Gephe ID',
                'Logs date',
                'Logs description',
                'User'
                ),',');

                foreach($logs as $row) {
                    fputcsv($handle,array(
                        $row['logname'],
                        $row['gepheId'],
                        $row['datelog']->format('Y-m-d H:i:s'),
                        $row['description'],
                        mb_convert_encoding($row['name'], 'UTF-16LE', 'UTF-8').' '.mb_convert_encoding($row['surname'], 'UTF-16LE', 'UTF-8').' '.mb_convert_encoding($row['username'], 'UTF-16LE', 'UTF-8')
                    ),',');
                }
    
                fclose($handle);
        });
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition','attachment; filename="Gephebase-logs-'.$date->format('Y-m-d').'_'.$dateEnd->format('Y-m-d').'.csv"');
        return $response; 
    }
}