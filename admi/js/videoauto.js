
    var numOfImports = 0;
    var importList;

    function videoImportAll()
    {
        modalWindowShow(false);

        //first we will get a list of all the videos in the directory from the server
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
            if(this.responseText.slice(0, 2) != "OK") {
                divs[0].innerHTML = '<p>' + this.responseText + '</p>';
            } else {
                importList = this.responseText.split("|");
                numOfImports = importList.length - 1;
                //now just display a button to start the import process
                var htmltext = '<form><p>' + numOfImports + ' videos found. Import them all:';
                htmltext += '<br /><br /><button type="button" onclick="divs[0].innerHTML=\'\';videoImportStart(1, false); return false;" class="green">Start Import</button>';
                htmltext += '<br /><br />Import all and skip transcode if video is already suitable';
                htmltext += '<br /><br /><button type="button" onclick="divs[0].innerHTML=\'\';videoImportStart(1, true); return false;" class="purple">Start Import (Skip Existing)</button>';
                htmltext += '</p></form>';

                divs[0].innerHTML = htmltext;
            }
        }
        var req = "/admi/api.php?a=VideoUploads";
        xhttp.open("GET", req);
        xhttp.send();
    }

    //A recursive function to import all the videos in the directory
    function videoImportStart(fileIndex, allowSkip)
    {
        htmltext = divs[0].innerHTML;
        if(fileIndex > numOfImports) {
            htmltext += '<p>Import complete.</p>';
            divs[0].innerHTML = htmltext;
            return;
        }

        filename = importList[fileIndex++];
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
            if(this.responseText.slice(0, 2) != "OK") {
                htmltext += '<p class="error">' + filename + ': ' + this.responseText + '</p>';
                divs[0].innerHTML = htmltext;
            } else {
                htmltext += '<br />' + filename +' imported.';
                divs[0].innerHTML = htmltext;
            }
            videoImportStart(fileIndex, allowSkip);
        }
        var req = "/admi/api.php?a=VideoImport&allowSkip=" + allowSkip + "&filename=" + encodeURIComponent(filename);
        xhttp.open("GET", req);
        xhttp.send();
    }
