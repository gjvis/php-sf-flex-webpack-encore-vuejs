<?php
/**
 * run it with phpunit --group git-pre-push
 */
namespace App\Tests;

use App\Tests\Common\ToolsAbstract;

/**
 * Quick test on all login pages
 */
class LoginJsonTest extends ToolsAbstract
{
    /**
     * @group git-pre-push
     */
    public function testLogin()
    {
        $client = $this->getClient();
        $router = $this->getRouter();
        $uriSecured = $router->generate('demo_secured_page_json', []);
        $uriLogin = $router->generate('demo_login_json', []);
        $uriToken = $router->generate('token', []);
        $errMsg = sprintf("route: %s", $uriSecured);
        $headers = [
            'ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
            ];

        $crawler = $client->request('GET', $uriSecured);
        $this->assertEquals(401, $client->getResponse()->getStatusCode(), $errMsg);
        $this->assertEquals(["message" => "Authentication Required", ], json_decode($client->getResponse()->getContent(), true), $errMsg);

        $crawler = $client->request('POST', $uriLogin, [], [], $headers, json_encode(['login_username' => 15, 'login_password' => $this->testPwd,]));
        $this->assertEquals(403, $client->getResponse()->getStatusCode(), $errMsg);
        $this->assertEquals(["message" => "_csrf_token mandatory", "code" => 420, ], json_decode($client->getResponse()->getContent(), true), $errMsg);

        $client->request('GET', $uriToken);
        $token = $client->getResponse()->getContent();

        $crawler = $client->request('POST', $uriLogin, [], [], $headers, json_encode(['login_username' => 15, 'login_password' => $this->testPwd, '_csrf_token' => 'toto',]));
        $this->assertEquals(403, $client->getResponse()->getStatusCode(), $errMsg);
        $this->assertEquals(["message" => "Invalid CSRF token", "code" => 403, ], json_decode($client->getResponse()->getContent(), true), $errMsg);

        $crawler = $client->request('POST', $uriLogin, [], [], $headers, json_encode(['login_username' => 15, 'login_password' => $this->testPwd, '_csrf_token' => $token,]));
        $this->assertEquals(403, $client->getResponse()->getStatusCode(), $errMsg);
        $this->assertEquals(["message" => "Invalid CSRF token", "code" => 403, ], json_decode($client->getResponse()->getContent(), true), $errMsg);

        $crawler = $client->request('POST', $uriLogin, [], [], $headers, json_encode(['login_username' => 15, 'login_password' => $this->testPwd, '_csrf_token' => $token,]));
        $this->assertEquals(403, $client->getResponse()->getStatusCode(), $errMsg);
        $this->assertEquals(["message" => "Forbidden.", "code" => 403, ], json_decode($client->getResponse()->getContent(), true), $errMsg);
    }
}
