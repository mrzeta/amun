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

namespace AmunService\Content\Page\Gadget;

use Amun\Data\FormAbstract;
use Amun\Form as AmunForm;
use Amun\Form\Element\Panel;
use Amun\Form\Element\Reference;
use Amun\Form\Element\Input;
use Amun\Form\Element\TabbedPane;
use Amun\Form\Element\Textarea;
use Amun\Form\Element\Captcha;
use Amun\Form\Element\Select;

/**
 * Form
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/gpl.html GPLv3
 * @link    http://amun.phpsx.org
 */
class Form extends FormAbstract
{
	public function create()
	{
		$form = new AmunForm('POST', $this->url);


		$panel = new Panel('gadget', 'Gadget');


		$pageId = new Reference('pageId', 'Page ID');
		$pageId->setValueField('id');
		$pageId->setLabelField('title');
		$pageId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/page');

		$panel->add($pageId);


		$gadgetId = new Reference('gadgetId', 'Gadget ID');
		$gadgetId->setValueField('id');
		$gadgetId->setLabelField('name');
		$gadgetId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/gadget');

		$panel->add($gadgetId);


		$sort = new Input('sort', 'Sort');
		$sort->setType('text');

		$panel->add($sort);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function update($id)
	{
		$record = $this->hm->getHandler('AmunService\Content\Page\Gadget')->getRecord($id);


		$form = new AmunForm('PUT', $this->url);


		$panel = new Panel('gadget', 'Gadget');


		$id = new Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$pageId = new Reference('pageId', 'Page ID', $record->pageId);
		$pageId->setValueField('id');
		$pageId->setLabelField('title');
		$pageId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/page');

		$panel->add($pageId);


		$gadgetId = new Reference('gadgetId', 'Gadget ID', $record->gadgetId);
		$gadgetId->setValueField('id');
		$gadgetId->setLabelField('name');
		$gadgetId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/gadget');

		$panel->add($gadgetId);


		$sort = new Input('sort', 'Sort', $record->sort);
		$sort->setType('text');

		$panel->add($sort);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}

	public function delete($id)
	{
		$record = $this->hm->getHandler('AmunService\Content\Page\Gadget')->getRecord($id);


		$form = new AmunForm('DELETE', $this->url);


		$panel = new Panel('gadget', 'Gadget');


		$id = new Input('id', 'ID', $record->id);
		$id->setType('hidden');

		$panel->add($id);


		$pageId = new Reference('pageId', 'Page ID', $record->pageId);
		$pageId->setValueField('id');
		$pageId->setLabelField('title');
		$pageId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/page');
		$pageId->setDisabled(true);

		$panel->add($pageId);


		$gadgetId = new Reference('gadgetId', 'Gadget ID', $record->gadgetId);
		$gadgetId->setValueField('id');
		$gadgetId->setLabelField('name');
		$gadgetId->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/content/gadget');
		$gadgetId->setDisabled(true);

		$panel->add($gadgetId);


		$sort = new Input('sort', 'Sort', $record->sort);
		$sort->setType('text');
		$sort->setDisabled(true);

		$panel->add($sort);


		if($this->user->isAnonymous() || $this->user->hasInputExceeded())
		{
			$captcha = new Captcha('captcha', 'Captcha');
			$captcha->setSrc($this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/core/captcha');

			$panel->add($captcha);
		}


		$form->setContainer($panel);


		return $form;
	}
}

