<?php

/*
  Plugin Name: Easy Content Templates
  Plugin URI: http://wpsolutions.llemos.com/easy-content-templates-wordpress-plugin
  Description: This plugin lets you define content templates to quickly and easily create new posts or pages.
  Version: 1.4.3.1
  Author: Japa Alekhin Llemos
  Author URI: http://japaalekhin.llemos.com
  License: GPL2
  Text Domain: easy-content-templates

  Copyright 2011-2014  Japa Alekhin Llemos  (email : japaalekhin@llemos.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

define("ECTDS", DIRECTORY_SEPARATOR);

function ectdir($parts = array()) {
    $parts = is_array($parts) ? $parts : array();
    return dirname(__FILE__) . ECTDS . implode(ECTDS, $parts);
}

function ecturl($parts) {
    $parts = is_array($parts) ? $parts : array();
    return plugins_url("", __FILE__) . "/" . implode("/", $parts);
}

include ectdir(array("obj", "template.class.php"));
