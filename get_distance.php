<?php 

function Vincenty_Distance($lat1,$lon1,$lat2,$lon2) {
	// http://www.movable-type.co.uk/scripts/LatLongVincenty.html
	if (abs(floatval($lat1)) > 90 or abs(floatval($lon1)) > 180 or abs(floatval($lat2)) > 90 or abs(floatval($lon2)) > 180) { return 'n/a'; }
	if ($lat1 == $lat2 and $lon1 == $lon2) { return '0'; }
	
	$lat1 = deg2rad($lat1); $lon1 = deg2rad($lon1);
	$lat2 = deg2rad($lat2); $lon2 = deg2rad($lon2);

	$a = 6378137;
    $b = 6356752.3142;
    $f = 1/298.257223563;
	$L = $lon2 - $lon1;
	$U1 = atan((1-$f) * tan($lat1));
	$U2 = atan((1-$f) * tan($lat2));
	$sinU1 = sin($U1); $cosU1 = cos($U1);
	$sinU2 = sin($U2); $cosU2 = cos($U2);
	$lambda = $L; $lambdaP = 2*pi();
	$iterLimit = 50;
	while (abs($lambda-$lambdaP) > 1e-12 && --$iterLimit > 0) {
		$sinLambda = sin($lambda); $cosLambda = cos($lambda);
		$sinSigma = sqrt(($cosU2*$sinLambda) * ($cosU2*$sinLambda) + 
		  ($cosU1*$sinU2-$sinU1*$cosU2*$cosLambda) * ($cosU1*$sinU2-$sinU1*$cosU2*$cosLambda));
		$cosSigma = $sinU1*$sinU2 + $cosU1*$cosU2*$cosLambda;
		$sigma = atan2($sinSigma, $cosSigma);
		$alpha = asin($cosU1 * $cosU2 * $sinLambda / $sinSigma);
		$cosSqAlpha = cos($alpha) * cos($alpha);
		$cos2SigmaM = (!$cosSqAlpha) ? 0 : $cosSigma - 2*$sinU1*$sinU2/$cosSqAlpha;
		$C = $f/16*$cosSqAlpha*(4+$f*(4-3*$cosSqAlpha));
		$lambdaP = $lambda;
		$lambda = $L + (1-$C) * $f * sin($alpha) * ($sigma + $C*$sinSigma*($cos2SigmaM+$C*$cosSigma*(-1+2*$cos2SigmaM*$cos2SigmaM)));
	}
	if ($iterLimit==0) { return (NaN); }  // formula failed to converge
	$uSq = $cosSqAlpha*($a*$a-$b*$b)/($b*$b);
	$A = 1 + $uSq/16384*(4096+$uSq*(-768+$uSq*(320-175*$uSq)));
	$B = $uSq/1024 * (256+$uSq*(-128+$uSq*(74-47*$uSq)));
	$deltaSigma = $B*$sinSigma*($cos2SigmaM+$B/4*($cosSigma*(-1+2*$cos2SigmaM*$cos2SigmaM) - $B/6*$cos2SigmaM*(-3+4*$sinSigma*$sinSigma)*(-3+4*$cos2SigmaM*$cos2SigmaM)));
	$s = $b*$A*($sigma-$deltaSigma);

		return $s;
}

/*$lat1 = 12.44593;
$lon1 = 55.75080;
$lat2 = 12.46223;
$lon2 = 55.75080;*/

/*$lat1 = 10.02507;
$lon1 = 56.46255;
$lat2 = 10.02672;
$lon2 = 56.46261;
echo Vincenty_Distance($lat1,$lon1,$lat2,$lon2);*/
