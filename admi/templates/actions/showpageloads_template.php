<section class="tabledata">
<?php
    if($this->num_of_rows) {
		$label = $this->dbo->getNextMessage();
		if($label) {
			echo "<p>$label</p>\n";
		} else {
			echo "<p>Pageloads since " . gmdate("H:i:s D, d-M-y", $this->range_val) . ":</p>";
		}
?>
&nbsp;Page:&nbsp;<?=$this->pagination();?><br />
<table class="admin1">
<tr>
<th>#.<br />Time
<br /><?=$this->sortlink("Time");?>[Sort&nbsp;Up]</a>
<br /><?=$this->sortlink("TimeR");?>[Sort&nbsp;Down]</a></th>
<th>Page Visited<br />Load Time</th>
<th>Visit Type<br />First Visit<br />Last Visit</th>
<th>Referrer <?=$this->sortlink("Referrer");?>[Sort By]</a>
<br />IP Address / Hostname <?=$this->sortlink("IP");?>[Sort By]</a>
<br />Browser - User agent <?=$this->sortlink("Client");?>[Sort By]</a></th>
<th>Visitor ID<br />Visitor IP<br />Cookie ID</th>
<th>First time<br />Session time <?=$this->sortlink("Session");?>[Sort By]</a><br />Last time</th>
<th>User</th>
</tr>
<?php
		foreach($this->results as $row) {
			extract($row);
			$country = htmlspecialchars($country);
			$referstring = htmlspecialchars($referstring);
			$pagename = htmlspecialchars($pagename);
			$useragent = htmlspecialchars($useragent);
			if($domainstring) $domainstring = htmlspecialchars($domainstring);
			if($refdom) $refdom = htmlspecialchars($refdom);
			if($linkname) $linkname = htmlspecialchars($linkname);

			$ctime = $ctime - $stime;
			if($ctime < 0) {
				$ctime = "Unknown";
			} else {
				$ctime .= " seconds";
			}

			if($stime != 0) {
				$sdate = gmdate("d-M-y", $stime);
				$stime = gmdate("H:i:s", $stime);
			} else {
					$sdate = "";
			}

			if($first_time != 0) $first_time = gmdate("H:i:s d-M-y", $first_time);
			else $first_time = "[No cookie]";

			if($last_time != 0) $last_time = gmdate("H:i:s d-M-y", $last_time);
			else $last_time = "[Unknown]";

			if($session_time != 0) $session_time = gmdate("H:i:s d-M-y", $session_time);
			else $session_time = "[Unknown]";

			switch($flag) {
				case "N":
					$type = "New";
					$vcolour = "#ffa000";
					break;
				case "S":
					$type = "Page";
					$vcolour = "#f0f000";
					break;
				case "R":
					$type = "Returning";
					$vcolour = "#f020f0";
					break;
				case "U":
					$type = "Not known";
					$vcolour = "#a0a0a0";
					break;
				case "C":
					if($pagename[0] == "@") {
						$type = ucfirst(substr($pagename, 1));
						if($linkname != "") $pagename = $linkname;
						elseif($site_name != "") $pagename = $site_name;
						elseif($title != "") $pagename = $title;
					} else {
						$type = "Click";
					}
					$vcolour = "#20f020";
					break;
				case "B":
					$type = "Bot";
					$vcolour = "#ff0000";
					break;
				case "F":
					$type = "Refresh";
					$vcolour = "c0c000";
					break;
				default:
					$type = "Error";
					$vcolour = "#ffffff";
					break;
			}

			$ref = $domainstring . $referstring;
			$referrer = "http://" . $ref;
			if(strlen($ref) > 128) $ref = substr($ref, 0, 128);
			if(strlen($pagename) > 64) $pagename = substr($pagename, 0, 64);

			$ip1 = hexdec(substr($ip_address, 6, 2));
			$ip2 = hexdec(substr($ip_address, 4, 2));
			$ip3 = hexdec(substr($ip_address, 2, 2));
			$ip4 = hexdec(substr($ip_address, 0, 2));

			$ip = $ip4 . "." . $ip3 . "." . $ip2 . "." . $ip1;

			$vip1 = hexdec(substr($visitor_ip, 6, 2));
			$vip2 = hexdec(substr($visitor_ip, 4, 2));
			$vip3 = hexdec(substr($visitor_ip, 2, 2));
			$vip4 = hexdec(substr($visitor_ip, 0, 2));

			$vip = $vip4 . "." . $vip3 . "." . $vip2 . "." . $vip1;

			$cellclass = (($rownum / 2) == round($rownum / 2)) ? "admin1" : "admin1x";
?>
<tr class="<?=$cellclass;?>">
<td><?=$rownum+1;?>.<br /><nobr><?=$stime;?></nobr><br /><nobr><?=$sdate;?></nobr></td>
<td><?=$pagename;?><br /><nobr><font color="#a0a0a0"><?=$ctime;?></font></nobr></td>
<td><font color="<?=$vcolour;?>"><?=$type;?></font><br /><nobr><?=$first_time;?></nobr><br /><nobr><?=$last_time?><nobr></td>
<td><?=($domainstring == "") ? "$ref" : "<a href=\"$referrer\">$ref</a>";?>
<?=($refdom) ? " [$refdom]" : "";?>
<br /><?=$ip;?> <?=$country;?>
<?=($visitor_id) ? "&nbsp;" . $this->link("a=ShowVisitors&id=$visitor_id") . "[View]</a>" : "";?>
<br /><font color="#a0a0a0"><?=$useragent;?></font></td>
<td><?=$visitor_id;?><br /><?=$vip;?><br /><?=$cookie_id;?></td>
<td style="white-space:nowrap;"><?=$first_time;?><br /><?=$session_time;?><br /><?=$last_time;?></td>
<td><?=$user_id;?></td>
</tr>
<?php
    	}
?>
</table>
<br /><br />
&nbsp;Page:&nbsp;<?=$this->pagination();?><br />
<?php
    } else {
        echo "<p>Either this action does not yet exist or there is no page data to display.</p>";
    }
?>
</section>
</body></html>