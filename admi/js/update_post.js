
var inputs;
var lines;
var linesIx;
const siteObj = {
    siteName : '',
    siteURL : '',
    scrapeURL : '',
    pageName : '',
    postID : 0
    };

const reviewURLs = [
                    'https://www.rabbitsreviews.com/porn/reviews/##',
                    'https://www.thebestporn.com/review/##/',
                    'https://www.adultreviews.com/review/videos/##.html',
                    'https://x3guide.com/reviews/##',
                    'https://www.honestpornreviews.com/##/Review.cfm'
                    ];
var urlsIndex = 0;
var resultsText = '';

    function fetchIconInit(postID, siteURL, siteTitle)
    {
        modalWindowShow(false);

        let htmltext = '<form><p>Try to fetch the favicon from:</p><br /><input type="text" value="';
        htmltext += siteURL;
        htmltext += '" size="64" maxlength="255" /><br />';
        htmltext += '<br /><button type="button" onclick="fetchIconGet();return false;">Start</button></p>';
        htmltext += '</form>';

        siteObj.postID = postID;
        siteObj.siteName = siteTitle;

        divs[0].innerHTML = htmltext;
    }

    function fetchIconGet()
    {
        inputs = modal.getElementsByTagName('input');
        siteObj.siteURL = inputs[0].value;
        divs[0].innerHTML = '<img src="/images/anim/loading.gif" alt="" />';
        const objData = JSON.stringify(siteObj);
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function()
        {
            if(this.responseText.slice(0, 5) == "Error")
            {
                divs[0].innerHTML = '<p>' + this.responseText + '.</p>';
            }
            else
            {
                fetchIconResult(this.responseText);
            }
        }
        req = "/admi/ajax.php?a=fetch_icon&type=apis";
        xhttp.open("POST", req);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("x=" + objData);
        return false;
    }

    function fetchIconResult(responseText)
    {
        divs[0].innerHTML = '<p>Found icon ' + responseText + '. Updating table.</p>';
        var table = document.getElementById("posts");
        for (var i = 0, row; row = table.rows[i]; i++)
        {
            var cell = row.cells[1];
            var id = cell.innerHTML;
            if(id == siteObj.postID)
            {
                var siteInfo = row.cells[6].innerHTML;
                var p = siteInfo.indexOf('<a');
                siteInfo = siteInfo.slice(0, p);
                siteInfo += '<img src="' + baseURL + responseText + '" width="16" height="16" alt="" />';
                row.cells[6].innerHTML = siteInfo;
                break;
            }
        }
        divs[0].innerHTML += '<p>Done.</p>';
        return false;
    }

    function reviewsScraperInit(pageName, postID)
    {
        modalWindowShow(false);

        siteObj.postID = postID;
        resultsText = '';
        urlsIndex = 0;
        let siteURL = reviewURLs[0];
        let urlSplit = siteURL.split('##');

        var htmltext = '<form><p>Try to fetch review score from:<br />' + urlSplit[0] + '<input type="text" value="' + pageName + '" size="24" maxlength="255" />' + urlSplit[1];
        htmltext += '<br /><br /><button type="button" onclick="reviewsScraperStart();return false;">Start</button></p>';
        htmltext += '</form>';
        divs[0].innerHTML = htmltext;

    }

    function reviewsScraperStart()
    {
        inputs = modal.getElementsByTagName('input');
        siteObj.pageName = inputs[0].value;
        reviewsScraperGet();
    }

    function reviewsScraperGet()
    {
        if(urlsIndex == reviewURLs.length)
        {
            var table = document.getElementById("posts");
            for (var i = 0, row; row = table.rows[i]; i++)
            {
                var cell = row.cells[1];
                var id = cell.innerHTML;
                if(id == siteObj.postID)
                {
                    var siteInfo = row.cells[7].innerHTML;
                    siteInfo = siteInfo.replace("Get Review", "Update Review");
                    row.cells[7].innerHTML = siteInfo;
                    break;
                }
            }
            divs[0].innerHTML = resultsText + "<br /><br />Done.";
            return;
        }
        siteObj.siteURL = reviewURLs[urlsIndex];
        divs[0].innerHTML = resultsText + '<img src="/images/anim/loading.gif" alt="" />';
        const objData = JSON.stringify(siteObj);
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function()
        {
            resultsText += this.responseText + "<br />";
            urlsIndex++;
            reviewsScraperGet();
        }
        req = "/admi/ajax.php?a=scrape_review&type=apis";
        xhttp.open("POST", req);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("x=" + objData);
        return false;
    }
