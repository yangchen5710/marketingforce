<?php

namespace Ycstar\Marketingforce;

use GuzzleHttp\Client;
use Ycstar\Marketingforce\Exceptions\InvalidArgumentException;
use Ycstar\Marketingforce\Exceptions\InvalidResponseException;

class Marketingforce
{
    protected $client;
    protected $host;
    protected $key;
    protected $secret;

    public function __construct(array $config)
    {
        if (!isset($config['host'])) {
            throw new InvalidArgumentException("Missing Config -- [host]");
        }

        if (!isset($config['key'])) {
            throw new InvalidArgumentException("Missing Config -- [key]");
        }

        if (!isset($config['secret'])) {
            throw new InvalidArgumentException("Missing Config -- [secret]");
        }
        $this->host = $config['host'];
        $this->key = $config['key'];
        $this->secret = $config['secret'];
    }

    public function getToken()
    {
        if(!empty($this->token)){
            return ['token' => $this->token, 'expires_in' => $this->expiresIn];
        }

        $response = $this->getHttpClient()->post('/thirdparty/user/login/client', [
            'json' => [
                'clientId' => $this->key,
                'clientSecret' => $this->secret,
            ],
        ])->getBody()->getContents();
        $result = json_decode($response, true);
        $code = $result['code'] ?? 1;
        if ($code != 0){
            throw new InvalidResponseException($result['message'] ?? '未知错误');
        }
        $data = $result['data'];

        $this->token = $data['value'];
        $this->expiresIn = $data['expiredTime'];
        return ['token' => $this->token, 'expires_in' => $this->expiresIn];
    }

    public function setToken(string $token)
    {
        $this->token = $token;
    }

    /**
     * 获取个人号列表
     * @param $params
     * @return mixed
     * @throws InvalidResponseException
     */
    public function getWxAccList($params)
    {
        $url = '/business/wxAcc/list';
        return $this->doRequest('post', $url, ['json' => $params]);
    }

    /**
     * 企业微信号号列表
     * @param $params
     * @return mixed
     * @throws InvalidResponseException
     */
    public function getEnterprisePersonalList($params)
    {
        $url = '/busi-etp/enterprisePersonal/enterprisePersonalList';
        return $this->doRequest('post', $url, ['json' => $params]);
    }

    /**
     * 发送私聊消息
     * @param $params
     * @return mixed
     * @throws InvalidResponseException
     */
    public function personalPrivateMessage($params)
    {
        $url = '/thirdparty/personal/privateMessage';
        return $this->doRequest('post', $url, ['json' => $params]);
    }

    /**
     * 查询企业微信好友
     * @param $params
     * @return mixed
     * @throws InvalidResponseException
     */
    public function selectEnterFreInfo($params)
    {
        $url = '/thirdparty/wxFre/selectEnterFreInfo';
        return $this->doRequest('post', $url, ['json' => $params]);
    }

    /**
     * 同步企微客户
     * @param $params
     * @return mixed
     * @throws InvalidResponseException
     */
    public function synchRobotsFriendsList($params)
    {
        $url = '/thirdparty/wxFre/synchRobotsFriendsList';
        return $this->doRequest('post', $url, ['json' => $params]);
    }

    /**
     * 查询素材库素材
     * @param $params
     * @return mixed
     * @throws InvalidResponseException
     */
    public function queryMateList($params)
    {
        $url = '/thirdparty/personal/queryMateList';
        return $this->doRequest('post', $url, ['json' => $params]);
    }

    /**
     * 订阅个人号回调消息
     * @param $params
     * @return mixed
     * @throws InvalidResponseException
     */
    public function partnerPersonOn($params)
    {
        $url = '/thirdparty/partner/personal/on';
        return $this->doRequest('post', $url, ['json' => $params]);
    }

    private function doRequest(string $method, $uri = '', array $options = [])
    {
        try {
            $options['headers'] = [
                'Authorization' => 'Bearer '. $this->token
            ];
            $response = $this->getHttpClient()->request($method, $uri, $options)->getBody()->getContents();
            $result = json_decode($response, true);
            if(!$result){
                throw new InvalidResponseException('invalid response');
            }
            $code = $result['code'] ?? 1;
            if($code != 0){
                throw new InvalidResponseException($result['message'] ?? '未知错误', $code);
            }
            return $result;

        } catch (InvalidResponseException $e){
            throw new InvalidResponseException($e->getMessage(), $e->getCode());
        }
    }

    private function getHttpClient()
    {
        if(!$this->client){
            return new Client(['base_uri' => $this->host]);
        }
        return $this->client;
    }

}