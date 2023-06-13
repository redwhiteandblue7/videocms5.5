
    var overlay;
    var columntype = 1;
    var startdays = 0;
    var gNum = 0;
    gList = new Array('range', 'lasthour', '1hour', '2hours', 'yesterday', 'all');

    function modalWindowInit(ajaxRequest)
    {
        if(!overlay)
        {
            overlay = document.getElementById('modal_window');
            divs = overlay.getElementsByTagName('div');
        }
		overlay.style.display = 'block';
        divs[0].innerHTML = '<img src="/images/anim/loading.gif" alt="" />';
        req = "/admi/api.php?" + ajaxRequest;
        xhttp = new XMLHttpRequest();
        xhttp.onload = function()
        {
            if(this.responseText == 'Error')
            {
                divs[0].innerHTML = '<p>Something went wrong.</p>';
            }
            else
            {
                divs[0].innerHTML = this.responseText;
            }
        }
        xhttp.open("GET", req, true);
        xhttp.send();

    }

    function modalWindowClose()
    {
        overlay.style.display = 'none';
    }

    function startStats()
    {
        setTimeout("statsSummary()", 400);
    }

	//generates one line of the stats summary table and appends it to the table
	function statsSummary()
	{
		var statType = gList[gNum++];
		var tblDiv = document.getElementById("genTable");
		var tblContent = tblDiv.innerHTML.replace("</table>", "");
        const xhttp = new XMLHttpRequest();
		req = '/admi/analytics.php?a=StatsLine&type=' + statType + '&daterange=' + daterange;
        xhttp.onload = function() {
            if(this.responseText.slice(0, 5) == "Error") {
                tblDiv.innerHTML = '<p>' + this.responseText + '.</p>';
            } else {
                tblDiv.innerHTML = tblContent + this.responseText + "</table>";
				if(gNum < gList.length) {
					statsSummary();
				}
            }
        }
        xhttp.open("GET", req, true);
        xhttp.send();
	}

	function showTable(tblType)
	{
	    var tblDiv = document.getElementById(tblType + '_tbl');
	    if(tblDiv.innerHTML == '') tblDiv.innerHTML = '<br />Fetching data...<br />';
        req = "/admi/analytics.php?a=" + tblType + "&daterange=" + daterange;
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function()
        {
            if(this.responseText.slice(0, 5) == "Error")
            {
                tblDiv.innerHTML = '<p>' + this.responseText + '.</p>';
            }
            else
            {
                tblDiv.innerHTML = this.responseText;
            }
        }
        xhttp.open("GET", req, true);
        xhttp.send();
	}

	function showGroupTable(tblType, testGroup)
	{
	    var tblDiv = document.getElementById(tblType + '_tbl');
		tblDiv.innerHTML = 'This functionality not yet implemented';
/*		
	    if(tblDiv.innerHTML == '') tblDiv.innerHTML = '<br />Loading...<br />';

		ajax1.open("GET", "jx.php?show_tbl=" + tblType + "&show_grp=" + testGroup + "&dr=" + daterange, true);
		ajax1.onreadystatechange=function()
		{
			if (ajax1.readyState==4 && ajax1.status==200)
			{
				tblDiv.innerHTML = ajax1.responseText;
			}
		}
		ajax1.send(null);
*/
	}

	function hideTable(tblType)
	{
	    var tblDiv = document.getElementById(tblType + '_tbl');
		tblDiv.innerHTML = '';
	}

	function changeSearch(srchDomain, srchType)
	{
	    var tblDiv = document.getElementById('referrers_tbl');
	    if(tblDiv.innerHTML == '') tblDiv.innerHTML = '<br />Fetching data...<br />';
        req = "/admi/analytics.php?a=referrers&daterange=" + daterange + "&" + srchType + "=" + srchDomain;
        const xhttp = new XMLHttpRequest();
        xhttp.onload = function()
        {
            if(this.responseText.slice(0, 5) == "Error")
            {
                tblDiv.innerHTML = '<p>' + this.responseText + '.</p>';
            }
            else
            {
                tblDiv.innerHTML = this.responseText;
            }
        }
        xhttp.open("GET", req, true);
        xhttp.send();
	}

    function setGraphType(ctype)
    {
		columntype = ctype;
		showGraphs();
    }

    function addStartDays(amount)
    {
		startdays += amount;
		showGraphs();
    }

    function subStartDays(amount)
    {
		startdays -= amount;
		showGraphs();
    }

    function zeroStartDays()
    {
		startdays = 0;
		showGraphs();
    }

    function showGraphs()
    {
		var gg = '';
		var t = Math.floor(Date.now() / 1000);
		var gdiv = document.getElementById("graphs");
		var gcontent = '<img src="graph.php?t=visits' + gg + '&c=' + columntype + '&d=' + domain_id + '&st=' + startdays + '&time=' + t + '" width="1800" height="320" alt="" />';
		var gcontent = gcontent + '<img src="graph.php?t=pages' + gg + '&c=' + columntype + '&d=' + domain_id + '&st=' + startdays + '&time=' + t + '" width="1800" height="320" alt="" />';
		gdiv.innerHTML = gcontent;
    }
