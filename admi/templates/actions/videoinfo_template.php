</nav></header>
<section class="edit"><div>
<?php
    $base_filename = $video->vars()->base_filename ?? "";
    $base_url = $video->vars()->base_url ?? "";
    $public_path = $domain->vars()->public_path ?? "";

    $filepath = $public_path . $base_url . $base_filename;
    $ffprobe = shell_exec("ffprobe -show_streams $filepath 2>&1");
?>
<form method="post" action="?a=VideoInfo">
<br />Info from FFProbe about the streams in this video file:
<br /><textarea name="ffprobe" rows="40" cols="120"><?=$ffprobe;?></textarea>
</form></div>
</section>