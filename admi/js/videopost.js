
    //Init the modal window and write a form for the title, description and channel selection
    function videoPostInit(videoID)
    {
        modalWindowShow(true);
        videoObj.videoID = videoID;

        //first we need to get the list of channels from the server
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
            if(this.responseText.slice(0, 2) != "OK") {
                divs[0].innerHTML = '<p>' + this.responseText + '</p>';
            } else {
                var msgArray = this.responseText.split("|");
                //we just need a title and description and the channel selection for the video so build the form
                var htmltext = '<form><p>Enter a title and description for the video';
                htmltext += '<br /><br />Title:<br /><input type="text" name="title" id="title" size="97" maxlength="254" />';
                htmltext += '<br /><br />Description:';
                htmltext += '<br /><textarea id="desctext" name="description" cols="80" rows="12" onkeyup="videoHashtagHelp();"></textarea>';
                htmltext += '<br /><p>Select a channel to post this video to: ';
                htmltext += '<select name="channel" id="channel">';
                for(var i = 1; i < msgArray.length; i++) {
                    var channelArray = msgArray[i].split(",");
                    htmltext += '<option value="' + channelArray[0] + '">' + channelArray[1] + '</option>';
                }
                htmltext += '</select>';
                htmltext += '<br /><br /><button type="button" onclick="videoPost();return false;">Post This</button></p>';
                htmltext += '</form>';
                divs[0].innerHTML = htmltext;
                videoWriteAnchor();
            }
        }
        req = "/admi/api.php?a=VideoChannels&id=" + videoID;
        xhttp.open("GET", req);
        xhttp.send();
    }

    //If the user is typing a hashtag in the text field then show a list of hashtags to choose from
    //we will look at the last word in the text field and if it starts with a # then we will send it to the server and get back a list of suggestions
    function videoHashtagHelp()
    {
        var desc = document.getElementById("desctext").value;
        var descArray = desc.split(" ");
        var lastWord = descArray[descArray.length - 1];
        //get the last word typed and if it starts with a # and is more than 1 character long then send it to the server
        if(lastWord.charAt(0) == "#" && lastWord.length > 1) {
            //we have a hashtag so send it to the server
            const xhttp = new XMLHttpRequest();
            xhttp.onload = function() {
                if(this.responseText.slice(0, 2) != "OK") {
                    divs[1].innerHTML = '<p>' + this.responseText + '</p>';
                } else {
                    var msgArray = this.responseText.split("|");
                    var htmltext = '<p>Hashtag suggestions:<br />';
                    for(var i = 1; i < msgArray.length; i++) {
                        htmltext += '<a href="#" onclick="videoHashtagAdd(\'' + msgArray[i] + '\');return false;">' + msgArray[i] + '</a> ';
                    }
                    divs[1].innerHTML = htmltext;
                }
            }
            req = "/admi/api.php?a=HashtagHelp&hashtag=" + lastWord;
            xhttp.open("GET", req);
            xhttp.send();
        }
    }

    //convert the first occurrence of the string "video" in the div to a clickable link to the video
    function videoWriteAnchor()
    {
        //first fetch the row from the database of the current video id as a JSON object
        fetchRow(videoObj.videoID)
        .then((response) => {
            //now we have the row, we can write the anchor
            var row = JSON.parse(response);
            var anchor = '<a href="/' + row.base_url + '/' + row.base_filename + '" target="_blank">video</a>';
            divs[0].innerHTML = divs[0].innerHTML.replace('video', anchor);
        })
        .catch((error) => {
            divs[0].innerHTML = '<p>' + error + '</p>';
        });
    }

    //now to post the form values into a new post and update the video table row
    function videoPost()
    {
        var title = document.getElementById("title").value;
        var description = document.getElementById("desctext").value;
        var channel = document.getElementById("channel").value;

        //put the above values into an object so we can send to the server as JSON
        var obj = {
            "title": title,
            "description": description,
            "channel": channel,
            "id": videoObj.videoID
        };

        //now we need to send the object to the server
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function() {
            if(this.responseText.slice(0, 2) != "OK") {
                divs[0].innerHTML = '<p>' + this.responseText + '</p>';
            } else {
                divs[0].innerHTML = '<p>Video successfully posted.</p>';
                updateVideoTableRow();
            }
        }
        req = "/admi/api.php?a=VideoSavePost";
        objData = encodeURIComponent(JSON.stringify(obj));
        xhttp.open("POST", req);
        xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8");
        xhttp.send("x=" + objData);
    }
