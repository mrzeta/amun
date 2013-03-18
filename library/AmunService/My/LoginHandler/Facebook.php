<?php
/*
 *  $Id: FriendsAbstract.php 635 2012-05-01 19:46:37Z k42b3.x@googlemail.com $
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

namespace AmunService\My\LoginHandler;

use Amun\Exception;
use Amun\DataFactory;
use Amun\Security;
use AmunService\My\LoginHandlerAbstract;
use AmunService\User\Account;
use PSX\Http;
use PSX\Http\GetRequest;
use PSX\Url;
use PSX\Json;
use PSX\Sql\Condition;
use PSX\Oauth2;
use PSX\Oauth2\Authorization\AuthorizationCode;

/**
 * AmunService_My_LoginHandler_Facebook
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_My
 * @version    $Revision: 635 $
 */
class Facebook extends LoginHandlerAbstract implements CallbackInterface
{
	const CLIENT_ID      = '';
	const CLIENT_SECRET  = '';

	const AUTHENTICATE   = 'https://www.facebook.com/dialog/oauth';
	const ACCESS_TOKEN   = 'https://graph.facebook.com/oauth/access_token';
	const VERIFY_ACCOUNT = 'https://graph.facebook.com/me';

	protected $http;
	protected $oauth;

	public function __construct()
	{
		parent::__construct();

		$this->http  = new Http();
		$this->oauth = new Oauth2();
	}

	public function isValid($identity)
	{
		return filter_var($identity, FILTER_VALIDATE_EMAIL) !== false && strpos($identity, '@facebook.com') !== false;
	}

	public function hasPassword()
	{
		return false;
	}

	public function handle($identity, $password)
	{
		// build callback
		$callback = $this->pageUrl . '/login/callback/facebook';

		AuthorizationCode::redirect(new Url(self::AUTHENTICATE), self::CLIENT_ID, $callback);
	}

	public function callback()
	{
		$code = new AuthorizationCode($this->http, new Url(self::ACCESS_TOKEN));
		$code->setClientPassword(self::CLIENT_ID, self::CLIENT_SECRET, AuthorizationCode::AUTH_POST);

		$accessToken = $code->getAccessToken($this->pageUrl . '/login/callback/facebook');

		// request user informations
		$url    = new Url(self::VERIFY_ACCOUNT);
		$header = array(
			'Authorization' => $this->oauth->getAuthorizationHeader($accessToken),
		);

		$request  = new GetRequest($url, $header);
		$response = $this->http->request($request);

		if($response->getCode() == 200)
		{
			$acc = Json::decode($response->getBody());

			if(empty($acc))
			{
				throw new Exception('No user informations provided');
			}

			if(empty($acc['id']))
			{
				throw new Exception('No user id provided');
			}

			$identity = $acc['id'];	
			$con      = new Condition(array('identity', '=', sha1(Security::getSalt() . $identity)));
			$userId   = DataFactory::getTable('User_Account')->getField('id', $con);

			if(empty($userId))
			{
				// user doesnt exist so register a new user check whether 
				// registration is enabled
				if(!$this->registry['my.registration_enabled'])
				{
					throw new Exception('Registration is disabled');
				}

				if(empty($acc['username']))
				{
					throw new Exception('No username provided');
				}

				$name = $this->normalizeName($acc['username']);

				// create user account
				$handler = new Account\Handler($this->user);

				$account = DataFactory::getTable('User_Account')->getRecord();
				$account->setGroupId($this->registry['core.default_user_group']);
				$account->setStatus(Account\Record::NORMAL);
				$account->setIdentity($identity);
				$account->setName($name);
				$account->setPw(Security::generatePw());

				$account->profileUrl   = isset($acc['link']) ? $acc['link'] : null;
				$account->thumbnailUrl = 'http://graph.facebook.com/' . $identity . '/picture';

				$account = $handler->create($account);
				$userId  = $account->id;

				// if the id is not set the account was probably added to
				// the approval table
				if(!empty($userId))
				{
					$this->setUserId($userId);
				}
				else
				{
					throw new Exception('Could not create account');
				}
			}
			else
			{
				$this->setUserId($userId);
			}

			// redirect
			header('Location: ' . $this->config['psx_url']);
			exit;
		}
		else
		{
			throw new Exception('Authentication failed');
		}
	}
}
