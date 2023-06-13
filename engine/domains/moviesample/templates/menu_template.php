<div id="menu" data-visible="false">
<div class="topbar-srch">
<form action="/search.html" method="GET" name="menusearch">
<input type="text" placeholder="Search" name="q" size="60" />
<input type="submit" value="Search" />
</form>
</div>
<ul class="topbar-mob" id="menu_lr">
<?php
    include(INCLUDE_PATH . "domains/moviesample/templates/lrbuttons_tpl.php");
?>
</ul>
<ul>
<li><a href="/tags.html">All Tags</a></li>
<li><a href="/channels.html">All Channels</a></li>
<li><a href="/history.html">Your History</a></li>
<li><a href="/popular.html">Most Popular</a></li>
<li><a href="/trending.html">Trending</a></li>
<li><a href="/tag/movietrailers">Movie Trailers</a></li>
<li><a href="/tag/music">Music Videos</a></li>
</ul>
</div>