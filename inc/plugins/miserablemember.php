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
		"description"	=> "Miserable Member, based on the vBulletin plugin called Miserable User, makes it hell for anyone caught in its grasp.  Worse than an ordinary ban, any users that are placed in Miserable Member's usergroups are constantly errored, have slow page loads, and other things that make the user think twice about doing anything on your board.",
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

	// Settings group
	$group = array(
		'name'			=> 'miserablemember',
		'title'			=> 'Miserable Member Settings',
		'description'	=> 'Settings for the Miserable Member plugin.',
		'disporder'		=> 23,
		'isdefault'		=> 0,
	);
	$db->insert_query('settinggroups', $group);
	$gid = intval($db->insert_id());

	// Settings
	$settings = array();

	$settings[] = array(
		'name'			=> 'miserablemember_groups',
		'title'			=> 'Miserable Groups',
		'description'	=> 'A CSV of groups that are affected by Miserable Member.',
		'optionscode'	=> 'text',
		'value'			=> ''
	);

	$settings[] = array(
		'name'			=> 'miserablemember_messages',
		'title'			=> 'Miserable Messages',
		'description'	=> 'A list of error messages that Miserable Member can display.  Seperate by a new line.',
		'optionscode'	=> 'textarea',
		'value'			=> 'MyBB could not load due to an unknown error.
The user session could not be loaded.
IP invalid for this session.
Hacking attempt detected.  Please refresh the page.
Could not connect to database.
Database took too long to respond.'
	);

	$i = 1;
	$insert_settings = array();
	foreach($settings as $setting)
	{
		$insert_settings[] = array(
			'name' => $db->escape_string($setting['name']),
			'title' => $db->escape_string($setting['title']),
			'description' => $db->escape_string($setting['description']),
			'optionscode' => $db->escape_string($setting['optionscode']),
			'value' => $db->escape_string($setting['value']),
			'disporder' => $i,
			'gid' => $gid
		);

		$i++;
	}

	$db->insert_query_multiple('settings', $insert_settings);

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
	global $mybb;

	$groups = explode(",", $mybb->settings['miserablemember_groups']);

	if(in_array($mybb->user['usergroup'], $groups))
	{
		// Sleep for between 30 and 60 seconds
		set_time_limit(61);
		$sleeptime = rand(30, 60);
		//sleep($sleeptime);

		// 25% chance of MyBB error
		$mybberror = rand(1, 4);
		if($mybberror == 1)
		{
			global $error_handler;
			$errorarray = explode("\n",$mybb->settings['miserablemember_messages']);
			shuffle($errorarray);
			$error_handler->output_error(MYBB_GENERAL, $errorarray[0], "", "");
		} else {
			$other = rand(1,10);

			// When you dont get an error you have

			// A 40% chance of getting a blank page
			if($other <= 4) // $other 1, 2, 3, 4
			{
				die;
			}

			// A 20% chance of being redirected to the home page
			elseif($other >= 5 && $other < 7) // $other 5, 6
			{
				header("Location: {$mybb->settings['bburl']}");
			}

			// A 20% chance of no permission page
			elseif($other >= 7 && $other < 9) // $other 7, 8
			{
				error_no_permission();
			}

			// A 10% chance of being redirected to the homepage after a random amount of time
			elseif($other == 9) // $other 9
			{
				global $plugins;

				$plugins->add_hook("global_end", "miserablemember_waitredirect");
			}

			// Or a 10% chance that you get what you actaully wanted!
			else // $other 10
			{
				return;
			}
		}
	}
}

function miserablemember_waitredirect()
{
	global $headerinclude, $mybb;

	$redirect_time = rand(30, 180);

	// Add the redirect to the head tag with a meta tag and script to make doubly sure ;)
	$headerinclude .= "<noscript><meta http-equiv=\"refresh\" content=\"{$redirect_time}; url={$mybb->settings['bburl']}\"></noscript><script>setTimeout(function() { window.location.replace(\"{$mybb->settings['bburl']}\");}, {$redirect_time}000);</script>";
}
?>