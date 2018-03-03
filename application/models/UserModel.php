<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class UserModel extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Description: The following method is used get the user profile details
     * @param $user_id
     * @return mixed
     */
    public function getUserDetails($user_id)
    {
        return $this->db->get_where('wp_apiuser', array('id' => $user_id))->row();
    }

}