<?php

function extract_satellite_map($map_name){
	$path = "/opt/lampp/htdocs/tiles/$map_name/";
	$ar = scandir($path); 
	$zooms = array();

	for ($i=0; $i<sizeof($ar); $i++){ //find zoom levels of OSM
		if (is_numeric($ar[$i])){
			array_push($zooms, intval($ar[$i]));
		}
	}

	$pathkom = '/opt/lampp/htdocs/tiles_kom/';
	$kom = scandir($pathkom);
	mkdir("/opt/lampp/htdocs/tiles_sat/$map_name");
	for ($z=0; $z<sizeof($zooms); $z++){
		//echo $zooms[$z];
		mkdir("/opt/lampp/htdocs/tiles_sat/$map_name/" . $zooms[$z]);
		list($xmin, $xmax, $ymin, $ymax) = find_bounds_on_OSM($zooms[$z], $zooms, $path);
		//echo $xmin . ' ' . $xmax . ' ' . $ymin . ' ' . $ymax;
		$tiles_found = array_fill($xmin, $xmax, array_fill($ymin, $ymax, 0));
		for ($k=2; $k < sizeof($kom); $k++){
			//echo $kom[$k];
			$komx_ar = scandir($pathkom . $kom[$k] . '/' . $zooms[$z]);
			if (intval($komx_ar[2]) > $xmax or intval($komx_ar[sizeof($komx_ar)-1]) < $xmin){
				continue;
			} else {
				$komy_ar = scandir($pathkom . $kom[$k] . '/' . $zooms[$z] . '/' . $komx_ar[2]);
				if ( intval( substr( $komy_ar[2], 0, strlen($komy_ar[2])-4 ) ) > $ymax or intval( substr( $komy_ar[sizeof($komy_ar)-1], 0, strlen($komy_ar[sizeof($komy_ar)-1])-4 ) ) < $ymin){
					continue;
				} else {
					for ($x = $xmin; $x <= $xmax; $x++){
						if (!file_exists("/opt/lampp/htdocs/tiles_sat/$map_name/" . $zooms[$z] . "/$x")) {
							mkdir("/opt/lampp/htdocs/tiles_sat/$map_name/" . $zooms[$z] . "/$x");
						}
						//echo json_encode(array_values($tiles_found[$x])) . "\n" . json_encode(array_values(array_fill($ymin,$ymax,1))) . "\n";
						if(array_values($tiles_found[$x]) == array_values(array_fill($ymin,$ymax,1))){ 
							echo 'skip';
							continue;
						} else {
							check_kommune($zooms[$z], $pathkom, $x, $xmax, $ymin, $ymax, $kom[$k], $map_name,$ymin,$ymax);
						}
					}
				}
			}
		}
	}
}

function check_kommune($level, $pathkom, $x, $xmax, $ymin_new, $ymax_new, $kom_res, $map_name, $ymin, $ymax){
	if (check_y_empty($kom_res, $level, $pathkom, $x, $ymin_new) == 1){
		if (check_y_empty($kom_res, $level, $pathkom, $x, $ymax_new) == 1){
			copy_pngs($level,$x,$ymin_new,$ymax_new, $pathkom, $kom_res, $map_name);
		} else {
			 return check_kommune($level, $pathkom, $x, $xmax, $ymin_new, $ymax_new-1, $kom_res, $map_name, $ymin, $ymax);
		}
	} else {
		if ($ymin_new>$ymax){
			return check_kommune($level, $pathkom, $x, $xmax, $ymin_new+1, $ymax_new, $kom_res, $map_name, $ymin, $ymax);
		}else {
			return;
		}
	}
}


function copy_pngs($level, $x, $ymin, $ymax, $pathkom, $kom_res, $map_name){
	for ($y=$ymin; $y<=$ymax; $y++){
		if(!copy($pathkom . $kom_res . "/" . $level . "/$x/$y" . ".png", "/opt/lampp/htdocs/tiles_sat/$map_name/" . $level . "/$x/$y" . ".png")){
			echo "z: $level, x: $x, y: $y";
		} else {
			$tiles_found[$x][$y] = 1;
		}
	}
}

function check_y_empty($kom_res, $level, $pathkom, $x, $y){
	if (!file_exists($pathkom . $kom_res . '/' . $level . '/' . $x . '/' . $y . '.png')){	
		return 0;
	}
	if (filesize($pathkom . $kom_res . '/' . $level . '/' . $x . '/' . $y . '.png') > 400){
		//echo 'imagenanalysis';
		$img = new Imagick($pathkom . $kom_res . '/' . $level . '/' . $x . '/' . $y . '.png');
		//$d = $img->getImageGeometry();
		//$w = $d['width'];
		//$h = $d['height'];

		$colors_bottom = array(array_values($img->getImagePixelColor(1, 254)->getColor()), 
						array_values($img->getImagePixelColor(125, 254)->getColor()), 
						array_values($img->getImagePixelColor(254, 254)->getColor())
						);

		$colors_top = array(array_values($img->getImagePixelColor(1, 1)->getColor()), 
						array_values($img->getImagePixelColor(125, 1)->getColor()), 
						array_values($img->getImagePixelColor(254, 1)->getColor())
						);

		$colors_left = array(array_values($img->getImagePixelColor(1, 254)->getColor()), 
						array_values($img->getImagePixelColor(1, 125)->getColor()), 
						array_values($img->getImagePixelColor(1, 1)->getColor())
						);

		$colors_right = array(array_values($img->getImagePixelColor(254, 254)->getColor()), 
						array_values($img->getImagePixelColor(254, 125)->getColor()), 
						array_values($img->getImagePixelColor(254, 1)->getColor())
						);

		if (array_values($colors_left) == array_values(array( array(0, 0, 0, 0), array(0, 0, 0, 0), array(0, 0, 0, 0) ))) {
			return 0;
		} else if (array_values($colors_bottom) == array_values(array( array(0, 0, 0, 0),array(0, 0, 0, 0), array(0, 0, 0, 0) ))) {
			return 0;
		} else if (array_values($colors_top) == array_values(array( array(0, 0, 0, 0),array(0, 0, 0, 0), array(0, 0, 0, 0) ))) {
			return 0;
		} else if (array_values($colors_right) == array_values(array( array(0, 0, 0, 0),array(0, 0, 0, 0), array(0, 0, 0, 0) ))) {
			return 0;
		} else {

			return 1;
		}
	} else{
		return 0;
	}
}


function convert_tms_xyz($y,$z){
	return pow(2,$z)-$y-1;
}

function find_bounds_on_OSM($level, $zooms, $path){
	for ($z=0; $z<sizeof($zooms); $z++){
		if ($zooms[$z] == $level){
			$ar = scandir($path . $zooms[$z]);
			$xmin = intval($ar[2]);
			$xmax = intval($ar[sizeof($ar)-1]);
			$ar = scandir($path . $zooms[$z] . '/' . $xmin);
			$ymax = convert_tms_xyz( intval( substr($ar[2],0,strlen($ar[2])-4) ), $zooms[$z] );
			$ymin = convert_tms_xyz( intval( substr($ar[sizeof($ar)-1],0,strlen($ar[sizeof($ar)-1])-4) ), $zooms[$z] );
			return array($xmin, $xmax, $ymin, $ymax);
		}
	}
}

/*function find_kommune($level, $kom, $pathkom, $xmin, $xmax, $ymin, $ymax){
	for ($k=2; $k < sizeof($kom); $k++){
		$komx_ar = scandir($pathkom . $kom[$k] . '/' . $level);
		if (intval($komx_ar[2]) <= $xmin ){
			$komy_ar = scandir($pathkom . $kom[$k] . '/' . $level . '/' . $komx_ar[2]);
			if ( intval( substr( $komy_ar[2], 0, strlen($komy_ar[2])-4 ) ) <= $ymin ){
				return $kom[$k];
			} 
		} 
	}
}*/
