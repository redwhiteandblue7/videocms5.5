<?php
	$domain_id = $_SESSION["domain_id"] ?? 0;
?>
<script>
	const domain_id = <?=$domain_id;?>;
	const daterange = <?=$this->range_val;?>;
</script>
<script src="/admi/js/stat_tables.js"></script>
<section class="tabledata">
<div id="genTable"><table><tr><th>Stats from:</th><th>Uniques</th><th>New</th><th>Returning</th><th>Pageloads</th><th>Clickthrus</th><th>Clicks/k</th>
<th>Sponsor Hits</th><th>Sponsor Hits/k</th><th>Bad / Bots</th>
<th>Other SE</th><th>bing.com</th><th>google.com</th><th><?=$this->tracked;?></th><th>Total SE</th>
<th>Page speed</th><th>Logins</th><th>Registrations</th></tr></table></div>
<script>
	startStats();
</script>
<br />
<div id="graphs">
<span><a href="#" onclick="setGraphType(1); return false;">[Days]</a> <a href="#" onclick="setGraphType(2); return false;">[Weeks]</a> <a href="#" onclick="setGraphType(3); return false;">[Months]</a>  - <a href="#" onclick="subStartDays(180); return false;">[-180 Days]</a> <a href="#" onclick="subStartDays(60); return false;">[-60 Days]</a> <a href="#" onclick="zeroStartDays(); return false;">[0 Days]</a> <a href="#" onclick="addStartDays(60); return false;">[+60 Days]</a> <a href="#" onclick="addStartDays(180); return false;">[+180 Days]</a></span>
<img src="graph.php?t=visits&c=1&d=<?=$domain_id;?>&st=0&time=5513903" width="1800" height="320" alt="" />
<img src="graph.php?t=pages&c=1&d=<?=$domain_id;?>&st=0&time=5513903" width="1800" height="320" alt="" />
</div><br />
</section><section class="minidata">
Productivity By Referring Domain <a href="#" onclick="showTable('referrers'); return false;">[Show]</a> <a href="#" onclick="hideTable('referrers'); return false;">[Hide]</a><br />
<div id="referrers_tbl"></div>
<br />Productivity By Platform <a href="#" onclick="showTable('platformprod'); return false;">[Show]</a> <a href="#" onclick="hideTable('platformprod'); return false;">[Hide]</a><br />
<div id="platformprod_tbl"></div>
<br />Productivity By Platform/Browser <a href="#" onclick="showTable('useragentprod'); return false;">[Show]</a> <a href="#" onclick="hideTable('useragentprod'); return false;">[Hide]</a><br />
<div id="useragentprod_tbl"></div>
<br />Productivity By Browser Version <a href="#" onclick="showTable('browserprod'); return false;">[Show]</a> <a href="#" onclick="hideTable('browserprod'); return false;">[Hide]</a><br />
<div id="browserprod_tbl"></div>
<br />Entry Pages <a href="#" onclick="showTable('entrypages'); return false;">[Show]</a> <a href="#" onclick="hideTable('entrypages'); return false;">[Hide]</a><br />
<div id="entrypages_tbl"></div>
<br />Most Popular Pages <a href="#" onclick="showTable('popularpages'); return false;">[Show]</a> <a href="#" onclick="hideTable('popularpages'); return false;">[Hide]</a><br />
<div id="popularpages_tbl"></div>
<br />Entry Pages From Search <a href="#" onclick="showTable('searchhits'); return false;">[show]</a> <a href="#" onclick="hideTable('searchhits'); return false;">[hide]</a><br />
<div id="searchhits_tbl"></div>
<br />Site Referring Pages <a href="#" onclick="showTable('siterefpages'); return false;">[Show]</a> <a href="#" onclick="hideTable('siterefpages'); return false;">[Hide]</a><br />
<div id="siterefpages_tbl"></div>
<br />Internal Referring Pages <a href="#" onclick="showTable('intrefpages'); return false;">[Show]</a> <a href="#" onclick="hideTable('intrefpages'); return false;">[Hide]</a><br />
<div id="intrefpages_tbl"></div>
<br />CTR by Page <a href="#" onclick="showTable('pagectr'); return false;">[Show]</a> <a href="#" onclick="hideTable('pagectr'); return false;">[Hide]</a><br />
<div id="pagectr_tbl"></div>
<br />Site Hits <a href="#" onclick="showTable('sitehits'); return false;">[Show]</a> <a href="#" onclick="hideTable('sitehits'); return false;">[Hide]</a><br />
<div id="sitehits_tbl"></div>
<br />
</section>
<div id="modal_window">
<span class="close_x"><a href="#" onclick="modalWindowClose();return false;">&times;</a></span>
<div class="modal_scroll"></div>
</div>
</body></html>