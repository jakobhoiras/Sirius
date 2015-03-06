
function getDistance(array3) {
    var lengths = "";
    var array = JSON.parse(array3);
    for (i = 0; i < array.length; i++) {
        var length = 0;
        var array2 = array[i];
        var id = array2[0][0];
        
        for (j = 1; j < array2.length-1; j++) {
            var lon1 = array2[j][1];
            var lat1 = array2[j][2];
            var lat2 = array2[j + 1][1];
            var lon2 = array2[j + 1][2];
            length = length + Vincenty_Distance(lat1, lon1, lat2, lon2);
        }
        if (length !== 0) {
            lengths = lengths + "<p>Team " + id + ":<br>Distance traveled: " + length + "</p>";
        }
        document.getElementById('element').innerHTML = lengths;
    }
    


}

function deg2rad(angle) {
  return angle * .017453292519943295; // (angle / 180) * Math.PI;
}

function Vincenty_Distance(lat1, lon1, lat2, lon2) {
    // http://www.movable-type.co.uk/scripts/LatLongVincenty.html
    if (Math.abs(parseFloat(lat1)) > 90 || Math.abs(parseFloat(lon1)) > 180 || Math.abs(parseFloat(lat2)) > 90 || Math.abs(parseFloat(lon2)) > 180) {
        return 'n/a';
    }
    if (lat1 == lat2 && lon1 == lon2) {
        return '0';
    }

    lat1 = deg2rad(lat1);
    lon1 = deg2rad(lon1);
    lat2 = deg2rad(lat2);
    lon2 = deg2rad(lon2);

    var a = 6378137, b = 6356752.3142, f = 1 / 298.257223563;
    var L = lon2 - lon1;
    var U1 = Math.atan((1 - f) * Math.tan(lat1));
    var U2 = Math.atan((1 - f) * Math.tan(lat2));
    var sinU1 = Math.sin(U1), cosU1 = Math.cos(U1);
    var sinU2 = Math.sin(U2), cosU2 = Math.cos(U2);
    var lambda = L, lambdaP = 2 * Math.PI;
    var iterLimit = 50;
    while (Math.abs(lambda - lambdaP) > 1e-12 && --iterLimit > 0) {
        var sinLambda = Math.sin(lambda), cosLambda = Math.cos(lambda);
        var sinSigma = Math.sqrt((cosU2 * sinLambda) * (cosU2 * sinLambda) +
                (cosU1 * sinU2 - sinU1 * cosU2 * cosLambda) * (cosU1 * sinU2 - sinU1 * cosU2 * cosLambda));
        var cosSigma = sinU1 * sinU2 + cosU1 * cosU2 * cosLambda;
        var sigma = Math.atan2(sinSigma, cosSigma);
        var alpha = Math.asin(cosU1 * cosU2 * sinLambda / sinSigma);
        var cosSqAlpha = Math.cos(alpha) * Math.cos(alpha);
        var cos2SigmaM = (!cosSqAlpha) ? 0 : cosSigma - 2 * sinU1 * sinU2 / cosSqAlpha;
        var C = f / 16 * cosSqAlpha * (4 + f * (4 - 3 * cosSqAlpha));
        lambdaP = lambda;
        lambda = L + (1 - C) * f * Math.sin(alpha) * (sigma + C * sinSigma * (cos2SigmaM + C * cosSigma * (-1 + 2 * cos2SigmaM * cos2SigmaM)));
    }
    if (iterLimit == 0) {
        return (NaN);
    }  // formula failed to converge
    var uSq = cosSqAlpha * (a * a - b * b) / (b * b);
    var A = 1 + uSq / 16384 * (4096 + uSq * (-768 + uSq * (320 - 175 * uSq)));
    var B = uSq / 1024 * (256 + uSq * (-128 + uSq * (74 - 47 * uSq)));
    var deltaSigma = B * sinSigma * (cos2SigmaM + B / 4 * (cosSigma * (-1 + 2 * cos2SigmaM * cos2SigmaM) - B / 6 * cos2SigmaM * (-3 + 4 * sinSigma * sinSigma) * (-3 + 4 * cos2SigmaM * cos2SigmaM)));
    var s = b * A * (sigma - deltaSigma);

    return s;
}