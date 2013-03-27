<?php

namespace Etcpasswd\OAuthBundle\Provider;

use Etcpasswd\OAuthBundle\Provider\Token\VkToken;

/**
 * OAuth provider for vk
 *
 * @author Marcel Beerta <marcel@etcpasswd.de>
 * @link   https://vk.com/developers.php
 */
class VkProvider extends Provider
{
    /**
     * {@inheritDoc}
     */
    public function createTokenResponse($clientId, $secret, $code, $redirectUrl = "")
    {
        //$redirectUrl = "http://test.outline.cc/app_dev.php/auth/vk";
        $url = 'https://oauth.vk.com/access_token'
            .'?client_id='.$clientId
            .'&redirect_uri='.$redirectUrl
            .'&client_secret='.$secret
            .'&code='.$code;

        parse_str($this->request($url), $result);

        if (isset($result['error'])) {
            return;
        }
        $json_response = array_keys($result);
        $result = json_decode($json_response[0], true);
        $accessToken = $result['access_token'];
        $userId = $result['user_id'];
        $expiresAt = time() + $result['expires_in'];
        $url = 'https://api.vk.com/method/users.get'
            .'?uids='.$userId
            .'&access_token='.$accessToken;

        $json = json_decode($this->request($url));
        $json = $json->response[0];
        return new VkToken($json, $accessToken, $expiresAt);
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthorizationUrl($clientId, $scope, $redirectUrl)
    {
        //$redirectUrl = "http://test.outline.cc/app_dev.php/auth/vk";
        return 'https://oauth.vk.com/authorize'
            .'?client_id='.$clientId
            .'&redirect_uri='.$redirectUrl;
    }

    function setOptions($options, $request)
    {
        // TODO: Implement setOptions() method.
    }
}