<?php

class Send_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function sendMessage($userData, $botRresponse)
    {
        $this->senderAction($userData, "typing");
        $data = [
                    'chat_id'=>$userData['chat_id'],
                    'text'=> $botRresponse,
                    'parse_mode'=>'MarkDown',
                    'reply_to_message_id'=>null,
                    'reply_markup'=>null
                ];
        $this->telegram(array('type'=>'sendMessage', 'data'=>$data));
    }

    public function senderAction($userData, $sender_action)
    {
        $data = [
                'chat_id'=>$userData['chat_id'],
                'action'=>$sender_action
                ];
        return $this->telegram(array('type'=>'sendChatAction','data'=>$data));
    }

    public function queryWitai($userData)
    {
        $token = getenv('WITAI_ACCESS_TOKEN');
        $headers = array('Authorization: Bearer '.$token);
        $body = array('q' => $userData['message'], 'v' => '20181116');
        $url = "https://api.wit.ai/message?".http_build_query($body);
        return $this->doCurl($url, $headers, '', '');
    }

    public function queryDialogFlow($userData)
    {
        $token = exec('gcloud auth application-default print-access-token');
        $projectId = getenv('DIALOGFLOW_PROJECT_ID');
        $headers = array("Authorization: Bearer $token", "Content-Type: application/json; charset=utf-8");
        $body = json_encode(array('query_input' => array('text' => array('text'=>$userData['message'], 'language_code'=>'en-US'))));
        $url = "https://dialogflow.googleapis.com/v2/projects/$projectId/agent/sessions/session-id:detectIntent";
        return $this->doCurl($url, $headers, 'dialogflow', $body);
    }

    public function telegram($data)
    {
        $token = getenv('TELEGRAM_ACCESS_TOKEN');
        $headers = array();
        $body = $data['data'];
        $url = "https://api.telegram.org/bot$token/".$data['type']."?".http_build_query($body);
        return $this->doCurl($url, $headers, '', '');
    }

    public function doCurl($url, $headers, $reqType, $body)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($reqType == 'dialogflow') {
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $body );
        }
        else {
            curl_setopt($ch, CURLOPT_POST, false);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $res = curl_exec($ch);
        if (curl_error($ch)) {
            print_r(curl_error($ch));
        }
        curl_close($ch);
        return json_decode($res, true);
    }
}