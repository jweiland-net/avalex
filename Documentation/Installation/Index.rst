..  include:: /Includes.rst.txt


..  _installation:

============
Installation
============

..  rst-class:: bignums

1.  Start Analysis

    As a first step you should enter your domain name at https://www.avalex.de
    and start the analysis. The web site is automatically scanned for services
    used (i.e. Google Analytics, Facebook Pixel, Matomo, etc.).

    ..  figure:: ../Images/ScanWebsite.png
        :width: 300px
        :alt: Scan your website

2.  Add undetected services manually

    If a service is not detected automatically, it can be added manually by
    searching for the name of the service (over 300 services are currently
    supported, more are added as needed). A search for ``Google`` offers a list
    of 16 services to choose from.

    ..  figure:: ../Images/DetectedServices.png
        :width: 300px
        :alt: Detected services

3.  Download TYPO3 ``avalex`` extension

    After completing the data you can download the TYPO3 extension directly
    from the Avalex site, however we recommend always using the latest version
    from the TYPO3 Extension Repository (TER) or Packagist (Composer).

4.  Install the ``avalex`` extension

    In the next step install the extension (Note: when upgrading from version
    1.0.0 of the extension, please run the update script in the extension
    manager). No other settings are need to be made in the extension manager.

5.  Add ``avalex`` plugin

    Visit a page in the TYPO3 backend and click on ``+ Content`` to
    open the Content Element Wizard. Navigate to tab ``Avalex`` and choose
    one of the available plugins.
    No further settings are necessary. The text output will be with standard
    ``h2`` headers and therefore should blend with the normal content.

    ..  figure:: ../Images/AddPlugin.png
        :width: 300px
        :alt: Add frontend plugin

6.  Create ``avalex`` configuration record

    Now it is required to add a configuration which contains the Avalex API
    key. Use the ``Add configuration`` button at one of the avalex plugins in
    the ``Page`` view for that.

    ..  figure:: ../Images/AddConfigurationButton.jpg
        :width: 300px
        :alt: Add configuration button

7.  Add ``avalex`` Site Set

    Add the `Avalex Site Set` to `Sets for this Site` in TYPO3 Sites module:

    ..  figure:: ../Images/AddSiteSet.png
        :width: 300px
        :alt: Add avalex Site Set

8.  Configure ``avalex``

    In the record you select the root page of your web site and the API key
    for the domain (which you get from the Avalex web site after registering
    for the service).

    ..  figure:: ../Images/CreateRecordTCA.png
        :width: 300px
        :alt: Create record
