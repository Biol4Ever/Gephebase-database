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

namespace AppBundle\Http;

class RequestHandler
{
    private $error = false;

    public function postFile($targetUrl, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $targetUrl);                                                         
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: form-data',
        ));                                                                                                                                                                                    
                                                                                                                             
        $response = curl_exec($ch);
        $this->checkStatusCode($ch);
        curl_close($ch);

        if ($this->error) {
            return false;
        }

        return $response;
    }

    public function postJson($targetUrl, $data)
    {

        $ch = curl_init($targetUrl);                                                            
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);                                                                      
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
            'Content-Type: application/json',                                                                                
            'Content-Length: ' . strlen($data))                                                                       
        );                                                                                                                   
                                                                                                                             
        $response = curl_exec($ch);
        $this->checkStatusCode($ch);
        curl_close($ch);

        if ($this->error) {
            return false;
        }

        return $response;
    }

    public function get($targetUrl)
    {
        $count = 0;
        do {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $targetUrl);                                                             
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Gephebase User Agent');

            $response = curl_exec($ch);
            $this->checkStatusCode($ch);
            curl_close($ch);
            sleep(1);
            $count++;
        } while(($count < 4 && $this->error === true));
        
        if ($this->error) {
            return false;
        }

        return $response;
    }

    /**
     * Normal get request without the loop for retrying
     */
    public function getRequest($targetUrl)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $targetUrl);                                                             
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Gephebase User Agent');

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    private function checkStatusCode($ch)
    {
        $info = curl_getinfo($ch);

        if (substr($info['http_code'], 0, 1) !== "2") {
            $this->error = true;
        } else {
            $this->error = false;
        }
    }
}