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
     * Telegram webhook method.
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
        // $orders stores sample order data such as tracking_id, description and status of the order
        $orders = array(
            array( 'tracking_id'=>'1234', 'description'=>'Iphone 11', 'status'=>'Waiting for pick up'),
            array( 'tracking_id'=>'9797', 'description'=>'Pixel 4', 'status'=>'Shipment dispatched'),
            array( 'tracking_id'=>'4343', 'description'=>'Samsung Note 10', 'status'=>'Shipment accepted by airline'),
            array( 'tracking_id'=>'2892', 'description'=>'Haweii P90', 'status'=>'Delivery Successful'),
        );
        if ($userData['message'] == '/start') {
            // if a user sends '/start', the default message is sent as a response
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
            $witAiTrackingId = $queryWitAI['entities']['tracking_id'][0]['value'];
            if (isset($witAiIntent) && $witAiIntent == 'track') {
                // Check if the message contains entity 'track'
                if (isset($witAiTrackingId) && $witAiTrackingId !== null) {
                    // Check if the message contains a Tracking ID
                    $botResponse = $this->orderSearch($orders, $witAiTrackingId);
                } else {
                    $botResponse = "Please enter your tracking id";
                }
                return $this->Send_model->sendMessage($userData, $botResponse);
            } elseif (is_numeric($witAiTrackingId) && $witAiTrackingId !== null) {
                // Check if the message contains a Tracking ID
                $botResponse = $this->orderSearch($orders, $witAiTrackingId);
                return $this->Send_model->sendMessage($userData, $botResponse);
            } else {
                $queryDialogFlow = $this->Send_model->queryDialogFlow($userData);
                $botResponse = $queryDialogFlow['queryResult']['fulfillmentText'];
                if (isset($botResponse) && $botResponse != null) {
                    // Check if the message contains greetings such as hi, hello, hey
                    return $this->Send_model->sendMessage($userData, $botResponse);
                } else {
                    // if !$botResponse, send the default message
                    $botResponse = "My little witty brain could not comprehend";
                    return $this->Send_model->sendMessage($userData, $botResponse);
                }
            }
        }
    }
    
    /**
     * Handles finding orders using its Tracking ID
     */
    public function orderSearch($orders, $query)
    {
        $row = array_search($query, array_column($orders, 'tracking_id'));
        if (isset($row) && $row !== false) {
            $trackingId = $orders[$row]['tracking_id'];
            $description = $orders[$row]['description'];
            $status = $orders[$row]['status'];
            return "Tracking ID: $trackingId\n\nOrder Description: $description\n\nOrder Status: $status";
        } else {
            return "Order not found";
        }
    }
}
