<nav class="admin col-9">
<p class="submenu">
<a href="?a=ShowPageloads"><button>All Traffic</button></a>
<a href="?a=ShowPageloads&type=real"><button>Real Traffic</button></a>
<a href="?a=ShowPageloads&type=new"><button>New Visits</button></a>
<a href="?a=ShowPageloads&type=nandr"><button>New & Return</button></a>
<a href="?a=ShowPageloads&type=clicks"><button>Clicks</button></a>
<a href="?a=ShowPageloads&type=bots"><button>Bot Traffic</button></a>
<a href="?a=ShowVisitors"><button>Visitors</button></a>
<a href="?a=ShowRawData"><button>Raw Stats</button></a>
</p>
<p><form method="POST" action="">
Today is <?=gmdate("H:i:s D, d-M-y");?>. Server time <?=date("H:i:s D, d-M-y");?>. Stats updated <?=date("H:i:s D, d-M-y", $update_time);?>
<br />Select period:<select name="daterange" onchange="this.form.submit()">
<option value="today"<?=($this->daterange == "today" ? " selected = \"selected\"" : "");?>>So Far Today</option>
<option value="last24"<?=($this->daterange == "last24" ? " selected = \"selected\"" : "");?>>Last 24 hours</option>
<option value="yesterday"<?=($this->daterange == "yesterday" ? " selected = \"selected\"" : "");?>>Since Yesterday</option>
<option value="last48"<?=($this->daterange == "last48" ? " selected = \"selected\"" : "");?>>Last 48 hours</option>
<option value="twodaysago"<?=($this->daterange == "twodaysago" ? " selected = \"selected\"" : "");?>>Since Two Days Ago</option>
<option value="last72"<?=($this->daterange == "last72" ? " selected = \"selected\"" : "");?>>Last 72 hours</option>
<option value="thisweek"<?=($this->daterange == "thisweek" ? " selected = \"selected\"" : "");?>>So Far This Week</option>
<option value="last7"<?=($this->daterange == "last7" ? " selected = \"selected\"" : "");?>>Last 7 Days</option>
</select>
&nbsp;Display Test Stats<input type="checkbox" name="shtest" value="1"<?=(isset($_SESSION['show_test'])) ? " checked=\"checked\"" : "";?> onchange="this.form.submit()" />&nbsp;<input type="submit" value="Select" />
</form></p>
<?php
/*
	if(isset($_SESSION['show_test']))
	{
		$prefix = $this->table_prefix;
		echo "<form name=\"groupselect\" action=\"" . $_SERVER["REQUEST_URI"] . "\" method=\"POST\">";
		$q = "select min(stime) as mt from {$prefix}_pageloads where testgroup>0";
		$r = $this->dbc->query($q) or die($this->dbc->error);

		$mt = $r->fetch_row()[0];
		if(is_numeric($mt))
		{
			echo " Test group started " . gmdate("H:i:s D, d-M-y", $mt);
			echo ". <a href=\"admin.php?a=resettest&subpage=stats\">Clear test?</a>";
		}
		else
		{
			echo " No test groups found.";
		}
		$r->close();
		echo " Select Test Group <select name=\"testgroup\">\n";
		echo "<option value=\"0\"";
		if($this->group_filter == 0) echo " selected=\"selected\"";
		echo ">-None-</option>\n";
		for($i = 1; $i < 5; $i++)
		{
			echo "<option value=\"$i\"";
			if($this->group_filter == $i) echo " selected=\"selected\"";
			echo ">$i</option>\n";
		}
		echo "</select><input type=\"submit\" value=\"Go\" />\n";
		echo "</form></div>\n";
	}
*/
?>
</nav>
</header>