<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title><?=$this->page->description()->title;?></title>
<?=($this->page->description()->meta ?? "") ? "<meta name=\"description\" content=\"{$this->page->description()->meta}\" />" : "";?>
<link rel="stylesheet" type="text/css" href="<?=$this->domain->vars()->default_css;?>?ver=<?=$this->domain->vars()->css_version;?>" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<?=($this->page->description()->robots ?? "") ? "<meta name=\"robots\" content=\"noindex,nofollow\">" : "";?>
<?=($this->canonical_url) ? "<link rel=\"canonical\" href=\"". $this->canonical_url . "\" />" : "";?>
</head>
<body>