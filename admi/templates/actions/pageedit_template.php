<section class="edit"><div>
<?php
    if($this->action_status) {
        switch($this->action_status) {
            case "ok":
                echo "<p class=\"success centre\">Page saved</p>\n";
                break;
            case "copy":
                echo "<p class=\"success centre\">Page copied</p>\n";
                break;
            case "page_exists":
                echo "<p class=\"error centre\">Page already exists</p>\n";
                break;
            case "not_found":
                echo "<p class=\"error centre\">Page not found</p>\n";
                break;
            case "no_name":
                echo "<p class=\"error centre\">You must provide a page name</p>\n";
                break;
            case "no_filename":
                echo "<p class=\"error centre\">You must provide either a page filename or destination URL</p>\n";
                break;
            case "vars_empty":
                echo "<p>Add / Edit Page:</p>";
                break;
            default:
                echo "<p class=\"error centre\">Unknown error</p>\n";
                break;
        }
    }
?>
<form method="post" action="?a=PageEdit" name="copydata">
<input type="hidden" name="page_id" value="<?=$this->post_object->page_id;?>" />
<input type="hidden" name="id" value="<?=$this->post_object->id;?>" />
<?php
    echo "Copy from:<select name=\"copy_id\" onchange=\"this.form.submit();\">";
    echo "<option value=\"0\">--None--</option>\n";
    while($row = $pages->next()) {
            echo "<option value=\"" . $row->id . "\"";
            if($row->id == ($copy_id ?? 0)) echo " selected";
            echo ">" . $row->page_name . "</option>\n";
    }
    echo "</select></form>";
?>
<form method="post" action="?a=PageEdit&id=<?=$this->post_object->id;?>" name="pagedata"><br />
Page name: (including directory)<br />
<input type="text" name="page_name" value="<?=$this->post_object->page_name ?? "";?>" <?=($this->post_object->id) ? "readonly " : "";?>size="64" /><br /><br />
Page filename: (Filename to load)<br />
<input type="text" name="page_filename" value="<?=$this->post_object->page_filename ?? "";?>" size="96" /><br /><br />
Redirect URL: (only if the page redirects, overrides page filename)<br />
<input type="text" name="dest_url" value="<?=$this->post_object->dest_url ?? "";?>" size="96" /><br /><br />
<input type="hidden" name="page_id" value="<?=$this->post_object->page_id;?>" />
<input type="hidden" name="id" value="<?=$this->post_object->id;?>" />
Page description:<br />
<?php
    if(!($this->post_object->description ?? "")) {
        echo " [<a href=\"#\" onclick=\"document.getElementById('desc_txt').innerHTML='";
        echo "<?xml version=\'1.0\' encoding=\'UTF-8\'?>\\r\\n<pagetext>\\r\\n<title></title>\\r\\n<heading></heading>\\r\\n<content></content>\\r\\n<meta></meta>\\r\\n</pagetext>";
        echo "';return false;\">Fill XML template</a>] ";
    }
?>
[<a href="#" onclick="document.getElementById('desc_txt').innerHTML='';return false;">Clear</a>]
<br /><textarea id="desc_txt" name="description" rows="16" cols="112"><?=$this->post_object->description ?? "";?></textarea><br /><br />
<input type="submit" value="<?=($this->post_object->id) ? "Update Page" : "Add Page";?>" />
</form></div></section>