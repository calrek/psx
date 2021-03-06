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

namespace PSX\Http;

/**
 * CookieStoreInterface
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
interface CookieStoreInterface
{
	/**
	 * Saves a cookie for the domain
	 *
	 * @param string $domain
	 * @param PSX\Http\Cookie $cookie
	 * @return void
	 */
	public function store($domain, Cookie $cookie);

	/**
	 * Returns all cookies for the domain
	 *
	 * @param string $domain
	 * @return array<PSX\Http\Cookie>
	 */
	public function load($domain);

	/**
	 * Removes the cookie from the store
	 *
	 * @param string $domain
	 * @param PSX\Http\Cookie $cookie
	 * @return void
	 */
	public function remove($domain, Cookie $cookie);
}

