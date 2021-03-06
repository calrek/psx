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

namespace PSX\Rss;

use DOMDocument;
use DOMElement;
use PSX\DateTime;
use PSX\Data\InvalidDataException;
use PSX\Data\NotSupportedException;
use PSX\Data\ReaderInterface;
use PSX\Data\ReaderResult;
use PSX\Data\RecordAbstract;
use PSX\Rss;

/**
 * Item
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://phpsx.org
 */
class Item extends RecordAbstract
{
	public $title;
	public $link;
	public $description;
	public $author;
	public $category  = array();
	public $comments;
	public $enclosure;
	public $guid;
	public $pubdate;
	public $source;

	private $dom;
	private $element;

	public function getName()
	{
		return 'item';
	}

	public function getFields()
	{
		return array(

			'title'       => $this->title,
			'link'        => $this->link,
			'description' => $this->description,
			'author'      => $this->author,
			'category'    => $this->category,
			'comments'    => $this->comments,
			'enclosure'   => $this->enclosure,
			'guid'        => $this->guid,
			'pubdate'     => $this->pubdate,
			'source'      => $this->source,

		);
	}

	public function import(ReaderResult $result)
	{
		switch($result->getType())
		{
			case ReaderInterface::DOM:

				$item = $result->getData();

				if($item instanceof DOMDocument)
				{
					$this->dom = $item;

					$root = $item->documentElement;
				}
				else if($item instanceof DOMElement)
				{
					$this->dom = $item->ownerDocument;

					$root = $item;
				}
				else
				{
					throw new InvalidDataException('Data must be an instance of DOMDocument or DOMElement');
				}

				if(strcasecmp($root->localName, 'item') == 0)
				{
					$this->parseItemElement($root);
				}
				else
				{
					throw new InvalidDataException('No item element found');
				}

				break;

			default:

				throw new NotSupportedException('Reader is not supported');
		}
	}

	private function parseItemElement(DOMElement $item)
	{
		$this->element = $item;

		$childNodes = $item->childNodes;

		for($i = 0; $i < $childNodes->length; $i++)
		{
			$item = $childNodes->item($i);

			if($item->nodeType != XML_ELEMENT_NODE)
			{
				continue;
			}


			$name = strtolower($item->localName);

			switch($name)
			{
				case 'title':
				case 'link':
				case 'description':
				case 'author':
				case 'comments':
				case 'guid':

					$this->$name = $item->nodeValue;

					break;

				case 'category':

					array_push($this->$name, Rss::categoryConstruct($item));

					break;

				case 'enclosure':

					$this->enclosure = array(

						'url'    => $item->getAttribute('url'),
						'length' => $item->getAttribute('length'),
						'type'   => $item->getAttribute('type'),

					);

					break;

				case 'pubdate':

					$this->pubdate = new DateTime($item->nodeValue);

					break;

				case 'source':

					$this->source = array(

						'text' => $item->nodeValues,
						'url'  => $item->getAttribute('url'),

					);

					break;
			}
		}
	}

	public function getDom()
	{
		return $this->dom;
	}

	public function getElement()
	{
		return $this->element;
	}
}


