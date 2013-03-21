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
        $url = 'https://oauth.vk.com/access_token'
            .'?client_id='.$clientId
            .'&redirect_uri='.$redirectUrl
            .'&client_secret='.$secret
            .'&code='.$code;

//        $url = 'https://graph.facebook.com/oauth/access_token'
//            .'?client_id='.$clientId
//            .'&redirect_uri='.$redirectUrl
//            .'&client_secret='.$secret
//            .'&code='.$code;
        parse_str($this->request($url), $result);

        if (isset($result['error'])) {
            return;
        }

        $accessToken = $result['access_token'];
        $userId = $result['user_id'];
        $expiresAt = time() + $result['expires_in'];
        $url = 'https://graph.facebook.com/method/users.get'
            .'?uids='.$userId
            .'&fields=nickname'
            .'&access_token='.$accessToken;

        $json = json_decode($this->request($url));

        return new VkToken($json, $accessToken, $expiresAt);
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthorizationUrl($clientId, $scope, $redirectUrl)
    {
        return 'https://oauth.vk.com/authorize'
            .'?client_id='.$clientId
            .'&redirect_uri='.$redirectUrl
            .'&display=popup';
    }

    function setOptions($options, $request)
    {
        // TODO: Implement setOptions() method.
    }
}