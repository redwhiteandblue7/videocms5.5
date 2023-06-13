<header>
<div class="container">
<div class="topbar">
<?php
    if($this->uri[0] == "index.html") {
?>
<img src="/mvs/images/logo.png" loading="lazy" style="cursor:pointer;" onclick="window.scrollTo({top:0, left:0, behavior:'smooth'});" width="185" height="54" alt="logo" />
<div id="burger"><div></div></div>
<?php
    } else {
?>
<a href="/"><img src="/mvs/images/logo.png" loading="lazy" width="185" height="54" alt="logo" /></a>
<div id="burger"><div></div></div>
<?php
    }
?>
<div class="topbar-left">
<form action="/search.html" method="GET" name="navbarsearch">
<input type="text" placeholder="Search" name="q" size="60" />
<input type="submit" value="Search" />
</form>
</div>
<div class="topbar-right">
<ul id="navbar_lr">
<?php
    include(INCLUDE_PATH . "domains/moviesample/templates/lrbuttons_tpl.php");
?>
</ul>
</div>
</div>
<?php
    include(INCLUDE_PATH . "domains/moviesample/templates/menu_template.php");
?>
</div>
</header>
<div class="container">