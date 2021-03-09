<?php
// +----------------------------------------------------------------------
// | ShopXO 国内领先企业级B2C免费开源电商系统
// +----------------------------------------------------------------------
// | Copyright (c) 2011~2019 http://shopxo.net All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Devil
// +----------------------------------------------------------------------
namespace base;

require_once 'vendor/autoload.php';
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Sms\V20190711\SmsClient;
use TencentCloud\Sms\V20190711\Models\SendSmsRequest;

/**
 * 短信驱动
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  0.0.1
 * @datetime 2016-12-01T21:51:08+0800
 */
class SmsTencent
{
    // 保存错误信息
    public $error;

    // endpoint
    private $endpoint = 'sms.tencentcloudapi.com';
    // Access Key ID
    private $SecretId = 'AKIDBCyvOWfVAp9MYT451g5JGYaLqrcBBtEJ';

    // Access Access Key Secret
    private $SecretKey = 'xf5HDtIPQGrAH7xaw3tTq2lEQQwBArHQ';

    // 签名
    private $signName = '谱匠网';

    // 模版ID
    private $TemplateID = '886583';

    // sms app id
    private $SmsSdkAppid = '1400491798';

    private $expire_time;
	private $key_code;

    /**
	 * [__construct 构造方法]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2017-03-07T14:03:02+0800
	 * @param    [int]        $param['interval_time'] 	[间隔时间（默认30）单位（秒）]
	 * @param    [int]        $param['expire_time'] 	[到期时间（默认30）单位（秒）]
	 * @param    [string]     $param['key_prefix'] 		[验证码种存储前缀key（默认 空）]
	 */
	public function __construct($param = array())
	{
		$this->interval_time = isset($param['interval_time']) ? intval($param['interval_time']) : 120;
		$this->expire_time = isset($param['expire_time']) ? intval($param['expire_time']) : 120;
		$this->key_code = isset($param['key_prefix']) ? trim($param['key_prefix']).'_sms_code' : '_sms_code';

		// $this->signName = MyC('common_sms_sign');
		// $this->SecretId = 'AKIDBCyvOWfVAp9MYT451g5JGYaLqrcBBtEJ';
		// $this->SecretKey = 'xf5HDtIPQGrAH7xaw3tTq2lEQQwBArHQ';

	}

    /**
     * [KindofSession 种验证码session]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2017-03-07T14:59:13+0800
     * @param    [string]      $code [验证码]
     */
    private function KindofSession($code)
    {
        $data = array(
                'code' => $code,
                'time' => time(),
            );
        cache($this->key_code, $data, $this->expire_time);
    }

    /**
     * [CheckExpire 验证码是否过期]
     * @author   Devil
     * @blog     http://gong.gg/
     * @version  0.0.1
     * @datetime 2017-03-05T19:02:26+0800
     * @return   [boolean] [有效true, 无效false]
     */
    public function CheckExpire()
    {
        $data = cache($this->key_code);
        if(!empty($data))
        {
            return (time() <= $data['time']+$this->expire_time);
        }
        return false;
    }

	/**
	 * [CheckCorrect 验证码是否正确]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2017-03-05T16:55:00+0800
	 * @param    [string] $code    [验证码（默认从post读取）]
	 * @return   [booolean]        [正确true, 错误false]
	 */
	public function CheckCorrect($code = '')
	{
        // 安全验证
        if(SecurityPreventViolence($this->key_code, 1, $this->expire_time))
        {
            // 验证是否正确
            $data = cache($this->key_code);
            if(!empty($data))
            {
                if(empty($code) && isset($_POST['code']))
                {
                    $code = trim($_POST['code']);
                }
                return ($data['code'] == $code);
            }
        }
		return false;
	}

	/**
	 * [Remove 验证码清除]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2017-03-08T10:18:20+0800
	 * @return   [other] [无返回值]
	 */
	public function Remove()
	{
		cache($this->key_code, null);
        SecurityPreventViolence($this->key_code, 0);
	}

	/**
	 * [IntervalTimeCheck 是否已经超过控制的间隔时间]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  0.0.1
	 * @datetime 2017-03-10T11:26:52+0800
	 * @return   [booolean]        [已超过间隔时间true, 未超过间隔时间false]
	 */
	private function IntervalTimeCheck()
	{
		$data = cache($this->key_code);
		if(!empty($data))
		{
			return (time() > $data['time']+$this->interval_time);
		}
		return true;
	}


    public function SendCode($mobile, $code, $template_code) {

        // 单个验证码需要校验是否频繁
        if(is_string($code))
        {
            // 是否频繁操作
            if(!$this->IntervalTimeCheck())
            {
                $this->error = '防止造成骚扰，请勿频繁发送';
                return false;
            }
            $codes = ['code'=>$code];
        } else {
            $codes = $code;
        }

        try {

            $cred = new Credential($this->SecretId, $this->SecretKey);
            $httpProfile = new HttpProfile();
            $httpProfile->setEndpoint($this->endpoint);
              
            $clientProfile = new ClientProfile();
            $clientProfile->setHttpProfile($httpProfile);
            $client = new SmsClient($cred, "", $clientProfile);
        
            $req = new SendSmsRequest();
            
            $params = array(
                "PhoneNumberSet" => array( '+86'.$mobile ),
                "TemplateID" => $this->TemplateID,
                "Sign" => $this->signName,
                "TemplateParamSet" => array( $code ),
                "SmsSdkAppid" => $this->SmsSdkAppid
            );
            $req->fromJsonString(json_encode($params));
        
            $resp = $client->SendSms($req);
        
            $result = $resp->toJsonString();

            $result = json_decode ( $result, true );
            //print_r($result);//die;
            if (isset ($result['Response']['SendStatusSet'][0]['Code']) && $result['Response']['SendStatusSet'][0]['Code'] != 'OK') {
                $this->error = $result['Response']['SendStatusSet'][0]['Message'];
                return false;
            }

        }
        catch(TencentCloudSDKException $e) {
            $this->error = $e;
            return false;
        }

        
        // 种session
        if(is_string($code))
        {
            $this->KindofSession($code);
        }

        return true;
    }
}
?>