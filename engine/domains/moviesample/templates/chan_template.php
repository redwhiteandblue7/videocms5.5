<?php
        $title = htmlspecialchars($channel->channelname);
        $url = "/channel/" . $channel->channelid;
        $video_count = $channel->video_count;
?>
<div class="cthmb">
<div><a href="<?=$url;?>"><img src="<?=$channel->thumb_url;?>" width="384" height="216" loading="lazy" alt="Movie thumbnail preview" /></a></div>
<div><p><?="$title - $video_count&nbsp;videos";?></p></div>
</div>