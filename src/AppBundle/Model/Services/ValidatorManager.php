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

class ValidatorManager
{
	private $fosManager;

	public function __construct($fosManager)
	{
		$this->fosManager = $fosManager;
	}

	/**
	 * Creates a validator from a given email, and optionnally given name and surname
	 */
	public function createValidator($email, $name = null, $surname = null)
	{
		$user = $this->fosManager->createUser();
    
        $date = new \DateTime();
        $interval = new \DateInterval('P1M');
        $date->add($interval);

        $user->setPlainPassword($email . date('Y'));
        $username = "";
        $username .= $name;
        $username .= $surname;
        $username .= $this->createToken(20);
        $user->setUsername($username);
        $user->addRole('ROLE_VALIDATOR');
        $user->setExpiresAt($date);
        $user->setEmail($email);
        $user->setName($name);
        $user->setSurname($surname);
        $user->setEnabled(true);
        $user->setToken($this->createToken(50));

        $this->fosManager->updateUser($user);


		return $user;
	}

    /**
     * Creates a token of given length
     */
    private function createToken($length)
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        $max = strlen($codeAlphabet) - 1;
        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[$this->crypto_rand_secure(0, $max)];
        }

        return $token;
    }

    /**
     * random number generator with min and max limits
     */
    private function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) return $min; 
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
}