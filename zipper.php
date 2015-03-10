<?php

//require_once 'Mysql.php';

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
                	$this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
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

        if (!file_exists(getcwd() . '/Games/' . $gameName)) {
            mkdir(getcwd() . '/Games/' . $gameName);
        }

        if (!file_exists(getcwd() . '/Games/' . $gameName . '/questionfile')) {
            mkdir(getcwd() . '/Games/' . $gameName . '/questionfile');
        }


        for ($i = 0; $i < sizeof($assigns); $i++) {
            $src = getcwd() . '/Opgaver/' . $assigns[$i][0];
            $dst = getcwd() . '/Games/' . $gameName . '/questionfile/' . $assigns[$i][0];
            $this->recurse_copy($src, $dst);
        }

        $this->zip(getcwd() . '/Games/' . $gameName . '/questionfile', getcwd() . '/Games/' . $gameName . '/questionfile.zip');
    }

}
