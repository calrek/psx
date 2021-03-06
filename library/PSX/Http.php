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

namespace PSX;

use PSX\Http\Cookie;
use PSX\Http\CookieStoreInterface;
use PSX\Http\GetRequest;
use PSX\Http\HandlerInterface;
use PSX\Http\Handler\Curl;
use PSX\Http\RedirectException;
use PSX\Http\Request;
use PSX\Http\Response;

/**
 * This class offers a simple way to make http requests. It can use either curl
 * (recommended) or fsockopen handler to send the request. Here an example of
 * an basic GET request
 * <code>
 * $http     = new Http();
 * $request  = new GetRequest('http://google.com');
 * $response = $http->request($request);
 *
 * if($response->getCode() == 200)
 * {
 *   echo $response->getBody();
 * }
 * </code>
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class Http
{
	public static $newLine = "\r\n";
	public static $codes   = array(

		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported'

	);

	private $lastError;
	private $request;
	private $response;
	private $cookieStore;

	/**
	 * If no handler is defined the curl handler is used as fallback
	 *
	 * @param PSX\Http\HandlerInterface $handler
	 */
	public function __construct(HandlerInterface $handler = null)
	{
		$this->setHandler($handler !== null ? $handler : new Curl());
	}

	/**
	 * Sends the request through the given handler and returns the response
	 *
	 * @param PSX\Http\Request $request
	 * @return PSX\Http\Response
	 */
	public function request(Request $request, $count = 0)
	{
		// set cookie headers
		if($this->cookieStore !== null)
		{
			$cookies = $this->cookieStore->load($request->getUrl()->getHost());

			if(!empty($cookies))
			{
				$kv = array();

				foreach($cookies as $cookie)
				{
					if($cookie->getExpires() !== null && $cookie->getExpires()->getTimestamp() < time())
					{
						$this->cookieStore->remove($request->getUrl()->getHost(), $cookie);
					}
					else if($cookie->getPath() !== null && substr($request->getUrl()->getPath(), 0, strlen($cookie->getPath())) != $cookie->getPath())
					{
						// path does not fit
					}
					else
					{
						$kv[] = $cookie->getName() . '=' . $cookie->getValue();
					}
				}

				$request->addHeader('Cookie', implode('; ', $kv));
			}
		}

		// make request
		$response = Response::convert($this->handler->request($request));

		// store request infos
		$this->lastError = $this->handler->getLastError();
		$this->request   = $this->handler->getRequest();
		$this->response  = $this->handler->getResponse();

		// store cookies
		if($this->cookieStore !== null)
		{
			$cookies = $response->getHeader('Set-Cookie');

			if(!empty($cookies))
			{
				foreach($cookies as $rawCookie)
				{
					$cookie = Cookie::convert($rawCookie);

					if($cookie instanceof Cookie)
					{
						$domain = $cookie->getDomain() !== null ? $cookie->getDomain() : $request->getUrl()->getHost();

						$this->cookieStore->store($domain, $cookie);
					}
				}
			}
		}

		// check follow location
		if($request->getFollowLocation() && ($response->getCode() >= 300 && $response->getCode() < 400))
		{
			$location = $response->getHeader('Location');

			if(!empty($location) && $location != $request->getUrl()->__toString())
			{
				if($request->getMaxRedirects() > $count)
				{
					if(strpos($location, '://') === false)
					{
						$location = $request->getUrl()->getScheme() . '://' . $request->getUrl()->getHost() . '/' . $location;
					}

					$locationRequest = new GetRequest($location);
					$locationRequest->setFollowLocation(true, $request->getMaxRedirects());

					return $this->request($locationRequest, ++$count);
				}
				else
				{
					throw new RedirectException('Max redirection reached');
				}
			}
		}

		return $response;
	}

	/**
	 * Sets the handler
	 *
	 * @param PSX\Http\HandlerInterface $handler
	 * @return void
	 */
	public function setHandler(HandlerInterface $handler)
	{
		$this->handler = $handler;
	}

	/**
	 * Returns the handler
	 *
	 * @return PSX\Http\HandlerInterface
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * Sets an cookie store
	 *
	 * @return void
	 */
	public function setCookieStore(CookieStoreInterface $cookieStore)
	{
		$this->cookieStore = $cookieStore;
	}

	/**
	 * Returns the cookie store
	 *
	 * @return PSX\Http\CookieStoreInterface
	 */
	public function getCookieStore()
	{
		return $this->cookieStore;
	}

	/**
	 * Returns the error from the last request or false if no error has occured
	 *
	 * @return string
	 */
	public function getLastError()
	{
		return $this->lastError;
	}

	/**
	 * Returns the raw http request string
	 *
	 * @return string
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Returns the raw http response string
	 *
	 * @return string
	 */
	public function getResponse()
	{
		return $this->response;
	}
}

