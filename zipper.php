<?php

require_once 'Mysql.php';

class zipper {

    function zip($source, $destination) {

        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
                    continue;

                $file = realpath($file);

                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } else if (is_file($file) === true) {
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        } else if (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }

        return $zip->close();
    }

    function recurse_copy($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ( $file = readdir($dir))) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . '/' . $file)) {
                    recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    function createZip() {
        $gameName = $_SESSION['cg'];
        $Mysql = new Mysql_spil();

        $assigns = $Mysql->get_assignments();


        echo sizeof($assigns);

        if (!file_exists(getcwd() . '/games/' . $gameName)) {
            mkdir(getcwd() . '/games/' . $gameName);
        }

        if (!file_exists(getcwd() . '/games/' . $gameName . '/questionfile')) {
            mkdir(getcwd() . '/games/' . $gameName . '/questionfile');
        }


        for ($i = 0; $i < sizeof($assigns); $i++) {
            $src = getcwd() . '/opgaver/' . $assigns[$i][1];
            $dst = getcwd() . '/games/' . $gameName . '/questionfile/' . $assigns[$i][1];
            $this->recurse_copy($src, $dst);
        }

        $this->zip(getcwd() . '/games/' . $gameName . '/questionfile', getcwd() . '/games/' . $gameName . '/questionfile.zip');
    }

}
