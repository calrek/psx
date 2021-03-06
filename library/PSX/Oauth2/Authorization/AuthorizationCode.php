<?php
/*
 * psx
 * A object oriented and modular based PHP framework for developing
 * dynamic web applications. For the current version and informations
 * visit <http://phpsx.org>
 *
 * Copyright (c) 2010-2013 Christoph Kappestein <k42b3.x@gmail.com>
 *
 * This file is part of psx. psx is free software: you can
 * redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * psx is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with psx. If not, see <http://www.gnu.org/licenses/>.
 */

namespace PSX\Oauth2\Authorization;

use PSX\Base;
use PSX\Oauth2\AuthorizationAbstract;
use PSX\Url;

/**
 * AuthorizationCode
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class AuthorizationCode extends AuthorizationAbstract
{
	public function getAccessToken($redirectUri = null)
	{
		$state = isset($_GET['state']) ? $_GET['state'] : null;

		if(!isset($_GET['error']))
		{
			// request data
			$code = isset($_GET['code']) ? $_GET['code'] : null;
			$data = array(

				'grant_type' => 'authorization_code',
				'code'       => $code,

			);

			if(isset($redirectUri))
			{
				$data['redirect_uri'] = $redirectUri;
			}

			// authentication
			$header = array(
				'Accept'     => 'application/json',
				'User-Agent' => __CLASS__ . ' ' . Base::VERSION,
			);

			if($this->type == self::AUTH_BASIC)
			{
				$header['Authorization'] = 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret);
			}

			if($this->type == self::AUTH_POST)
			{
				$data['client_id']     = $this->clientId;
				$data['client_secret'] = $this->clientSecret;
			}

			// send request
			return $this->request($header, $data);
		}
		else
		{
			self::throwErrorException($_GET);
		}
	}

	public static function redirect(Url $url, $clientId, $redirectUri = null, $scope = null, $state = null)
	{
		$url->setScheme('https');
		$url->addParam('response_type', 'code');
		$url->addParam('client_id', $clientId);

		if(isset($redirectUri))
		{
			$url->addParam('redirect_uri', $redirectUri);
		}

		if(isset($scope))
		{
			$url->addParam('scope', $scope);
		}

		if(isset($state))
		{
			$url->addParam('state', $state);
		}

		header('Location: ' . strval($url));
		exit;
	}
}
