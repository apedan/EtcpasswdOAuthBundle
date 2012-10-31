<?php

namespace Etcpasswd\OAuthBundle\Provider;

use Etcpasswd\OAuthBundle\Provider\Token\TwitterToken;

class TwitterProvider extends Provider
{
    private $options;
    private $request;
    private $api;

    public function __construct($client)
    {
        parent::__construct($client);
    }

    /**
     * {@inheritDoc}
     */
    public function createTokenResponse($clientId, $secret, $code, $redirectUrl = "")
    {
        $session = $this->request->getSession();

        //set OAuth token in the API
        $this->api->setOAuthToken($this->request->get('oauth_token'), $session->get('oauth_token_secret'));

        /* Check if the oauth_token is old */
        if ($session->has('oauth_token')) {
            if ($session->get('oauth_token') && ($session->get('oauth_token') !== $this->request->get('oauth_token'))) {
                $session->remove('oauth_token');
                return null;
            }
        }

        /* Request access tokens from twitter */
        $accessToken = $this->api->getAccessToken($this->request->get('oauth_verifier'));

//        public 'consumer' =>
//    object(OAuthConsumer)[102]
//      public 'key' => string '3gt9DKq3HThAayskZzMQ' (length=20)
//      public 'secret' => string 'Cv74j1GHx54IVhIRo76mNGrfRyZVvIYRiXABJdYs' (length=40)

        /* Save the access tokens. Normally these would be saved in a database for future use. */
        $session->set('access_token', $accessToken['oauth_token']);
        $session->set('access_token_secret', $accessToken['oauth_token_secret']);

        /* Remove no longer needed request tokens */
        !$session->has('oauth_token') ?: $session->remove('oauth_token', null);
        !$session->has('oauth_token_secret') ?: $session->remove('oauth_token_secret', null);

        /* If HTTP response is 200 continue otherwise send to connect page to retry */
        if (200 == $this->api->http_code) {

          $this->api->setOAuthToken($session->get('access_token'), $session->get('access_token_secret'));

//          try {
//              $info = $this->api->get('account/verify_credentials');
//          } catch (Exception $e) {
//              $info = null;
//          }

          //die(var_dump($info));
//            $accessToken = $result['access_token'];
//
//            $url = 'https://graph.facebook.com/me'.'?access_token='.$accessToken;
//
//              $json = json_decode($this->request($url));
//            $expiresAt = time() + $result['expires'];

//
            /* The user has been verified and the access tokens can be saved for future use */
            return new TwitterToken($accessToken);
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthorizationUrl($clientId, $scope, $redirectUrl)
    {
        $session = $this->request->getSession();
        $redirectUrl = urldecode($redirectUrl);

        /* Get temporary credentials. */
        $requestToken = (!empty($redirectUrl)) ?
            $this->api->getRequestToken($redirectUrl)
            : $this->api->getRequestToken();

        /* Save temporary credentials to session. */
        $session->set('oauth_token', $requestToken['oauth_token']);
        $session->set('oauth_token_secret', $requestToken['oauth_token_secret']);

        $this->api->getRequestToken($redirectUrl);

        return $this->api->getAuthorizeURL($requestToken);
    }

    function setOptions($options, $request)
    {
        $this->options = $options;
        $this->request = $request;

        $this->api = new \TwitterOAuth_TwitterOAuth($this->options['client_id'],$this->options['client_secret']);
        // TODO: Implement setOptions() method.
    }
}