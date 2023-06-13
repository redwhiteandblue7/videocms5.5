<section class="admin">
<?php
	while($msg = $this->dbo->getNextMessage())
	{
		echo "$msg<br />\n";
	}
?>
</section>
</body></html>