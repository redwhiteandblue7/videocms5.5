<section class="tabledata">
<?php
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

    if($this->num_of_rows)
    {
?>
&nbsp;Page:&nbsp;<?=$this->writePagination($this->pages, $this->page);?><br />
<table>
<tr>
<th>#.</th>
<th>Submit ID</th>
<th>Status</th>
<th>Submitted:</th>
<th>User</th>
<th>Title</th>
<th>URL</th>
<th>Category</th>
<th>Backlink</th>
<th>Content</th>
<th>Actions</th>
</tr>
<?php
        while($row = $this->dbo->getNextResultsRow())
        {
            extract($row);
            switch($progress)
            {
                case "rejected":
                    $tcolour = "#808080";
                    break;
                case "processed":
                    $tcolour = "#808070";
                    break;
                case "pending":
                    $tcolour = "#ffd0d0";
                    break;
                default:
                    $tcolour = "#e0e0e0";
                    break;
            }
            $submit_time = gmdate("H:i:s d-M-y", $submit_time);
            $progress = ucfirst($progress);

            if(($submit_url != "") && (substr($submit_url, 0, 5) != "http:") && (substr($submit_url, 0, 6) != "https:")) $submit_url = "http://" . $submit_url;
            $cellclass = (($rownum / 2) == round($rownum / 2)) ? "admin1" : "admin1x";
?>
<tr class="<?=$cellclass;?>">
<td><?=($rownum+1);?>.</td>
<td><?=$submit_id;?></td>
<td><?=$progress;?></td>
<td><?=$submit_time;?></td>
<td><?=$user_name;?><br /><?=$email_addr;?><br /><?=$ip_address;?><br /><?=$useragent;?></td>
<td><font color="<?=$tcolour;?>"><?=$submit_title;?></font></td>
<td><a href="<?=$submit_url;?>" target="_blank"><?=$submit_url;?></a></td>
<td><?=$submit_category;?></td>
<td><?=$submit_tags;?></td>
<td><font color="<?=$tcolour;?>"><?=$submit_content;?></font></td>
<td><a href="?subpage=submittools&a=import_site&gid=<?=$submit_id;?>">[Import Into Post]</a></td>
</tr>
<?php
        }
?>
</table>
<br />
&nbsp;Page:&nbsp;<?=$this->writePagination($this->pages, $this->page);?><br />
<?php
    }
    else
    {
        echo "<p>There is nothing to display, nada, zilch, nought.</p>";
    }
?>
</section>
