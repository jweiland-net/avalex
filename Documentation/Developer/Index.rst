..  include:: /Includes.rst.txt


..  _developer-manual:

================
Developer manual
================

Usage of Events
===============

You can modify the behaviour of some tasks using PSR-11 (Events).

PostProcessApiResponseContentEvent
----------------------------------

This event is triggered after the content from avalex server has been
retrieved. Here you can adopt changes to the content before it will be written
to TYPO3 cache. This is useful to modify contained links like email addresses.

EXT:avalex itself uses this event to modify links in retrieved content. Have a
look into file `EventListener/UpdateLinksInAvalexContentEventListener.php` to
use that as an example for your implementation.
