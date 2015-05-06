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
		if ($type == 'install' || $type == 'update')
		{
			$installationPath = $parent->getParent()->getPath('source');

			jimport('joomla.filesystem.folder');

			// Moving Layouts
			if (JFolder::move($installationPath . '/layouts', JPATH_ROOT . '/layouts/libraries/neno') !== true)
			{
				return false;
			}

			// Moving media files
			if (JFolder::move($installationPath . '/media', JPATH_ROOT . '/media/neno') !== true)
			{
				return false;
			}

			$app = JFactory::getApplication();
			$app->setUserState('com_installer.redirect_url', JRoute::_('index.php?option=com_neno&view=installation'));
		}
		elseif ($type == 'uninstall')
		{
			JFolder::delete(JPATH_ROOT . '/layouts/libraries/neno');
			JFolder::delete(JPATH_ROOT . '/media/neno');
		}

		return true;
	}
}
