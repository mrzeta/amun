<?php
/*
 *  $Id: add.php 875 2012-09-30 13:51:45Z k42b3.x@googlemail.com $
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
 * add
 *
 * @author     Christoph Kappestein <k42b3.x@gmail.com>
 * @license    http://www.gnu.org/licenses/gpl.html GPLv3
 * @link       http://amun.phpsx.org
 * @category   module
 * @package    application
 * @subpackage plugin
 * @version    $Revision: 875 $
 */
class add extends Amun_Module_ApplicationAbstract
{
	public function onLoad()
	{
		if($this->user->hasRight('service_plugin_add'))
		{
			// form url
			$id = $this->get->id('integer');

			$url = $this->config['psx_url'] . '/' . $this->config['psx_dispatch'] . 'api/service/plugin/release/form?format=json&method=create&pluginId=' . $id;


			// plugin
			$plugin = Amun_Sql_Table_Registry::get('Plugin')->getRecord($id);


			// add path
			$this->path->add($plugin->title, $this->page->url . '/view?id=' . $plugin->id);
			$this->path->add('Add Release', $this->page->url . '/release/add?id=' . $plugin->id);


			// template
			$this->htmlJs->add('amun');
			$this->htmlJs->add('ace');
			$this->htmlJs->add('plugin');


			echo <<<HTML
<div id="response"></div>

<div id="form"></div>

<script type="text/javascript">
amun.services.plugin.loadForm("form", "{$url}");
</script>

<p><span class="small">As Href please provide a direct link to the service .tar file.</span></p>

HTML;
		}
		else
		{
			throw new Amun_Exception('Access not allowed');
		}
	}
}
