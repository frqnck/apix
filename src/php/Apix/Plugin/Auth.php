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

use Apix\Service,
    Apix\Exception;

class Auth extends PluginAbstractEntity
{
    public static $hook = array('entity', 'early');

    protected $options = array(
        // 'enable'        => true,        // wether to enable or not
        'adapter'       => 'Apix\Plugin\Auth\Adapter',
        'public_group'  => 'public',    // public group to skip auth
    );

    protected $annotation = 'api_auth';

    public function update(\SplSubject $entity)
    {
        $this->entity = $entity;

        $groups = $this->getSubTagValues('groups');
        $users = $this->getSubTagValues('users');

        // skip auth if groups and users are both null
        // or if the group is public.
        if(
            null === $users
            && null === $groups
            || null !== $groups
            && in_array($this->options['public_group'], $groups)
        ) {
            return null;
        }

        $logger = Service::get('logger');

        // authenticate
        if ( !$this->adapter->authenticate() ) {
            
            // $logger->info(
            //     'Login failed for "{username}"',
            //     array('username' => $this->adapter->getUsername())
            // );
            
            $this->adapter->send();

            // TODO: eventually in Auth...
            // $response = Service::get('response');
            // $response->setHeaders($headers);
            // $response->send();

            throw new Exception('Authentication required', 401);
        }

        // TODO: get the Session object.
        if (Service::has('session')) {
            $session = Service::get('session');
            
            $context = array('user' => $session->getUsername());

            // check the username is in the authorised list.
            if (null !== $users && !in_array($context['user'], $users)) {

                $logger->notice('Auth: User unauthorised [{user}]', $context);

                throw new Exception('Access unauthorised', 401);
            }

            // check user group
            $context['group'] = $session->getGroup();
            if (null !== $groups && !in_array($context['group'], $groups) ) {

                $logger->notice(
                    'Auth: Sessions\'s group unauthorised [{user}/{group}]".',
                    $context
                );

                throw new Exception('Access unauthorised.', 401);
            }

            // check for (required) trusted user IPs
            if ($session->hasTrustedIps()) {
                $context['ip'] = Service::get('response')->getRequest()->getIp();
                if (!$this->isTrustedIp($context['ip'], $session->getTrustedIps())) {

                    $logger->notice(
                        'Auth: Session\'s IP not trusted [{user}/{group}/{ip}].',
                        $context
                    );

                    throw new Exception('Session\'s IP not trusted', 401);
                }
            }

            // TODO: set X_REMOTE_USER or X_AUTH_USER
            $_SERVER['X_AUTH_USER'] = $context['user'];
            
            $logger->info(
                'Auth: User logged in [{user}/{group}/{ip}]',
                $context
            );
        }

        return true;
    }

    protected function isTrustedIp($ip, array $ips)
    {
        // TODO: improve this, check IP ranges, etc...
        return in_array($ip, $ips);
    }

}
