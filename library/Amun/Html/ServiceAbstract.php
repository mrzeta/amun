<?php
/*
 *  $Id: HtmlAbstract.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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

namespace Amun\Html;

use PSX\Config;

/**
 * Amun_Html_ServiceAbstract
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Ext
 * @version    $Revision: 635 $
 */
abstract class ServiceAbstract
{
	protected $config;
	protected $services = array();

	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	public function add($service)
	{
		if(!in_array($service, $this->services))
		{
			$this->services[] = $service;
		}
	}

	public function clear()
	{
		$this->services = array();
	}

	public function toString()
	{
		if(count($this->services) > 0)
		{
			return sprintf($this->getTag(), implode('|', $this->services));
		}
		else
		{
			return '';
		}
	}

	public function __toString()
	{
		return $this->toString();
	}

	abstract public function getTag();
}
