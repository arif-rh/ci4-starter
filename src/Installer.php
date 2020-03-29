<?php
/**
 * Part of CodeIgniter Composer Installer
 *
 * @author     Arif RH <https://bitbucket.org/arif-rh>
 * @license    MIT License
 * @copyright  2015 Arif RH
 * @link       https://bitbucket.org/arif-rh/ci3-installer
 */

namespace Arifrh\CI4Starter;

use Composer\Script\Event;

class Installer
{
    const APPFOLDER = 'app';
    const FCFOLDER  = 'public';
    /**
     * Composer post install script
     *
     * @param Event $event
     */
    public static function postInstall(Event $event = null)
    {
        // Set 404 overide route
        $file = static::APPFOLDER.'/Config/Routes.php';
        $contents = file_get_contents($file);
        $contents = str_replace(
            '$routes->setDefaultController(\'Home\');',
            '$routes->setDefaultController(\'Material\');',
            $contents
        );
        file_put_contents($file, $contents);

        self::setupThemes();

        // Show message
        self::showMessage($event);

        // Delete unneeded files
        self::deleteSelf();
    }

    private static function setupThemes()
    {
        // Copy Themes Library
        copy('vendor/arif-rh/ci4-themes-material-kit/src/Config/Themes.php', static::APPFOLDER.'/Config/Themes.php');
        copy('vendor/arif-rh/ci4-themes-material-kit/src/Controllers/Material.php', static::APPFOLDER.'/Controllers/Material.php');

        self::recursiveCopy('vendor/arif-rh/ci4-themes-material_kit/src/Views/material', static::APPFOLDER.'/Views/material');
        self::recursiveCopy('vendor/arif-rh/ci4-themes-material_kit/public/themes/material-kit', static::FCFOLDER.'/themes/material-kit');
    }

    private static function composerUpdate()
    {
        passthru('composer update');
    }

    /**
     * Composer post install script
     *
     * @param Event $event
     */
    private static function showMessage(Event $event = null)
    {
        $io = $event->getIO();
        $io->write('==================================================');
        $io->write(
            '<info>CI4 Project is Ready!</info>'
        );
        $io->write('==================================================');
    }

    private static function deleteSelf()
    {
        self::recursiveRemoveDir('vendor/arif-rh/ci4-starter/src');
    }

    private static function recursiveRemoveDir($dir) 
    {
        if (is_dir($dir)) { 
          $objects = scandir($dir); 
          foreach ($objects as $object) { 
            if ($object != "." && $object != "..") { 
              if (is_dir($dir."/".$object))
                self::recursiveRemoveDir($dir."/".$object);
              else
                unlink($dir."/".$object); 
            } 
          }
          rmdir($dir); 
        } 
    }

    /**
     * Recursive Copy
     *
     * @param string $src
     * @param string $dst
     */
    private static function recursiveCopy($src, $dst)
    {
        mkdir($dst, 0755);
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($src, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                mkdir($dst . '/' . $iterator->getSubPathName());
            } else {
                copy($file, $dst . '/' . $iterator->getSubPathName());
            }
        }
    }
}
