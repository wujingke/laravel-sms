<?php namespace Toplan\Sms;

class MontnetsAgent extends Agent {

    public function sendSms($tempId, $to, Array $data, $content)
    {
        $this->sendContentSms($to, $content);
    }

    public function sendContentSms($to, $content)
    {
        $url = 'http://61.145.229.29/MWGate/wmgw.asmx/MongateCsSpSendSmsNew';
        $accountSid = $this->accountSid;
        $accountToken= $this->accountToken;
        $serverPort= $this->serverPort;


        $content = urlencode("$content");
        $postString = "iMobiCount=".count(explode($to,','))."&pszMobis=$to&pszMsg=$content&userId=$accountSid&password=$accountToken&pszSubPort=$serverPort";
        $response = $this->sockPost($url, $postString,9003);
        $array = json_decode(json_encode((array) simplexml_load_string($response)), true);
        if(strlen($array[0]) > 10 && strlen($array[0]) < 25 ){
            $this->result['success'] = true;
            $this->result['info'] = $array[0];
            $this->result['code'] = 200;
        }

    }

    public function sendTemplateSms($tempId, $to, Array $data)
    {
        return null;
    }

}
