<?php

/**
 * Installs some files that the installer does not move.
 *
 * @since  1.0
 */
class com_NenoInstallerScript
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

		// If the layout folder exists, let's delete them first
		if (JFolder::exists(JPATH_ROOT . '/layouts/libraries/neno'))
		{
			JFolder::delete(JPATH_ROOT . '/layouts/libraries/neno');
		}

		// Moving Layouts
		JFolder::move($installationPath . '/layouts', JPATH_ROOT . '/layouts/libraries/neno');

		// If the media folder exists, let's delete them first
		if (JFolder::exists(JPATH_ROOT . '/media/neno'))
		{
			JFolder::delete(JPATH_ROOT . '/media/neno');
		}

		// Moving media files
		JFolder::move($installationPath . '/media', JPATH_ROOT . '/media/neno');

		return true;
	}

	/**
	 * Copying files
	 *
	 * @return bool False if something happens
	 */
	public function uninstall()
	{
		JFolder::delete(JPATH_ROOT . '/layouts/libraries/neno');
		JFolder::delete(JPATH_ROOT . '/media/neno');
	}
}
