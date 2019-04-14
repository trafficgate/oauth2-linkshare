<?php

namespace Linkshare\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Linkshare\OAuth2\Client\Provider\Linkshare;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class LinkshareTest extends TestCase
{
    protected $provider;

    protected function setUp(): void
    {
        $this->provider = new Linkshare([
            'clientId'     => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri'  => 'none',
        ]);
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl(): void
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testScopes(): void
    {
        $options = ['scope' => [uniqid(), uniqid()]];

        $url = $this->provider->getAuthorizationUrl($options);

        $this->assertStringContainsString(urlencode(implode(',', $options['scope'])), $url);
    }

    public function testGetAuthorizationUrl(): void
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        $this->assertEquals('/token', $uri['path']);
    }

    public function testGetBaseAccessTokenUrl(): void
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('/token', $uri['path']);
    }

    public function testGetAccessToken(): void
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn('{"token_type":"bearer", "expires_in":"3600", "refresh_token":"mock_refresh_token", "access_token":"mock_access_token"}');
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('scoped_password', [
            'username' => 'mock_username',
            'password' => 'mock_password',
            'scope'    => 'mock_scope',
        ]);

        $this->assertEquals(time() + 3600, $token->getExpires());
        $this->assertEquals('mock_refresh_token', $token->getRefreshToken());
        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testExceptionThrownWhenErrorObjectReceived(): void
    {
        $this->expectException(IdentityProviderException::class);

        $message      = uniqid();
        $status       = rand(400, 600);
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn(' {"error":"server_error", "error_description":"'.$message.'"}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn($status);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->provider->setHttpClient($client);
        $token = $this->provider->getAccessToken('scoped_password', [
            'username' => 'mock_username',
            'password' => 'mock_password',
            'scope'    => 'mock_scope',
        ]);
    }
}
