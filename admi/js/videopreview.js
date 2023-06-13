
    var video;
    var frames_per_second = 0;
    var videoTime = 0;
    var videoURL;

    //Init the modal window and get the url of the video we are going to record the preview clip from
    function videoPreviewInit(videoID, fps)
    {
        modalWindowShow(false);

        videoObj.videoID = videoID;
        frames_per_second = fps;

        //now we need to do an ajax call to find the url of the highest resolution video to get the poster image from
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
            if(this.responseText.slice(0, 2) != "OK") {
                divs[0].innerHTML = '<p>' + this.responseText + '</p>';
            } else {
                var msgArray = this.responseText.split("|");
                videoURL = msgArray[1];
                videoPreviewShow();
            }
        }
        var req = "/admi/api.php?a=VideoURL&id=" + videoID;
        xhttp.open("GET", req);
        xhttp.send();
    }

    //Display the video player and controls to allow the user to select the preview image frame by frame
    function videoPreviewShow()
    {
        var htmltext = '<p>Preview video';
        htmltext += '<br /><br /><video width="1024" height="576" controls >';
        htmltext += '<source src="' + videoURL + '" type="video/mp4">';
        htmltext += '</video><br />';
        htmltext += '<button type="button" onclick="video.play(); return false;" class="green">Play</button> ';
        htmltext += '<button type="button" onclick="video.pause(); return false;" class="orange">Pause</button> ';
        htmltext += '<button type="button" onclick="video.pause(); video.currentTime -= ' + (1.0/frames_per_second) + '; return false;">Back 1FR</button> ';
        htmltext += '<button type="button" onclick="video.pause(); video.currentTime += ' + (1.0/frames_per_second) + '; return false;">Forward 1FR</button>&nbsp;&nbsp;';
        htmltext += '<button type="button" onclick="videoPreviewSave(); return false;" class="red">Save Preview Image</button>';
        htmltext += '</p>';

        divs[0].innerHTML = htmltext;

        video = divs[0].getElementsByTagName("video")[0];
        video.currentTime = videoTime;
    }

    //Get the current video time and send it via ajax to a function that will save the preview image
    function videoPreviewSave()
    {
        videoTime = video.currentTime;
        divs[0].innerHTML = '<p>Saving preview image</p><img src="/images/anim/loading.gif" alt="" />';
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
            if(this.responseText.slice(0, 2) != "OK") {
                divs[0].innerHTML = '<p>' + this.responseText + '</p>';
            } else {
                var msgArray = this.responseText.split("|");
                var posterURL = msgArray[1];
                //to defeat caching, we add a random number to the end of the image url
                posterURL += "?r=" + Math.random();
                htmltext = '<p>Preview image saved at ' + videoTime + 's</p><img src="' + posterURL + '" class="modal_preview" alt="" /><br /><br />';
                htmltext += '<button type="button" onclick="videoPreviewShow(); return false;" class="purple">Reject</button> ';
                htmltext += '<button type="button" onclick="videoRecordClip(); return false;" class="green">Continue</button>';
                divs[0].innerHTML = htmltext;

            }
        }
        var req = "/admi/api.php?a=VideoSavePreview&id=" + videoObj.videoID + "&time=" + videoTime;
        xhttp.open("GET", req);
        xhttp.send();
    }

    //Now we need to record the clip from the video from the same point as the preview image
    function videoRecordClip()
    {
        divs[0].innerHTML = '<p>Recording clip</p><img src="/images/anim/loading.gif" alt="" />';
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
            if(this.responseText.slice(0, 2) != "OK") {
                divs[0].innerHTML = '<p>' + this.responseText + '</p>';
            } else {
                watchTranscode();
            }
        }
        var req = "/admi/api.php?a=VideoMakePreview&id=" + videoObj.videoID + "&time=" + videoTime;
        xhttp.open("GET", req);
        xhttp.send();
    }

    //After the clip has finished transcoding the watchTranscode() function will show a button that brings us here
    //to show the preview clip
    function videoShowClip()
    {
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
            if(this.responseText.slice(0, 2) != "OK") {
                divs[0].innerHTML = '<p>' + this.responseText + '</p>';
            } else {
                var msgArray = this.responseText.split("|");
                var posterURL = msgArray[1];
                var videoURL = msgArray[2];
                //to defeat caching, we add a random number to the end of the image url
                posterURL += "?r=" + Math.random();
                var htmltext = '<p>Preview video clip</p>';
                htmltext += '<br /><br /><p><video width="384" height="216" poster="' + posterURL + '" controls >';
                htmltext += '<source src="' + videoURL + '" type="video/mp4">';
                htmltext += '</video></p>';
                htmltext += '<button type="button" onclick="videoMakeVTT(); return false;" class="green">Make Preview Thumbnails</button>';
                divs[0].innerHTML = htmltext;
            }
        }
        var req = "/admi/api.php?a=VideoURL&id=" + videoObj.videoID + "&type=preview";
        xhttp.open("GET", req);
        xhttp.send();

    }

    //Finished with the preview clip, now make the preview thumbs sprite image and the vtt file
    function videoMakeVTT()
    {
        divs[0].innerHTML = '<p>Generating preview thumbnails</p><img src="/images/anim/loading.gif" alt="" />';
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
            if(this.responseText.slice(0, 2) != "OK") {
                divs[0].innerHTML = '<p>' + this.responseText + '</p>';
            } else {
                var msgArray = this.responseText.split("|");
                var vttURL = msgArray[1];
                intervalID = setInterval(videoWatchVTT, 500, vttURL);
            }
        }
        var req = "/admi/api.php?a=VideoMakeVTT&id=" + videoObj.videoID;
        xhttp.open("GET", req);
        xhttp.send();
    }

    //Wait for the vtt sprite image to be generated
    function videoWatchVTT(vttURL)
    {
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
            var response = xhttp.responseText;
            if(response.slice(0, 5) == "Error") {
                divs[0].innerHTML = '<p>' + response + '</p>';
                clearInterval(intervalID);
                return;
            }
            if(response == 'Done') {
                divs[0].innerHTML = '<p><a href="' + vttURL + '" target=\"_blank\">VTT and sprite file generated</a>. Video is now ready to be posted.</p>';
                clearInterval(intervalID);
                updateVideoTableRow();
            }
        }
        var req = "/admi/api.php?a=VideoVTTProgress&id=" + videoObj.videoID;
        xhttp.open("GET", req);
        xhttp.send();
    }
