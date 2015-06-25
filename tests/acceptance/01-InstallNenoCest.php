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
		$I->see('Get Started');
		$I->click(['xpath' => "//button[@type='button']"]);
		$I->click(['xpath' => "//button[@type='button']"]);
		$I->click(['xpath' => "//button[@type='button']"]);
		$I->click(['css' => "#add-languages-button"]);
		$I->click(['xpath' => "(//button[@type='button'])[29]"]);
		$I->click(['xpath' => "(//button[@type='button'])[34]"]);
		$I->click("Close");
		$I->click(['xpath' => "(//button[@type='button'])[4]"]);
		$I->click(['css' => "#backup-created-checkbox"]);
		$I->click(['css' => "label.checkbox"]);
		$I->click(['css' => "#proceed-button"]);
		$I->waitForElement("#submenu > li > a");
		$I->doAdministratorLogout();
	}
}