Apix\\Server
============

.. php:namespace:: Apix

.. php:class:: Server

   .. php:method:: Server::onCreate()

      POST request handler

      :param string $path: The path name to match against.
      :param mixed $to: Callback that returns the response when matched.

      :see: Server::proxy

      :returns: Controller $rovides a fluent interface.

   .. php:method:: Server::onRead()

      GET request handler

      :param string $path: The path name to match against.
      :param mixed $to: Callback that returns the response when matched.

      :see: Server::proxy

      :returns: Controller $rovides a fluent interface.

   .. php:method:: Server::onUpdate()

      PUT request handler

      :param string $path: The path name to match against.
      :param mixed $to: Callback that returns the response when matched.

      :see: Server::proxy

      :returns: Controller $rovides a fluent interface.

   .. php:method:: Server::onModify()

      PATCH request handler

      :param string $path: The path name to match against.
      :param mixed $to: Callback that returns the response when matched.

      :see: Server::proxy

      :returns: Controller $rovides a fluent interface.

   .. php:method:: Server::onDelete()

      DELETE request handler

      :param string $path: The path name to match against.
      :param mixed $to: Callback that returns the response when matched.

      :see: Server::proxy

      :returns: Controller $rovides a fluent interface.

   .. php:method:: Server::onHelp()

      OPTIONS request handler

      :param string $path: The path name to match against.
      :param mixed $to: Callback that returns the response when matched.

      :see: Server::proxy

      :returns: Controller $rovides a fluent interface.

   .. php:method:: Server::onTest()

      HEAD request handler

      :param string $path: The path name to match against.
      :param mixed $to: Callback that returns the response when matched.

      :see: Server::proxy

      :returns: Controller $rovides a fluent interface.

   .. php:method:: Server::proxy()

      Acts as a shortcut to resources::add.

                                            when matched.

      :see: Resources::add
      :param string $method: The HTTP method to match against.
      :param string $path: The path name to match against.
      :param mixed $to: Callback that returns the response

      :returns: Controller

   .. php:method:: Server::setGroup()

      Test Read from a group (TODO).

      :param array $opts: Options are:

      :returns: string
