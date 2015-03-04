

<script>
var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange=function() {
                console.log('ready: ' + xmlhttp.readyState);
                console.log('status: ' + xmlhttp.status);
                if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                    //offset = xmlhttp.responseText;
                    //console.log('offset');
                }
            }
            xmlhttp.open("GET","game_control.php?func=get_offset",true);
            xmlhttp.send(); 

</script>
