Apix\\Main
==========

.. php:namespace:: Apix

.. php:class:: Main

   .. php:const:: Main:: VERSION = '@package_version@';

   .. php:attr:: $config

      :todo: review $his.

      :var: array

   .. php:attr:: $request

      :var: Request

   .. php:attr:: $route

      :var: Route

   .. php:attr:: $resources

      :var: Resources

   .. php:attr:: $entity

      :var: Entity

   .. php:attr:: $response

      :var: Response

   .. php:method:: Main::__construct()

      Constructor.

      :returns: void

   .. php:method:: Main::run()

      Run the show...

      :throws: \\InvalidArgumentException $04

      :codeCoverageIgnore:

   .. php:method:: Main::getServerVersion()

      Gets the server version string.

      :returns: string

   .. php:method:: Main::setRouting()

      Sets and initialise the routing processes.

      :param Request $request:

      :returns: void

   .. php:method:: Main::getRoute()

      Returns the route object.

      :returns: Router

   .. php:method:: Main::getResponse()

      Returns the response object.

      :returns: Response

   .. php:method:: Main::negotiateFormat()

      Returns the output format from the request chain.

                               - [default] => string e.g. 'json',
                               - [controller_ext] => boolean,
                               - [override] => false or $_REQUEST['format'],
                               - [http_accept] => boolean.

      :param array $opts: Options are:
      :param string|false $ext: The contoller defined extension.

      :returns: string
