<?php

class InstallLanguageCest
{
	public function installLanguage(AcceptanceTester $I)
	{
		$I->am('Administrator');
		$I->doAdministratorLogin();
		$I->click("Components");
		$I->click("Neno");
		$I->waitForElement('#add-languages-button', 10);
		$I->click("#add-languages-button");
		$I->waitForElementVisible("div.modal-footer");
		$I->click("(//button[@type='button'])[77]");
		$I->click("div.modal-footer");
		$I->click("Close");
		$I->click("#add-languages-button");
		$I->click("(//button[@type='button'])[77]");
		$I->click("Close");
		$I->doAdministratorLogout();
	}
}