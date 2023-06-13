</nav></header>
<section class="edit">
<?php
    if(sizeof($this->status_messages)) {
        foreach($this->status_messages as $msg) {
             echo "<p class=\"success centre\">$msg</p>\n";
        }
    }
?>
<table style="width:auto;">
<tr><td>Update tables. Alter tables to match schema defined in script.</td><td><a href="?a=UpdateTables"><button type="button">Update</button></a></td></tr>
<tr><td>Empty the Pages and ReferStrings tables</td><td><a href="?a=EmptyStats"><button type="button">Do it</button></a></td></tr>
<tr><td>Optimize the Stats and Pageloads etc tables</td><td><a href="?a=Optimise"><button type="button">Do it</button></a></td></tr>
<tr><td>Rebuild Pageloads etc tables</td><td><a href="?a=ResetTables"><button type="button">Do it</button></a></td></tr>
<tr><td>Create Stop Words Table</td><td><a href="?a=CreateStopWords"><button type="button">Do it</button></a></td></tr>
<tr><td>Update Googlebot visits table</td><td><a href="?a=StoreGooglebots"><button type="button">Do it</button></a></td></tr>
<tr><td>Show Googlebots visits</td><td><a href="?a=ShowGooglebots"><button type="button">Go for it</button></a></td></tr>
<tr><td>Upgrade Dynamic Pages from  Engine4.x to EngineCMS 5.1</td><td><a href="?a=UpgradePages"><button type="button">Go for it</button></a></td></tr>
<tr><td>Upgrade Dynamic Pages from  EngineCMS 5.0 to EngineCMS 5.1</td><td><a href="?a=Upgrade5Pages"><button type="button">Go for it</button></a></td></tr>
<tr><td>Upgrade Posts from Engine4.x to EngineCMS 5.4</td><td><a href="?a=UpgradePosts"><button type="button">Go for it</button></a></td></tr>
<tr><td>Upgrade Post descriptions from  Engine4.x to EngineCMS 5.x</td><td><a href="?a=UpgradePostdesc"><button type="button">Go for it</button></a></td></tr>
<tr><td>Upgrade Link descriptions from  Engine4.x to EngineCMS 5.1</td><td><a href="?a=UpgradeLinkdesc"><button type="button">Go for it</button></a></td></tr>
<tr><td>Convert FLVs to posts</td><td><a href="?a=UpgradeFlvs"><button type="button">Convert</button></a></td></tr>
<tr><td>Add trades to posts</td><td><a href="?a=AddTrades"><button type="button">Do it</button></a></td></tr>
<tr><td>Upgrade posts to XML</td><td><a href="?a=ConvertPostsXml"><button type="button">Do it</button></a></td></tr>
<tr><td>Analyse posts</td><td><a href="?a=AnalysePosts"><button type="button">Do it</button></a></td></tr>
<tr><td>Re-rank posts</td><td><a href="?a=RankPosts"><button type="button">Do it</button></a></td></tr>
<tr><td>Export Similarweb URLs</td><td><a href="?a=ExportSimilarweb"><button type="button">Do it</button></a></td></tr>
<tr><td>Import Similarweb Data</td><td><a href="?a=ImportSimilarweb"><button type="button">Do it</button></a></td></tr>
</table>
</section>