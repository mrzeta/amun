<?php
/*
 *  $Id: Form.php 696 2012-06-08 16:00:47Z k42b3.x@googlemail.com $
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
 * Amun_Service_Tracker_Form
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   Amun
 * @package    Amun_Service_Tracker
 * @version    $Revision: 696 $
 */
class AmunService_Tracker_Form extends Amun_Data_FormAbstract
{
	public function create($pageId = 0)
	{
		$form = new Amun_Form('POST', $this->url, 'multipart/form-data');


		$panel = new Amun_Form_Element_Panel('tracker', 'Tracker');


		if(empty($pageId))
		{
			$pageId = new Amun_Form_Element_Reference('pageId', 'Page ID');
			$pageId->setValueField('id');
			$pageId->setLabelField('title');
			$pageId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/page');

			$panel->add($pageId);
		}
		else
		{
			$pageId = new Amun_Form_Element_Input('pageId', 'Page ID', $pageId);
			$pageId->setType('hidden');

			$panel->add($pageId);
		}


		$title = new Amun_Form_Element_Input('title', 'Title');
		$title->setType('text');

		$panel->add($title);


		$torrent = new Amun_Form_Element_Input('torrent', 'Torrent');
		$torrent->setType('file');

		$panel->add($torrent);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function update($id)
	{
		$record = Amun_Sql_Table_Registry::get('Tracker')->getRecord($id);


		$form = new Amun_Form('PUT', $this->url);


		$panel = new Amun_Form_Element_Panel('tracker', 'Tracker');


		$id = new Amun_Form_Element_Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$title = new Amun_Form_Element_Input('title', 'Title', $record->title);
		$title->setType('text');

		$panel->add($title);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function delete($id)
	{
		$record = Amun_Sql_Table_Registry::get('Tracker')->getRecord($id);


		$form = new Amun_Form('DELETE', $this->url);


		$panel = new Amun_Form_Element_Panel('tracker', 'Tracker');


		$id = new Amun_Form_Element_Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$title = new Amun_Form_Element_Input('title', 'Title', $record->title);
		$title->setType('text');
		$title->setDisabled(true);

		$panel->add($title);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Amun_Form_Element_Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/system/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}
}
