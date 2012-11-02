<?php

namespace Etcpasswd\OAuthBundle\Security\Core\Authentication\Provider;

use Symfony\Component\Security\Core\User\UserProviderInterface,
    Symfony\Component\Security\Core\Exception\AuthenticationException,
    Symfony\Component\Security\Core\Authentication\Token\TokenInterface,
    Symfony\Component\Security\Core\User\UserCheckerInterface,
    Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface,
    Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;

use Etcpasswd\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;

/**
 * @author   Marcel Beerta <marcel@etcpasswd.de>
 */
class OAuthProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    private $providerKey;
    private $encoderFactory;

    public function __construct(UserProviderInterface $userProvider, $providerKey)
    {
        $this->userProvider = $userProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(TokenInterface $token)
    {
        $user = $this->userProvider->loadUserBySocialId($token->getSocialId());

        try {
            $this->userProvider->checkPostAuth($user);
        } catch (AccountStatusException $e) {
            // Don't authenticate locked, disabled or expired users
            throw new AuthenticationException($e->getMessage(), null, 0, $e);
        } catch (AuthenticationException $passthroughEx) {
            throw $passthroughEx;
        } catch (\Exception $ex) {
            throw new AuthenticationException($ex->getMessage(), null, 0, $ex);
        }

        if ($user) {
            $authenticatedToken = new OAuthToken($user->getRoles(), $token->getResponse());
            $authenticatedToken->setAuthenticated(true);
            $authenticatedToken->setUser($user);

            return $authenticatedToken;
        }

        throw new AuthenticationException('OAuth Authentication Failed.');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof OAuthToken;
    }

}