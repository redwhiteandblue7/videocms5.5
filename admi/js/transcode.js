
var intervalID;

//videoID is the video_id of the video not the id of the row in the table
const videoObj = {
    videoID : 0,
    };

    function transcodeInit(videoID)
    {
        modalWindowShow(false);

        videoObj.videoID = videoID;

        const xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
            var response = this.responseText;
            if(response.slice(0, 2) != "OK") {
                divs[0].innerHTML = '<p>' + this.responseText + '</p>';
            } else {
                var typeArray = response.split('|');
                var type = typeArray[1];
                if(type == '') {
                    divs[0].innerHTML = '<p>This video cannot be transcoded to any more sizes.</p>';
                    updateVideoTableRow();
                    return;
                }
                let htmltext = '<p>This video can be transcoded to ' + type;
                htmltext += '<br /><br /><button type="button" onclick="startTranscode();return false;">Start Transcode</button></p>';
                divs[0].innerHTML = htmltext;
            }
        }
        req = "/admi/api.php?a=TranscodeType&id=" + videoID;
        xhttp.open("GET", req);
        xhttp.send();
    }

    function startTranscode()
    {
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
            if(this.responseText.slice(0, 2) != "OK") {
                divs[0].innerHTML = '<p>' + this.responseText + '</p>';
            } else {
                watchTranscode();
            }
        }
        req = "/admi/api.php?a=TranscodeStart&id=" + videoObj.videoID;
        xhttp.open("GET", req);
        xhttp.send();
    }

    function watchTranscodeInit(videoID)
    {
        modalWindowShow(false);

        videoObj.videoID = videoID;
        watchTranscode();
    }

    function watchTranscode()
    {
        intervalID = setInterval(function() {
            const xhttp = new XMLHttpRequest();
            var req = '/admi/api.php?a=TranscodeProgress&id=' + videoObj.videoID;
            xhttp.open('GET', req);
            xhttp.onreadystatechange = function() {
                if(xhttp.readyState === XMLHttpRequest.DONE && xhttp.status === 200) {
                    var response = xhttp.responseText;
                    if(response.slice(0, 5) == "Error") {
                        divs[0].innerHTML = '<p>' + response + '</p>';
                        clearInterval(intervalID);
                        return;
                    }
                    if(response == 'Done') {
                        var htmltext = 'Transcoding has finished';
                        htmltext += '<br /><br /><progress value="100" max="100"></progress>';
                        divs[0].innerHTML = htmltext;
                        clearInterval(intervalID);
                        updateVideoTableRow();
                        return;
                    }
                    if(response == 'DoneClip') {
                        var htmltext = 'Transcoding has finished';
                        htmltext += '<br /><br /><button type="button" onclick="videoShowClip();return false;">Watch the preview clip</button>';
                        divs[0].innerHTML = htmltext;
                        clearInterval(intervalID);
                        updateVideoTableRow();
                        return;
                    }
                    var progressArray = response.split('|');
                    var progress = progressArray[0];
                    var videoType = progressArray[1];
                    var seconds_left = progressArray[2];
                    var seconds_elapsed = progressArray[3];
                    //convert seconds to minutes and seconds
                    var minutes = Math.floor(seconds_left / 60);
                    var seconds = seconds_left - minutes * 60;
                    if(seconds < 10) {
                        seconds = '0' + seconds;
                    }
                    var eminutes = Math.floor(seconds_elapsed / 60);
                    var eseconds = seconds_elapsed - eminutes * 60;
                    if(eseconds < 10) {
                        eseconds = '0' + eseconds;
                    }
                    var htmltext = 'Transcoding to ' + videoType + '<br />Elapsed time: ' + eminutes + ':' + eseconds + ', time remaining: ' + minutes + ':' + seconds;
                    //now we need to display a progress bar
                    htmltext += '<br /><br /><progress value="' + progress + '" max="100"></progress>';
                    divs[0].innerHTML = htmltext;
                }
            }
            xhttp.send();
        }, 500);
    }

    //update the html table row with the current data for this video
    function updateVideoTableRow()
    {
        //first fetch the row from the database of the current video id as a JSON object
        fetchRow(videoObj.videoID)
        .then((response) => {
            var obj = JSON.parse(response);
            writeVideoTableRow(obj);
        })
        .catch((error) => {
            divs[0].innerHTML = '<p>' + error + '</p>';
        });
    }
                
    function writeVideoTableRow(obj)
    {
        var table = document.getElementById("videos");
        for (var i = 0, row; row = table.rows[i]; i++)
        {
            var cell = row.cells[1];
            var id = cell.innerHTML;
            if(id == obj.video_id)
            {
                row.cells[2].innerHTML = '<img src="/' + obj.url_thumbnail + '" width="80" height="45" alt="Thumbnail" />';
                row.cells[3].innerHTML = obj.process_state;
                if(obj.process_state == 'transcoded') {
                    row.cells[4].innerHTML = '<button type="button" class="yellow" onclick="videoPreviewInit(' + obj.video_id + ', ' + obj.fps + ');return false;">Get Poster</button>';
                } else {
                    if(obj.process_state == 'ready') {
                    row.cells[4].innerHTML = '<button type="button" class="green" onclick="videoPostInit(' + obj.video_id + ');return false;">Post Video</button>';
                    } else {
                        row.cells[4].innerHTML = '';
                    }
                }
                row.cells[5].innerHTML = 'none';
                row.cells[6].innerHTML = obj.progress + '%';
                if(obj.url_1080p) {
                    row.cells[7].innerHTML = '<a href="/' + obj.url_1080p + '" target="_blank">Yes</a>';
                } else {
                    row.cells[7].innerHTML = 'No';
                }
                if(obj.url_720p) {
                    row.cells[8].innerHTML = '<a href="/' + obj.url_720p + '" target="_blank">Yes</a>';
                } else {
                    row.cells[8].innerHTML = 'No';
                }
                if(obj.url_480p) {
                    row.cells[9].innerHTML = '<a href="/' + obj.url_480p + '" target="_blank">Yes</a>';
                } else {
                    row.cells[9].innerHTML = 'No';
                }
                if(obj.url_low) {
                    row.cells[10].innerHTML = '<a href="/' + obj.url_low + '" target="_blank">Yes</a>';
                } else {
                    row.cells[10].innerHTML = 'No';
                }
                if(obj.url_180p) {
                    row.cells[11].innerHTML = '<a href="/' + obj.url_180p + '" target="_blank">Yes</a>';
                } else {
                    row.cells[11].innerHTML = 'No';
                }
                if(obj.url_vtt) {
                    row.cells[12].innerHTML = 'Yes';
                } else {
                    row.cells[12].innerHTML = 'No';
                }
                row.cells[13].innerHTML = obj.channel_name;
                break;
            }
        }
    }

    //function using a promise to fetch a row of data from the database
    function fetchRow(id)
    {
        return new Promise((resolve, reject) => {
            const xhttp = new XMLHttpRequest();
            xhttp.onload = function() {
                if(this.responseText.slice(0, 2) != "OK") {
                    reject(this.responseText);
                } else {
                    resolve(this.responseText.slice(3));
                }
            }
            req = "/admi/api.php?a=VideoRow&id=" + id;
            xhttp.open("GET", req);
            xhttp.send();
        });
    }