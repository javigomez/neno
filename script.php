<?php

/**
 * Installs some files that the installer does not move.
 *
 * @since  1.0
 */
class pkg_NenoInstallerScript
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

		// Enabling Neno plugin
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->update('#__extensions')
			->set('enabled = 1')
			->where(
				array (
					'type = ' . $db->quote('plugin'),
					'folder = ' . $db->quote('system'),
					'element = ' . $db->quote('neno')
				)
			);

		$db->setQuery($query);
		$db->execute();

		$parent->getParent()->setRedirectURL(JRoute::_('index.php?option=com_neno&view=installation', false));

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
