
var siteURL = '';

var ypos_adjust = 0;
var viewer_width = 0;

const imgobj = {
    filename :'',
    sitename : '',
    thumbnail : '',
    poster : '',
    mbposter : '',
    post_id : 0,
    width : 0,
    height : 0,
    crop_y : 0,
    crop_x : 0,
    crop_w : 0,
    crop_h : 0,
    scale_x : 0,
    scale_y : 0
    };

    function screenshotEditorInit(site_url, post_id)
    {
        modalWindowInit(true);
        resetFunc = function() {
            let p = imgobj.post_id;
            imgobj.post_id = 0;
            screenshotEditorInit(siteURL, p);
        }

        modal = document.getElementById('modal_window');
        divs = modal.getElementsByTagName('div');
        if(post_id != imgobj.post_id)
        {
            imgobj.post_id = post_id;
            siteURL = site_url;
            let htmltext = '<form><p>URL to get the screenshot of:</p><br /><input type="text" value="';
            htmltext += siteURL;
            htmltext += '" size="64" maxlength="255" /><br /><br /><button type="button" onclick="screenshotEditorGet();return false;">Get Screenshot</button></p>';
            htmltext += '<br /><br /><br /><p> OR <a href="#" onclick="screenshotEditorUpload();return false;">upload a screenshot image</a></p>';
            htmltext += '<br /><p> OR <a href="#" onclick="screenshotEditorLoad();return false;">use existing image</a></p>';
            htmltext += '</form>';

            divs[0].innerHTML = htmltext;
        }
    }

    function screenshotEditorGet()
    {
        inputs = modal.getElementsByTagName('input');
        url = inputs[0].value;
        divs[0].innerHTML = '<img src="/images/anim/loading.gif" alt="" />';
        req = "/admi/ajax.php?a=get_scr&type=apis&url=" + url;
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function()
        {
            if(this.responseText.slice(0, 5) == "Error")
            {
                divs[0].innerHTML = '<p>' + this.responseText + '.</p>';
            }
            else
            {
                screenshotEditorCrop(this.responseText);
            }
        }
        xhttp.open("GET", req, true);
        xhttp.send();
    }

    function screenshotEditorUpload()
    {
        let htmltext = '<form><p>Choose an image file to upload:</p><br /><input id="screenshot_upload" type="file" name="screenshot_upload" size="80" />';
        htmltext += '<br /><br /><button type="button" onclick="uploadFile();return false;">Upload File</button>';
        htmltext += '</form>';

        divs[0].innerHTML = htmltext;

    }

    async function uploadFile()
    {
        let fileField = document.getElementById('screenshot_upload');
        let formData = new FormData();
        formData.append("screenshot_upload", fileField.files[0]);
        divs[0].innerHTML = '<img src="/images/anim/loading.gif" alt="" />';
        let response = await fetch('/admi/ajax.php?a=upload_img&type=apis', { method: "POST", body: formData });
        if(response.ok)
        {
            let return_text = await response.text();
            if(return_text.slice(0, 5) == "Error")
            {
                divs[0].innerHTML = '<p>' + return_text + '.</p>';
            }
            else
            {
                screenshotEditorCrop(return_text);
            }
        }
        else
        {
            divs[0].innerHTML = '<p>Something went badly wrong.</p>';
        }
    }

    function screenshotEditorLoad()
    {
        divs[0].innerHTML = '<img src="/images/anim/loading.gif" alt="" />';
        req = "/admi/ajax.php?a=get_img_list&type=apis";
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function()
        {
            if(this.responseText.slice(0, 5) == "Error")
            {
                divs[0].innerHTML = '<p>' + this.responseText + '.</p>';
            }
            else
            {
                divs[0].innerHTML = this.responseText;
            }
        }
        xhttp.open("GET", req, true);
        xhttp.send();
    }

    function loadImgFile(el)
    {
        divs[0].innerHTML = '<img src="/images/anim/loading.gif" alt="" />';
        filename = el.options[el.selectedIndex].text;
        screenshotEditorCrop('/images/screenshots/' + filename);
    }

    async function screenshotEditorCrop(imgPath)
    {
        const img = new Image();
        imgobj.filename = imgPath;
        img.src = baseURL + imgPath;
        await img.decode();
        imgobj.width = img.width;
        imgobj.height = img.height;

        let htmltext = '<form>';
        if((imgobj.height < min_image_height) || (imgobj.width < min_image_width))
        {
            htmltext += 'Image cannot be used. Needs to be at least ' + min_image_width + 'px x ' + min_image_height + 'px.';
        }
        else
        {
            if(imgobj.height > imgobj.width)
            {
                let cropvalue = imgobj.height - imgobj.width;
                htmltext += 'Image height ' + imgobj.height + ' is greater than width ' + imgobj.width + '. Crop ' + cropvalue + 'px to make square:<br /><br />';
                htmltext += '<div id="imgviewer" class="thumbviewer">';
                htmltext += '<img id="imgviewerimg" src="' + baseURL + imgobj.filename + '" alt="" />';
                htmltext += '<div class="imgvieweredge"></div><div id="imgviewerbox" class="thumbviewer"></div>';
                htmltext += '<div class="imgvieweredge"></div>';
                htmltext += '</div>';
                htmltext += '<div id="imgviewerctrl">';
                htmltext += '<br />Crop from top: <input type="number" id="imgcropy" name="imgcropy" value="0" min="0" max="' + cropvalue + '" onchange="setImgCropY(this);" />';
                htmltext += '<button type="button" onclick="sendCropData();return false;">Continue &raquo;</button> OR: <a href="#" onclick="screenshotEditorPoster();return false;">Skip this bit</a>';
                htmltext += '</div>';
            }
            else
            {
                htmltext += '<img src="' + baseURL + imgobj.filename + '" id="editoverlayimg" alt="" /><br /><br />';
                htmltext += 'Make the thumbnail from this: ';
                htmltext += '<button type="button" onclick="sendThumbData();return false;">Continue &raquo;</button> OR: <a href="#" onclick="screenshotEditorPoster();return false;">Skip this bit</a>';
            }
        }
        htmltext += '</form>';
        divs[0].innerHTML = htmltext;
        ypos_adjust = document.getElementsByClassName('imgvieweredge')[0].clientHeight;
        viewer_width = document.getElementById('imgviewer').clientWidth;
        document.getElementById("imgviewerimg").style.top = ypos_adjust + 'px';
        imgobj.crop_y = 0;
    }

    function sendCropData()
    {
        divs[0].innerHTML = '<img src="/images/anim/loading.gif" alt="" />';
        const objData = JSON.stringify(imgobj);
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function()
        {
            if(this.responseText.slice(0, 5) == "Error")
            {
                divs[0].innerHTML = '<p>' + this.responseText + '.</p>';
            }
            else
            {
                screenshotEditorCrop(this.responseText);
            }
        }
        req = "/admi/ajax.php?a=crop_img_to_sqr&type=apis";
        xhttp.open("POST", req);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("x=" + objData);
        return false;
    }

    function sendThumbData()
    {
        divs[0].innerHTML = '<img src="/images/anim/loading.gif" alt="" />';
        const objData = JSON.stringify(imgobj);
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function()
        {
            if(this.responseText.slice(0, 5) == "Error")
            {
                divs[0].innerHTML = '<p>' + this.responseText + '.</p>';
            }
            else
            {
                imgobj.thumbnail = this.responseText;
                setThumb();
            }
        }
        req = "/admi/ajax.php?a=make_thumb&type=apis";
        xhttp.open("POST", req);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("x=" + objData);
        return false;
    }

    function setImgCropY(el)
    {
        imgobj.crop_y = el.value;
        let y = Math.round((imgobj.crop_y * viewer_width) / imgobj.width);
        let real_y = ypos_adjust - y;
        document.getElementById("imgviewerimg").style.top = real_y + 'px';
    }

    function setThumb()
    {
        const objData = JSON.stringify(imgobj);
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function()
        {
            if(this.responseText.slice(0, 5) == "Error")
            {
                divs[0].innerHTML = '<p>' + this.responseText + '.</p>';
            }
            else
            {
                var table = document.getElementById("posts");
                for (var i = 0, row; row = table.rows[i]; i++)
                {
                    var cell = row.cells[1];
                    var id = cell.innerHTML;
                    if(id == imgobj.post_id)
                    {
                        row.cells[3].innerHTML = '<img src="' + baseURL + imgobj.thumbnail + '" width="150" />';
                        break;
                    }
                }
                screenshotEditorPoster();
            }
        }
        req = "/admi/ajax.php?a=set_thumb&type=apis";
        xhttp.open("POST", req);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("x=" + objData);
        return false;
    }

    async function screenshotEditorPoster()
    {
        const img = new Image();
        img.src = baseURL + imgobj.filename;
        await img.decode();
        imgobj.width = img.width;
        imgobj.height = img.height;

        let scaledHeight = Math.floor((imgobj.width / poster_width) * poster_height);
        let htmltext = '<form>';
        if(imgobj.height > scaledHeight)
        {
            let cropvalue = imgobj.height - scaledHeight;
            htmltext += 'Crop to ' + scaledHeight + 'px to make the poster image:<br /><br />';
            htmltext += '<div id="imgviewer" style="width:' + poster_width + 'px;">';
            htmltext += '<img id="imgviewerimg" src="' + baseURL + imgobj.filename + '" alt="" />';
            htmltext += '<div class="imgvieweredge"></div><div id="imgviewerbox" style="width:' + poster_width + 'px;height:' + poster_height + 'px;"></div>';
            htmltext += '<div class="imgvieweredge"></div>';
            htmltext += '</div>';
            htmltext += '<div id="imgviewerctrl">';
            htmltext += '<br />Crop from top: <input type="number" id="imgcropy" name="imgcropy" value="0" min="0" max="' + cropvalue + '" onchange="setImgCropY(this);" />';
            htmltext += ' <button type="button" onclick="sendPosterData();return false;">Continue &raquo;</button> OR: <a href="#" onclick="screenshotEditorMobile();return false;">Skip this bit</a>';
            htmltext += '</div>';
        }
        else
        {
            htmltext += '<img src="' + baseURL + imgobj.filename + '" id="editoverlayimg" alt="" /><br /><br />';
            htmltext += 'Make the poster from this: ';
            htmltext += '<button type="button" onclick="sendPosterData();return false;">Continue &raquo;</button> OR: <a href="#" onclick="screenshotEditorMobile();return false;">Skip this bit</a>';
        }
        htmltext += '</form>';
        divs[0].innerHTML = htmltext;
        ypos_adjust = document.getElementsByClassName('imgvieweredge')[0].clientHeight;
        viewer_width = document.getElementById('imgviewer').clientWidth;
        document.getElementById("imgviewerimg").style.top = ypos_adjust + 'px';
        imgobj.crop_y = 0;
    }

    function sendPosterData()
    {
        divs[0].innerHTML = '<img src="/images/anim/loading.gif" alt="" />';
        const objData = JSON.stringify(imgobj);
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function()
        {
            if(this.responseText.slice(0, 5) == "Error")
            {
                divs[0].innerHTML = '<p>' + this.responseText + '.</p>';
            }
            else
            {
                imgobj.poster = this.responseText;
                setPoster();
            }
        }
        req = "/admi/ajax.php?a=make_poster&type=apis";
        xhttp.open("POST", req);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("x=" + objData);
        return false;
    }

    function setPoster()
    {
        const objData = JSON.stringify(imgobj);
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function()
        {
            if(this.responseText.slice(0, 5) == "Error")
            {
                divs[0].innerHTML = '<p>' + this.responseText + '.</p>';
            }
            else
            {
                var table = document.getElementById("posts");
                for (var i = 0, row; row = table.rows[i]; i++)
                {
                    var cell = row.cells[1];
                    var id = cell.innerHTML;
                    if(id == imgobj.post_id)
                    {
                        row.cells[4].innerHTML = '<img src="' + baseURL + imgobj.poster + '" width="200" />';
                        break;
                    }
                }
                screenshotEditorMobile();
            }
        }
        req = "/admi/ajax.php?a=set_poster&type=apis";
        xhttp.open("POST", req);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("x=" + objData);
        return false;
    }

    function screenshotEditorMobile()
    {
        let scaledHeight = Math.floor((imgobj.width / mbposter_width) * mbposter_height);
        let htmltext = '<form>';
        if(imgobj.height > scaledHeight)
        {
            let cropvalue = imgobj.height - scaledHeight;
            htmltext += 'Crop to ' + scaledHeight + 'px to make the mobile poster image:<br /><br />';
            htmltext += '<div id="imgviewer" style="width:' + mbposter_width + 'px;">';
            htmltext += '<img id="imgviewerimg" src="' + baseURL + imgobj.filename + '" alt="" />';
            htmltext += '<div class="imgvieweredge"></div><div id="imgviewerbox" style="width:' + mbposter_width + 'px;height:' + mbposter_height + 'px;"></div>';
            htmltext += '<div class="imgvieweredge"></div>';
            htmltext += '</div>';
            htmltext += '<div id="imgviewerctrl">';
            htmltext += '<br />Crop from top: <input type="number" id="imgcropy" name="imgcropy" value="0" min="0" max="' + cropvalue + '" onchange="setImgCropY(this);" />';
            htmltext += ' <button type="button" onclick="sendMobileData();return false;">Continue &raquo;</button>';
            htmltext += '</div>';
        }
        else
        {
            htmltext += '<img src="' + baseURL + imgobj.filename + '" id="editoverlayimg" alt="" /><br /><br />';
            htmltext += 'Make the poster from this: ';
            htmltext += '<button type="button" onclick="sendMobileData();return false;">Continue &raquo;</button>';
        }
        htmltext += '</form>';
        divs[0].innerHTML = htmltext;
        ypos_adjust = document.getElementsByClassName('imgvieweredge')[0].clientHeight;
        viewer_width = document.getElementById('imgviewer').clientWidth;
        document.getElementById("imgviewerimg").style.top = ypos_adjust + 'px';
        imgobj.crop_y = 0;
    }

    function sendMobileData()
    {
        divs[0].innerHTML = '<img src="/images/anim/loading.gif" alt="" />';
        const objData = JSON.stringify(imgobj);
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function()
        {
            if(this.responseText.slice(0, 5) == "Error")
            {
                divs[0].innerHTML = '<p>' + this.responseText + '.</p>';
            }
            else
            {
                imgobj.mbposter = this.responseText;
                setMobile();
            }
        }
        req = "/admi/ajax.php?a=make_mobile&type=apis";
        xhttp.open("POST", req);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("x=" + objData);
        return false;
    }

    function setMobile()
    {
        const objData = JSON.stringify(imgobj);
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function()
        {
            if(this.responseText.slice(0, 5) == "Error")
            {
                divs[0].innerHTML = '<p>' + this.responseText + '.</p>';
            }
            else
            {
                var table = document.getElementById("posts");
                for (var i = 0, row; row = table.rows[i]; i++)
                {
                    var cell = row.cells[1];
                    var id = cell.innerHTML;
                    if(id == imgobj.post_id)
                    {
                        row.cells[5].innerHTML = '<img src="' + baseURL + imgobj.mbposter + '" width="150" />';
                        break;
                    }
                }
                screenshotEditorDone();
            }
        }
        req = "/admi/ajax.php?a=set_mobile&type=apis";
        xhttp.open("POST", req);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("x=" + objData);
        return false;
    }

    function screenshotEditorDone()
    {
        divs[0].innerHTML = '<p>Done.</p>';
        imgobj.post_id = 0;
    }
