<?php
/**
 * run it with phpunit --group git-pre-push
 */
namespace App\Tests;

use App\Tests\Common\WebPagesAbstract;

/**
 * Quick test on all en page that should return at least 200 OK + some other checks
 */
class HTTP200WebPagesTest extends WebPagesAbstract
{
    /**
     * @group git-pre-push
     *
     * Check the login page :
     *   * standard display with the header
     *   * form login with wrong credentials : should return to the same page with alert
     *   * form login with good credentials : should go to '/#form'
     */
    public function testLogin()
    {
        $client = $this->getClient();
        $router = $this->getRouter();
        $uri = $router->generate('demo_login_standard', []);

        $errMsg = sprintf("route: %s", $uri);
        $crawler = $client->request('GET', $uri);
        $body = $crawler->filter('body');

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $errMsg);

        $this->assertEquals(1, $body->count(), $errMsg);
        $this->assertNotEquals("", trim($body->text()), $errMsg);

        $this->checkSEO($crawler, $errMsg);
        $this->checkHeader($crawler, $errMsg);

        $form = $crawler->selectButton('login')->form();
        $form->setValues(array(
            'login_username' => $this->testLogin,
            'login_password' => 'fake',
        ));
        $crawler = $client->submit($form);
        $bc = $crawler->filter('body div.alert');
        $this->assertContains('Invalid credentials.', trim($bc->text()));

        $crawler = $this->doLogin($client);
        $bc = $crawler->filter('body div.container');
        $text = trim($bc->text());
        $this->assertContains('Hello Test', $text);
        $this->assertContains('You are in', $text);
    }

    /**
     * @group git-pre-push
     */
    public function testHttp200OnAllPages()
    {
        $client = $this->getClient();
        $router = $this->getRouter();

        $demoRoutes['basic: simple controller'] = ['uri'=> $router->generate('simple'), ];
        $demoRoutes['basic: hello controller with twig'] = ['uri'=> $router->generate('app_hello_world', ['uri'=> 'world', ]), ];
        $demoRoutes['basic: httpplug demo'] = ['uri'=> $router->generate('app_httpplug_call'), ];

        $demoRoutes['login: symfony secured page with standard login'] = ['uri'=> $router->generate('demo_secured_page'), ];
        $demoRoutes['login: vuejs secured page with json login'] = ['uri'=> $router->generate('app_loginjson_index'), ];

        $demoRoutes['js: csrf token generation'] = ['uri'=> $router->generate('token'), ];
        $demoRoutes['js: user login check for js app'] = ['uri'=> $router->generate('demo_secured_page_json_is_logged_in'), 'statusCode' => 401, ];

        $demoRoutes['vuejs: page with vue-router'] = ['uri'=> $router->generate('app_vuejs_index'), ];
        $demoRoutes['vuejs: with quasar and vue-router'] = ['uri'=> $router->generate('app_quasar_index'), ];

        $demoRoutes['form & grid: quasar with vuejs'] = ['uri'=> $router->generate('app_formquasarvuejs_index'), ];
        $demoRoutes['form & grid: devxpress with angular5'] = ['uri'=> $router->generate('app_formdevxpressangular_index'), ];

        $demoRoutes['api-platform: rest'] = ['uri'=> $router->generate('api_entrypoint'), ];
        $demoRoutes['api-platform: graphql'] = ['uri'=> $router->generate('api_graphql_entrypoint'), ];
        $demoRoutes['api-platform: admin react'] = ['uri'=> $router->generate('app_apiplatformadminreact_index'), ];
        $demoRoutes['easy admin'] = ['uri'=> $router->generate('admin'), ];

        foreach ($demoRoutes as $routeInfos) {
            $headers = [];

            if (array_key_exists('headers', $routeInfos)) {
                $headers = array_merge($headers, $routeInfos['headers']);
                foreach ($headers as $keys => $value) {
                    $prefix = 'HTTP_';
                    if (strpos($keys, $prefix) === 0) {
                        continue;
                    }

                    $headers[$prefix . $keys] = $value;
                    unset($headers[$keys]);
                }
            }

            $uri = $routeInfos['uri'];

            $errMsg = sprintf("route: %s, headers: %s", $uri, json_encode($headers));

            $crawler = $client->request('GET', $uri, [], [], $headers);

            $this->assertEquals(array_key_exists('statusCode', $routeInfos) ? : 200, $client->getResponse()->getStatusCode(), $errMsg);
        }
    }
}
