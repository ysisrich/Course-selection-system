<?php
namespace app\index\controller;
/**
 * Created by PhpStorm.
 * User: JimLee
 * Date: 2019/1/22
 * Time: 10:53
 */
class Aliyun {

    // Access Key ID
    private $accessKeyId = 'LTAI4FzueDz3CwpXN3BomFhm';
    // Access Access Key Secret
    private $accessKeySecret = 'eB3ZkJGcssskiuQDIxb2xCO62Z8NkU';
    // 签名
    private $signName = '森邻里';
    // private $signName = '森邻里1';
    // 模版ID
    private $templateCode = 'SMS_174991174';

    private function percentEncode($string) {
        $string = urlencode ( $string );
        $string = preg_replace ( '/\+/', '%20', $string );
        $string = preg_replace ( '/\*/', '%2A', $string );
        $string = preg_replace ( '/%7E/', '~', $string );
        return $string;
    }

    //签名
    private function computeSignature($parameters, $accessKeySecret) {
        ksort ( $parameters );
        $canonicalizedQueryString = '';
        foreach ( $parameters as $key => $value ) {
            $canonicalizedQueryString .= '&' . $this->percentEncode ( $key ) . '=' . $this->percentEncode ( $value );
        }
        $stringToSign = 'GET&%2F&' . $this->percentencode ( substr ( $canonicalizedQueryString, 1 ) );
        $signature = base64_encode ( hash_hmac ( 'sha1', $stringToSign, $accessKeySecret . '&', true ) );
        return $signature;
    }


    public function sendCode($mobile, $code) {
        $params = array (   //此处作了修改
            'SignName' => $this->signName,
            'Format' => 'JSON',
            'Version' => '2017-05-25',
            'AccessKeyId' => $this->accessKeyId,
            'SignatureVersion' => '1.0',
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureNonce' => uniqid (),
            'Timestamp' => gmdate ( 'Y-m-d\TH:i:s\Z' ),
            'Action' => 'SendSms',
            'TemplateCode' => $this->templateCode,
            //'TemplateCode' => 'SMS_115750019',
            'PhoneNumbers' => $mobile,
            'TemplateParam' => '{"code":"' . $code . '"}'
        );

        //var_dump($params);die;
        // 计算签名并把签名结果加入请求参数
        $params ['Signature'] = $this->computeSignature ( $params, $this->accessKeySecret );
        // 发送请求（此处作了修改）
        //$url = 'https://sms.aliyuncs.com/?' . http_build_query ( $params );
        $url = 'http://dysmsapi.aliyuncs.com/?' . http_build_query ( $params );

        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_TIMEOUT, 10 );
        $result = curl_exec ( $ch );
        curl_close ( $ch );
        $result = json_decode ( $result, true );
        //var_dump($result);die;
        if ($result['Code']!="OK") {
            $data['info']=$this->aliyunErrorInfo($result['Code']);
            $data['flag']=false;
            return $data;
        }
        $data['flag']=true;
        return $data;
    }
    
    
    
    protected function aliyunErrorInfo($str){
        switch ($str){
            case 'isv.AMOUNT_NOT_ENOUGH':
                return '账户余额不足';
                break;
            case 'isv.TEMPLATE_MISSING_PARAMETERS':
                return '模板缺少变量';
                break;
            case 'isv.BUSINESS_LIMIT_CONTROL':
                return '业务限流';
                break;
            case 'isv.OUT_OF_SERVICE':
                return '业务停机';
                break;
            case 'isv.ACCOUNT_NOT_EXISTS':
                return '账户不存在';
                break;
            case 'isv.ACCOUNT_ABNORMAL':
                return '账户异常';
                break;
            case 'isv.MOBILE_NUMBER_ILLEGAL':
                return '非法手机号';
                break;
            case 'isv.BLACK_KEY_CONTROL_LIMIT':
                return '黑名单管控';
                break;
            case 'isv.TEMPLATE_PARAMS_ILLEGAL':
                return '模板变量包含非法关键字';
                break;
            case 'isv.MOBILE_NUMBER_ILLEGAL':
                return '非法手机号';
                break;
            default:
                echo "请刷新后重试";
    
        }
    }


}

