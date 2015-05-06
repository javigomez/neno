<?php

/**
 * Installs some files that the installer does not move.
 *
 * @since  1.0
 */
class com_nenoInstallerScript
{
	/**
	 * Copying files
	 *
	 * @param   string                     $type   Installation type
	 * @param   JInstallerAdapterComponent $parent Installation adapter
	 *
	 * @return bool False if something happens
	 */
	public function postflight($type, $parent)
	{
		$installationPath = $parent->getParent()->getPath('source');

		jimport('joomla.filesystem.folder');

		// Moving Layouts
		JFolder::move($installationPath . '/layouts', JPATH_ROOT . '/layouts/libraries/neno');

		// Moving media files
		JFolder::move($installationPath . '/media', JPATH_ROOT . '/media/neno');

		$parent->getParent()->setRedirectURL(JRoute::_('index.php?option=com_neno&view=installation'));

		return true;
	}

	/**
	 * Copying files
	 *
	 * @param   JInstallerAdapterComponent $parent Installation adapter
	 *
	 * @return bool False if something happens
	 */
	public function uninstall($parent)
	{
		JFolder::delete(JPATH_ROOT . '/layouts/libraries/neno');
		JFolder::delete(JPATH_ROOT . '/media/neno');
	}
}
