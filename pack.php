<?php

$extractPath = dirname(__FILE__);
$folders     = folders(dirname(__FILE__));
$packagePath = $extractPath;

// Neno Component folders
$componentPath = $packagePath . DIRECTORY_SEPARATOR . 'com_neno';

// Creating package
if (file_exists($componentPath))
{
	if (rmdirRecursive($componentPath) !== true)
	{
		return false;
	}
}

if (file_exists($packagePath . DIRECTORY_SEPARATOR . 'plg_system_neno'))
{
	if (rmdirRecursive($packagePath . DIRECTORY_SEPARATOR . 'plg_system_neno') !== true)
	{
		return false;
	}
}

if (file_exists($packagePath . DIRECTORY_SEPARATOR . 'lib_neno'))
{
	if (rmdirRecursive($packagePath . DIRECTORY_SEPARATOR . 'lib_neno') !== true)
	{
		return false;
	}
}

if (file_exists($packagePath . DIRECTORY_SEPARATOR . 'packages'))
{
	if (rmdirRecursive($packagePath . DIRECTORY_SEPARATOR . 'packages') !== true)
	{
		return false;
	}
}

if (mkdir($componentPath, 0777, true) !== true)
{
	return false;
}

// Administrator
if (rename($extractPath . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_neno', $componentPath . '/back') !== true)
{
	return false;
}

// Languages
if (rename($extractPath . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'language', $componentPath . DIRECTORY_SEPARATOR . 'languages') !== true)
{
	return false;
}

// Front-end
if (rename($extractPath . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_neno', $componentPath . DIRECTORY_SEPARATOR . 'front') !== true)
{
	return false;
}

// Media files
if (rename($extractPath . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'neno', $componentPath . DIRECTORY_SEPARATOR . 'media') !== true)
{
	return false;
}

// Layouts
if (rename($extractPath . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'neno', $componentPath . DIRECTORY_SEPARATOR . 'layouts') !== true)
{
	return false;
}

// Cli
if (rename($extractPath . DIRECTORY_SEPARATOR . 'cli', $componentPath . DIRECTORY_SEPARATOR . 'cli') !== true)
{
	return false;
}

// Moving installation manifest
if (rename($componentPath . DIRECTORY_SEPARATOR . 'back' . DIRECTORY_SEPARATOR . 'neno.xml', $componentPath . DIRECTORY_SEPARATOR . 'neno.xml') !== true)
{
	return false;
}

// Moving installation script
if (rename($componentPath . DIRECTORY_SEPARATOR . 'back' . DIRECTORY_SEPARATOR . 'script.php', $componentPath . DIRECTORY_SEPARATOR . 'script.php') !== true)
{
	return false;
}

// Neno Plugin folder
if (rename($extractPath . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'neno', $packagePath . DIRECTORY_SEPARATOR . 'plg_system_neno') !== true)
{
	return false;
}

// Neno library folder
if (rename($extractPath . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'neno', $packagePath . DIRECTORY_SEPARATOR . 'lib_neno') !== true)
{
	return false;
}

// Deleting empty folders
if (rmdirRecursive($extractPath . DIRECTORY_SEPARATOR . 'administrator') !== true)
{
	return false;
}

if (rmdirRecursive($extractPath . DIRECTORY_SEPARATOR . 'components') !== true)
{
	return false;
}

if (rmdirRecursive($extractPath . DIRECTORY_SEPARATOR . 'plugins') !== true)
{
	return false;
}

if (rmdirRecursive($extractPath . DIRECTORY_SEPARATOR . 'libraries') !== true)
{
	return false;
}

if (rmdirRecursive($extractPath . DIRECTORY_SEPARATOR . 'layouts') !== true)
{
	return false;
}

$files = files($extractPath);

$rootFiles          = array ('pkg_neno.xml', 'script.php');
$noExtensionFolders = array ('tests', 'media', 'layouts', 'cli');

foreach ($files as $file)
{
	if (!in_array($file, $rootFiles))
	{
		unlink($extractPath . DIRECTORY_SEPARATOR . $file);
	}
}

$folders = folders($extractPath);

foreach ($folders as $extensionFolder)
{
	if ($extensionFolder != 'tests')
	{
		// Parse installation file.
		$installationFileContent = file_get_contents($extractPath . DIRECTORY_SEPARATOR . $extensionFolder . DIRECTORY_SEPARATOR . 'neno.xml');

		if ($extensionFolder == 'lib_neno')
		{
			$libraryFolders   = folders($extractPath . DIRECTORY_SEPARATOR . $extensionFolder);
			$libraryStructure = '';

			foreach ($libraryFolders as $libraryFolder)
			{
				$libraryStructure .= '<folder>' . $libraryFolder . '</folder>' . "\r\t\t";
			}

			$libraryFiles = files($extractPath . DIRECTORY_SEPARATOR . $extensionFolder);

			foreach ($libraryFiles as $libraryFile)
			{
				if ($libraryFile != 'neno.xml')
				{
					$libraryStructure .= '<filename>' . $libraryFile . '</filename>' . "\r\t\t";
				}
			}

			$installationFileContent = str_replace('XXX_LIBRARY_STRUCTURE', $libraryStructure, $installationFileContent);
		}

		file_put_contents($extractPath . DIRECTORY_SEPARATOR . $extensionFolder . DIRECTORY_SEPARATOR . 'neno.xml', $installationFileContent);

		// Creating zip
		$zipData = array ();
		$files   = files($extractPath . DIRECTORY_SEPARATOR . $extensionFolder, '.', true, true);

		if (!empty($files))
		{
			foreach ($files as $file)
			{
				// Unify path structure
				$file = str_replace('/', DIRECTORY_SEPARATOR, $file);
				$file = str_replace('\\', DIRECTORY_SEPARATOR, $file);

				// Add files to zip
				$zipData[] = array (
					'name' => str_replace($extractPath . DIRECTORY_SEPARATOR . $extensionFolder . DIRECTORY_SEPARATOR, '', $file),
					'file' => $file
				);
			}
		}

		if (!empty($zipData))
		{
			if (createZip($extractPath . DIRECTORY_SEPARATOR . 'packages' . DIRECTORY_SEPARATOR . $extensionFolder . '.zip', $zipData) === false)
			{
				return false;
			}
			else
			{
				rmdirRecursive($extractPath . DIRECTORY_SEPARATOR . $extensionFolder);
			}
		}
		else
		{
			return false;
		}
	}
}

// Parse installation file.
$installationFileContent = file_get_contents($extractPath . DIRECTORY_SEPARATOR . 'pkg_neno.xml');

file_put_contents($extractPath . DIRECTORY_SEPARATOR . 'pkg_neno.xml', $installationFileContent);

$zipData = array ();
$files   = files($extractPath, '.', true, true);

if (!empty($files))
{
	foreach ($files as $file)
	{
		$zipData[] = array (
			'name' => substr(str_replace($extractPath, '', $file), 1),
			'file' => $file
		);
	}
}

if (!empty($zipData))
{
	if (createZip(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'releases' . DIRECTORY_SEPARATOR . 'pkg_neno.zip', $zipData) === false)
	{
		return false;
	}
	else
	{
		rmdirRecursive($extractPath);

		return true;
	}
}
else
{
	return false;
}

function folders($path)
{
	$it      = new DirectoryIterator($path);
	$folders = array ();

	while ($it->valid())
	{
		if (is_dir($it->getPathname()) && !$it->isDot() && $it->getFilename() != '.git')
		{
			$folders[] = $it->getFilename();
		}

		$it->next();
	}

	return $folders;
}

function files($path)
{
	$it    = new DirectoryIterator($path);
	$files = array ();

	while ($it->valid())
	{
		if (is_file($it->getPathname()) && !$it->isDot() && $it->getFilename() != '.git')
		{
			$files[] = $it->getFilename();
		}

		$it->next();
	}

	return $files;
}

function createZip($path, $zipData)
{
	$zip = new ZipArchive;

	if ($zip->open($path, ZipArchive::CREATE) !== true)
	{
		exit("cannot open <$path>\n");
	}

	foreach ($zipData as $element)
	{
		$zip->addFile($element['name'], $element['file']);
	}

	$zip->close();
}

function rmdirRecursive($dir)
{
	$it = new RecursiveDirectoryIterator($dir);
	$it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
	foreach ($it as $file)
	{
		if ('.' === $file->getBasename() || '..' === $file->getBasename())
		{
			continue;
		}
		if ($file->isDir())
		{
			rmdir($file->getPathname());
		}
		else
		{
			unlink($file->getPathname());
		}
	}

	return rmdir($dir);
}