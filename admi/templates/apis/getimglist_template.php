<form>
<select name="filename" size="10" onclick="loadImgFile(this)">
<?php
	foreach($this->files as $filename) {
		echo "<option value=\"$filename\">$filename</option>";
	}
?>
</select>
</form>