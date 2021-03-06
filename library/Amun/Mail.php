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

namespace Amun;

/**
 * Mail
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Mail
{
	protected $config;
	protected $sql;
	protected $registry;

	public function __construct(Registry $registry)
	{
		$this->config   = $registry->getConfig();
		$this->sql      = $registry->getSql();
		$this->registry = $registry;
	}

	public function send($name, $email, array $values = array())
	{
		if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			// no valid email provided
			return;
		}

		$sql = <<<SQL
SELECT
	`mail`.`from`,
	`mail`.`subject`,
	`mail`.`text`,
	`mail`.`html`,
	`mail`.`values`
FROM 
	{$this->registry['table.mail']} `mail`
WHERE 
	`mail`.`name` = ?
SQL;

		$row = $this->sql->getRow($sql, array($name));

		if(!empty($row))
		{
			// check values
			$neededValues = array();

			if(!empty($row['values']))
			{
				$neededValues = explode(';', $row['values']);
			}

			if(count(array_diff(array_keys($values), $neededValues)) > 0)
			{
				throw new Exception('Missing values in ' . $name);
			}


			// send mail
			$mail = new \Zend\Mail\Message();
			$mail->setBody($this->substituteVars($row['text'], $values));
			$mail->setFrom($row['from']);
			$mail->addTo($email);
			$mail->setSubject($this->substituteVars($row['subject'], $values));

			$transport = new \Zend\Mail\Transport\Sendmail();
			$transport->send($mail);
		}
		else
		{
			throw new Exception('Invalid mail template');
		}
	}

	private function substituteVars($content, array $values)
	{
		foreach($values as $k => $v)
		{
			$content = str_replace('{' . $k . '}', $v, $content);
		}

		return $content;
	}
}
