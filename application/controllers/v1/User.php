<?php

class User extends CI_Controller
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
     * Description: The following method is used to provide the respective jwt_token for authorization
     */
    public function login()
    {
        header('Content-Type: application/json');
        if ($this->input->method(true) != 'POST') {
            echo json_encode(array("message:" => "Use the HTTP POST method to login to the system."));
            return;
        } else
            if (check_jwt_cookie($this->auth["service_name"], $this->auth["cookie_name"])) {
                echo json_encode(regenerate_jwt_cookie($this->auth["service_name"], $this->auth["cookie_name"]));
                return;
            } else {
                echo json_encode(authorize($this->auth["table"], $this->auth["fields"],
                    $this->auth["username_field"], $this->auth["password_field"], $this->auth["id_field"],
                    $_POST["username"], hash("md5", $_POST["password"], false),
                    $this->auth["service_name"], $this->auth["cookie_name"]
                ));
                return;
            }
    }

    /**
     * Description: The following method is used to get the profile details of the signed in user
     */
    public function getProfileDetails()
    {
        // check if the respective cookies exist or not
        if (check_jwt_cookie($this->auth["service_name"], $this->auth["cookie_name"])) {
            // check if the user_id is provided
            if (empty($_POST['user_id'])) {
                echo json_encode(array(
                    "code" => BAD_DATA,
                    "message" => "user_id missing from input params"
                ));
                return;
            }
            $user_id = $_POST['user_id'];
            $user_details = $this->UserModel->getUserDetails($user_id);
            if (isset($user_details)) {
                echo json_encode(array(
                    "code" => SUCCESS,
                    "message" => "User profile details fetched",
                    "data" => [
                        "user_id" => $user_details->id,
                        "email" => $user_details->email,
                        "first_name" => $user_details->firstname,
                        "last_name" => $user_details->lastname,
                        "phone_number" => $user_details->phonenumber,
                        "gender" => $user_details->gender,
                    ]));
                return;
            }
        } else {
            echo json_encode(array(
                "code" => UNAUTHORIZED,
                "message" => "Invalid cookies"
            ));
        }
        return;
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