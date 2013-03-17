<?php
/*
 *  $Id: Forum.php 880 2012-10-27 13:14:26Z k42b3.x@googlemail.com $
 *
 * amun
 * A social content managment system based on the psx framework. For
 * the current version and informations visit <http://amun.phpsx.org>
 *
 * Copyright (c) 2010-2012 Christoph Kappestein <k42b3.x@gmail.com>
 *
 * This file is part of amun. amun is free software: you can
 * redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or any later version.
 *
 * amun is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with amun. If not, see <http://www.gnu.org/licenses/>.
 */

namespace AmunService\Forum;

use Amun\DataFactory;
use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use Amun\Filter as AmunFilter;
use Amun\Util;
use PSX\Data\WriterInterface;
use PSX\Data\WriterResult;
use PSX\DateTime;
use PSX\Filter;
use PSX\Util\Markdown;
use PSX\Sql;
use PSX\Sql\Join;

/**
 * Amun_Service_Forum
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Forum
 * @version    $Revision: 880 $
 */
class Record extends RecordAbstract
{
	const NORMAL = 0x0;
	const STICKY = 0x1;
	const CLOSED = 0x2;

	protected $_page;
	protected $_user;
	protected $_date;
	protected $_replyCount;
	protected $_lastReply;

	public function setId($id)
	{
		$id = $this->_validate->apply($id, 'integer', array(new AmunFilter\Id($this->_table)), 'id', 'Id');

		if(!$this->_validate->hasError())
		{
			$this->id = $id;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setPageId($pageId)
	{
		$pageId = $this->_validate->apply($pageId, 'integer', array(new AmunFilter\Id(DataFactory::getTable('Content_Page'))), 'pageId', 'Page Id');

		if(!$this->_validate->hasError())
		{
			$this->pageId = $pageId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setSticky($sticky)
	{
		$sticky = $this->_validate->apply($sticky, 'boolean', array(), 'sticky', 'Sticky');

		if(!$this->_validate->hasError())
		{
			$this->sticky = $sticky ? '1' : '0';
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setClosed($closed)
	{
		$closed = $this->_validate->apply($closed, 'boolean', array(), 'closed', 'Closed');

		if(!$this->_validate->hasError())
		{
			$this->closed = $closed ? '1' : '0';
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setUrlTitle($urlTitle)
	{
		$urlTitle = $this->_validate->apply($urlTitle, 'string', array(new AmunFilter\UrlTitle(), new Filter\Length(3, 128)), 'urlTitle', 'Url Title');

		if(!$this->_validate->hasError())
		{
			$this->urlTitle = $urlTitle;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setTitle($title)
	{
		$title = $this->_validate->apply($title, 'string', array(new Filter\Length(3, 256), new Filter\Html()), 'title', 'Title');

		if(!$this->_validate->hasError())
		{
			$this->setUrlTitle($title);

			$this->title = $title;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setText($text)
	{
		$text = Markdown::decode($text);
		$text = $this->_validate->apply($text, 'string', array(new Filter\Length(3, 4096), new AmunFilter\Html($this->_config, $this->_user)), 'text', 'Text');

		if(!$this->_validate->hasError())
		{
			$this->text = $text;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function getId()
	{
		return $this->_base->getUrn('forum', $this->id);
	}

	public function getPage()
	{
		if($this->_page === null)
		{
			$this->_page = DataFactory::getTable('Content_Page')->getRecord($this->pageId);
		}

		return $this->_page;
	}

	public function getUser()
	{
		if($this->_user === null)
		{
			$this->_user = DataFactory::getTable('User_Account')->getRecord($this->userId);
		}

		return $this->_user;
	}

	public function getText()
	{
		return htmlspecialchars($this->text, ENT_NOQUOTES, 'UTF-8');
	}

	public function getDate()
	{
		if($this->_date === null)
		{
			$this->_date = new DateTime($this->date, $this->_registry['core.default_timezone']);
		}

		return $this->_date;
	}

	public function getReplyCount()
	{
		if($this->_replyCount === null)
		{
			$this->_replyCount = DataFactory::getTable('Comment')
				->select()
				->where('pageId', '=', $this->pageId)
				->where('refId', '=', $this->id)
				->getTotalResults();
		}

		return $this->_replyCount;
	}

	public function getLastReply()
	{
		if($this->_lastReply === null)
		{
			$this->_lastReply = DataFactory::getTable('Comment')
				->select(array('id', 'sticky', 'closed', 'pageId', 'title', 'url', 'date'))
				->join(Join::INNER, DataFactory::getTable('User_Account')
					->select(array('name', 'profileUrl'), 'author')
				)
				->where('pageId', '=', $this->pageId)
				->where('refId', '=', $this->id)
				->getRow(Sql::FETCH_OBJECT);
		}

		return $this->_lastReply;
	}

	public function getUrl()
	{
		return $this->_config['psx_url'] . '/' . $this->_config['psx_dispatch'] . $this->pagePath . '/view/' . $this->id . '/' . $this->urlTitle;
	}

	public function isSticky()
	{
		return (boolean) $this->sticky;
	}

	public function isClosed()
	{
		return (boolean) $this->closed;
	}

	public function export(WriterResult $result)
	{
		switch($result->getType())
		{
			case WriterInterface::JSON:
			case WriterInterface::XML:

				return parent::export($result);

				break;

			case WriterInterface::ATOM:

				$entry = $result->getWriter()->createEntry();

				$entry->setTitle($this->title);
				$entry->setId('urn:uuid:' . $this->globalId);
				$entry->setUpdated($this->getDate());
				$entry->addAuthor($this->authorName, $this->authorProfileUrl);
				$entry->addLink($this->getUrl(), 'alternate', 'text/html');
				$entry->setContent($this->text, 'html');

				return $entry;

				break;

			default:

				throw new Exception('Writer is not supported');

				break;
		}
	}

	public static function getStatus($status = false)
	{
		$s = array(

			self::NORMAL => 'Normal',
			self::STICKY => 'Sticky',
			self::CLOSED => 'Closed',

		);

		if($status !== false)
		{
			$status = intval($status);

			if(array_key_exists($status, $s))
			{
				return $s[$status];
			}
			else
			{
				return false;
			}
		}
		else
		{
			return $s;
		}
	}
}


