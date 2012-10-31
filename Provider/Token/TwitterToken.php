<?php

namespace Etcpasswd\OAuthBundle\Provider\Token;

class TwitterToken implements TokenResponseInterface
{


    private $accessToken;

    /**
     * Constructs a new token
     *
     * @param string $accessToken Api access token
     *
     * @return void
     */
    public function __construct($accessToken)
    {
//             ["oauth_token"]=> string(46) "17892816-n2b6KI0EyJVKWMVBhkMsG9MAXQGgBoNRT01dE"
//             ["oauth_token_secret"]=> string(42) "8ZEBWzcosMjK830Dn6nwatCmJKMIRz5l6kd59kKNGw"
//             ["user_id"]=> string(8) "17892816"
//             ["screen_name"]=> string(9) "kyaroslav"
        $this->accessToken = $accessToken;
    }

    /**
     * {@inheritDoc}
     */
    public function getExpires()
    {
        return $this->expiresAt;
    }

    /**
     * {@inheritDoc}
     */
    public function getUsername($field = 'screen_name')
    {
        return $this->accessToken[$field];
    }

    /**
     * {@inheritDoc}
     */
    public function isLongLived()
    {
        return true;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function getProviderKey()
    {
        return 'twitter';
    }

    public function getJson()
    {
        return $this->accessToken;
    }

    /**
     * Returns the uniq used id assigned to user by social network
     * id
     *
     * @returun id
     */
    function getSocialId()
    {
        $data = $this->getJson();
        return $data['user_id'];
    }
}