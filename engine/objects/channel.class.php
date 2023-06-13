<?php
    require_once(DB_PATH .'channels.db.class.php');
    require_once(OBJECTS_PATH . 'domain.class.php');

class Channel
{
    private $channels = [];
    private $result_pointer = 0;
    private $dbo;
    private $row = stdClass::class;

    public $channel_name;
    public $error_type = "";

    public function __construct(int $channel_id = 0)
    {
        $this->dbo = new ChannelsDB();
        $this->dbo->setPrefix();

        if($channel_id) {
            if($this->row = $this->dbo->fetchChannel($channel_id)) {
                $this->channel_name = $this->row->channel_name;
            }
        }
    }

    public function channels(int $start = 0, int $limit = 9999, int $user_id = 0) : int
    {
        $num_of_rows = $this->dbo->fetchChannels($start, $limit, $user_id);
        $this->channels = $this->dbo->results();
        $this->result_pointer = 0;

        return $num_of_rows;
    }

    public function getChannelByName(string $channel_name) : bool
    {
        if($this->row = $this->dbo->fetchRow("channels", "channel_name", $channel_name, true)) {
            $this->channel_name = $this->row->channel_name;
            return true;
        }

        return false;
    }

    public function next()
    {
        if($this->result_pointer < sizeof($this->channels)) {
            $row = $this->channels[$this->result_pointer++];
            $this->row = $row;
            return $row;
        }

        return "";
    }

    public function vars() : mixed
    {
        if(isset($this->row->channel_id)) {
            return $this->row;
        }
        return "";
    }

    public function numRows() : int
    {
        return sizeof($this->channels);
    }

    /** save the video data to the database
     * @param stdClass object containing channel data
     * @return bool true if saved successfully, false if not
     */
    public function save(stdClass $vars) : bool
    {
        if(!$vars->channel_name) {
            $this->error_type = "no_name";
            return false;
        }

        if(!isset($vars->display_state) || !$vars->display_state) {
            $this->error_type = "no_state";
            return false;
        }

        $this->row = $vars;
        if($vars->id) {
            $this->dbo->updateRow("channels", $vars, true);
            return true;
        }
        $channel_id = $this->dbo->maxColumn("channels", "channel_id", true);
        if($channel_id < 1000) {
            $channel_id = 1000;
        } else {
            $channel_id += 7;
        }
        $vars->channel_id = $channel_id;
        if($this->dbo->insertRow("channels", $vars, "channel_name", true)) {
            $this->row->id = $this->dbo->getInsertID();
            $this->row->channel_id = $channel_id;
            return true;
        }
        $this->error_type = "channel_exists";
        //set the channel id to 0 so that the form is displayed again
        $vars->channel_id = 0;
        return false;
    }

    /** Get a list of the sites from the sites table
     * @return array of sites 
     */
    public function getSites() : array
    {
        return $this->dbo->fetchTable("sites", "site_name", true);
    }

    /** Get an array of values being the possible values from an enum column in the channels table
     * @return array of values
     */
    public function getDisplayStates() : array
    {
        return $this->dbo->getEnumValues("channels", "display_state", true);
    }
}