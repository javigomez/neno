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
		$I->enablePlugin('Neno');
		$I->click("Components");
		$I->click("Neno");
		$I->waitForElement(".next-step-button");
		$I->waitForElement(".next-step-button");
		$I->waitForElement(".next-step-button");
		$I->waitForElement(".next-step-button");
		$I->waitForElement(".next-step-button");
		$I->waitForElement(".next-step-button");
		$I->waitForElement(".next-step-button");
		$I->click("#add-languages-button");
		$I->click(['xpath' => "(//button[@type='button'])[39]"]);
		$I->click(['xpath' => "(//button[@type='button'])[32]"]);
		$I->click(['xpath' => "(//button[@type='button'])[20]"]);
		$I->click("Close");
		$I->click(".next-step-button");
		$I->waitForElement("#backup-created-checkbox");
		$I->click("#backup-created-checkbox");
		$I->click("label.checkbox");
		$I->click("#proceed-button");
		$I->waitForElement("#submenu > li > a");
		$I->doAdministratorLogout();
	}
}