<?php

function zip($source, $destination) {
		echo 1;
        if (!extension_loaded('zip') || !file_exists($source)) {
			echo '3';
            return 'extension fail';
        }
		echo 2;
        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return 'archive fail';
        }
		echo 'test';
        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
                    continue;

                $file = realpath($file);
				ini_set('max_execution_time', 300);
				ini_set('memory_limit', '350M');
                if (is_dir($file) === true) {
                    $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                } else if (is_file($file) === true) {
					ini_set('max_execution_time', 300);
                    $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                }
            }
        } else if (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }
		echo 'here';
        return $zip->close();
    }
$map_name = 'Vejle';
echo getcwd() . "/../tiles/" . $map_name;
zip(getcwd() . "/../tiles/" . $map_name, getcwd() . "/../tiles/" . $map_name . '.zip');
?>
