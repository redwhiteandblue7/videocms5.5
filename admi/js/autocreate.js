
var lines;
var linesIx;
const siteObj = {
    siteName : '',
    siteURL : '',
    categories : '',
    getIcon : 1,
    useTitle : 1,
    useMeta : 1
    };

    function autoCreateInit()
    {
        modalWindowShow(false);

        let htmltext = '<form><p>Dump a list of sites in here:</p><br /><textarea id="sitelist" name="sitelist" rows="20" cols="120"></textarea>';
        htmltext += '<br /><br /><input type="checkbox" name="usetitle" id="usetitle" /> Use title tag';
        htmltext += '&nbsp;&nbsp;<input type="checkbox" name="usemeta" id="usemeta" /> Use meta description';
        htmltext += '&nbsp;&nbsp;<input type="checkbox" name="geticon" id="geticon" /> Get favicon';
        htmltext += '<br /><br /><button type="button" onclick="autoCreateGet();return false;">Start</button></p>';
        htmltext += '</form>';

        divs[0].innerHTML = htmltext;
    }

    function autoCreateGet()
    {
        inputs = modal.getElementsByTagName('textarea');
        var textdump = inputs[0].value;
        textdump = textdump.trim();
        if(textdump == '')
        {
            divs[0].innerHTML = '<p>Does not seem to be anything there.</p>';
            return;
        }
        lines = textdump.split(/\r?\n/);
        linesIx = 0;
        divs[0].innerHTML = '';
        autoCreateProcess('Working...');
    }

    function autoCreateProcess(responseTxt)
    {
        divs[0].innerHTML += '<p>' + responseTxt + '</p>';
        if(linesIx >= lines.length)
        {
            divs[0].innerHTML += '<p>Finished.</p>';
            return;
        }
        var line = lines[linesIx++];
        var lineParts = line.split('|');
        if(lineParts.length < 2)
        {
            autoCreateProcess('Not enough info in line starting with ' + line);
        }
        else
        {
            siteObj.siteName = lineParts[0];
            siteObj.siteURL = encodeURIComponent(lineParts[1]);
            if(lineParts.length > 2) siteObj.categories = lineParts[2];
            const objData = JSON.stringify(siteObj);
            const xhttp = new XMLHttpRequest();
            xhttp.onload = function()
            {
                autoCreateProcess(this.responseText);
            }
            req = "/admi/ajax.php?a=create_post_from&type=apis";
            xhttp.open("POST", req);
            xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhttp.send("x=" + objData);
            return false;
        }
    }
