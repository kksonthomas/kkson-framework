<?php

namespace KKsonFramework\CRUD;


use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CleanUp
{

    public static function cleanUp() {

        $dirs = [
           // 'vendor/almasaeed2010/adminlte/plugins',
            'vendor/almasaeed2010/adminlte/documentation',
        ];

        foreach ($dirs as $dir) {
            CleanUp::deleteDir($dir);
        }
    }

    public static function deleteDir($dir) {
        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it,
            RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->isDir()){
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dir);
    }

}