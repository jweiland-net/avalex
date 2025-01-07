..  include:: /Includes.rst.txt


..  _upgrade:

=======
Upgrade
=======

If you update `avalex` to a newer version, please read this section
carefully!

Upgrade to Version 9.0.0
========================

With this version we have removed compatibility for TYPO3 versions:
6, 7, 8, 9, 10, 11, 12. If you still need avalex for these versions please
install avalex in version <= 8.

We have changed the hook usage to PSR-11 (Events). If you make use of hooks
so modify the retrieved content you have to switch to EventListeners now. Please
see section `Developer` here in this manual for example usage.

With TYPO3 13 the new feature `Site Sets` was implemented. EXT:avalex relates
on that new feature. Please make sure to load our Site Set from Site module.
