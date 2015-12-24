<?php namespace Toplan\Sms;

use \Input;
use \SmsManager;
use \Validator;
use Illuminate\Routing\Controller;
use \Mail;

class SmsController extends Controller {

    public $smsModel;

    public function __construct()
    {
        $this->smsModel = config('laravel-sms.smsModel', 'Toplan/Sms/Sms');
    }

    public function postSendCode($username = '')
    {
        $vars = [];
        $input = ['username' => $username];

        $vars['success'] = false;

        if(strpos($username,'@')){
            //验证手机号合法性-------------------------------
            $validator = Validator::make($input, [
                'username' => 'required|email'
            ]);

            if ($validator->fails()) {
                $vars['msg'] = 'E-mail格式错误，请输入正确的E-mail地址';
                $vars['type'] = 'email_error';
                return response()->json($vars);
            }
        }
        else
        {
            //验证手机号合法性-------------------------------
            $validator = Validator::make($input, [
                'username' => 'required|mobile'
            ]);
            if ($validator->fails()) {
                $vars['msg'] = '手机号格式错误，请输入正确的11位手机号';
                $vars['type'] = 'mobile_error';
                return response()->json($vars);
            }
        }



        //------------------------------------------
        $smsData = SmsManager::getSmsDataFromSession();
        if(!empty($smsData ) && ((time()+(30*60))-$smsData['deadline_time']) < 120 ){
            $vars['success'] = false;
            $vars['msg'] = '重复发送';
            $vars['type'] = 'sent_fails';

            return response()->json($vars);
        }


        // 发送短信----------------------------------
        $code      = SmsManager::generateCode(4);
        $minutes   = 30;


        if(strpos($username,'@')){
            Mail::send('emails.sendcode', ['code' => $code], function($message) use($username)
            {
                $message->to($username)->subject("CC房车验证码");
            });
        }else{
            $sms       = new $this->smsModel;
            $result    = $sms->to($username)
                             ->content("注册验证码：".$code."，您正在注册CC房车，请填写验证码并完成注册！30分钟内有效")
                             ->send();
        }



            $data = SmsManager::getSmsData();

            $data['username'] = $username;
            $data['code'] = $code;
            $data['deadline_time'] = time() + ($minutes * 60);
            SmsManager::storeSmsDataToSession($data);


            $vars['success'] = true;
            $vars['msg'] = '验证码发送成功，请注意查收';
            $vars['type'] = 'sent_success';

        return response()->json($vars);
    }

    public function getInfo()
    {
        $html = '<h2 align="center" style="margin-top: 20px;">Hello, welcome to laravel-sms for l5.</h2>';
        $html .= '<p style="color: #666;"><a href="https://github.com/toplan/laravel-sms" target="_blank">laravel-sms源码</a>托管在GitHub，欢迎你的使用。如有问题和建议，欢迎提供issue。当然你也能为该项目提供开源代码，让laravel-sms支持更多服务商。</p>';
        $html .= '<hr>';
        $html .= '<p>你可以在调试模式(设置config/app.php中的debug为true)下查看到存储在session中的验证码短信相关数据(方便你进行调试)：</p>';
        echo $html;
        if (config('app.debug')) {
            dd(SmsManager::getSmsDataFromSession());
        } else {
            echo '<p align="center" style="color: #ff0000;;">现在是非调试模式，无法查看验证码短信数据</p>';
        }
    }

}
