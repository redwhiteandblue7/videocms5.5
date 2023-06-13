<?php
    require_once(HOME_DIR . 'admi/classes/analytics/analyseaction.class.php');

class StatsLineAction extends AnalyseAction
{
	private $stat;

    public function prerender() : void
    {
		$stat_type = $_GET["type"];
		$daterange = $_GET["daterange"];
		$t = time();

		switch($stat_type) {
			case "range":
				$this->getStatInDateRange($daterange, 0, 0, "Since " . gmdate("H:i:s D, d-M-y", $daterange));
				break;
			case "range1":
				$this->getStatInDateRange($daterange, 0, 1, " - group 1");
				break;
			case "range2":
				$this->getStatInDateRange($daterange, 0, 2, " - group 2");
				break;
			case "range3":
				$this->getStatInDateRange($daterange, 0, 3, " - group 3");
				break;
			case "range4":
				$this->getStatInDateRange($daterange, 0, 4, " - group 4");
				break;
			case "lasthour":
				$this->getStatInDateRange($t - 3600, 0, 0, "Last hour");
				break;
			case "1hour":
				$this->getStatInDateRange($t - 7200, $t - 3600, 0, "One hour ago");
				break;
			case "2hours":
				$this->getStatInDateRange($t - 10800, $t - 7200, 0, "Two hours ago");
				break;
			case "yesterday":
				$gmt_td = (intval(date("O")) / 100) * 3600;		//get difference between time zone and GMT
				$datearray = getdate($t - $gmt_td);
				$day = $datearray["mday"];
				$month = $datearray["mon"];
				$year = $datearray["year"];

				$today = gmmktime(0, 0, 0, $month, $day, $year);
				$yesterday = $today - (60 * 60 * 24);
				$then = $t - (60 * 60 * 24);
				if($then > $yesterday + 3600) {
					$this->getStatInDateRange($yesterday, $then, 0, "This time yesterday");
				} else {
					$this->getStatInDateRange($yesterday, $today, 0, "Yesterday");
				}
				break;
			case "all":
                $this->getStatAll();
				break;
			default:
				echo "Unknown range $stat_type";
				break;
		}
    }

	public function render() : void
	{
		extract($this->stat);
        include('templates/analytics/statsline_template.php');
	}

	private function getStatInDateRange(int $date_start, int $date_end, int $group, string $range)
	{
        $dbo = new AnalyticsDB();
        $dbo->setPrefix();
        $dbo->setDates($date_start, $date_end);
        $dbo->setGroup($group);
    
		$stat = array();

        $stat["range"] = $range;

		$stat["newvisits"] = $dbo->countStats("new");
		$stat["returnvisits"] = $dbo->countStats("return");
		$stat["uniques"] = $dbo->countStats("uniques");
		$stat["pageloads"] = $dbo->countStats("pageloads");
		$stat["clickthrus"] = $dbo->countStats("clickthrus");
		$stat["totrades"] = $dbo->countStats("trades");
		$stat["tosponsors"] = $dbo->countStats("sponsors");
		$stat["badclicks"] = $dbo->countStats("bad");
		$stat["searches"] = $dbo->countStats("searches");
		$stat["googledotcom"] = $dbo->countStats("google");
		$stat["bingdotcom"] = $dbo->countStats("bing");
		$stat["tracked"] = $dbo->countStats("tracked");
		$stat["pagetime"] = $dbo->countStats("pagetime");

		$stat["otherse"] = $stat["searches"] - $stat["tracked"];
        if(isset($_SESSION["se_track_id"])) {
            if(isset($_SESSION["se_bing_id"])) {
                if($_SESSION["se_track_id"] != $_SESSION["se_bing_id"]) $stat["otherse"] -= $stat["bingdotcom"];
            }
            if(isset($_SESSION["se_google_id"])) {
                if($_SESSION["se_track_id"] != $_SESSION["se_google_id"]) $stat["otherse"] -= $stat["googledotcom"];
            }
        }
		$stat["registrations"] = $dbo->countStats("regs");
		$stat["logins"] = $dbo->countStats("logins");

		if($stat["clickthrus"] > 0) {
			$stat["totradespc"] = floor(($stat["totrades"] * 10000) / $stat["clickthrus"]) / 100;
			$stat["clickprod"] = floor(($stat["clickthrus"] * 1000) / $stat["uniques"]);
		} else {
			$stat["totradespc"] = 0;
			$stat["clickprod"] = 0;
		}

		if($stat["tosponsors"] > 0) {
			$stat["sponsorsprod"] = floor(($stat["tosponsors"] * 1000) / $stat["uniques"]);
		} else {
			$stat["sponsorsprod"] = 0;
		}

        $this->stat = $stat;
	}

    private function getStatAll()
    {
        $dbo = new AnalyticsDB();
        $dbo->setPrefix();
    
        /** 
         * !Change !! 
        */
        $domain_id = 1;

		$stat = array();

        $stat["newvisits"] = "-";
        $stat["returnvisits"] = "-";
        $stat["bookmarkers"] = "-";
        $stat["pageloads"] = $dbo->sumValuesFromTableRowsById("daily_stats", "domain_id", $domain_id, "page_loads");
        $stat["clickthrus"] = $dbo->sumValuesFromTableRowsById("daily_stats", "domain_id", $domain_id, "click_thrus");
        $stat["totrades"] = "-";
        $stat["tosponsors"] = "-";
        $stat["badclicks"] = "-";
        $stat["uniques"] = $dbo->sumValuesFromTableRowsById("daily_stats", "domain_id", $domain_id, "visitors");

        if($stat["clickthrus"] > 0) {
            $stat["clickprod"] = floor(($stat["clickthrus"] * 1000) / $stat["uniques"]);
        } else {
            $stat["clickprod"] = 0;
        }

        $stat["sponsorsprod"] = "-";

        $stat["searches"] = $dbo->sumValuesFromTableRowsById("daily_stats", "domain_id", $domain_id, "searches");
        $stat["tracked"] = "-";
        $stat["googledotcom"] = "-";
        $stat["bingdotcom"] = "-";
        $stat["otherse"] = "-";
        $stat["pagetime"] = "-";
        $stat["registrations"] = $dbo->countRows("users");
        $stat["logins"] = $dbo->sumColumn("users", "total_logins");
        $stat["range"] = "All";
        $this->stat = $stat;
    }

}