<?php

/**
 *
 * This file is part of the Apix Project.
 *
 * (c) Franck Cassedanne <franck at ouarz.net>
 *
 * @license     http://opensource.org/licenses/BSD-3-Clause  New BSD License
 *
 */

namespace Apix\Plugin;

use Apix\HttpRequest,
    Apix\Response,
    Apix\TestCase,
    Apix\Service;

class AuthTest extends TestCase
{
    protected $plugin, $request, $response, $opts, $authAdapter;

    public function setUp()
    {
        $this->request = new HttpRequest();
        $this->response = new Response($this->request);
        $this->response->unit_test = true;

        Service::set('response', $this->response);

        $this->entity = $this->getMock('Apix\Entity');

        $this->authAdapter = $this->getMock('Apix\Plugin\Auth\Adapter');
        $this->plugin = new Auth( array('adapter' => $this->authAdapter) );

        $this->opts = $this->plugin->getOptions();
    }

    protected function setEntityAnnotation($key, $value)
    {
        $this->entity
            ->expects($this->any())
            ->method('getAnnotationValue')
            ->with($this->equalTo($key))
            ->will($this->returnValue((string) $value));
    }

    protected function tearDown()
    {
        unset(
            $this->plugin, $this->request, $this->response, $this->opts,
            $this->authAdapter
        );
    }

    public function testIsDisableWhenBotGroupAndUserAreNotSet()
    {
        $this->assertNull( $this->plugin->update( $this->entity ) );
    }

    public function testIsDisableIfPublicGroup()
    {
        $this->setEntityAnnotation('api_auth', 'groups=' . $this->opts['public_group']);
        $this->assertNull( $this->plugin->update( $this->entity ) );
    }

    /**
     * @expectedException           \Apix\Exception
     * @expectedExceptionCode       401
     * @expectedExceptionMessage    Authentication required
     */
    public function testTryToAuthWhenUserIsSet()
    {
        $this->setEntityAnnotation('api_auth', 'users=frqnck');
        $this->plugin->update( $this->entity );
    }

    /**
     * @expectedException           \Apix\Exception
     * @expectedExceptionCode       401
     * @expectedExceptionMessage    Authentication required
     */
    public function testTryToAuthWhenGroupIsSet()
    {
        $this->setEntityAnnotation('api_auth', 'groups=admin');
        $this->plugin->update( $this->entity );
    }

    public function testAuthIsSuccessful()
    {
        Service::set('session', null);

        // $_SERVER['PHP_AUTH_USER'] = 'frqnck';

        $this->authAdapter->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(true));

        $this->setEntityAnnotation('api_auth', 'users=frqnck');
        $this->assertTrue( $this->plugin->update( $this->entity ) );
    }

    // -------- TEMP Session

    /**
     * @expectedException           \Apix\Exception
     * @expectedExceptionCode       401
     * @expectedExceptionMessage    Access unauthorised
     * @group session
     */
    public function testSessionWrongUser()
    {
        Service::set('session', new \Apix\Session('some_user'));

        $this->authAdapter->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(true));

        $this->setEntityAnnotation('api_auth', 'users=frqnck');
        $this->plugin->update( $this->entity );
    }

    /**
     * @expectedException           \Apix\Exception
     * @expectedExceptionCode       401
     * @expectedExceptionMessage    Access unauthorised.
     * @group session
     */
    public function testSessionWrongGroup()
    {
        Service::set('session', new \Apix\Session('frqnck', 'some_group'));

        $this->authAdapter->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(true));

        $this->setEntityAnnotation('api_auth', 'users=frqnck groups=admin');
        $this->plugin->update( $this->entity );
    }

    /**
     * @expectedException           \Apix\Exception
     * @expectedExceptionCode       401
     * @expectedExceptionMessage    Session's IP not trusted
     * @group session
     */
    public function testSessionIpIsNotTrusted()
    {
        Service::set('session', new \Apix\Session('frqnck', 'admin'));
        Service::get('session')->setTrustedIps((array) '1.2.3.4');

        $this->authAdapter->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(true));

        $this->setEntityAnnotation('api_auth', 'users=frqnck groups=admin');
        $this->plugin->update( $this->entity );
    }

    /**
     * @group session
     */
    public function testSessionIpIsTrusted()
    {
        Service::get('response')->getRequest()->setHeader(
            'HTTP_CLIENT_IP', '1.2.3.4'
        );

        Service::set('session', new \Apix\Session('frqnck', 'admin'));
        Service::get('session')->setTrustedIps((array) '1.2.3.4');

        $this->authAdapter->expects($this->once())
            ->method('authenticate')
            ->will($this->returnValue(true));

        $this->setEntityAnnotation('api_auth', 'groups=admin');
        $this->assertTrue( $this->plugin->update( $this->entity ) );
        $this->assertSame('frqnck', $_SERVER['X_AUTH_USER']);
    }

}
