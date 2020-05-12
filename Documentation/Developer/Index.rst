.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _developer-manual:

Developer manual
=================

Usage of hooks
--------------

You can modify the behaviour of some tasks using hooks. There are several HookInterfaces
that make your life easier. You can find them in `Classes/Hooks/`. They are described in this
manual.

ApiService hooks
................

The first step is to add your class to the hook object array in your ext_localconf.php.
You can use `Your\Extension\Hooks\YourCustomAvalexHook::class` for newer TYPO3 versions if you want.
Now you can proceed with adding the specific interfaces shown below.

Example:

.. code-block:: php

  $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['avalex']['JWeiland\\Avalex\\Service\\ApiService'][] = 'Your\\Extension\\Hooks\\YourCustomAvalexHook';

Modify the configuration before sending the request
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can use the `JWeiland\Avalex\Hooks\ApiService\PreApiRequestHookInterface` in your extension to
modify the configuration array that contains the api_key and the domain before sending the request
to avalex.

Modify the content before caching and rendering it
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

You can use the `JWeiland\Avalex\Hooks\ApiService\PostApiRequestHookInterface` in your extension to
modify the content API returned. Please make sure to take a look at the public functions of the ApiService
which will be passed to the hook as second parameter. The first parameter $content is a reference so
you can modify the output completely.

.. code-block:: php

  <?php
  namespace Your\Extension\Hooks;

  use JWeiland\Avalex\Hooks\ApiService\PostApiRequestHookInterface;
  use JWeiland\Avalex\Service\ApiService;

  class ModifyContentHook implements PostApiRequestHookInterface
  {
      public function postApiRequest(&$content, ApiService $apiService)
      {
          if ($apiService->getCurlInfo()['http_code'] === 200) {
              // add class to p tags
              $content = str_replace('<p>', '<p class="privacy">', $content);
          }
      }
  }
