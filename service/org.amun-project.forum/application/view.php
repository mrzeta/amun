<?php
/*
 *  $Id: view.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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

/**
 * view
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    application
 * @subpackage forum
 * @version    $Revision: 875 $
 */
class view extends Amun_Module_ApplicationAbstract
{
	private $forumId;

	public function onLoad()
	{
		if($this->service->hasViewRight())
		{
			// load forum
			$recordForum = $this->getForum();

			$this->template->assign('recordForum', $recordForum);

			// load comments
			$resultComments = $this->getComments();

			$this->template->assign('resultComments', $resultComments);

			// add path
			$this->path->add($recordForum->title, $this->page->url . '/view?id=' . $this->forumId);

			// options
			$options = array();

			if($recordForum->isSticky())
			{
				$options[] = array('sticky', 'Unstick', 'javascript:amun.services.forum.setSticky(' . $this->forumId . ',0,\'' . $this->service->getApiEndpoint() . '\',this)');
			}
			else
			{
				$options[] = array('sticky', 'Sticky', 'javascript:amun.services.forum.setSticky(' . $this->forumId . ',1,\'' . $this->service->getApiEndpoint() . '\',this)');
			}

			if($recordForum->isClosed())
			{
				$options[] = array('close', 'Open', 'javascript:amun.services.forum.setClosed(' . $this->forumId . ',0,\'' . $this->service->getApiEndpoint() . '\',this)');
			}
			else
			{
				$options[] = array('close', 'Close', 'javascript:amun.services.forum.setClosed(' . $this->forumId . ',1,\'' . $this->service->getApiEndpoint() . '\',this)');
			}

			$options[] = array('edit', 'Edit', $this->page->url . '/edit?id=' . $this->forumId);

			$this->setOptions($options);

			// form url
			$url = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/service/comment/form?format=json&method=create&pageId=' . $this->page->id . '&refId=' . $this->forumId;

			$this->template->assign('formUrl', $url);

			// template
			$this->htmlCss->add('forum');
			$this->htmlCss->add('comment');
			$this->htmlJs->add('amun');
			$this->htmlJs->add('forum');
			$this->htmlJs->add('prettify');
			$this->htmlJs->add('ace');

			$this->template->set(__CLASS__ . '.tpl');
		}
		else
		{
			throw new Amun_Exception('Access not allowed');
		}
	}

	private function getForum()
	{
		// get id
		$fragments = $this->getUriFragments();
		$id = isset($fragments[0]) ? intval($fragments[0]) : $this->get->id('integer');

		// query
		$result = Amun_Sql_Table_Registry::get('Service_Forum')
			->select(array('id', 'sticky', 'closed', 'urlTitle', 'title', 'text', 'date'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('Content_Page')
				->select(array('path'), 'page')
			)
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('User_Account')
				->select(array('name', 'profileUrl', 'thumbnailUrl'), 'author')
			)
			->where('id', '=', $id)
			->getRow(PSX_Sql::FETCH_OBJECT);

		$this->forumId = $result->id;

		// redirect to correct url
		if(!isset($fragments[1]) || $fragments[1] != $result->urlTitle)
		{
			PSX_Base::setResponseCode(301);
			header('Location: ' . $this->page->url . '/view/' . $result->id . '/' . $result->urlTitle);
		}

		return $result;
	}

	private function getComments()
	{
		$table = Amun_Sql_Table_Registry::get('Service_Comment')
			->select(array('id', 'text', 'date'))
			->join(PSX_Sql_Join::INNER, Amun_Sql_Table_Registry::get('User_Account')
				->select(array('name', 'profileUrl', 'thumbnailUrl'), 'author')
			)
			->where('pageId', '=', $this->page->id)
			->where('refId', '=', $this->forumId)
			->orderBy('date', PSX_Sql::SORT_ASC);


		$url = new PSX_Url($this->base->getSelf());

		$result = $table->getResultSet($url->getParam('startIndex'), $url->getParam('count'), $url->getParam('sortBy'), $url->getParam('sortOrder'), $url->getParam('filterBy'), $url->getParam('filterOp'), $url->getParam('filterValue'), $url->getParam('updatedSince'), PSX_SQL::FETCH_OBJECT);


		$paging = new PSX_Html_Paging($url, $result);

		$this->template->assign('pagingComments', $paging, 0);


		return $result;
	}
}

