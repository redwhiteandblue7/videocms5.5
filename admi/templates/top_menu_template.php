<?php
    $domain_url = $this->base_url
?>
<div class="col-3 lhead">
<h1>EngineCMS 5.4</h1>
<form method="post" name="selectsite" action="">
<p><?=ucfirst($sub_page);?> page for:
<select name="select_domain_id" onchange="this.form.submit()">
<?php
    foreach($this->domain_list as $domain)
    {
        echo "<option value=\"" . $domain["domain_id"] . "\"";
        if($_SESSION["domain_id"] == $domain["domain_id"]) echo " selected=\"selected\"";
        echo ">" . $domain["domain_name"] . "</option>\n";
    }
    $have_posts = strpos(ADMIN_MODULES, "posts");
    $have_links = strpos(ADMIN_MODULES, "links");
    $have_videos = strpos(ADMIN_MODULES, "videos");
    $have_submits = strpos(ADMIN_MODULES, "submits");
    $have_sites = strpos(ADMIN_MODULES, "sites");
//    if($have_submits) $submits = $this->dbo->getNewSubmitsCount();

?>
</select></p>
</form>
<p>
<a href="?a=DomainEdit"><button class="black">Edit Domain</button></a>
<a href="?a=DomainEdit&type=new"><button class="black">New Domain</button></a>
</p>
<p class="small"><?=$this->username;?> [<a href="?a=Logout">Log Out</a>] [<a href="<?=$domain_url;?>" target="_blank">View Site</a>]
Modes:[<a href="<?=($_SESSION["mode_strict"] == "strict") ? "?a=Mode&strict_mode=fast" : "?a=Mode&strict_mode=strict";?>"><?=$_SESSION["mode_strict"];?></a>]
[<a href="<?=($_SESSION["mode_list"] == "full") ? "?a=Mode&list_mode=short" : "?a=Mode&list_mode=full";?>"><?=$_SESSION["mode_list"];?></a>]</p>
</div>
<div class="col-9">
<nav>
<a href="?a=ShowData&clear=true"><button class="orange">Traffic</button></a>
<?=($have_posts !== false) ? "<a href=\"?a=ShowPosts\"><button class=\"green\">Posts</button></a>" : "";?>
<?=($have_videos !== false) ? "<a href=\"?a=ShowVideos\"><button class=\"green\">Videos</button></a>" : "";?>
<?=($have_sites !== false) ? "<a href=\"?a=ShowSites\"><button class=\"blue\">Sites</button></a>" : "";?>
<a href="?a=ShowTags"><button class="blue">Tags</button></a>
<a href="?a=ShowPages"><button class="purple">Pages</button></a>
<?=($have_links !== false) ? "<a href=\"?a=ShowLinks\"><button class=\"yellow\">Link Trades</button></a>" : "";?>
<?=($have_submits !== false) ? "<a href=\"?a=ShowSubmits\"><button class=\"" . (($submits) ? "red" : "yellow") . "\">Submissions " . (($submits) ? "($submits)" : "") . "/button></a>" : "";?>
<a href="?a=ShowTools&clear=true"><button>Tools</button></a>
</nav>
</div>
