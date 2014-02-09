<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Nope.  Also, the game.");
}


$plugins->add_hook("global_start", "miserablemember");

//basic plugin info for ACP
function miserablemember_info()
{
	return array(
		"name"			=> "Miserable Member",
		"description"	=> "Miserable Member, based on the vBulletin plugin called Miserable User, it hell for anyone caught in its grasp.  Worse than an ordinary ban, any users that are placed in Miserable Member's usergroups are contantly errored, have slow page loads, and other things that make the user think twice about doing anything on your board.",
		"website"		=> "https://github.com/PenguinPaul/miserable-member",
		"author"		=> "Paul H.",
		"authorsite"	=> "http://www.paulhedman.com",
		"version"		=> "1.0",
		"guid" 			=> "",
		"compatibility" => "*"
	);
}



function miserablemember_activate()
{
	global $db;

	//settings group
	$group = array(
		'gid'			=> 'NULL',
		'name'			=> 'miserablemember',
		'title'			=> 'Miserable Member Settings',
		'description'	=> 'Settings for the Miserable Member plugin.',
		'disporder'		=> '0',
		'isdefault'		=> 'no',
	);
	$db->insert_query('settinggroups', $group);
	$gid = $db->insert_id();
	
	//settings
	$setting = array(
		'name'			=> 'miserablemember_groups',
		'title'			=> 'Miserable Groups',
		'description'	=> 'A CSV of groups that are affected by Miserable Member.',
		'optionscode'	=> 'text',
		'value'			=> '',
		'disporder'		=> 1,
		'gid'			=> intval($gid),
	);
	$db->insert_query('settings', $setting);		
	
	$setting = array(
		'name'			=> 'miserablemember_messages',
		'title'			=> 'Miserable Messages',
		'description'	=> 'A list of error messages that Miserable Member can display.  Seperate by a new line.',
		'optionscode'	=> 'textarea',
		'value'			=> 'MyBB could not load due to an unknown error.
The user session could not be loaded.
IP invalid for this session.
Hacking attempt detected.  Please refresh the page.
Could not connect to database.
Database took too long to respond.',
		'disporder'		=> 2,
		'gid'			=> intval($gid),
	);
	$db->insert_query('settings', $setting);	
	
	rebuild_settings();

}

function miserablemember_deactivate()
{
	global $db;
	
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name IN ('miserablemember_groups','miserablemember_messages')");
	$db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='miserablemember'");
	rebuild_settings(); 		
}

function miserablemember()
{
	global $error_handler,$mybb;

	$groups = explode(",",$mybb->settings['miserablemember_groups']);
	
	if(in_array($mybb->user['usergroup'],$groups))
	{
		//sleep
		set_time_limit(61);
		$sleeptime = rand(30,60);
		sleep($sleeptime);
		$mybberror = rand(1,4);
		//25% of the time error
		if($mybberror == 1)
		{
			$errorarray = explode("\n",$mybb->settings['miserablemember_messages']);
			shuffle($errorarray);
			$error_handler->output_error(MYBB_GENERAL, $errorarray[0], "", "");
		} else {
			//when you dont get an error you have
			$other = rand(1,10);
			//a 50% chance of getting a blank page
			if($other < 5)
			{
				die;
			}
			
			//a 40% chance of being redirected to the home page
			if($other > 4 && $other != 10)
			{
				header("Location: {$mybb->settings['bburl']}");
			} else {
				//or a 10% chance that you get what you actaully wanted!
				return;
			}
		}
	}
}

?>
