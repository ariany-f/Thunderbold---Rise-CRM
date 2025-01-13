<?php

namespace App\Libraries;

use App\Controllers\App_Controller;
use App\Controllers\Security_Controller;

class Outlook_calendar {

    private $responseCode = 0;
    private $ci;
    private $cis;
    private $client_id;
    private $client_secret;
    private $login_url;
    private $graph_url;
    private $redirect_uri;

    public function __construct() {
        $this->ci = new App_Controller();
        $this->cis = new Security_Controller(false);
        $this->client_id = get_setting("outlook_calendar_client_id");
        $this->client_secret = get_setting('outlook_calendar_client_secret');
        $this->login_url = "https://login.microsoftonline.com/common/oauth2/v2.0";
        $this->graph_url = "https://graph.microsoft.com/beta/me/";
        $this->redirect_uri = get_uri("microsoft_api/save_outlook_calendar_access_token");

        //load EmailReplyParser resources
      //  require_once(APPPATH . "ThirdParty/Imap/EmailReplyParser/vendor/autoload.php");
    }

    public function run_import() {
        $events = $this->do_request("GET", 'calendar/events',[], true, "Prefer: outlook.timezone=\"America/Sao_Paulo\"");

        foreach ($events->value as $event) {           
            $this->_create_event_from_calendar($event);
        }
    }

    //authorize connection
    public function authorize() {
        $url = "$this->login_url/authorize?";
        $auth_array = array(
            "client_id" => $this->client_id,
            "response_type" => "code",
            "redirect_uri" => $this->redirect_uri,
            "response_mode" => "query",
            "scope" => "Calendars.ReadWrite offline_access User.Read",
        );

        foreach ($auth_array as $key => $value) {
            $url .= "$key=$value";

            if ($key !== "scope") {
                $url .= "&";
            }
        }

        app_redirect($url, true);
    }

    private function common_error_handling_for_curl($result, $err, $decode_result = true) {
        if ($decode_result) {
            try {
                $result = json_decode($result);
            } catch (\Exception $ex) {
                echo json_encode(array("success" => false, 'message' => $ex->getMessage()));
                log_message('error', $ex); //log error for every exception
                exit();
            }
        }

        if ($err) {
            //got curl error
            echo json_encode(array("success" => false, 'message' => "cURL Error #:" . $err));
            log_message('error', $err); //log error for every exception
            exit();
        }

        if (isset($result->error_description) && $result->error_description) {
            //got error message from curl
            echo json_encode(array("success" => false, 'message' => $result->error_description));
            log_message('error', $result->error_description); //log error for every exception
            exit();
        }

        if (isset($result->error) && $result->error &&
                isset($result->error->message) && $result->error->message &&
                isset($result->error->code) && $result->error->code !== "InvalidAuthenticationToken") {
            //got error message from curl
            echo json_encode(array("success" => false, 'message' => $result->error->message));
            log_message('error', $result->error->message); //log error for every exception
            exit();
        }

        return $result;
    }

    //fetch access token with auth code and save to database
    public function save_access_token($code, $is_refresh_token = false) {
        $fields = array(
            "client_id" => $this->client_id,
            "client_secret" => $this->client_secret,
            "redirect_uri" => $this->redirect_uri,
            "scope" => "Calendars.ReadWrite offline_access User.Read",
            "grant_type" => "authorization_code",
        );

        if ($is_refresh_token) {
            $fields["refresh_token"] = $code;
            $fields["grant_type"] = "refresh_token";
        } else {
            $fields["code"] = $code;
        }

        $fields_string = http_build_query($fields);

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, "$this->login_url/token");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Cache-Control: no-cache",
            "Content-Type: application/x-www-form-urlencoded",
        ));

        //So that curl_exec returns the contents of the cURL;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //execute post
        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        $result = $this->common_error_handling_for_curl($result, $err);

        if (!(
                (!$is_refresh_token && isset($result->access_token) && isset($result->refresh_token)) ||
                ($is_refresh_token && isset($result->access_token))
                )) {
            echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
            exit();
        }

        if ($is_refresh_token) {
            //while refreshing token, refresh_token value won't be available
            $result->refresh_token = $code;
        }
      
        // Save the token to database
        $new_access_token = json_encode($result);

        if ($new_access_token) {

            $user = $this->ci->Users_model->get_one($this->cis->login_user->id);
            
            $user->outlook_calendar_access_token = $new_access_token;
            
            $this->ci->Users_model->ci_save($user, $this->cis->login_user->id);

            $user = $this->ci->Users_model->get_one($this->cis->login_user->id);
           
            if (!$is_refresh_token) {
                //store email address for the first time
                $user_info = $this->do_request("GET"); 
                if (isset($user_info->userPrincipalName) && $user_info->userPrincipalName) {
                    $user->outlook_calendar_email = $user_info->userPrincipalName;
                } else { 
                    echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
                    exit();
                }
            }
            $user->outlook_calendar_authorized = 1;
          
            $this->ci->Users_model->ci_save($user, $this->cis->login_user->id);
        }
    }

    private function headers($access_token, $extra_header = "") {
        if(!empty($extra_header)){
            return array(
                'Authorization: Bearer ' . $access_token,
                'Content-Type: application/json',
                $extra_header
            );
        }
        else
        {
            return array(
                'Authorization: Bearer ' . $access_token,
                'Content-Type: application/json'
            );
        }
    }

    private function do_request($method, $path = "", $body = array(), $decode_result = true, $extra_header = "") {
        if (is_array($body)) {
            // Treat an empty array in the body data as if no body data was set
            if (!count($body)) {
                $body = '';
            } else {
                $body = json_encode($body);
            }
        }

        if($this->cis->login_user)
        {
            $user = $this->ci->Users_model->get_one($this->cis->login_user->id);
            if(!$user->outlook_calendar_authorized)
            {
                echo json_encode(array("success" => false, 'message' => app_lang('error_occurred')));
                exit();
            }
            $oauth_access_token = $user->outlook_calendar_access_token;
            $oauth_access_token = json_decode($oauth_access_token);

            $method = strtoupper($method);
            $url = $this->graph_url . $path;

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers($oauth_access_token->access_token, $extra_header));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
            if (in_array($method, array('DELETE', 'PATCH', 'POST', 'PUT', 'GET'))) {

                // All except DELETE can have a payload in the body
                if ($method != 'DELETE' && strlen($body)) {
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                }

                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            }

            $result = curl_exec($ch);
            $err = curl_error($ch);
            $this->responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $result = $this->common_error_handling_for_curl($result, $err, $decode_result);

            if (isset($result->error->code) && $result->error->code === "InvalidAuthenticationToken") {
                //access token is expired
                $this->save_access_token($oauth_access_token->refresh_token, true);
                return $this->do_request($method, $path, $body, $decode_result);
            }
        }
        else
        {
            $users = $this->ci->Users_model->get_details(array('outlook_calendar_authorized' => 1))->getResults();
            foreach($users as $user)
            {
                $oauth_access_token = $user->outlook_calendar_access_token;
                $oauth_access_token = json_decode($oauth_access_token);
        
                $method = strtoupper($method);
                $url = $this->graph_url . $path;
        
                $ch = curl_init();
        
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers($oauth_access_token->access_token, $extra_header));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
               
                if (in_array($method, array('DELETE', 'PATCH', 'POST', 'PUT', 'GET'))) {
        
                    // All except DELETE can have a payload in the body
                    if ($method != 'DELETE' && strlen($body)) {
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
                    }
        
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                }
        
                $result = curl_exec($ch);
                $err = curl_error($ch);
                $this->responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
        
                $result = $this->common_error_handling_for_curl($result, $err, $decode_result);
        
                if (isset($result->error->code) && $result->error->code === "InvalidAuthenticationToken") {
                    //access token is expired
                    $this->save_access_token($oauth_access_token->refresh_token, true);
                    return $this->do_request($method, $path, $body, $decode_result);
                }
            }
        }
        

        return $result;
    }

    private function _create_event_from_calendar($event_info = "") {
        if ($event_info) {
          
            $title = $event_info->subject;

            // Extraindo os valores das propriedades
            $start_date_time = $event_info->start->dateTime;
            $end_date_time = $event_info->end->dateTime;

            // Convertendo para variáveis
            $start_date = substr($start_date_time, 0, 10); // '2024-07-31'
            $start_time = substr($start_date_time, 11, 8);  // '00:00:00'

            $end_date = substr($end_date_time, 0, 10); // '2024-08-01'
            $end_time = substr($end_date_time, 11, 8);  // '00:00:00'

            $description = $event_info->bodyPreview;
            $outlook_event_id = $event_info->id;

            $created_by = $this->cis->login_user->id;

            $options = array(
                "outlook_event_id" => $outlook_event_id
            );
            $event = $this->ci->Events_model->get_one_where($options);
           
            if ($event->id) {
                if($event->deleted)
                {
                  //  $event->deleted = 0;
                  //  $this->ci->Events_model->ci_save($event, $event->id);
                }
            } else {
                $events_data = array(
                    "title" => $title,
                    "start_date" => $start_date,
                    "start_time" => $start_time,
                    "end_date" => $end_date,
                    "end_time" => $end_time,
                    "description" => $description,
                    "created_by" => $created_by,
                    "outlook_event_id" => $outlook_event_id
                );

                $event_id = $this->ci->Events_model->ci_save($events_data);
             
                if ($event_id) {

                    if ($event_id) {
                     log_notification("event_created", array("event_id" => $event_id));
                    }
                }
            }
        }
    }

    public function save_event($user_id, $event_id)
    {
        $user = $this->ci->Users_model->get_one($user_id);
        $event = $this->ci->Events_model->get_one($event_id);

        $outlook_event =  $this->do_request("POST", "calendar/events", array(
            "subject" => $event->title,
            "start" => array(
                "dateTime" => $event->start_date . "T" . $event->start_time,
                "timeZone" => "America/Sao_Paulo"
            ),
            "end" => array(
                "dateTime" => $event->end_date . "T" . $event->end_time,
                "timeZone" => "America/Sao_Paulo"
            ),
            "body" => array(
                "contentType" => "text",
                "content" => $event->description
            )
        ));

        if($outlook_event->id)
        {
            $event->outlook_event_id = $outlook_event->id;
            $this->ci->Events_model->ci_save($event, $event_id);
        }
    }
}
