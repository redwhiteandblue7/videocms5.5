// Modal window funcs

	var divs;
	var inputs;
	var modal;
	var resetFunc;

    function modalWindowShow(showReset = true)
    {
        if(!modal) {
            modal = document.getElementById('modal_window');
            divs = modal.getElementsByTagName('div');
        }
        var resetBtn = document.getElementById('reset_btn');
        if(showReset) {
			resetBtn.style.display = 'block';    //show the reset button
		} else {
			resetBtn.style.display = 'none';    //hide the reset button
		}
		divs[0].innerHTML = '';
        modal.style.display = 'block';
    }

	function modalWindowReset()
	{
		resetFunc();
	}

    function modalWindowHide()
    {
        modal.style.display = 'none';
    }

	// These are for displaying and hiding the posters and thumbnail next to the cursor. Each image needs to have its own ID
	function showOverImage(imageID, e)
	{
		var x = e.clientX;
		var y = e.clientY;
		var image = document.getElementById(imageID);
		image.style.display = "inline";
		image.style.position = "absolute";
		image.style.top = (y - 300) + "px";
		image.style.left = (x + 80) + "px";
		image.style.zIndex = "20";
	}

	function hideOverImage(imageID)
	{
		var image = document.getElementById(imageID);
		image.style.display = "none";
	}

	//Misc. helper functions

	//this function is used to count the words in the description text area
	function wordCount(txtValue)
	{
		var words = txtValue.split(/\s/);
		document.getElementById('desc_cnt').innerHTML = words.length;
	}

	//this function is used to add the XML template to the description text area
	function addXML()
	{
		var xml = document.getElementById('desctext');
		if(xml.value == '') {
			xml.value = '<?xml version=\'1.0\' encoding=\'UTF-8\'?>\r\n<post>\r\n<fulltext></fulltext>\r\n<snippet></snippet>\r\n</post>';
		}
	}

	//this function is used to add the XML template to the description text area for review posts
	function addReviewXML()
	{
		var xml = document.getElementById('desctext');
		if(xml.value == '') {
	        xml.value = "<?xml version=\'1.0\' encoding=\'UTF-8\'?>\r\n<post>\r\n<short></short>\r\n<fulltext></fulltext>\r\n<snippet></snippet>\r\n<pricing></pricing>\r\n<price></price>\r\n<reasonsto></reasonsto>\r\n<reasonsnot></reasonsnot>\r\n<quality></quality><value></value><total></total>\r\n<author></author>\r\n</post>";
		}
	}
