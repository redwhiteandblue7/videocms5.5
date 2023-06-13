<?php
    require_once(HOME_DIR . 'admi/classes/action.class.php');

abstract class DisplayAction extends Action
{
	protected $tracked;
    protected $dbo;
    protected $results = [];
    protected $num_of_rows = 0;
    protected $pages = 0;
	protected $range_val;
	protected $daterange;
	protected $sort_by = "";

	abstract protected function sortby();

    public function __construct()
    {
		$this->getSantizedGetObject();
        $this->page();
        $this->action();
		$this->type();
		$this->id();
		$this->sortby();
    }

    public function process() : bool
    {
        return false;
    }

    public function prerender() : void
    {
        return;
    }

    public function render() : void
    {
        return;
    }

	protected function page()
	{
		$this->page = 0;
		if(isset($this->get_object->p) && is_numeric($this->get_object->p)) {
			$this->page = intval($this->get_object->p) - 1;
		}
	}

	protected function action()
	{
		$this->action = "";
		if(isset($this->get_object->a)) {
			$this->action = $this->get_object->a;
		}
	}

    public function pagination() : string
    {
        $page = $this->page;
        $pages = $this->pages;

		$text = "";

		if($page == 0)
			$text .= "&lt;&lt;Prev&nbsp;";
		else
			$text .= $this->anchor($page) . "&lt;&lt;Prev</a>&nbsp;";

		for($i = 1; $i <= $pages; $i++) {
			if($i == $page + 1) {
				$text .= $i . "&nbsp;";
			} elseif(($i < 6) || ($i > $pages - 5) || (($i < $page + 5) && ($i > $page - 5))) {
				$text .= $this->anchor($i) . "$i</a>&nbsp;";
			} else {
				if(($i % 10) == 0) $text .= "." . $this->anchor($i) . "$i</a>.";
			}
		}
		if($page >= $pages - 1)
			$text .= "&nbsp;Next&gt;&gt;";
		else
			$text .= "&nbsp;" . $this->anchor($page + 2) . "Next&gt;&gt;</a>&nbsp;";
		return $text;
    }

	private function anchor(int $page) : string
	{
		$action = $this->action;
		$type = $this->type;
		$id = $this->id;

		$type_param = ($type) ? "&type=$type" : "";
		$id_param = ($id) ? "&id=$id" : "";
		if($this->in_modal)
			return "<a href=\"#\" onclick=\"modalWindowInit('a=$action" . $id_param . $type_param . "&p=$page');return false;\">";
		else
			return "<a href=\"?a=$action" . $id_param . $type_param . "&p=$page\">";
	}

	protected function link(string $action_string) : string
	{
		if($this->in_modal)
			return "<a href=\"#\" onclick=\"modalWindowInit('$action_string');return false;\">";
		else
			return "<a href=\"?$action_string\">";
	}

	protected function sortlink(string $sort)
	{
		$action = $this->action;
		$type = $this->type;
		$id = $this->id;
		$page = $this->page;

		$type_param = ($type) ? "&type=$type" : "";
		$id_param = ($id) ? "&id=$id" : "";
		$page_param = ($page) ? "&p=" . ($page + 1) : "";

		if($this->in_modal)
			return "<a href=\"#\" onclick=\"modalWindowInit('a=$action" . $id_param . $type_param . $page_param . "&sortBy=$sort');return false;\">";
		else
			return "<a href=\"?a=$action" . $id_param . $type_param . $page_param . "&sortBy=$sort\">";
	}

	protected function sponsorsGen() : Generator
	{
		$this->dbo->fetchTable("sponsor_names", "sponsor_name", false);
		$sponsors = array();
		while($row = $this->dbo->getNextTableRow()) {
			$sponsors[] = $row;
		}
		foreach($sponsors as $s) {
			yield $s;
		}
	}

	protected function categoriesGen($vars_array)
	{
		$row = $this->dbo->getColumnFromTable("posts", "categories", true);
		//In some versions of MySQL/MariaDB, the row key is "Type", in others it's just row[1]
		if(array_key_exists("Type", $row)) {
			$cats = explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2",$row["Type"]));
		} else {
			$cats = explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2",$row[1]));
		}

		extract($vars_array);

		$i = 0;
		echo "<table>";
		foreach($cats as $category)
		{
			if(!$i) echo "<tr>";
			echo "<td>[<input type=\"checkbox\" name=\"$category\" value=\"$category\"";
			if(isset($$category)) echo " checked=\"checked\"";
			echo " />&nbsp;$category]</td>";
			if(++$i == 4)
			{
				$i = 0;
				echo "</tr>";
			}
		}
		if($i) echo "<td>&nbsp;</td></tr>";
		echo "</table>\n";
	}
}