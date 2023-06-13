</div>
<footer>
<div class="container">
<div><div>
MovieSample.me (c)2023
<p>This is a simple demo project to develop and test the enginecms script as a video tube script. 
The site is currently under development and is not yet ready for public use. If you are interested in using the script for your own site, please contact me for further details.
All video content is copyright of the respective owners. All script code written by and copyright of t.prosser except the videojs html5 video player and related plugins.
The code does not use any third party libraries or frameworks, it is written in PHP and Javascript entirely from scratch and runs on PHP 7.4 or 8. The code is not licensed for use on any other site.</p>
<p>Interested parties please contact tprosser@protonmail.com</p>
<p>For DMCA removal requests please use the contact email above.</p>
</div></div>
</div>
</footer>
<div id="overlay"></div>
<div id="popup"></div>
<script>
	var post_id = <?=$this->post_id;?>;
	var desktop = <?=($this->visitor->desktop == true) ? "true" : "false";?>;
	var accountTemplate = "<?=$this->account_template;?>";
	var includeHistory = <?=($include_add_history ?? false == true) ? "true" : "false";?>;
    var sessionToken = "<?=$this->session_token;?>";
</script>
<script src="/js/front.js?ver=<?=$this->domain->vars()->css_version;?>"></script>
<script>
/*
    var docReady = function(callback)
    {
        if (document.readyState != "loading") callback();
        else document.addEventListener("DOMContentLoaded", callback);
    };

    docReady(function()
    {
        var a = document.querySelectorAll('[data-id]');
        for(var i = 0, n = a.length; i < n; i++) {
            var id = a[i].getAttribute('data-id');
            a[i].setAttribute('onclick', "navigator.sendBeacon('/track.php?type=link&id=" + id + "', null);");
        };
    });
*/
</script>
</body></html>