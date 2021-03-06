<?php
/*
 * amun
 * A social content managment system based on the psx framework. For
 * the current version and informations visit <http://amun.phpsx.org>
 *
 * Copyright (c) 2010-2013 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace AmunService\News\Application;

use Amun\Exception;
use Amun\Module\ApplicationAbstract;
use Amun\Html;
use PSX\Atom;
use PSX\DateTime;
use PSX\Sql;
use PSX\Url;
use PSX\Html\Paging;
use DateInterval;

/**
 * Index
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Index extends ApplicationAbstract
{
	/**
	 * @httpMethod GET
	 * @path /
	 */
	public function doIndex()
	{
		if($this->user->hasRight('news_view'))
		{
			// load news
			$resultNews = $this->getNews();

			$this->template->assign('resultNews', $resultNews);

			// options
			$url = $this->service->getApiEndpoint() . '/form?format=json&method=create&pageId=' . $this->page->getId();

			$this->setOptions(array(
				array('news_add', 'Add', 'javascript:amun.services.news.showForm(\'' . $url . '\')')
			));

			// template
			$this->htmlCss->add('news');
			$this->htmlJs->add('news');
			$this->htmlJs->add('ace-html');
			$this->htmlJs->add('bootstrap');
			$this->htmlJs->add('prettify');
			$this->htmlContent->add(Html\Content::META, Atom\Writer::link($this->page->getTitle(), $this->service->getApiEndpoint() . '?format=atom&filterBy=pageId&filterOp=equals&filterValue=' . $this->page->getId()));
		}
		else
		{
			throw new Exception('Access not allowed');
		}
	}

	/**
	 * @httpMethod GET
	 * @path /archive/{year}/{month}
	 */
	public function doArchive()
	{
		$this->doIndex();
	}

	private function getNews()
	{
		$con = $this->getRequestCondition();
		$con->add('pageId', '=', $this->page->getId());

		// archive
		$year  = (integer) $this->getUriFragments('year');
		$month = (integer) $this->getUriFragments('month');

		// i think this software will not be used after the year 3000 if so 
		// please travel back in time and slap me in the face ... nothing 
		// happens ;D
		if(($year > 2010 && $year < 3000) && ($month > 0 && $month < 13))
		{
			$date = new DateTime($year . '-' . ($month < 10 ? '0' : '') . $month . '-01', $this->registry['core.default_timezone']);

			$con->add('date', '>=', $date->format(DateTime::SQL));
			$con->add('date', '<', $date->add(new DateInterval('P1M'))->format(DateTime::SQL));
		}

		$url   = new Url($this->base->getSelf());
		$count = $url->getParam('count') > 0 ? $url->getParam('count') : 8;
		$count = $count > 16 ? 16 : $count;

		$result = $this->getHandler()->getResultSet(array(),
			$url->getParam('startIndex'), 
			$count, 
			$url->getParam('sortBy'), 
			$url->getParam('sortOrder'), 
			$con,
			SQL::FETCH_OBJECT);


		$paging = new Paging($url, $result);

		$this->template->assign('pagingNews', $paging, 0);


		return $result;
	}
}

