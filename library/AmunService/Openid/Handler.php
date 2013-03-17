<?php
/*
 *  $Id: Handler.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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

namespace AmunService\Openid;

use Amun\DataFactory;
use Amun\Data\HandlerAbstract;
use Amun\Data\RecordAbstract;
use Amun\Exception;
use Amun\Security;
use AmunService\Core\Approval;
use PSX\DateTime;
use PSX\Data\RecordInterface;
use PSX\Data\ResultSet;
use PSX\Sql;
use PSX\Sql\Condition;
use PSX\Sql\Join;

/**
 * Amun_System_Connect_Handler
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_System_Connect
 * @version    $Revision: 635 $
 */
class Handler extends HandlerAbstract
{
	public function getByConsumerKey($consumerKey)
	{
		return DataFactory::getTable('Openid')
			->select(array('id', 'consumerKey'))
			->where('consumerKey', '=', $consumerKey)
			->getRow();
	}

	public function getStatus($userId, $assocId)
	{
		return (integer) DataFactory::getTable('Openid')
			->select(array('status'))
			->where('userId', '=', $userId)
			->where('assocId', '=', $assocId)
			->getField();
	}

	public function create(RecordInterface $record)
	{
		throw new Exception('Connect can not created');
	}

	public function update(RecordInterface $record)
	{
		throw new Exception('Connect can not updated');
	}

	public function delete(RecordInterface $record)
	{
		if($record->hasFields('id'))
		{
			$con = new Condition(array('id', '=', $record->id));

			$this->table->delete($con);


			$this->notify(RecordAbstract::DELETE, $record);


			return $record;
		}
		else
		{
			throw new Exception('Missing field in record');
		}
	}

	protected function getDefaultSelect()
	{
		return $this->table
			->select(array('id', 'userId', 'status', 'claimedId', 'returnTo', 'date'))
			->join(Join::INNER, DataFactory::getTable('User_Account')
				->select(array('name', 'profileUrl'), 'author')
			);
	}
}


