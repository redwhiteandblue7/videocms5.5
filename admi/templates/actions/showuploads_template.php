</nav></header>
<section class="minidata">
<?php
    if($this->action_status) {
        switch($this->action_status) {
            case "imported":
                echo "<p class=\"success\">Video imported.</p>";
                break;
            case "channel_saved":
                echo "<p class=\"success\">Channel saved.</p>";
                break;
            case "video_saved":
                echo "<p class=\"success\">Video saved.</p>";
                break;
            default:
                break;
        }
        $this->action_status = "";
    }

    $s = sizeof($this->video_uploads);
    if($s) {
?>
<p>The <?=$s;?> latest video file uploads: [<a href="#" onclick="videoImportAll();return false;">Import all</a>]</p>
<table id="uploads"><tr><th>#.</th><th>File</th><th>Size</th><th>Uploaded</th></tr>
<?php
        $rownum = 0;
        foreach($this->video_uploads as $f) {
            $cellclass = (($rownum / 2) == round($rownum / 2)) ? "admin1" : "admin1x";
            $d = date("H:i:s \o\\n D M j Y", $f["time"]);
            $s = $f["size"];
            if($s > 1048576) {
                $s = number_format($s / 1048576, 1) . " M";
            } elseif($s > 1024) {
                $s = number_format($s / 1024, 1) . " k";
            }
            $filename = $f["filename"];

?>
<tr class="<?=$cellclass;?>">
<td><?=($rownum+1);?>. <a href="?a=VideoImport&name=<?=urlencode($filename);?>">[Import]</a></td>
<td><?=$filename;?></td>
<td><?=$s;?></td>
<td><?=$d;?></td>
</tr>
<?php
            $rownum++;
        }
?>
</table>
<?php
    } else {
        echo "<p>There no file uploads to display, nada, zilch, nought.</p>";
    }
?>
</section>
<?php
    require_once("modalscroll.php");
?>
</body></html>