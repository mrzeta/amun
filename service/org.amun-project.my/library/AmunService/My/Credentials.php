<?php
/*
 *  $Id: Credentials.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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
 * Amun_Service_My_Credentials
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_My
 * @version    $Revision: 635 $
 */
class AmunService_My_Credentials extends AmunService_Core_User_Account_Record
{
	protected $_user;

	public function __construct(Amun_Sql_TableInterface $table, Amun_User $user)
	{
		parent::__construct($table);

		$this->_user = $user;
	}

	public function getName()
	{
		return 'credentials';
	}

	public function getFields()
	{
		return array(

			'id'           => $this->id,
			'name'         => $this->name,
			'profileUrl'   => $this->profileUrl,
			'thumbnailUrl' => $this->thumbnailUrl,
			'loggedIn'     => !$this->_user->isAnonymous(),
			'gender'       => $this->gender,
			'group'        => $this->getGroup()->title,
			'status'       => self::getStatus($this->status),
			'timezone'     => $this->getTimezone()->getName(),
			'updated'      => $this->getUpdated()->format(DateTime::ATOM),
			'date'         => $this->getDate()->format(DateTime::ATOM),

		);
	}
}
