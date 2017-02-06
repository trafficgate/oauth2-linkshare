<?php

namespace Linkshare\OAuth2\Client\Grant;

use League\OAuth2\Client\Grant\AbstractGrant;

/**
 * Represents a resource owner password credentials grant.
 *
 * @link http://tools.ietf.org/html/rfc6749#section-1.3.3 Resource Owner Password Credentials (RFC 6749, §1.3.3)
 */
class ScopedPassword extends AbstractGrant
{
    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return 'scoped_password';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredRequestParameters()
    {
        return [
            'username',
            'password',
            'scope',
        ];
    }
}
