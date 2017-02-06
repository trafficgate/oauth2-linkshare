<?php

namespace Linkshare\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Linkshare\OAuth2\Client\Grant\ScopedPassword;
use Psr\Http\Message\ResponseInterface;

class Linkshare extends AbstractProvider
{
    /**
     * @var string Key used in the access token response to identify the resource owner
     */
    const ACCESS_TOKEN_RESOURCE_OWNER_ID = null;

    /**
     * Returns the base URL for authorizing a client.
     *
     * Eg. https://oauth.service.com/authorize
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return 'https://api.rakutenmarketing.com/token';
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * Eg. https://oauth.service.com/token
     *
     * @param array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://api.rakutenmarketing.com/token';
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     *
     * @param AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return 'https://api.rakutenmarketing.com/productsearch/1.0?';
    }

    /**
     * Returns the default scopes used by this provider.
     *
     * This should only be the scopes that are required to request the details
     * of the resource owner, rather than all the available scopes.
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return ['Production'];
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessToken($grant = 'scoped_password', array $options = [])
    {
        // Allow LinkShare-specific 'scoped_password' to be specified as a string,
        // keeping consistent with the other grant types.
        if ($grant === 'scoped_password') {
            $grant = new ScopedPassword();
        }

        return parent::getAccessToken($grant, $options);
    }

    /**
     * Checks a provider response for errors.
     *
     * @param ResponseInterface $response
     * @param array|string      $data     Parsed response data
     *
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['error'])) {
            throw new IdentityProviderException(
                $data['error_description'] ?: $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
            );
        }
    }

    /**
     * Generates a resource owner object from a successful resource owner
     * details request.
     *
     * @param array       $response
     * @param AccessToken $token
     *
     * @return ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new LinkshareResourceOwner($response);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthorizationHeaders($token = null)
    {
        if (! isset($token)) {
            return [];
        }

        $authorizationHeaders = parent::getAuthorizationHeaders($token);

        if (is_string($token)) {
            $authorizationHeaders['Authorization'] = 'Bearer '.$token;
        } elseif ($token instanceof AccessToken) {
            $authorizationHeaders['Authorization'] = 'Bearer '.$token->getToken();
        }

        return $authorizationHeaders;
    }
}
