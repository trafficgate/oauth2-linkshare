<?php

namespace League\OAuth2\Client\Provider;

class LinkshareResourceOwner implements ResourceOwnerInterface
{
    /**
     * Returns the identifier of the authorized resource owner.
     *
     * @return mixed
     */
    public function getId()
    {
        return null;
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [];
    }
}
