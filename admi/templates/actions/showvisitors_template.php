<section class="tabledata">
<?php
    if($this->num_of_rows) {
?>
<p>Visitor data since <?=gmdate("H:i:s D, d-M-y", $this->range_val);?>:</p>
&nbsp;Page:&nbsp;<?=$this->pagination();?><br />
<table>
<tr><th>#.</th><th>First Visited</th>
<th>Last Visited<br /><?=$this->sortlink("Time");?>[Sort&nbsp;Up]</a><br /><?=$this->sortlink("TimeR");?>[Sort&nbsp;Down]</a></th><th>Visits</th><th>Referred by domain<br />Referred by trade</th><th>IP Address / Hostname<br />User agent <?=$this->sortlink("Client");?>[Sort By]</a></th>
<th>Browser</th><th>Screen Type W x H</th><th>User</th></tr>
<?php
		$rownum = 1;
		foreach($this->results as $row) {
			extract($row);
			if($useragent) $useragent = htmlspecialchars($useragent);
			if($domain) $domain = htmlspecialchars($domain);
			if($country) $country = htmlspecialchars($country);
			if($first_time != 0) $first_time = gmdate("H:i:s d-M-y", $first_time);
			else $first_time = "[No cookie]";

			if($last_time != 0) {
				$last_time = gmdate("H:i:s d-M-y", $last_time);
			} else {
				$last_time = "[Unknown]";
			}
			if($linkname == "") $linkname = "[Unknown]";
			if($domain == "") $domain = "[Unknown]";

			$rcolour = ($linkname != $domain && $domain != "[Unknown]") ? "yellow" : "white";

			$ip1 = hexdec(substr($ip_address, 6, 2));
			$ip2 = hexdec(substr($ip_address, 4, 2));
			$ip3 = hexdec(substr($ip_address, 2, 2));
			$ip4 = hexdec(substr($ip_address, 0, 2));

			$ip = $ip4 . "." . $ip3 . "." . $ip2 . "." . $ip1;
			$hostname = " - ";
			if($this->action == "ShowVisitor") $hostname = gethostbyaddr($ip);

			$scr1 = $screentype % 65536;
			$screentype = $screentype / 65536;
			$scr2 = $screentype % 65536;
			$screen = $scr1 . "x" . $scr2;

			$cellclass = (($rownum / 2) == round($rownum / 2)) ? "admin1" : "admin1x";
			echo "<tr class=\"$cellclass\">\n";

			echo "<td>" . (++$rownum) . ".</td>";
			echo "<td><nobr>$first_time<nobr></td>";
			echo "<td><nobr>$last_time<nobr></td>";
			echo "<td>$visits&nbsp;";
			if($visitor_id != "") {
				echo $this->link("a=ShowPageloads&type=visitor&id=$visitor_id") . "[View]</a>";
				echo "&nbsp;<a href=\"?&a=RemoveVisitor&id=$visitor_id\" onclick=\"return confirm('Are you sure you want to remove this visitor from the stats?');\">[Remove]</a>";
			}
			echo "</td>";
			echo "<td><font color=\"$rcolour\">";
			if($domain != $linkname) echo "$domain<br />";
			echo "$linkname</font></td>";
			echo "<td>$ip (<font color=\"#00c040\">$hostname</font>) $country";
			echo "<br /><font color=\"#a0a0a0\">$useragent</font></td>";
			echo "<td>$browser</td>";
			echo "<td>$screen</td>";
			echo "<td>$user_id</td>";
			echo "\n</tr>\n";
		}
?>
</table>
<br /><br />
&nbsp;Page:&nbsp;<?=$this->pagination();?><br />
<?php
    } else {
        echo "<p>No visitor data anywhere to be found.</p>";
    }
?>
</section>
</body></html>