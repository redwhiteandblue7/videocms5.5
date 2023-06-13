<?php
    require_once(DB_PATH ."stats.db.class.php");

//class to provide functions that read page data using various filters
class PagedataDB extends StatsDB
{
    public function fetchPageloads($display_type, $start, $end, $id = 0)
    {
        $prefix = $this->table_prefix;
        $daterange = $this->daterange;
        $sort_by = $this->sort_by;

        if(!$display_type) $display_type = "all";

        switch($display_type) {
            case "new":
                $description = "New visits";
                $whereclause = " flag='N' and";
                break;
            case "nandr":
                $description = "New and Returning visits";
                $whereclause = " (flag='N' or flag='R') and";
                break;
            case "all":
                $description = "All Pageloads";
                $whereclause = "";
                break;
            case "real":
                $description = "Real Pageloads";
                $whereclause = " flag!='B' and ";
                break;
            case "domain":
                $description = "Pageloads from domain $id";
                $whereclause = " {$prefix}_pageloads.dom_id=$id and (flag='N' or flag='R') and ";
                break;
            case "alldomain":
                $description = "All pageloads and clicks from domain $id";
                $whereclause = " {$prefix}_pageloads.first_dom_id=$id and ";
                break;
            case "visitor":
                $description = "Pageloads from visitor $id";
                $whereclause = " {$prefix}_pageloads.visitor_id=$id and ";
                break;
            case "clicks":
                $description = "Click thrus";
                $whereclause = " flag='C' and ";
                break;
            case "bots":
                $description = "Bots and Bad traffic";
                $whereclause = " flag='B' and ";
                break;
            case "epages":
                $description = "Entry page hits to page #$id";
                $whereclause = " (flag='N' or flag='R') and {$prefix}_pageloads.page_id=$id and ";
                break;
            case "spages":
                $description = "Search engine entry page hits to page #$id";
                $whereclause = " (flag='N' or flag='R') and {$prefix}_pageloads.page_id=$id and searches.id IS NOT NULL and ";
                break;
            case "pages":
                $description = "Page hits to page #$id";
                $whereclause = " {$prefix}_pageloads.page_id=$id and ";
                break;
            case "sclicks":
                $description = "Site hits to site #$id";
                $whereclause = " {$prefix}_pageloads.site_id=$id and ";
                break;
            default:
                return 0;
        }

        $groupclause = "";
        if($this->group_filter > 0) $groupclause = " testgroup=$this->group_filter and";

        $this->messages[] =  $description . " since " . gmdate("H:i:s D, d-M-y", $daterange) ;

        switch($sort_by) {
            case "Time":
                $order = "stime";
                break;
            case "TimeR":
                $order = "stime desc";
                break;
            case "IP":
                $order = "visitor_id, stime";
                break;
            case "Referrer":
                $order = "{$prefix}_pageloads.dom_id, stime";
                break;
            case "Client":
                $order = "agent_id";
                break;
            case "Session":
                $order = "session_time, stime";
                break;
            default:
                $order = "stime";
                break;
        }

        $q = "select count(1) as cnt from {$prefix}_pageloads
                left join searches on {$prefix}_pageloads.dom_id=searches.dom_id
                where" . $whereclause . $groupclause . " stime>=$daterange";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $num_of_rows = $r->fetch_row()[0];
        $r->close();

        if($this->group_filter > 0) $groupclause = " {$prefix}_visitors.testgroup=$this->group_filter and";
        $q = "select
                stime,
                ctime,
                flag,
                country,
                {$prefix}_pageloads.visitor_id,
                {$prefix}_visitors.user_id,
                cookie_id,
                referdomains.domainstring,
                referstring,
                {$prefix}_pagenames.pagename,
                conv({$prefix}_pageloads.ip_address,10,16) as ip_address,
                conv({$prefix}_visitors.ip_address,10,16) as visitor_ip,
                useragent,
                first_time,
                last_time,
                session_time,
                site_name,
                '' as title,
                r1.domainstring as refdom,
                r2.domainstring as linkname
                from {$prefix}_pageloads
                left join {$prefix}_visitors on {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id
                left join {$prefix}_referstrings on {$prefix}_pageloads.ref_id={$prefix}_referstrings.ref_id
                left join referdomains on {$prefix}_pageloads.dom_id=referdomains.dom_id
                left join {$prefix}_pagenames on {$prefix}_pageloads.pagename_id={$prefix}_pagenames.pagename_id
                left join useragents on {$prefix}_visitors.agent_id=useragents.agent_id
                left join {$prefix}_sites on {$prefix}_pageloads.site_id={$prefix}_sites.site_id
                left join {$prefix}_hardlinks as r2 on {$prefix}_pageloads.hardlink_id=r2.hardlink_id
                left join {$prefix}_hardlinks as r1 on {$prefix}_pageloads.first_reflink_id=r1.ref_code
                left join searches on {$prefix}_pageloads.dom_id=searches.dom_id
                where $whereclause $groupclause stime>=$daterange
                order by $order
                limit $start, $end
                ";

        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $i = $start;

        while($row = $r->fetch_assoc()) {
            $row["rownum"] = $i++;
            $this->table_rows[] = $row;
        }
        $r->close();
        return $num_of_rows;
    }

    public function fetchRawData($start, $end, $search_string = "")
    {
        $prefix = $this->table_prefix;
        $daterange = $this->daterange;
//        $sort_by = $this->sort_by;
        $sort_by = "Time";

        $search = ($search_string) ? "and useragent like '%$search_string%'" : "";

        $q = "select count(1) as cnt from {$prefix}_stats where
            stime>=$daterange
            $search
            ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q");
        $row = $r->fetch_assoc();
        $num_of_rows = $row["cnt"];
        $r->close();

        switch($sort_by) {
            case "Time":
                $order = "stime";
                break;
            case "TimeR":
                $order = "stime desc";
                break;
            default:
                $order = "stime";
                break;
        }

        $q = "select
                stime,
                ctime,
                referrer,
                pagename,
                country,
                conv(ip_address,10,16) as ip_address,
                cookie_id,
                first_cookie_time,
                last_cookie_time,
                useragent,
                browser,
                testgroup
                from
                {$prefix}_stats
                where
                stime>=$daterange
                $search
                order by $order
                limit $start, $end
                ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $i = $start;

        while($row = $r->fetch_assoc()) {
            $row["rownum"] = $i++;
            $this->table_rows[] = $row;
        }
        $r->close();
        return $num_of_rows;
    }

    public function fetchVisitors($visitor_id, $start, $end)
    {
        $prefix = $this->table_prefix;
        $daterange = $this->daterange;
        $sort_by = $this->sort_by;

        if($visitor_id) {
            $whereclause = " where visitor_id=$visitor_id and";
        } else {
            $whereclause = " where";
        }

        $q = "select count(1) as cnt from {$prefix}_visitors" . $whereclause . " last_time>=$daterange";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);
        $num_of_rows = $r->fetch_row()[0];
        $r->close();

        switch($sort_by) {
            case "Time":
                $order = "last_time";
                break;
            case "TimeR":
                $order = "last_time desc";
                break;
            case "Client":
                $order = "agent_id, last_time";
                break;
            default:
                $order = "last_time";
                break;
        }

        $q = "select
                visitor_id,
                screentype,
                country,
                conv(ip_address,10,16) as ip_address,
                useragent,
                browser,
                first_time,
                last_time,
                {$prefix}_visitors.user_id,
                first_dom_id,
                first_reflink_id,
                referdomains.domainstring as domain,
                {$prefix}_hardlinks.domainstring as linkname,
                (select count(1) from {$prefix}_pageloads where {$prefix}_pageloads.visitor_id={$prefix}_visitors.visitor_id and flag='C') as visits
                from
                {$prefix}_visitors
                left join useragents on {$prefix}_visitors.agent_id=useragents.agent_id
                left join browsers on {$prefix}_visitors.browser_id=browsers.browser_id
                left join referdomains on referdomains.dom_id={$prefix}_visitors.first_dom_id
                left join {$prefix}_hardlinks on {$prefix}_visitors.first_reflink_id={$prefix}_hardlinks.ref_code
                $whereclause
                last_time>=$daterange
                order by $order
                limit $start, $end
                ";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);

        while($row = $r->fetch_assoc()) {
            $this->table_rows[] = $row;
        }
        $r->close();
        return $num_of_rows;
    }

    public function removeVisitorData($visitor_id) : int
    {
        $prefix = $this->table_prefix;

        $q = "delete from {$prefix}_pageloads where visitor_id=$visitor_id";
        $r = $this->dbc->query($q) or die($this->dbc->error . " query was $q in " . __FILE__ . " at line " . __LINE__);

        return $this->dbc->affected_rows;
    }
}