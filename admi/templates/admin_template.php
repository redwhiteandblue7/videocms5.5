<?php
	include('header_template.php');
?>
<header class="row">
<?php
//	echo "Current domain_id: " . ($_SESSION["domain_id"] ?? "") . ", old domain_id: " . ($_SESSION["old_domain_id"] ?? "");
    if($this->num_of_domains) {
		include('top_menu_template.php');
	} elseif($action == "EditDomain") {
		echo "<br />Domains table is empty. Create the first domain below.";
	}
?>
