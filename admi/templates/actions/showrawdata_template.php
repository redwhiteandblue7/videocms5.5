<section class="tabledata">
<?php
    if($this->num_of_rows) {
?>
<p>Raw traffic data since <?=gmdate("H:i:s D, d-M-y", $this->range_val);?>:</p>
&nbsp;Page:&nbsp;<?=$this->pagination();?><br />
<table class="admin1">
<tr><th>#.<br />Time</th><th>Page Visited</th><th>Referrer<br />IP Addrss / Language<br />User Agent</th><th>Platform/Browser</th><th>Group</th><th>Client time</th><th>First Cookie<br />Last Cookie<br />Cookie ID</th></tr>
<?php
		foreach($this->results as $row) {
			extract($row);
			$useragent = htmlspecialchars($useragent);
			$referrer = htmlspecialchars($referrer);
			$country = htmlspecialchars($country);
			$pagename = htmlspecialchars($pagename);
			if($stime != 0) {
				$sdate = gmdate("d-M-y", $stime);
				$stime = gmdate("H:i:s", $stime);
			} else {
					$sdate = "";
			}

			if($first_cookie_time != 0) {
				$first_cookie_time = gmdate("d-M-y H:i:s", $first_cookie_time);
			}

			if($last_cookie_time != 0) {
				$last_cookie_time = gmdate("d-M-y H:i:s", $last_cookie_time);
			}

			$ref = $referrer;
			if(strlen($ref) > 128) $ref = substr($ref, 0, 128);
			if(strlen($pagename) > 64) $pagename = substr($pagename, 0, 64);
			$ip1 = hexdec(substr($ip_address, 6, 2));
			$ip2 = hexdec(substr($ip_address, 4, 2));
			$ip3 = hexdec(substr($ip_address, 2, 2));
			$ip4 = hexdec(substr($ip_address, 0, 2));

			$ip = $ip4 . "." . $ip3 . "." . $ip2 . "." . $ip1;

			$cellclass = (($rownum / 2) == round($rownum / 2)) ? "admin1" : "admin1x";
?>
<tr class="<?=$cellclass;?>">
<td><?=($rownum+1);?>.<br /><nobr><?=$stime;?></nobr><br /><nobr><?=$sdate;?></nobr></td><td><?=$pagename;?></td>
<td><?=$ref;?><br /><?=$ip;?> <?=$country;?><br /><font color="#a0a0a0"><?=$useragent;?></font></td><td><?=$browser;?></td><td><?=$testgroup;?></td><td><?=$ctime;?></td><td style="white-space:nowrap;"><?=$first_cookie_time;?><br /><?=$last_cookie_time;?><br /><?=$cookie_id;?></td>
</tr>
<?php
	    }
?>
</table>
<br /><br />
&nbsp;Page:&nbsp;<?=$this->pagination();?><br />
<?php
    } else {
        echo "<p>There is nothing to display, nada, zero, zilch, zip.</p>";
    }
?>
</section>
</body></html>