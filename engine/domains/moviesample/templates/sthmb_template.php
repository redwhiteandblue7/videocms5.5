<?php
    $title = htmlspecialchars($video->title);
    $url = "/video/" . $video->pagename;
    $alt_title = htmlspecialchars($video->alt_title);
?>
<div class="sthmb"><a href="<?=$url;?>"><img src="/<?=$video->url_thumbnail;?>" loading="lazy" alt="<?=$alt_title;?>" /></a><p><?=$video->title;?></p></div>
