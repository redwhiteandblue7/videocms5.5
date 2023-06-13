
    var currentVideoNode;
    var currentNodeThumb;
    var currentTimerID;
    var video_id = 0;
    var video = '';
    var displayState = '';
    const videoObj = {
        videoID : 0,
        videoTime : 0,
    };
    var overlay = document.getElementById('overlay');
    overlay.innerHTML = '<div></div>';
    var divs = overlay.getElementsByTagName('div');
    var intervalID = null;
    const close_x = '<span id="close_x" onclick="overlayHide();return false;">&times;</span>';
    const burgerButton = document.getElementById('burger');
    const menu = document.getElementById('menu');
    const historyDiv = document.getElementById('history');
    const sideHistoryDiv = document.getElementById('sidehistory');

    const popup = document.getElementById('popup');
    const popup_x = '<span id="popup_x" onclick="popupHide();return false;">&times;</span>';

    const loggedInContentDiv = document.getElementById('loggedin-content');

    function setThumbsEvents()
    {
        var thumbs = document.querySelectorAll('.thmb');
        for (let i = 0; i < thumbs.length; i++) {
            thumbs[i].addEventListener('touchmove', handleTouchMove, false);
//            thumbs[i].addEventListener('touchstart', handleTouchStart, false);
            thumbs[i].addEventListener('mouseleave', handleMouseLeave, false);
            thumbs[i].addEventListener('mouseover', handleMouseOver, false);
        }
    }

    function handleTouchStart(e)
    {
        e.preventDefault();
        var anchors = this.getElementsByTagName('a');
        if(anchors.length) {
            thumbSetVideo(anchors);
        }
    }

    function handleTouchMove(e)
    {
        var anchors = this.getElementsByTagName('a');
        if(anchors.length) {
            thumbSetVideo(anchors);
        }
    }

    function handleMouseLeave(e)
    {
        thumbSetThumb();
    }

    function handleMouseOver(e)
    {
        var anchors = this.getElementsByTagName('a');
        if(anchors.length) {
            thumbSetVideo(anchors);
        }
    }

    function thumbSetVideo(anchors)
    {
        if(currentVideoNode != anchors[0]) {
            if(currentVideoNode) {
                currentVideoNode.innerHTML = currentNodeThumb;
            }
            currentVideoNode = anchors[0];
            currentNodeThumb = anchors[0].innerHTML;
            var thumbImg = anchors[0].firstChild;
            var thumbURL = thumbImg.getAttribute('src');
            var video = document.createElement('video');
            var f = thumbURL.replace('_thmb.jpg', '_180p.mp4');
            video.playsinline = true;
            video.zIndex = 1000;
            video.src = f;
            video.poster = thumbURL;
            video.height = 216;
            video.width = 384;
            video.controls = false;
            video.muted = true;
            video.autoplay = true;
            if(desktop) video.loop = true;

            video.disablePictureInPicture = true;	//note that in Edge this is case sensitive
            video.onended = thumbSetThumb;
            video.disableRemotePlayback = true;
//            video = "<video src='" + f + "' height='216' width='384' controls='false' muted='true' autoplay='true' playsinline='true'></video>";
            thumbImg.replaceWith(video);
        }
    }

    function thumbSetThumb()
    {
        if(currentVideoNode) {
            currentVideoNode.innerHTML = currentNodeThumb;
            currentVideoNode = '';
        }
        if(currentTimerID) {
            clearTimeout(currentTimerID);
            currentTimerID = null;
        }
    }

    setThumbsEvents();

    burgerButton.onclick = function(e) {
        const menu_state = menu.getAttribute('data-visible');
        if(menu_state == 'true') {
            menu.setAttribute('data-visible', 'false');
            burgerButton.setAttribute('data-state', 'closed');
        } else {
            menu.setAttribute('data-visible', 'true');
            burgerButton.setAttribute('data-state', 'open');
        }
        return false;
    }


    if(historyDiv) {
        //the div exists
        //now we will get the user's post_ids from the local storage and send it to the server via ajax, and receive back the html for the history posts

        var post_ids = localStorage.getItem('post_ids');
        if(post_ids) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/api/history', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (this.status == 200) {
                    //we have the html, we add it after the history div
                    historyDiv.insertAdjacentHTML('afterend', this.responseText);
                    //now we add the thumbs events to the new thumbs
                    setThumbsEvents();
                }
            }
            xhr.send('post_ids=' + post_ids);
        }
    }


    if(sideHistoryDiv) {
        //the sidebar history div exists
        //now we will get the user's post_ids from the local storage and send it to the server via ajax, and receive back the html for the history posts

        var post_ids = localStorage.getItem('post_ids');
        if(post_ids) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/api/sidehistory', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (this.status == 200) {
                    //we have the html, we add it after the history div
                    sideHistoryDiv.insertAdjacentHTML('afterend', this.responseText);
                    //now we add the thumbs events to the new thumbs
                }
            }
            xhr.send('post_ids=' + post_ids);
        }
    }

    function overlayHide()
    {
        overlayHalt();
        overlay.style.display = 'none';
        divs[0].innerHTML = '';
        displayState = '';
    }

    function overlayContent(content)
    {
        divs[0].innerHTML = close_x + content;
        overlay.style.display = 'block';
    }

    function overlayHalt()
    {
        if(intervalID) {
            clearInterval(intervalID);
            intervalID = null;
        }
    }


    //display a popup message and restart its animation
    function popupMessage(message)
    {
        popup.innerHTML = popup_x + '<p>' + message + '</p>';
        popup.style.display = 'block';
        popup.style.backgroundColor = '#87cefa';
        popup.style.animation = 'none';
        popup.offsetHeight;	//this is needed to reset the animation
        popup.style.animation = null;
        //we need to set up a delay to hide the popup
        setTimeout(popupHide, 4000);
    }

    //display a popup error message and restart its animation
    function popupError(message)
    {
        popup.innerHTML = popup_x + '<p>' + message + '</p>';
        popup.style.display = 'block';
        popup.style.backgroundColor = '#ffb6c1';
        popup.style.animation = 'none';
        popup.offsetHeight;	//this is needed to reset the animation
        popup.style.animation = null;
        //we need to set up a delay to hide the popup
        setTimeout(popupHide, 4000);
    }

    //this will hide the popup message box
    function popupHide()
    {
        popup.style.display = 'none';
    }

    function loginFormInit()
    {
        var htmlText = '<br /><p>Log in to your account to upload new videos, view your video and channel info and more.</p>';
        htmlText += '<form id="login_form" method="post" onsubmit="submitLoginForm();return false;">';
        htmlText += '<p id="err_msg" class="error"><br /></p>';
        htmlText += 'Username:<br /><input type="text" name="name" placeholder="Username" required />';
        htmlText += '<br />Password:<br /><input type="password" name="pass" placeholder="Password" required />';
        htmlText += '<br /><br /><input type="submit" value="Log In" />';
        htmlText += '<div id="loginlinks"><br /><a href="#" onclick="forgotFormInit();return false;">Forgot your password?</a>';
        htmlText += '<br /><br />Don\'t have an account? <a href="#" onclick="registerFormInit();return false;">Register</a></div>';
        htmlText += '</form>';
        overlayContent(htmlText);
    }

    function registerFormInit()
    {
        var htmlText = '<br /><p>Register an account to create your own video channels, upload your videos, comment on videos and more.</p>';
        htmlText += '<form id="register_form" method="post" onsubmit="registerFormNext();return false;">';
        htmlText += '<p id="err_msg" class="error"><br /></p>';
        htmlText += 'Choose a username:<br /><input type="text" name="new_name" id="new_name" oninput="validateUser();" placeholder="Username, letters or numbers only" required />';
        htmlText += '<br />Enter your email address:<br /><input type="email" name="email" id="email" oninput="validateEmail();" placeholder="Email" required />';
        htmlText += '<br /><br /><input type="submit" id="reg_submit" value="Register" disabled="true" /></form>';
        overlayContent(htmlText);
    }

    function forgotFormInit()
    {
        var htmlText = '<br /><p>This form doesn\'t work yet because I haven\'t configured the email server so I can\'t automatically send you a ';
        htmlText += 'link to reset your password.</p><p> If you genuinely have forgotten your password, send me an email at ';
        htmlText += 'tprosser@protonmail.com from the email address you signed up with and I\'ll see what I can do about it.</p>';
        overlayContent(htmlText);
    }

    //check the username is valid and not already taken
    function validateUser(quiet = false)
    {
        var name = document.getElementById('new_name').value;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/api/validateuser', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (this.status == 200) {
                if(this.responseText == 'OK') {
                    document.getElementById('err_msg').innerHTML = '<br />';
                    if(quiet) {
                        document.getElementById('reg_submit').disabled = false;
                    } else {
                        validateEmail(true);
                    }
                    return;
                } else if(this.responseText == 'name_exists') {
                    var msg = 'That username is already taken';
                } else if(this.responseText == 'name_short') {
                    var msg = 'That username is too short';
                } else if(this.responseText == 'name_invalid') {
                    var msg = 'That username is not valid';
                } else {
                    var msg = 'An error occurred' + this.responseText;
                }
                if(!quiet) {
                    document.getElementById('err_msg').innerHTML = msg;
                }
                document.getElementById('reg_submit').disabled = true;
            }
        }
        xhr.send('name=' + name);
    }

    //check the email address is valid and not already taken
    function validateEmail(quiet = false)
    {
        var email = document.getElementById('email').value;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/api/validateemail', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (this.status == 200) {
                if(this.responseText == 'OK') {
                    document.getElementById('err_msg').innerHTML = '<br />';
                    if(quiet) {
                        document.getElementById('reg_submit').disabled = false;
                    } else {
                        validateUser(true);
                    }
                    return;
                } else if(this.responseText == 'email_exists') {
                    var msg = 'That email address is already in use';
                } else if(this.responseText == 'email_invalid') {
                    var msg = 'That email address is not valid';
                } else {
                    var msg = 'An error occurred' + this.responseText;
                }
                if(!quiet) {
                    document.getElementById('err_msg').innerHTML = msg;
                }
                document.getElementById('reg_submit').disabled = true;
            }
        }
        xhr.send('email=' + encodeURIComponent(email));
    }

    //submit the login form via ajax
    function submitLoginForm()
    {
        var form = document.getElementById('login_form');
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/api/login', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (this.status == 200) {
                if(this.responseText.slice(0, 5) == 'Error') {
                    //there was an error, split the response text into an array to find out what the error was
                    var errorArray = this.responseText.split('|');
                    var errorType = errorArray[1];
                    //now highlight the error field and show a message corresponding to the error type
                    var nameClass = '';
                    var passClass = '';
                    if(errorType == 'user_pass_invalid') {
                        var errorMessage = 'The username or password you entered is incorrect.';
                        nameClass = 'error';
                        passClass = 'error';
                    } else if(errorType == 'zero_priv') {
                        var errorMessage = 'Your account has been suspended.';
                    } else {
                        var errorMessage = 'An unknown error occurred.';
                    }
                    form.elements["name"].className = nameClass;
                    form.elements["pass"].className = passClass;
                    document.getElementById('err_msg').innerHTML = errorMessage;
                } else {
                    //the login was successful so we need to update the navbar and menu
                    htmlText = this.responseText;
                    document.getElementById('navbar_lr').innerHTML = htmlText;
                    document.getElementById('menu_lr').innerHTML = htmlText;
                    //now hide the overlay
                    overlayHide();
                    renderLoggedInContent();
                    popupMessage('You are now logged in. Click the \'My Account\' link in the menu to view your account page.');
                }
            }
            if(this.status == 403) {
                document.getElementById('err_msg').innerHTML = 'The request to the server is being blocked.';
            }
        }
        xhr.send('name=' + form.elements["name"].value + '&pass=' + form.elements["pass"].value);
        return false;
    }

    //submit the register form via ajax
    function registerFormNext()
    {
        alert('Session token: ' + sessionToken);
        var emailVal = document.getElementById('email').value;
        var nameVal = document.getElementById('new_name').value;
        //write the second step of the registration form
        var htmlText = '<br /><p>Register an account to create your own video channels, upload your videos, comment on videos and more.</p>';
        htmlText += '<p id="err_msg" class="error"><br /></p>';
        htmlText += '<form id="register_form" method="post" onsubmit="submitRegisterForm();return false;">';
        htmlText += 'Choose a password:<br /><input type="password" name="new_pass" placeholder="Password (at least 11 characters)" required />';
        htmlText += '<br />Retype the password:<br /><input type="password" name="confirm_pass" placeholder="Confirm Password" required />';
        htmlText += '<input type="hidden" name="new_name" value="' + nameVal + '" />';
        htmlText += '<input type="hidden" name="email" value="' + emailVal + '" />';
        htmlText += '<input type="hidden" name="token" value="' + sessionToken + '" />'
        htmlText += '<br /><br /><input type="submit" value="Register" /></form>';
        overlayContent(htmlText);
    }

    //submit the register form via ajax
    function submitRegisterForm()
    {
        var form = document.getElementById('register_form');
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/api/register', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (this.status == 200) {
                if(this.responseText != 'OK') {
                    //there was an error, split the response text into an array to find out what the error was
                    var errorArray = this.responseText.split('|');
                    var errorType = errorArray[1];
                    //now highlight the error field and show a message corresponding to the error type
                    var passClass = '';
                    var confirmClass = '';
                    switch(errorType) {
                        case 'logged_in':
                            //this shouldn't happen, but just in case
                            var errorMessage = 'You are already logged in.';
                            break;
                        case 'pass_short':
                            passClass = 'error';
                            form.elements["new_pass"].focus();
                            var errorMessage = 'That password is too short.';
                            break;
                        case 'pass_invalid':
                            passClass = 'error';
                            form.elements["new_pass"].focus();
                            var errorMessage = 'That password is invalid.';
                            break;
                        case 'pass_silly':
                            passClass = 'error';
                            form.elements["new_pass"].focus();
                            var errorMessage = 'You can\'t have "password" as your password you fool.';
                            break;
                        case 'token':
                            var errorMessage = 'There was a problem with your request. Please try again later.';
                            break;
                        case 'pass_mismatch':
                            passClass = 'error';
                            confirmClass = 'error';
                            form.elements["confirm_pass"].focus();
                            var errorMessage = 'The passwords you entered don\'t match.';
                            break;
                        default:
                            var errorMessage = 'An unknown error occurred. Please try again later. ' + errorType;
                            break;
                    }
                    document.getElementById('err_msg').innerHTML = errorMessage;
                    form.elements["new_pass"].className = passClass;
                    form.elements["confirm_pass"].className = confirmClass;
                } else {
                    //the registration was successful
                    document.getElementById('err_msg').innerHTML = '<br />';
                    form.innerHTML = '<p>Great, you have registered!</p><br /><p><a href="#" onclick="loginFormInit();return false;">Click here to login.</a></p>';
                }
            }
        }
        xhr.send('new_name=' + form.elements["new_name"].value + '&token=' + form.elements["token"].value + '&new_pass=' + form.elements["new_pass"].value + '&confirm_pass=' + form.elements["confirm_pass"].value + '&email=' + form.elements["email"].value);
        return false;
    }

    //log the user out via ajax
    function logoutUser()
    {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/api/logout', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            //if successful it will return the html for the login and register buttons to place in the navbar and menu
            if (this.status == 200) {
                htmlText = this.responseText;
                document.getElementById('navbar_lr').innerHTML = htmlText;
                document.getElementById('menu_lr').innerHTML = htmlText;
                renderLoggedInContent();
                popupMessage('You are now logged out.');
            }
        }
        xhr.send();
        return false;
    }

    //this must be called every time the user logs in or out on a page that has a div with id 'loggedin-content'
    function renderLoggedInContent()
    {
        if(!loggedInContentDiv) {
            return;
        }
        //we have a div to render the content in, so let's do it
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/api/usercontent', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            //if successful it will return the html for logged in content
            if (this.status == 200) {
                htmlText = this.responseText;
                loggedInContentDiv.innerHTML = htmlText;
            }
        }
        xhr.send('template=' + accountTemplate);
    }

    function uploadVideoInit()
    {
        var htmlText = '<br /><p>Upload a video from your device here. I will then convert it to a format that can be played in the browser.';
        htmlText += '<br />Depending on the size of the video you upload this may take some time so please bear with me!</p>';
        htmlText += '<form id="upload_form" method="post" enctype="multipart/form-data" onsubmit="uploadVideo();return false;">';
        htmlText += '<br /><br /><input type="file" name="video_file" id="video_file" accept="video/*" />';
        htmlText += '<p><input type="submit" value="Upload" /></p>';
        htmlText += '</form>';
        overlayContent(htmlText);
    }

    function uploadVideo()
    {
        const video_file = document.getElementById("video_file").files[0];
        if(!video_file) {
            popupError('You must select a video file to upload.');
            return false;
        }
        if(video_file.size > 524288000) {
            popupError('The video file you selected is too large. The maximum file size is 500MB.');
            return false;
        }
        var htmlText = '<br /><p>Uploading video...</p>';
        htmlText += '<p><progress id="upload_progress" value="0" max="100"></progress></p>';
        overlayContent(htmlText);

        const formData = new FormData();
        formData.append('video_file', video_file);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/api/uploadvideo', true);
        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                var percentComplete = (e.loaded / e.total) * 100;
                document.getElementById('upload_progress').value = percentComplete;
                if(e.loaded == e.total) {
                    var htmlText = '<br /><p>Uploading video...</p>';
                    htmlText += '<p><progress id="upload_progress" value="100" max="100"></progress></p>';
                    htmlText += '<br /><p>I\'m just checking your video for any errors...</p><img src="/images/anim/loading.gif" alt="" />';
                    overlayContent(htmlText);
                }
            }
        };
        xhr.onload = function() {
            if (this.status == 200) {
                var responseArray = this.responseText.split('|');
                if(responseArray[0] == 'Error') {
                    //there was an error, split the response text into an array to find out what the error was
                    var errorType = responseArray[1];
                    switch(errorType) {
                        case 'mimetype':
                            var errorMessage = 'This file does not seem to be a video.';
                            break;
                        case 'not_logged_in':
                            var errorMessage = 'You were logged out before the file was uploaded.';
                            break;
                        case 'max_file_size':
                            var errorMessage = 'The video file you selected is too large. The maximum file size is 500MB.';
                            break;
                        case 'no_file':
                            var errorMessage = 'There was no file uploaded.';
                            break;
                        case 'incomplete':
                            var errorMessage = 'The upload failed to complete.';
                            break;
                        case 'move_uploaded_file':
                            var errorMessage = 'There was an error moving the uploaded file.';
                            break;
                        case 'tmp_name':
                            var errorMessage = 'The file did not match the form data.';
                            break;
                        case 'corrupt':
                            var errorMessage = 'The file you uploaded has errors and cannot be used.';
                            break;
                        case 'probe':
                            var errorMessage = 'The file you uploaded is missing data and cannot be used.';
                            break;
                        case 'import':
                            var errorMessage = 'Sorry, there was an error importing the video. Please try again.';
                            break;
                        default:
                            var errorMessage = 'There was an undefined error - ' + errorType;
                            break;
                    }
                    var htmlText = '<br /><p>Uploading video...</p>';
                    htmlText += '<p><progress id="upload_progress" value="100" max="100"></progress></p>';
                    htmlText += '<p id="err_msg" class="error">' + errorMessage + '</p>';
                    overlayContent(htmlText);
                } else {
                    //the upload was successful, so let's get the post_id and start the encoding process
                    var v = responseArray[1];
                    overlayContent('<br /><p>Everything seems to be okay. I\'m now going to start encoding your video.</p><img src="/images/anim/loading.gif" alt="" />');
                    renderLoggedInContent();
                    watchVideoStatus(v);
                }
            }
        }
        xhr.send(formData);
    }

    function watchVideoStatus(videoID)
    {
        displayState = '';
        overlayContent('<br /><p>Checking progress...</p><img src="/images/anim/loading.gif" alt="" />');
        intervalID = setInterval(function() {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/api/videostatus', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if(this.readyState === XMLHttpRequest.DONE && this.status === 200) {
                    var responseArray = this.responseText.split('|');
                    if(responseArray[0] == "Error") {
                        var errorType = responseArray[1];
                        switch(errorType) {
                            case 'not_logged_in':
                                var errorMessage = 'You were logged out before the video was processed.';
                                break;
                            case 'video_id':
                                var errorMessage = 'There was no video ID. ' + videoID;
                                break;
                            case 'no_video':
                                var errorMessage = 'Video does not exist.';
                                break;
                            case 'invalid_user':
                                var errorMessage = 'You do not have permission to edit this video.';
                                break;
                            default:
                                var errorMessage = 'There was an undefined error - ' + errorType;
                                break;
                            }
                        overlayContent('<br /><p>There was an error processing your video.</p><p id="err_msg" class="error">' + errorMessage + '</p>');
                        overlayHalt();
                        return;
                    }
                    var transcodeType = responseArray[2];
                    var progress = responseArray[1];
                    var processState = responseArray[0];
                    //if transcode type is not none but process state is not transcoding or processing, it means transcoding hasn't had a chance to start yet
                    if(responseArray[0] == 'transcoded' && transcodeType == 'none') {
                        overlayHalt();
                        renderLoggedInContent();
                        videoPostInit(videoID, true);
                        return;
                    }
                    if(responseArray[0] == 'processed' && transcodeType == 'none') {
                        overlayHalt();
                        renderLoggedInContent();
                        videoMakeSprite(videoID, true);
                        return;
                    }
                    if(responseArray[0] == 'pending') {
                        if(displayState != 'pending') {
                            overlayContent('<br /><p>I\'m now going to start encoding your video.</p><img src="/images/anim/loading.gif" alt="" /><p>If nothing seems to be happening after some time, something may have gone wrong.</p>');
                            displayState = 'pending';
                        }
                        return;
                    }
                    if(processState == 'transcoding' || processState == 'processing') {
                        var seconds_left = responseArray[3];
                        var seconds_elapsed = responseArray[4];
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
                        var videoType = transcodeType;
                        if(transcodeType == 'low') {
                            videoType = 'low res version';
                        } else if(transcodeType == '180p') {
                            videoType = 'preview clip';
                        } else {
                            videoType += ' version';
                        }
                        //update the overlay with the progress

                        if(displayState != 'transcoding') {
                            displayState = 'transcoding';
                            var htmlText = '<br /><p id="trans_type"></p><p id="trans_time"></p><p id="trans_progress"></p>';
                            if(transcodeType != '180p') {
                                htmlText += '<p>This may take a few minutes. You do not have to wait on this page, you can close the page and I will carry on without you.';
                                htmlText += ' Just come back to your profile page in a while and click the yellow button to see how I\'m doing.</p>';
                            }
                            overlayContent(htmlText);
                        }
                        document.getElementById('trans_type').innerHTML = 'I am now making a ' + videoType + ' of your video.';
                        document.getElementById('trans_time').innerHTML = 'Elapsed time: ' + eminutes + ':' + eseconds + ', time remaining: ' + minutes + ':' + seconds;
                        document.getElementById('trans_progress').innerHTML = '<progress value="' + progress + '" max="100"></progress>';
                        return;
                    }
                }
            }
            xhr.send('video_id=' + videoID);
        }, 500);
    }

    function videoPostInit(videoID, continuing = false)
    {
        var htmlText = '<br />';
        if(continuing) {
            htmlText += '<p>I have finished encoding your video! Let\'s post it so everyone can see it.</p>';
        } else {
            htmlText += '<p>Okay let\'s get started.</p>'
        }
        htmlText += '<p>First let\'s get a snapshot from your video to make a thumbnail pic.</p>';
        htmlText += '<p>On the next page I will show you your video. Use the "Play/Pause", "Back" and "Forward" buttons to play through the video and find the best frame, then hit the "Save Snapshot" button.</p>';
        htmlText += '<p>Ready?</p>';
        htmlText += '<button class="large green" onclick="videoPostSnapshot(' + videoID + ');">Yes, let\'s go!</button>';
        overlayContent(htmlText);
        return;
    }

    function videoPostSnapshot(videoID)
    {
        videoObj.videoID = videoID;
        //now we need to do an ajax call to find the url of the highest resolution video to get the poster image from
        const xhr = new XMLHttpRequest();
        xhr.open("POST", '/api/videodata');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            var responseArray = this.responseText.split('|');
            if(responseArray[0] == "Error") {
                var errorType = responseArray[1];
                switch(errorType) {
                    case 'not_logged_in':
                        var errorMessage = 'You have been logged out. Be quicker next time.';
                        break;
                    case 'video_id':
                        var errorMessage = 'There was no video ID. ' + videoID;
                        break;
                    case 'no_video':
                        var errorMessage = 'Video does not exist.';
                        break;
                    case 'invalid_user':
                        var errorMessage = 'You do not have permission to edit this video.';
                        break;
                    default:
                        var errorMessage = 'There was an undefined error - ' + errorType;
                        break;
                    }
                overlayContent('<br /><p>There was an error getting the video data.</p><p id="err_msg" class="error">' + errorMessage + '</p>');
                return;
            }
            videoObj.videoURL = responseArray[1];
            videoObj.videoFPS = responseArray[2];
            videoPreviewShow();
        }
        xhr.send('video_id=' + videoID);
    }

    function videoPreviewShow()
    {
        var htmlText = '<br /><div id="videowrapper">';
        htmlText += '</div>';
        htmlText += '<button type="button" id="play_button" onclick="videoPlayButton();" class="green">Play</button> ';
        htmlText += '<button type="button" onclick="video.pause(); video.currentTime -= ' + (1.0/videoObj.videoFPS) + ';" class="lightblue">Back</button> ';
        htmlText += '<button type="button" onclick="video.pause(); video.currentTime += ' + (1.0/videoObj.videoFPS) + ';" class="lightblue">Forward</button>&nbsp;&nbsp;';
        htmlText += '<button type="button" onclick="videoPreviewSave();" class="red">Save Snapshot</button>';

        overlayContent(htmlText);
        video = document.createElement('video');
        video.src = videoObj.videoURL;
        video.width = 1024;
        video.height = 576;
        video.controls = true;
        video.playsinline = true;
        video.disablePictureInPicture = true;	//note that in Edge this is case sensitive
        video.currentTime = videoObj.videoTime;
        var videoEl = document.getElementById('videowrapper');
        videoEl.appendChild(video);
        return;
    }

    function videoPlayButton()
    {
        if(video.paused) {
            video.play();
            document.getElementById('play_button').innerHTML = 'Pause';
            document.getElementById('play_button').className = 'orange';
        } else {
            video.pause();
            document.getElementById('play_button').innerHTML = 'Play';
            document.getElementById('play_button').className = 'green';
        }
    }

    //Get the current video time and send it via ajax to a function that will save the preview image
    function videoPreviewSave()
    {
        videoObj.videoTime = video.currentTime;
        overlayContent('<br /><p>Saving preview image</p><img src="/images/anim/loading.gif" alt="" />');
        const xhr = new XMLHttpRequest();
        xhr.open("POST", '/api/videosavepreview');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            var responseArray = this.responseText.split('|');
            if(responseArray[0] == "Error") {
                var errorType = responseArray[1];
                switch(errorType) {
                    case 'not_logged_in':
                        var errorMessage = 'You have been logged out. Be quicker next time.';
                        break;
                    case 'video_id':
                        var errorMessage = 'There was no video ID. ' + videoObj.videoID;
                        break;
                    case 'no_video':
                        var errorMessage = 'Video does not exist.';
                        break;
                    case 'no_time':
                        var errorMessage = 'There was no time value.';
                        break;
                    case 'invalid_user':
                        var errorMessage = 'You do not have permission to edit this video.';
                        break;
                    default:
                        var errorMessage = 'There was an undefined error - ' + errorType;
                        break;
                    }
                overlayContent('<br /><p>There was an error saving the image.</p><p id="err_msg" class="error">' + errorMessage + '</p>');
            }
            var posterURL = responseArray[1];
            //to defeat caching, we add a random number to the end of the image url
            posterURL += "?r=" + Math.random();
            var htmlText = '<p>Here is your preview pic. Happy with it?</p>';
            htmlText += '<div id="videowrapper"><img src="' + posterURL + '" alt="" /></div>';
            htmlText += '<button type="button" onclick="videoPreviewShow();" class="purple">Don\'t like it, go back</button> ';
            htmlText += '<button type="button" onclick="videoRecordClip();" class="green">Yes I like it, let\'s go!</button>';
            overlayContent(htmlText);
        }
        xhr.send("video_id=" + videoObj.videoID + "&time=" + videoObj.videoTime);
    }

    function videoRecordClip()
    {
        overlayContent('<p>Recording clip</p><img src="/images/anim/loading.gif" alt="" />');
        const xhr = new XMLHttpRequest();
        xhr.open("POST", '/api/videorecordclip');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            var responseArray = this.responseText.split('|');
            if(responseArray[0] == "Error") {
                var errorType = responseArray[1];
                switch(errorType) {
                    case 'not_logged_in':
                        var errorMessage = 'You have been logged out. Be quicker next time.';
                        break;
                    case 'video_id':
                        var errorMessage = 'There was no video ID. ' + videoObj.videoID;
                        break;
                    case 'no_video':
                        var errorMessage = 'Video does not exist.';
                        break;
                    case 'invalid_user':
                        var errorMessage = 'You do not have permission to edit this video.';
                        break;
                    default:
                        var errorMessage = 'There was an undefined error - ' + errorType;
                        break;
                    }
                overlayContent('<br /><p>There was an error making the preview clip.</p><p id="err_msg" class="error">' + errorMessage + '</p>');
                return;
            } else {
                watchVideoStatus(videoObj.videoID);
            }
        }
        xhr.send('video_id=' + videoObj.videoID + '&time=' + videoObj.videoTime);
    }

    function videoMakeSprite(videoID, continuing = false)
    {
        videoObj.videoID = videoID;
        var htmlText = '<br />';
        if(continuing) {
            htmlText += '<p>I have finished making the preview clip, now I just need to make a couple more files. Bear with me a moment.</p>';
        } else {
            htmlText += '<p>I just need to make a couple more files. Bear with me a moment.</p>'
        }
        htmlText += '<img src="/images/anim/loading.gif" alt="" />';
        overlayContent(htmlText);
        const xhr = new XMLHttpRequest();
        xhr.open("POST", '/api/videomakesprite');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            var responseArray = this.responseText.split('|');
            if(responseArray[0] == "Error") {
                var errorType = responseArray[1];
                switch(errorType) {
                    case 'not_logged_in':
                        var errorMessage = 'You have been logged out. Be quicker next time.';
                        break;
                    case 'video_id':
                        var errorMessage = 'There was no video ID. ' + videoObj.videoID;
                        break;
                    case 'no_video':
                        var errorMessage = 'Video does not exist.';
                        break;
                    case 'invalid_user':
                        var errorMessage = 'You do not have permission to edit this video.';
                        break;
                    default:
                        var errorMessage = 'There was an undefined error - ' + errorType;
                        break;
                    }
                overlayContent('<br /><p>There was an error making the sprite image.</p><p id="err_msg" class="error">' + errorMessage + '</p>');
                return;
            }
            videoObj.videoTime = 0;
            renderLoggedInContent();
            videoPostEdit(videoID, true);
        }
        xhr.send('video_id=' + videoID);
    }

    function videoPostEdit(videoID, continuing = false)
    {
        videoObj.videoID = videoID;
        if(continuing) {
            var htmlText = '<br /><p>I have finshed making all the files I need. Now let\'s add a title and maybe a description.</p>';
        } else {
            var htmlText = '<br /><p>All we need to do to post your video is add a title and maybe a description.</p>';
        }
        htmlText += '<form id="video_post_form" method="post" onsubmit="videoPostEditNext(); return false;">';
        htmlText += 'Add a title - up to 90 characters (do not put hashtags here).<br />';
        htmlText += '<input type="text" placeholder="Video title" name="video_title" id="video_title" maxlength="90" /><br />';
        htmlText += '<button type="submit" class="large green">Next</button>';
        overlayContent(htmlText);
    }

    //has to be done in two steps, won't fit on one overlay on mobile
    function videoPostEditNext()
    {
        var postTitle = document.getElementById('video_title').value;
        var htmlText = '<form id="video_post_form" method="post" onsubmit="videoPostEditSubmit(); return false;">';
        htmlText += '<br />Add a description. This is optional but it is better if you add one. You can add hashtags in here,';
        htmlText += ' e.g. #music #movietrailer #funnycats etc, just separate with spaces.<br />';
        htmlText += '<textarea name="video_description" id="video_description" rows="4" placeholder="Description" ></textarea><br />';
        htmlText += 'You can also add hashtags here if you don\'t want them to appear in the description.<br />';
        htmlText += '<input type="text" name="video_hashtags" placeholder="Hidden hashtags" id="video_hashtags" maxlength="100" /><br />';
        htmlText += '<input type="hidden" name="video_title" id="video_title" value="' + postTitle + '" />';
        htmlText += '<button type="submit" class="large green">Post My Video!</button>';
        overlayContent(htmlText);
    }

    function videoPostEditSubmit()
    {
        var postTitle = document.getElementById('video_title').value;
        var postDescription = document.getElementById('video_description').value;
        var postHashtags = document.getElementById('video_hashtags').value;

        //save the form contents for later
        videoObj.title = postTitle;
        videoObj.description = postDescription;
        videoObj.hashtags = postHashtags;
        //this will be a new post so set the post ID to zero
        videoObj.postID = 0;
        videoObj.token = sessionToken;

        //now we need to find out how many channels the user has and either let them create one, use the one existing one or let them choose one.
        const xhr = new XMLHttpRequest();
        xhr.open("POST", '/api/videochannels');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            var responseArray = this.responseText.split('|');
            if(responseArray[0] == "Error") {
                var errorType = responseArray[1];
                switch(errorType) {
                    case 'not_logged_in':
                        var errorMessage = 'You have been logged out. Be quicker next time.';
                        break;
                    default:
                        var errorMessage = 'There was an undefined error - ' + errorType;
                        break;
                    }
                overlayContent('<br /><p>There was an error getting your channels.</p><p id="err_msg" class="error">' + errorMessage + '</p>');
                return;
            }
            var channelArray = JSON.parse(responseArray[1]);
            if(channelArray.length == 0) {
                //no channels, let's create one
                channelCreate(videoObj.videoID);
            } else if(channelArray.length == 1) {
                //only one channel, let's use it
                videoObj.channelID = channelArray[0].channel_id;
                videoPostEditSubmitFinal();
            } else {
                //more than one channel, let's let the user choose
                videoPostChooseChannel(channelArray);
            }
        }
        xhr.send('video_id=' + videoObj.videoID);
    }

    //calling this with a video ID means we are continuing from a previous step, otherwise the user clicked a button to get here
    function channelCreate(videoID = 0)
    {
        if(videoID) {
            var htmlText = '<br /><p>I noticed that you don\'t have any channels yet. Let\'s create one to put your video in.</p>';
        } else {
            var htmlText = '<br /><p>Create a new channel for your videos.</p>';
        }
        htmlText += '<form id="video_post_form" method="post" onsubmit="channelCreateSubmit(); return false;">';
        htmlText += '<p>What is your channel going to be called?</p>';
        htmlText += '<p id="err_msg" class="error">&nbsp;</p>';
        htmlText += '<input type="text" placeholder="Channel name" name="channel_name" id="channel_name" oninput="channelNameCheck();" maxlength="50" /><br />';
        htmlText += '<input type="hidden" name="video_id" id="video_id" value="' + videoID + '" />';
        htmlText += '<input type="hidden" name="channel_id" id="channel_id" value="0" />';
        htmlText += '<input type="submit" id="channel_submit" value="Create My Channel" class="large" />';
        overlayContent(htmlText);
    }

    function channelNameCheck()
    {
        var channelName = document.getElementById('channel_name').value;
        var channelID = document.getElementById('channel_id').value;
        if(channelName.length > 0) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", '/api/channelname');
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if(this.responseText == "OK") {
                    document.getElementById('err_msg').innerHTML = '&nbsp;';
                    document.getElementById('channel_submit').disabled = false;
                } else if(this.responseText == "NotOK") {
                    document.getElementById('err_msg').innerHTML = 'That channel name is already taken.';
                    document.getElementById('channel_submit').disabled = true;
                }
            }
            xhr.send('channel_name=' + channelName + '&channel_id=' + channelID);
        } else {
            document.getElementById('err_msg').innerHTML = '&nbsp;';
            document.getElementById('channel_submit').disabled = false;
        }
    }

    function channelCreateSubmit()
    {
        var channelName = document.getElementById('channel_name').value;
        var channelID = document.getElementById('channel_id').value;
        var videoID = document.getElementById('video_id').value;
        const xhr = new XMLHttpRequest();
        xhr.open("POST", '/api/editchannel');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            var responseArray = this.responseText.split('|');
            if(responseArray[0] != "OK") {
                var errorType = responseArray[1];
                switch(errorType) {
                    case 'not_logged_in':
                        var errorMessage = 'You have been logged out. Be quicker next time.';
                        break;
                    case 'channel_name':
                        var errorMessage = 'You need to give your channel a name.';
                        break;
                    case 'channel_id':
                        var errorMessage = 'No channel ID.';
                        break;
                    case 'channel_exists':
                        var errorMessage = 'There is already another channel with that name.';
                        break;
                    case 'no_channel':
                        var errorMessage = 'Channel does not exist.';
                        break;
                    case 'invalid_user':
                        var errorMessage = 'You are not the owner of this channel.';
                        break;
                    default:
                        var errorMessage = 'There was an undefined error - ' + errorType;
                        break;
                    }
                overlayContent('<br /><p>There was an error creating your channel.</p><p id="err_msg" class="error">' + errorMessage + '</p>');
                return;
            }
            if(videoID != 0) {
                channelID = responseArray[1];
                videoObj.channelID = channelID;
                videoPostEditSubmitFinal();
            } else {
                renderLoggedInContent();
                overlayHide();
                popupMessage('Your channel \'' + channelName + '\' has been created.');
            }
        }
        xhr.send('channel_name=' + encodeURIComponent(channelName) + '&channel_id=' + channelID + '&token=' + sessionToken);
    }

    function videoPostChooseChannel(channelArray)
    {
        var htmlText = '<br /><p>Now just choose the channel to put your video in.</p>';
        htmlText += '<form id="video_post_form" method="post" onsubmit="videoPostChannelSubmit(); return false;">';
        htmlText += '<select name="channel_id" id="channel_id">';
        for(var i = 0; i < channelArray.length; i++) {
            htmlText += '<option value="' + channelArray[i].channel_id + '">' + channelArray[i].channel_name + '</option>';
        }
        htmlText += '</select><br />';
        htmlText += '<input type="hidden" name="token" value="' + sessionToken + '" />'
        htmlText += '<button type="submit" class="large green">Use Channel</button>';
        overlayContent(htmlText);
    }

    function videoPostChannelSubmit()
    {
        var channelID = document.getElementById('channel_id').value;
        videoObj.channelID = channelID;
        videoPostEditSubmitFinal();
    }

    //this needs to be called with the videoObj set up
    function videoPostEditSubmitFinal()
    {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", '/api/videopostsave');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            var responseArray = this.responseText.split('|');
            if(responseArray[0] != "OK") {
                var errorType = responseArray[1];
                switch(errorType) {
                    case 'not_logged_in':
                        var errorMessage = 'You have been logged out. Be quicker next time.';
                        break;
                    case 'video_id':
                        var errorMessage = 'There was no video ID. ' + videoObj.videoID;
                        break;
                    case 'post_id':
                        var errorMessage = 'There was no post ID.';
                        break;
                    case 'no_video':
                        var errorMessage = 'Video does not exist.';
                        break;
                    case 'no_post':
                        var errorMessage = 'Post does not exist.';
                        break;
                    case 'invalid_user':
                        var errorMessage = 'You do not own this video.';
                        break;
                    default:
                        var errorMessage = 'There was an undefined error - ' + this.responseText;
                        break;
                    }
                overlayContent('<br /><p>There was an error posting your video.</p><p id="err_msg" class="error">' + errorMessage + '</p>');
                return;
            }
            renderLoggedInContent();
            overlayHide();
            popupMessage('Your video is now live!');
        }
        objData = JSON.stringify(videoObj);
        xhr.send('x=' + objData);
    }

    function deleteVideo(videoID)
    {
        var htmlText = '<br /><p>This will delete the video and all its associated files permanently and cannot be undone.</p><p>Are you sure you want to do this?</p>';
        htmlText += '<br /><button type="button" class="large red" onclick="deleteVideoConfirm(' + videoID + ');">Yes, Delete it!</button> ';
        htmlText += '<button type="button" class="large" onclick="overlayHide();">Cancel</button>';
        overlayContent(htmlText);
    }

    function deleteVideoConfirm(videoID)
    {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", '/api/videodelete');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            var responseArray = this.responseText.split('|');
            if(responseArray[0] != "OK") {
                var errorType = responseArray[1];
                switch(errorType) {
                    case 'not_logged_in':
                        var errorMessage = 'You have been logged out. Be quicker next time.';
                        break;
                    case 'video_id':
                        var errorMessage = 'There was no video ID.';
                        break;
                    case 'no_video':
                        var errorMessage = 'Video does not exist.';
                        break;
                    case 'invalid_user':
                        var errorMessage = 'You do not own this video.';
                        break;
                    default:
                        var errorMessage = 'There was an undefined error - ' + this.responseText;
                        break;
                    }
                overlayContent('<br /><p>There was an error deleting your video.</p><p id="err_msg" class="error">' + errorMessage + '</p>');
                return;
            }
            renderLoggedInContent();
            overlayHide();
            popupMessage('Your video has been deleted.');
        }
        xhr.send('video_id=' + videoID);
    }
 
    function hideVideo(postID)
    {
        var htmlText = '<br /><p>Hiding your video means no-one will be able to see it and it will not appear on any of the normal pages. ';
        htmlText += 'It does not change the video itself so you can unhide it again later and it will reappear.</p>';
        htmlText += '<p>Do you want to hide it now?</p>';
        htmlText += '<br /><button type="button" class="large purple" onclick="hideVideoConfirm(' + postID + ');">Yes, Hide it!</button> ';
        htmlText += '<button type="button" class="large" onclick="overlayHide();">Cancel</button>';
        overlayContent(htmlText);
    }

    function hideVideoConfirm(postID)
    {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", '/api/videohide');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            var responseArray = this.responseText.split('|');
            if(responseArray[0] != "OK") {
                var errorType = responseArray[1];
                switch(errorType) {
                    case 'not_logged_in':
                        var errorMessage = 'You have been logged out. Be quicker next time.';
                        break;
                    case 'post_id':
                        var errorMessage = 'There was no video ID.';
                        break;
                    case 'no_post':
                        var errorMessage = 'Video does not exist.';
                        break;
                    case 'invalid_user':
                        var errorMessage = 'You do not own this video.';
                        break;
                    default:
                        var errorMessage = 'There was an undefined error - ' + this.responseText;
                        break;
                    }
                overlayContent('<br /><p>There was an error hiding your video.</p><p id="err_msg" class="error">' + errorMessage + '</p>');
                return;
            }
            renderLoggedInContent();
            overlayHide();
            popupMessage('Your video has been hidden.');
        }
        xhr.send('post_id=' + postID);
    }

    function unhideVideo(postID)
    {
        const xhr = new XMLHttpRequest();
        xhr.open("POST", '/api/videounhide');
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            var responseArray = this.responseText.split('|');
            if(responseArray[0] != "OK") {
                var errorType = responseArray[1];
                switch(errorType) {
                    case 'not_logged_in':
                        var errorMessage = 'You have been logged out. Be quicker next time.';
                        break;
                    case 'post_id':
                        var errorMessage = 'There was no video ID.';
                        break;
                    case 'no_post':
                        var errorMessage = 'Video does not exist.';
                        break;
                    case 'invalid_user':
                        var errorMessage = 'You do not own this video.';
                        break;
                    default:
                        var errorMessage = 'There was an undefined error - ' + this.responseText;
                        break;
                    }
                overlayContent('<br /><p>There was an error unhiding your video.</p><p id="err_msg" class="error">' + errorMessage + '</p>');
                return;
            }
            renderLoggedInContent();
            overlayHide();
            popupMessage('Your video is now live again.');
        }
        xhr.send('post_id=' + postID);
    }

    if(includeHistory) {
        //now let's add the post_id to the history together with the current timestamp
        //if the post_id is already in the history, we'll just update the timestamp
        //we'll use the post_id as the key and the timestamp as the value
        var post_ids = JSON.parse(localStorage.getItem("post_ids"));
        if(post_ids == null) {
            post_ids = {};
        }
        post_ids[post_id] = Math.floor(Date.now() / 1000);
        localStorage.setItem("post_ids", JSON.stringify(post_ids));
    }
