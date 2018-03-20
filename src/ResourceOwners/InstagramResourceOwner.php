<?php

namespace Bonnier\WP\SoMe\ResourceOwners;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class InstagramResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;
    
    protected $response;
    
    public function __construct(array $response = [])
    {
        $this->response = ($response['data'] ?? $response) ?: [];
    }
    
    /**
     * Returns the identifier of the authorized resource owner.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getValueByKey($this->response, 'id');
    }
    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
