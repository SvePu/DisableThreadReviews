<?php

/*
Disable Thread Reviews Plugin for MyBB 1.8 v1.0
Copyright (C) 2015 SvePu

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.");
}


function disablethreadreviews_info()
{
	global $db, $lang;
	$lang->load('config_disablethreadreviews');
	return array(
		"name"		=>	$db->escape_string($lang->disablethreadreviews),
		"description"	=>	$db->escape_string($lang->disablethreadreviews_desc),
		"website"	=>	"https://github.com/SvePu/DisableThreadReviews",
		"author"	=>	"SvePu",
		"authorsite"	=> 	"https://github.com/SvePu",
		"codename"      => 	"disablethreadreviews",
		"version"       => 	"1.0",
		"compatibility" => 	"18*"
	);
}

function disablethreadreviews_activate()
{
	global $db, $lang;
	$lang->load('config_disablethreadreviews');
	$query_add = $db->simple_select("settinggroups", "COUNT(*) as rows");
	$rows = $db->fetch_field($query_add, "rows");
	$disablethreadreviews_group = array(
		"name" 		=>	"disablethreadreviews",
		"title" 	=>	$db->escape_string($lang->disablethreadreviews_settings_title),
		"description" 	=>	$db->escape_string($lang->disablethreadreviews_settings_title_desc),
		"disporder"	=> 	$rows+1,
		"isdefault" 	=>	0
	);
	$db->insert_query("settinggroups", $disablethreadreviews_group);
	$gid = $db->insert_id();

	$disablethreadreviews_1 = array(
		'name'		=>	'disablethreadreviews_enable',
		'title'		=>	$db->escape_string($lang->disablethreadreviews_enable_title),
		'description'  	=>	$db->escape_string($lang->disablethreadreviews_enable_title_desc),
		'optionscode'  	=>	'onoff',
		'value'        	=>	1,
		'disporder'	=> 	1,
		"gid" 		=>	(int)$gid
	);
	$db->insert_query('settings', $disablethreadreviews_1);


	$disablethreadreviews_2 = array(
		"name"		=>	"disablethreadreviews_forums",
		"title"		=>	$db->escape_string($lang->disablethreadreviews_forums_title),
		"description" 	=>	$db->escape_string($lang->disablethreadreviews_forums_title_desc),
		'optionscode'  	=>	'forumselect',
		'value'        	=>	'',
		"disporder"	=>	2,
		"gid" 		=>	(int)$gid
	);
	$db->insert_query("settings", $disablethreadreviews_2);
	rebuild_settings();
}

function disablethreadreviews_deactivate()
{
	global $db, $mybb;

	$result = $db->simple_select('settinggroups', 'gid', "name = 'disablethreadreviews'", array('limit' => 1));
	$group = $db->fetch_array($result);

	if(!empty($group['gid']))
	{
		$db->delete_query('settinggroups', "gid='{$group['gid']}'");
		$db->delete_query('settings', "gid='{$group['gid']}'");
		rebuild_settings();
	}
}

function disablethreadreviews_run()
{
	global $db, $mybb, $fid;
	if ($mybb->settings['disablethreadreviews_enable'] == 1 && !empty($mybb->settings['disablethreadreviews_forums']))
	{
		if ((my_strpos($mybb->settings['disablethreadreviews_forums'], $fid) !== false) || $mybb->settings['disablethreadreviews_forums'] == "-1")
		{
			$mybb->settings['threadreview'] = 0;
		}	
	}	
}
$plugins->add_hook('newreply_start','disablethreadreviews_run');
