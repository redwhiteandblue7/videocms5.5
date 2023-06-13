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
            default:
                break;
        }
        $this->action_status = "";
    }

    if($this->num_of_channels) {
?>
<p>The <?=$channel->numRows();?> latest channels:</p>
<table><tr><th>#.</th><th>ID</th><th>Channel</th><th>Site</th><th>Created</th><th>Owner</th></tr>
<?php
        $rownum = 0;
        while($row = $channel->next()) {
            $d = date("H:i:s \o\\n Y-m-d", $row->time_added);
            $cellclass = (($rownum / 2) == round($rownum / 2)) ? "admin1" : "admin1x";
?>
<tr class="<?=$cellclass;?>">
<td><?=($row->rownum+1);?>. <a href="?a=ChannelEdit&id=<?=$row->channel_id;?>">[Edit]</a></td>
<td><?=$row->channel_id;?></td>
<td><?=$row->channel_name;?></td>
<td><?=$row->site_name;?></td>
<td><?=$d;?></td>
<td><?=$row->user_name;?></td>
</tr>
<?php
            $rownum++;
        }
?>
</table>
<?php
    } else {
        echo "<p>There are no channels to display, nada, zilch, nought.</p>";
    }
?>
</section>
