<?php

/**
 * Created by PhpStorm.
 * User: victor
 * Date: 23/04/15
 * Time: 16:39
 */
class com_nenoInstallerScript
{
	public function postflight($type, $parent)
	{
		var_dump($type);
		var_dump($parent);
		exit;
	}
}