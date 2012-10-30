<?php
/*
 *  $Id: FriendsAbstract.php 638 2012-05-01 20:50:26Z k42b3.x@googlemail.com $
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
 * Amun_Service_My_FriendsAbstract
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_My
 * @version    $Revision: 638 $
 */
abstract class Amun_Service_My_FriendsAbstract extends Amun_Service_My_MyAbstract
{
	public function onLoad()
	{
		parent::onLoad();


		// friend request count
		$con = new PSX_Sql_Condition();
		$con->add('friendId', '=', $this->user->id);
		$con->add('status', '=', Amun_User_Friend::REQUEST);

		$requestCount = $this->sql->count($this->registry['table.user_friend'], $con);

		$this->template->assign('requestCount', $requestCount);


		// pending count
		$con = new PSX_Sql_Condition();
		$con->add('userId', '=', $this->user->id);
		$con->add('status', '=', Amun_User_Friend::REQUEST);

		$pendingCount = $this->sql->count($this->registry['table.user_friend'], $con);

		$this->template->assign('pendingCount', $pendingCount);


		// load groups
		$groupList = $this->getGroups();

		$this->template->assign('groupList', $groupList);


		// options
		$friends = new Amun_Option('friends', $this->registry, $this->user, $this->page);
		$friends->add('service_my_view', 'Friends', $this->page->url . '/friends');

		if($requestCount > 0)
		{
			$friends->add('service_my_view', 'Request (' . $requestCount . ')', $this->page->url . '/friends/request');
		}

		if($pendingCount > 0)
		{
			$friends->add('service_my_view', 'Pending (' . $pendingCount . ')', $this->page->url . '/friends/pending');
		}

		$friends->add('service_my_view', 'Groups', $this->page->url . '/friends/group');
		$friends->load(array($this->page));

		$this->template->assign('optionsFriends', $friends);
	}

	private function getGroups()
	{
		return Amun_Sql_Table_Registry::get('User_Friend_Group')
			->select(array('id', 'title', 'date'))
			->where('userId', '=', $this->user->id)
			->getAll();
	}
}

