<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Flow extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Send_model');
    }

    /**
     * Webhook for this controller.
     *
     * Maps to the following URL
     * 		http://localhost/index.php/flow
     *	- or -
     * 		http://localhost/index.php/flow/index
     *	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://localhost/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/flow/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */
    public function webhook()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $userData = array();
        $userData['chat_id'] = $input['message']['chat']['id'];
        $userData['message'] = $input['message']['text'];
        $userData['message_id'] = $input['message']['message_id'] ?? null;
        $userData['first_name'] = $input['message']['from']['first_name'] ?? null;
        $userData['last_name'] = $input['message']['from']['last_name'] ?? null;
        $userData['username'] = $input['message']['from']['username'] ?? null;
        $this->start($userData);
    }
    
    /**
     * Handles response to users input/utterances
     */
    public function start($userData)
    {
        $orders = array(
            array( 'tracking_id'=>'1234', 'description'=>'Iphone 11', 'status'=>'Waiting for pick up'),
            array( 'tracking_id'=>'9797', 'description'=>'Pixel 4', 'status'=>'Shipment dispatched'),
            array( 'tracking_id'=>'4343', 'description'=>'Samsung Note 10', 'status'=>'Shipment accepted by airline'),
            array( 'tracking_id'=>'2892', 'description'=>'Haweii P90', 'status'=>'Delivery Successful'),
        );
        if ($userData['message'] == '/start') {
            $count = 1;
            $tracking_id = "Sample Tracking IDs:\n\n";
            foreach ($orders as $row) {
                $tracking_id .= $count++.". ".$row['tracking_id']."\n";
            }
            $botResponse = "*Hi there*, `".$userData['first_name']."`. I am orderTracker🙂. \n\nI can track the status of your order within seconds. Just send in your tracking id to begin.\n\n\n$tracking_id";
            return $this->Send_model->sendMessage($userData, $botResponse);
        } else {
            $queryWitAI = $this->Send_model->queryWitai($userData);
            $witAiIntent = $queryWitAI['entities']['intent'][0]['value'];
            $witAiTrackingId = $queryWitAI['entities']['tracking_number'][0]['value'];
            if (isset($witAiIntent) && $witAiIntent == 'track') {
                if (isset($witAiTrackingId) && $witAiTrackingId !== null) {
                    $botResponse = $this->orderSearch($orders, $witAiTrackingId);
                } else {
                    $botResponse = "Please enter your tracking id";
                }
                return $this->Send_model->sendMessage($userData, $botResponse);
            }
        }
    }
}
