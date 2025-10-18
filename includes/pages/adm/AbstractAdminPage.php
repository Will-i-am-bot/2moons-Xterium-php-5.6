<?php

/**
 *  2Moons
 *  Copyright (C) 2012 Jan Kröpke
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package 2Moons
 * @author Jan Kröpke <info@2moons.cc>
 * @copyright 2012 Jan Kröpke <info@2moons.cc>
 * @license http://www.gnu.org/licenses/gpl.html GNU GPLv3 License
 * @version 1.7.3 (2013-05-19)
 * @info $Id: AbstractAdminPage.php 2024-04-12 $
 * @link http://2moons.cc/
 */

require_once(ROOT_PATH.'includes/classes/class.template.php');

abstract class AbstractAdminPage
{
    /** @var template */
    protected $tplObj;

    public function __construct()
    {
        $this->enforcePermission();
        $this->initTemplate();
    }

    protected function enforcePermission()
    {
        global $USER;

        if(!isset($USER['authlevel']) || $USER['authlevel'] < AUTH_OPS)
        {
            // FIXED: added Senate and Governors admin integration
            HTTP::redirectTo('admin.php?page=login');
            exit;
        }
    }

    protected function initTemplate()
    {
        if($this->tplObj instanceof template)
        {
            return;
        }

        $this->tplObj = new template();
        list($tplDir) = $this->tplObj->getTemplateDir();
        $this->tplObj->setTemplateDir($tplDir.'adm/');
    }

    protected function display($template)
    {
        $this->tplObj->show($template);
    }
}
