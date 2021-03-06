<?php

namespace Mediumart\Intercom\Tests\Unit;

use Mediumart\Intercom\Client;
use Mediumart\Intercom\IntercomClient;
use Mediumart\Intercom\Tests\TestCase;

class ClientTest extends TestCase
{
    public function api_resources()
    {
        return [
          ['users'],
          ['events'],
          ['companies'],
          ['messages'],
          ['conversations'],
          ['leads'],
          ['visitors'],
          ['admins'],
          ['tags'],
          ['segments'],
          ['counts'],
          ['bulk'],
          ['notes'],
        ];
    }

    public function client_api()
    {
        return [
            ['post',        ['endpoint', []]    ],
            ['put',         ['endpoint', []]    ],
            ['delete',      ['endpoint', []]    ],
            ['get',         ['endpoint', []]    ],
            ['nextPage',    ['endpoint']        ],
            ['getAuth'],
            ['getToken'],
        ];
    }

    /** @test */
    public function resolve_client_as_singleton()
    {
        $intercom = $this->app->make('intercom');
        $intercom2 = $this->app->make(Client::class);

        $this->assertInstanceOf(Client::class, $intercom);
        $this->assertInstanceOf(Client::class, $intercom2);
        $this->assertSame($intercom, $intercom2);
    }

    /**
     * @test
     *
     * @dataProvider api_resources
     * @param $resource
     */
    public function expose_intercom_api_resources($resource)
    {
        $intercom = $this->app->make(Client::class);

        $this->assertNotNull($intercom->{$resource});
    }

    /**
     * @test
     *
     * @dataProvider api_resources
     * @param $resource
     */
    public function expose_intercom_resources_as_part_of_client_api($resource)
    {
        $intercom = $this->app->make(Client::class);

        $this->assertNotNull(call_user_func([$intercom, $resource]));
    }

    /**
     * @test
     *
     * @dataProvider client_api
     * @param $method
     * @param array $parameters
     */
    public function expose_intercom_client_api($method, $parameters = [])
    {
        $api = $this->getMockBuilder(IntercomClient::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $api->method($method)->willReturn('true');

        $intercom = new Client($api);

        $this->assertEquals('true', call_user_func_array([$intercom, $method], $parameters));
    }

    /** 
     * @test
     */
    public function it_returns_null_for_unknown_resources_or_client_method() 
    {
        $this->assertNull($this->app->make('intercom')->unknownResource);
        $this->assertNull($this->app->make('intercom')->unknownClientMethod());
    }

    /** 
     * @test
     */
    public function it_set_token() 
    {
        $intercom = $this->app->make('intercom');

        $this->assertEquals('access_token', $intercom->setToken('access_token')->getToken());
    }

    /** 
     * @test
     */
    public function it_set_http_client() 
    {
        $intercom = $this->app->make('intercom');

        $httpClient = new \GuzzleHttp\Client;

        $this->assertSame($intercom, $intercom->setClient($httpClient));
    }

    /** 
     * @test
     */
    public function client_instance_is_macroable()
    {
        $intercom = $this->app->make(Client::class);

        $intercom->macro('fooUsers', function() use ($intercom) {
            return $intercom->users;
        });

        $this->assertTrue($intercom->hasMacro('fooUsers'));
        $this->assertInstanceOf(\Intercom\IntercomUsers::class, $intercom->fooUsers());
    }

    /** 
     * @test
     */
    public function client_facade_is_macroable()
    {
        \Intercom::macro('barUsers', function() {
            return \Intercom::users();
        });

        $this->assertTrue(\Intercom::hasMacro('barUsers'));
        $this->assertInstanceOf(\Intercom\IntercomUsers::class, \Intercom::barUsers());
    }
}
