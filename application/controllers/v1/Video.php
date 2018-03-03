<?php

class Video extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('error_code');
        $this->load->helper('database');
        $this->load->helper('jwt');
        $this->load->helper('request');
        $this->load->helper('auth');
        $this->load->helper('rest_api');
        $this->auth = array(
            "table" => "`wp_apiuser`",
            "fields" => "`email`, `id`, `password`",
            "username_field" => "`email`",
            "password_field" => "`password`",
            "id_field" => "`id`",
            "service_name" => "360mea.social",
            "cookie_name" => "360mea_social_token"
        );
    }

    /**
     * Description: The following method is used to get the 360video list for the homepage
     */
    public function trending360VideoListHomepage()
    {
        // check if the respective cookies exist or not
        if (check_jwt_cookie($this->auth["service_name"], $this->auth["cookie_name"])) {
            $start = $_POST['page_no'];
            $limit = 5;
            $trending_360 = true;
            $trending_360_video = $this->VideoModel->get_whats_hot_video_list($limit, $start, $trending_360);  //p($trending_360_video); exit;
            if (empty($trending_360_video)) {
                $trending_360_video = array();
            } else {
                foreach ($trending_360_video as $key => $value) {

                    if (empty($value['video_thumbnail'])) {
                        $trending_360_video[$key]['video_thumbnail'] = "";
                    }

                    $tablename = 'wp_video_features';
                    $where_tot_like = array(
                        'video_id' => $value['id'],
                        'like_count' => 1
                    );
                    $total_like_count = $this->VideoModel->get_videos_counts($tablename, $where_tot_like);
                    $where_tot_view = array(
                        'video_id' => $value['id'],
                        'view' => 1
                    );
                    $total_view_count = $this->VideoModel->get_videos_counts($tablename, $where_tot_view);

                    // view count random number
                    if ($total_view_count <= 300) {
                        $random_num_gen = $total_view_count + 10125;
                    } else if (($total_view_count > 300 && $total_view_count < 1000)) {
                        $random_num_gen = $total_view_count + 20058;
                    } else {
                        $random_num_gen = $total_view_count + 30108;
                    }

                    // like count random number
                    if ($total_like_count <= 300) {
                        $random_num_gen_like = $total_like_count + 7118;
                    } else if (($total_like_count > 300 && $total_like_count < 1000)) {
                        $random_num_gen_like = $total_like_count + 15034;
                    } else {
                        $random_num_gen_like = $total_like_count + 20025;
                    }

                    $trending_360_video[$key]['view_count'] = $random_num_gen;
                    $trending_360_video[$key]['like_count'] = $random_num_gen_like;
                }
            }
            // returning data
            json_output(SUCCESS, array(
                "code" => SUCCESS,
                "message" => "Trending Video list fetched",
                "data" => $trending_360_video));
            return;
        } else {
            json_output(UNAUTHORIZED, array(
                "code" => UNAUTHORIZED,
                "message" => "Invalid cookies"
            ));
        }
    }


    /**
     * Description: The following method is used to get the profile details of the signed in user
     */
    public function getChannelListHomepage()
    {
        // check if the respective cookies exist or not
        if (check_jwt_cookie($this->auth["service_name"], $this->auth["cookie_name"])) {
            $json = file_get_contents('php://input');
            $json = json_decode($json, true);
            $user_id = isset($json['user_id']) ? $json['user_id'] : '0';   //$user_id = 10;

            $channel_list = $this->VideoModel->channel_list();
            if (empty($channel_list)) {
                $channel_list = array();
            } else {
                foreach ($channel_list as $key => $value) {

                    $channel_type = $value['live_type'];
                    $channel_id = $value['id'];
                    // if the channel is paid
                    if ($channel_type == "paid") {
                        $table = "mea_channel_transation";
                        $where_contn = array(
                            'channel_id' => $channel_id,
                            'user_id' => $user_id,
                            'expiary_date >=' => date('Y-m-d'),
                            'status' => 1
                        );
                        // find if the respective channel is purchased or not
                        $channel_purchased = $this->VideoModel->get_videos_counts($table, $where_contn);

                        if ($channel_purchased) {  // channel purchased
                            $channel_list[$key]['subscribed'] = 1;
                            $channel_expiry_date = $this->VideoModel->channel_expiry_date($user_id, $channel_id);

                            if (!$channel_expiry_date) {
                                $channel_list[$key]['expiry_date'] = $channel_expiry_date->expiary_date;
                            }
                        } else {
                            $channel_list[$key]['subscribed'] = 0;
                        }
                    } else {  // free channel
                        $table = "mea_channel_transation";
                        $where_contn = array(
                            'channel_id' => $channel_id,
                            'user_id' => $user_id,
                            'status' => 1
                        );
                        $channel_purchased = $this->VideoModel->get_videos_counts($table, $where_contn);

                        if ($channel_purchased) {  // channel purchased
                            $channel_list[$key]['subscribed'] = 1;
                            $channel_expiry_date = $this->VideoModel->channel_expiry_date($user_id, $channel_id);

                            if (!$channel_expiry_date) {
                                $channel_list[$key]['expiry_date'] = $channel_expiry_date->expiary_date;
                            }
                        } else {
                            $channel_list[$key]['subscribed'] = 0;
                        }
                    }
                }
            }
            // returning data
            json_output(SUCCESS, array(
                "code" => SUCCESS,
                "message" => "Channel list fetched",
                "data" => $channel_list));
            return;
        } else {
            json_output(UNAUTHORIZED, array(
                "code" => UNAUTHORIZED,
                "message" => "Invalid cookies"
            ));
        }
    }

    /**
     * Index method for completeness. Returns a JSON response with an
     * error message.
     */
    public function index()
    {
        header('Content-Type: application/json');
        echo json_encode(array(
            "code" => BAD_DATA,
            "message" => "No resource specified."
        ));
    }
}