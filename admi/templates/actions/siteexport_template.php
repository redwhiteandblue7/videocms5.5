</nav></header>
<section class="edit"><div>
<?php
    if(sizeof($site_list))
    {
        $export_list = "";
        foreach($site_list as $row) {
            $site_ref = str_replace("\r", "", $row->site_ref);
            $site_ref = str_replace("\n", "", $site_ref);
            $export_list .= $row->site_name . "|" . $site_ref . "|paysite\n";
        }
?>
<form name="export_form" method="post" action="?a=SiteExport">
These are all the sites that do not have a post with the same title:
<textarea name="export_list" rows="30" cols="112"><?=$export_list;?></textarea>
</form>
<button id="copy_list">Copy List</button>
<?php
    }
    else
    {
        echo "Could not find any sites that do not have a post with the same name.";
    }
?>
</div></section>
<script>
//Script to allow the user to copy the list of sites to the clipboard
    document.getElementById("copy_list").addEventListener("click", function() {
        document.export_form.export_list.select();
        document.execCommand("copy");
    });
</script>