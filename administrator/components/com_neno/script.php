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
	 * @param string                     $type
	 * @param JInstallerAdapterComponent $parent
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
			if (JFolder::move($installationPath . DIRECTORY_SEPARATOR . 'layouts', JPATH_ROOT . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'neno') !== true)
			{
				return false;
			}

			// Moving media files
			if (JFolder::move($installationPath . DIRECTORY_SEPARATOR . 'media', JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'neno') !== true)
			{
				return false;
			}
		}
		elseif ($type == 'uninstall')
		{
			JFolder::delete(JPATH_ROOT . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'neno');
			JFolder::delete(JPATH_ROOT . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'neno');
		}

		return true;
	}
}