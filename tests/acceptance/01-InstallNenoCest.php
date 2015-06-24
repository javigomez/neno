<?php

/**
 * Created by PhpStorm.
 * User: victor
 * Date: 17/06/15
 * Time: 16:49
 */
class InstallNenoCest
{
	public function installNeno(AcceptanceTester $I)
	{
		$I->am('Administrator');
		$I->installJoomla();
		$I->doAdministratorLogin();
		$I->setErrorReportingToDevelopment();
		$I->amOnPage("/administrator/");
		$I->click("Extensions");
		$I->click("Extension Manager");
		$I->click("Upload Package File");
		$path = $I->getConfiguration('repo_folder');
		$I->installExtensionFromDirectory($path . 'lib_neno');
		$I->installExtensionFromDirectory($path . 'plg_system_neno');
		$I->installExtensionFromDirectory($path . 'com_neno');
		$I->doAdministratorLogout();
	}
}