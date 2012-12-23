<?php
/*
 *  $Id: index.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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

namespace asset\api;

use Amun_Module_ApiAbstract;
use AmunService_Asset_Provider_Css;
use AmunService_Asset_Provider_Js;
use AmunService_Asset_ProviderInterface;
use AmunService_Asset_Manager;
use PSX_Base;

/**
 * index
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    admin
 * @subpackage service_page
 * @version    $Revision: 875 $
 */
class index extends Amun_Module_ApiAbstract
{
	/**
	 * @httpMethod GET
	 * @path /js
	 * @nickname getJs
	 * @parameter query services string
	 * @responseClass string
	 */
	public function getJs()
	{
		$services = $this->get->services('string');
		$provider = new AmunService_Asset_Provider_Js($this->registry);

		$this->serve($provider, $services);
	}

	/**
	 * @httpMethod GET
	 * @path /css
	 * @nickname getCss
	 * @parameter query services string
	 * @responseClass string
	 */
	public function getCss()
	{
		$services = $this->get->services('string');
		$provider = new AmunService_Asset_Provider_Css($this->registry);

		$this->serve($provider, $services);
	}

	private function serve(AmunService_Asset_ProviderInterface $provider, $services)
	{
		// get response
		$manager  = new AmunService_Asset_Manager($this->config, $provider);
		$response = $manager->serve($services);

		// remove caching header
		header('Expires:');
		header('Last-Modified:');
		header('Cache-Control:');
		header('Pragma:');

		// gzip encoding
		$acceptEncoding = PSX_Base::getRequestHeader('Accept-Encoding');

		if($this->config['psx_gzip'] === true && strpos($acceptEncoding, 'gzip') !== false)
		{
			header('Content-Encoding: gzip');

			$response = gzencode($response, 9);
		}

		// caching header
		$etag  = md5($response);
		$match = PSX_Base::getRequestHeader('If-None-Match');
		$match = $match !== false ? trim($match, '"') : '';

		header('Etag: "' . $etag . '"');

		if($match != $etag)
		{
			echo $response;
		}
		else
		{
			PSX_Base::setResponseCode(304);
		}

		exit;
	}
}