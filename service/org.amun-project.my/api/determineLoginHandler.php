<?php
/*
 *  $Id: verifyCredentials.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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

namespace my\api;

use Amun_Module_ApiAbstract;
use AmunService_My_LoginHandlerFactory;
use AmunService_My_LoginHandlerAbstract;
use PSX_Json;

/**
 * verifyCredentials
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    api
 * @subpackage service_my
 * @version    $Revision: 875 $
 */
class determineLoginHandler extends Amun_Module_ApiAbstract
{
	/**
	 * Returns whether the given identity needs an password or not
	 *
	 * @httpMethod GET
	 * @path /
	 * @nickname needPassword
	 * @responseClass PSX_Data_Message
	 */
	public function needPassword()
	{
		$identity = $this->get->identity('string');
		$handles  = array_map('trim', explode(',', $this->registry['my.login_provider']));
		$result   = array();

		foreach($handles as $key)
		{
			$handler = AmunService_My_LoginHandlerFactory::factory($key);

			if($handler instanceof AmunService_My_LoginHandlerAbstract && $handler->isValid($identity))
			{
				$result = array(
					'handler'      => $key,
					'icon'         => $this->config['psx_url'] . '/img/icons/login/' . $key . '.png',
					'needPassword' => $handler->hasPassword(),
				);

				break;
			}
		}

		echo PSX_Json::encode($result);
	}
}


