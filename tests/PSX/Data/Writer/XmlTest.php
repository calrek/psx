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

namespace PSX\Data\Writer;

use PSX\Data\WriterTestCase;

/**
 * XmlTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class XmlTest extends WriterTestCase
{
	public function testWrite()
	{
		ob_start();

		$writer = new Xml();
		$writer->write($this->getRecord());

		$actual = ob_get_contents();

		ob_end_clean();


		$expect = <<<TEXT
<?xml version="1.0" encoding="UTF-8"?>
<record>
  <id>1</id>
  <author>foo</author>
  <title>bar</title>
  <content>foobar</content>
  <date>2012-03-11 13:37:21</date>
</record>
TEXT;

		$this->assertXmlStringEqualsXmlString($expect, $actual);
	}

	public function testWriteResultSet()
	{
		ob_start();

		$writer = new Xml();
		$writer->write($this->getResultSet());

		$actual = ob_get_contents();

		ob_end_clean();


		$expect = <<<TEXT
<?xml version="1.0" encoding="UTF-8"?>
<resultset>
  <totalResults>2</totalResults>
  <startIndex>0</startIndex>
  <itemsPerPage>8</itemsPerPage>
  <entry>
    <id>1</id>
    <author>foo</author>
    <title>bar</title>
    <content>foobar</content>
    <date>2012-03-11 13:37:21</date>
  </entry>
  <entry>
    <id>2</id>
    <author>foo</author>
    <title>bar</title>
    <content>foobar</content>
    <date>2012-03-11 13:37:21</date>
  </entry>
</resultset>
TEXT;

		$this->assertXmlStringEqualsXmlString($expect, $actual);
	}

	public function testWriteComplex()
	{
		ob_start();

		$writer = new Xml();
		$writer->write($this->getComplexRecord());

		$actual = ob_get_contents();

		ob_end_clean();


		$expect = <<<TEXT
<?xml version="1.0" encoding="UTF-8"?>
<activity>
  <published>2011-02-10T15:04:55+00:00</published>
  <actor>
    <displayName>Martin Smith</displayName>
    <id>tag:example.org,2011:martin</id>
    <objectType>person</objectType>
    <url>http://example.org/martin</url>
  </actor>
  <object>
    <id>tag:example.org,2011:abc123/xyz</id>
    <url>http://example.org/blog/2011/02/entry</url>
  </object>
  <target>
    <displayName>Martin's Blog</displayName>
    <id>tag:example.org,2011:abc123</id>
    <objectType>blog</objectType>
    <url>http://example.org/blog/</url>
  </target>
  <verb>post</verb>
</activity>
TEXT;

		$this->assertXmlStringEqualsXmlString($expect, $actual);
	}
}