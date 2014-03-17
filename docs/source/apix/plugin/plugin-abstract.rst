Apix\\Plugin\\PluginAbstract
============================

.. php:namespace:: Apix\Plugin

.. php:class:: PluginAbstract

   .. php:attr:: $adapter

      Holds a plugin's adapter.

      :var: closure|object

   .. php:attr:: $options

      Holds an array of plugin's options.

      :var: array

   .. php:method:: PluginAbstract::__construct()

      Constructor

      :param mix $options: Array of options

   .. php:method:: PluginAbstract::checkAdapterClass()

      Checks the plugin's adapter comply to a class/interface

      :param object $adapter:
      :param object $class:

      :throws: \\RuntimeException

      :returns: true

   .. php:method:: PluginAbstract::setOptions()

      Sets and merge the defaults options for this plugin

      :param mix $options: Array of options if it is an object set as an adapter

   .. php:method:: PluginAbstract::getOptions()

      Gets this plugin's options

      :returns: array

   .. php:method:: PluginAbstract::setAdapter()

      Sets this plugin's adapter

      :param closure|object $adapter:

   .. php:method:: PluginAbstract::getAdapter()

      Gets this plugin's adapter

      :returns: mix

   .. php:method:: PluginAbstract::log()

      Just a shortcut for now. This is TEMP and will be moved elsewhere!
      TODO: TEMP to refactor
