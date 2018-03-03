<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class VideoModel extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Description: The following method is used get the youtube video listing
     * @return array
     */
    public function getYouTubeVideoListing()
    {
        // loading the configuration file
        $config = parse_ini_file(__DIR__ . '/../../config.ini');

        $data = file_get_contents("https://www.googleapis.com/youtube/v3/playlistItems?part=snippet%2CcontentDetails&maxResults=50&playlistId=" . $config["youtube_play_list"] . "&key=" . $config["youtube_key"]);
        $result = json_decode($data, true);
        $datas = array();
        foreach ($result['items'] as $res) {
            $title = $res['snippet']['title'];
            $publishedAt = $res['snippet']['publishedAt'];
            $image = $res['snippet']['thumbnails']['standard']['url'];
            $video_id = $res['contentDetails']['videoId'];

            if (empty($image)) {
                $image = "";
            }

            $datas[] = ([
                'video_title' => $title,
                'publishedAt' => $publishedAt,
                'video_thumbnail' => $image,
                'id' => $video_id,
            ]);
        }
        $youtube_array = array_slice($datas, 0, 5);
        if (empty($youtube_array)) {
            $youtube_array = array();
        }

        return $youtube_array;
    }

    /**
     * Description:  The following method is used to get the channel list
     * @return array
     */
    public function channel_list()
    {
        $this->db->select("SQL_CALC_FOUND_ROWS s.*", FALSE);
        $this->db->from('mea_channel s');

        $this->db->where('status', 1);

        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return [];
        }
    }

    /**
     * Description: The following method is used to get the videos count
     * @param $table
     * @param array $where
     * @return mixed
     */
    function get_videos_counts($table, $where = array())
    {
        $this->db->from($table);
        $this->db->where($where);
        return $this->db->count_all_results();
    }


    /**
     * Description: The following method is used to get the channel expiry date for the respective user
     * @param $user_id
     * @return bool
     */
    public function channel_expiry_date($user_id)
    {
        $this->db->select('expiary_date');
        $this->db->where('user_id', $user_id);
        $this->db->where('channel_id', 4);
        $query = $this->db->get('mea_channel_transation');

        if ($query->num_rows() > 0) {
            return $query->row();
        }

        return null;
    }

    /**
     * Description: the following method is used to get the whats hot video list
     * @param string $limit
     * @param string $start
     * @param $trending_360
     * @return bool
     */
    public function get_whats_hot_video_list($limit = '', $start = '', $trending_360)
    {

        $this->db->select("SQL_CALC_FOUND_ROWS v.*,c.category_name", FALSE);
        $this->db->from('wp_video v');
        $this->db->join('wp_categories c', 'c.id = v.category_id', 'left');

        $this->db->where('v.whats-hot', 0);
        $this->db->where('v.popular', 0);
        $this->db->where('v.status', 0);
        $this->db->where('v.user_id', 0);

        $this->db->limit($limit, $start);
        if (!$trending_360) {
            $this->db->order_by("v.display_order", "asc");
        } else {
            $this->db->order_by("v.trending_order", "asc");
        }
        $query = $this->db->get();           //echo $this->db->last_query(); exit;
        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return FALSE;
        }
    }
}