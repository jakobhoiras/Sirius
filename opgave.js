
function optionCheck(id)
{

    var optionIndex = document.getElementById("form" + id).value;
    var divPicture = document.getElementById("image" + id);
    var divText = document.getElementById("text" + id);

    if (optionIndex === "textImage") {
        divPicture.className = "current-help";
        divText.className = "current-help";
    }
    if (optionIndex === "text") {
        divPicture.className = "current-help2";
        divText.className = "current-help";
    }
    if (optionIndex === "image") {

        divPicture.className = "current-help";
        divText.className = "current-help2";
    }
}

//Checker om en valgt fil er over en hvis størrelse, og kommer med en advarsel
//hvis den er.
function change_page(page_name) {
    window.location.href = ("http://www.matkonsw.com/sirius/" + page_name + ".php");
}

function checkFile(file_nr) {
    var control = document.getElementById("files" + file_nr);

    var file = control.files;
    var size = Math.round((file[0].size / 1048576) * 100) / 100;
    if (size > 10) {
        var str = 'File is too large: ' + size + 'mb' + ' - we only support 0mb';
        var result = str.fontcolor("red");
        document.getElementById('toolarge' + file_nr).innerHTML = result;
    }
    // var divBackground = document.getElementById("pic-ude");
    // divBackground.style.background = "none";
    // divBackground.style.backgroundImage = "url(Opgaver/ude.jpg)";

    //document.getElementById('toolarge'+file_nr).innerHTML = "hey";

}


function large_file(fileNr) {
    var control = document.getElementById("files" + fileNr);

    var file = control.files;
    var size = Math.round((file[0].size / 1048576) * 100) / 100;
    if (size > 0) {
        var str = 'File is too large: ' + size + 'mb' + ' - we only support 0mb';
        var result = str.fontcolor("red");
        document.getElementById('toolarge' + fileNr).innerHTML = result;

    }
    control.addEventListener("change", error_printer, false);

}

large_file(2);
large_file(1);

/**function check_submit() {
 if (large_file(2, 1) > 0) {
 alert("Atleast one of the files is too large");
 return false;
 }
 if (large_file(1, 1) > 0) {
 alert("Atleast one of the files is too large");
 return false;
 }
 }**/




