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

namespace AmunService\Xrds\Type;

use Amun\Data\RecordAbstract;
use Amun\Filter;
use Amun\Exception;

/**
 * Record
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Record extends RecordAbstract
{
	protected $_api;

	public function setId($id)
	{
		$id = $this->_validate->apply($id, 'integer', array(new Filter\Id($this->_table)), 'id', 'Id');

		if(!$this->_validate->hasError())
		{
			$this->id = $id;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setApiId($apiId)
	{
		$apiId = $this->_validate->apply($apiId, 'integer', array(new Filter\Id($this->_hm->getTable('AmunService\Xrds'))), 'apiId', 'Api Id');

		if(!$this->_validate->hasError())
		{
			$this->apiId = $apiId;
		}
		else
		{
			throw new Exception($this->_validate->getLastError());
		}
	}

	public function setType($type)
	{
		$this->type = $type;
	}

	public function getId()
	{
		return $this->_base->getUrn('xrds', 'type', $this->id);
	}

	public function getApi()
	{
		if($this->_api === null)
		{
			$this->_api = $this->_hm->getHandler('AmunService\Xrds')->getRecord($this->apiId);
		}

		return $this->_api;
	}
}

