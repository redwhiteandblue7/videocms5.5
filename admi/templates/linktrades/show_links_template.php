<section class="tabledata">
<?php
    $trade_skim_turbo1 = 0;
    $trade_skim_throttle = 0;

    if(sizeof($this->error_messages))
    {
        echo "<p class=\"error centre\">{$this->error_messages[0]}</p>\n";
    }
    elseif(sizeof($this->status_messages))
    {
        echo "<p class=\"success centre\">{$this->status_messages[0]}</p>\n";
    }
	elseif($label = $this->dbo->getNextMessage())
	{
		echo "<p>$label</p>\n";
	}
?>
<?php
    if($this->num_of_rows)
    {
?>
&nbsp;Page:&nbsp;<?=$this->writePagination($this->pages, $this->page);?><br />
<table>
<tr><th>#.</th><th>Link ID</th><th>Ref Code</th><th>Time Added</th><th>Title<br />Description</th><th>Status</th><th>Domain<br />Landing Page</th><th>Tags</th><th>Type</th>
<th>Hits In<br />24hrs<br /><a href="?subpage=linktrades&a=show_links&s=sortByIn1">[Sort]</a></th>
<th>Hits Out<br />24hrs</th><th>Clicks<br />24hrs<br /><a href="?subpage=linktrades&a=show_links&s=sortByC1">[Sort]</a></th>
<th>Prod<br />24hrs</th><th>Hits In<br />7 days<br /><a href="?subpage=linktrades&a=show_links&s=sortByIn7">[Sort]</a></th>
<th>Hits Out<br />7 days</th>
<th>Clicks<br />7 days<br /><a href="?subpage=linktrades&a=show_links&s=sortByC7">[Sort]</a></th>
<th>Prod<br />7 days</th>
<th>Action</th>
</tr>
<?php
        while($row = $this->dbo->getNextResultsRow())
        {
            extract($row);
            $tcolour = ($status == 0) ? "#808080" : "white";
            $lcolour1 = $tcolour;
            $lcolour2 = $tcolour;

            $time_visible = date("H:i:s d-M-y", $time_visible);

            if($description == "") $description = "None";

            if($outs_24_hours == 0)
            {
                $prod_24_hours = "-";
            }
            else
            {
                $prod_24_hours = intval(($clicks_24_hours * 100) / $outs_24_hours);
                if($status > 0)
                {
                    if($prod_24_hours >= $trade_skim_turbo1)
                    {
                        $boost = (($prod_24_hours - $trade_skim_turbo1) / 5) + 1.0;
                        if($ins_24_hours > $outs_24_hours)
                        {
                            $boost *= 1.2;
                        }
                        $prod = (($prod_24_hours * $boost) - $trade_skim_turbo1) / 4;
                        $r = 255;
                        $g = 255;
                        $b = 255 - $prod;
                        if($b < 0)
                        {
                            $b = 0;
                            $g = 255 - ($prod - 256) / 4;
                            if($g < 64) $g = 64;
                        }
                        $lcolour1 = "#" . dechex((int)$r) . dechex((int)$g) . dechex((int)$b);
                    }
                    elseif(($outs_24_hours > 0) && ($ins_24_hours == 0))
                    {
                        $lcolour1 = "#d000d0";
                    }
                    elseif($prod_24_hours < 100)
                    {
                        $lcolour1 = "#b0b0ff";
                    }
                    elseif($prod_24_hours < $trade_skim_throttle)
                    {
                        $lcolour1 = "#e0e0ff";
                    }

                }
            }

            if($outs_7_days == 0)
            {
                $prod_7_days = "-";
            }
            else
            {
                $prod_7_days = intval(($clicks_7_days * 100) / $outs_7_days);
                if($status > 0)
                {
                    if($prod_7_days >= $trade_skim_turbo1)
                    {
                        $boost = (($prod_7_days - $trade_skim_turbo1) / 5) + 1.0;
                        if($ins_7_days > $outs_7_days)
                        {
                            $boost *= 1.2;
                        }
                        $prod = (($prod_7_days * $boost) - $trade_skim_turbo1) / 4;
                        $r = 255;
                        $g = 255;
                        $b = 255 - $prod;
                        if($b < 0)
                        {
                            $b = 0;
                            $g = 255 - ($prod - 256) / 4;
                            if($g < 64) $g = 64;
                        }
                        $lcolour2 = "#" . dechex((int)$r) . dechex((int)$g) . dechex((int)$b);
                    }
                    elseif(($outs_7_days > 0) && ($ins_7_days == 0))
                    {
                        $lcolour2 = "#d000d0";
                    }
                    elseif($prod_7_days < 100)
                    {
                        $lcolour2 = "#b0b0ff";
                    }
                    elseif($prod_7_days < $trade_skim_throttle)
                    {
                        $lcolour2 = "#e0e0ff";
                    }

                }
            }

            $link_types = array("Off", "Top", "Trade");
            $type = $link_types[$request_status];
            $status_types = array("Suspended", "Pending", "Active", "Auto");
            $status_type = $status_types[$status];
            $scolour = "white";
            if(($status < 2) && ($request_status == 2)) $scolour = "#d000d0";
            if(($status < 2) && ($request_status == 1)) $scolour = "#8080ff";

            $cellclass = (($rownum / 2) == round($rownum / 2)) ? "admin1" : "admin1x";
?>
<tr class="<?=$cellclass;?>">
<td><?=($rownum+1);?>.</td>
<td><?=$link_id;?> <a href="?subpage=linktrades&a=edit_link&did=<?=$link_id;?>">[Edit]</a></td>
<td><?=$ref_code;?></td>
<td><?=$time_visible;?></td>
<td><font color="<?=$tcolour;?>"><?=$anchor;?></font><br />
<font color="<?=$tcolour;?>"><?=$description;?></font>
</td>
<td><font color="<?=$scolour;?>"><?=$status_type;?></font></td>
<td><?=$domainstring;?><br /><a href="<?=$landing_page;?>" target="_blank"><?=$landing_page;?></a></td>
<td>
<?php
            $this->dbo->fetchJoinedTags("hardlinks", "link_id", $link_id);
            while($row = $this->dbo->getNextTableRow())
			{
				extract($row);
				echo "<nobr>$title<a href=\"?subpage=linktrades&a=delete_tag&did=$link_id\">[Del]</a><br /></nobr>";
			}
			echo "<a href=\"?subpage=linktrades&a=add_tag&did=$link_id\">[Add]</a></td>";
?>
<td><font color="<?=$scolour;?>"><?=$type;?></font></td>
<td><font color="<?=$lcolour1;?>"><?=$ins_24_hours;?></font></td>
<td><font color="<?=$lcolour1;?>"><?=$outs_24_hours;?></font></td>
<td><font color="<?=$lcolour1;?>"><?=$clicks_24_hours;?></font></td>
<td><font color="<?=$lcolour1;?>"><?=$prod_24_hours;?></font></td>
<td><font color="<?=$lcolour2;?>"><?=$ins_7_days;?></font></td>
<td><font color="<?=$lcolour2;?>"><?=$outs_7_days;?></font></td>
<td><font color="<?=$lcolour2;?>"><?=$clicks_7_days;?></font></td>
<td><font color="<?=$lcolour2;?>"><?=$prod_7_days;?></font></td>
<td>
<?php
		if($status < 2)
		{
			echo "<a href=\"?subpage=linktrades&a=approve_link&did=$link_id\">[Activate]</a><br />";
			echo "<a href=\"?subpage=linktrades&a=delete_link&did=$link_id\" onclick=\"return(confirm('Are you sure you want to delete $domainstring?'));\">[Delete]</a>";
		}
		else
		{
			echo "<a href=\"?subpage=linktrades&a=suspend_link&did=$link_id\">[Suspend]</a>";
		}
?>
</td>
</tr>
<?php
        }
?>
</table>
<br /><br />
&nbsp;Page:&nbsp;<?=$this->writePagination($this->pages, $this->page);?><br />
<?php
    }
    else
    {
        echo "<p>There is nothing to display, nada, zilch, nought.</p>";
    }
?>
</section>