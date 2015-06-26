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

		// Installing library
		$I->installExtensionFromDirectory($path . 'lib_neno');

		// Installing Plugin
		$I->installExtensionFromDirectory($path . 'plg_system_neno');

		// Installing Component
		$I->installExtensionFromDirectory($path . 'com_neno');

		/*
		// Enabling plugin
		$I->enablePlugin('Neno plugin');

		// Going to Neno
		$I->click("Components");
		$I->click("Neno");
		$I->wait(5);

		// Get started Screen
		$I->click('Get Started');
		$I->waitForJS('return jQuery.active == 0', 20);
		$I->wait(5);

		// First step - Source language
		$I->see('Next', 'button');
		$I->click(['xpath' => "//button[@type='button']"]);
		$I->waitForJS('return jQuery.active == 0', 20);
		$I->wait(5);

		// Second step - Translation methods
		$I->see('Next');
		$I->click(['xpath' => "//button[@type='button']"]);
		$I->waitForJS('return jQuery.active == 0', 20);
		$I->wait(5);

		// Third step- Install language(s)
		$I->click(['css' => "#add-languages-button"]);
		$I->waitForJS('return jQuery.active == 0', 20);
		$I->wait(5);
		$I->waitForElementVisible(['css' => '#languages-modal']);
		$I->click(['css' => "[data-language='de-DE']"]);
		$I->click(['css' => "[data-language='es-ES']"]);
		$I->wait(10);
		$I->seeElement(['css' => ".loading-iso-de-DE"]);
		$I->seeElement(['css' => ".loading-iso-es-ES"]);
		$I->click("Close");
		$I->click(['xpath' => "(//button[@type='button'])[4]"]);

		// Fourth step- Installing Neno
		$I->wait(5);
		$I->click(['css' => "#backup-created-checkbox"]);
		$I->click(['css' => "label.checkbox"]);
		$I->click(['css' => "#proceed-button"]);

		// Fifth step- Installing Neno has been accomplish successfully
		$I->waitForElement("#submenu > li > a");
		$I->doAdministratorLogout();*/
	}
}