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

namespace AmunService\News\Gadget;

use Amun\Module\GadgetAbstract;
use PSX\DateTime;
use PSX\Sql;
use PSX\Sql\Condition;
use PSX\Sql\Join;

/**
 * LatestNews
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class LatestNews extends GadgetAbstract
{
	/**
	 * onLoad
	 *
	 * @param pageId integer
	 * @param count integer
	 */
	public function onLoad()
	{
		parent::onLoad();

		$pageId = $this->args->get('pageId', 0);
		$count  = $this->args->get('count', 8);

		// condition
		$con = null;
		if(!empty($pageId))
		{
			$con = new Condition(array('pageId', '=', $pageId));
		}

		// get latest news		
		$handler = $this->hm->getHandler('AmunService\News');
		$result  = $handler->getAll(array('id', 
			'urlTitle', 
			'title', 
			'date', 
			'authorId', 
			'authorName', 
			'authorProfileUrl', 
			'pagePath'), 0, $count, 'date', Sql::SORT_DESC, $con, Sql::FETCH_OBJECT);

		$this->display($result);
	}

	private function display(array $result)
	{
		$now = new DateTime('NOW', $this->registry['core.default_timezone']);

		echo '<ul>';

		foreach($result as $row)
		{
			$interval = $row->getDate()->diff($now);

			if($interval->format('%d') == 0)
			{
				if($interval->format('%h') == 0)
				{
					$ago = 'ago ' . $interval->format('%i minutes');
				}
				else
				{
					$ago = 'ago ' . $interval->format('%h hours');
				}
			}
			else
			{
				$ago = 'on ' . $row->getDate()->format($this->registry['core.format_date']);
			}

			$user  = '<a href="' . $row->authorProfileUrl . '">' . $row->authorName . '</a>';
			$title = '<a href="' . $row->getUrl() . '">' . $row->title . '</a>';

			echo '<li><span>' . $title . '</span><p class="muted">by ' . $user . ' ' . $ago . '</p></li>';
		}

		echo '</ul>';
	}
}


