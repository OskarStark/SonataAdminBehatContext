# Changelog

[![Build Status](https://travis-ci.org/OskarStark/SonataAdminBehatContext.svg?branch=master)](https://travis-ci.org/OskarStark/SonataAdminBehatContext)

## Upgrading from 1.x to 2.0

- The `friends-of-behat/symfony-extension` is used instead of `behat/symfony2-extension` (that is not maintained anymore)
- The context `SonataAdminContext` has been split into two separate contexts.

  - `SonataAdminContext` contains step definitions for `sonata-project/admin-bundle`.
  - `SonataAdminUserBundleContext` contains step definitions for `sonata-project/user-bundle`.

Before:
```yaml
default:
    suites:
        default:
            contexts:
                - OStark\Context\SonataAdminContext:
                    userManager: '@sonata.user.user_manager'
                    tokenStorage: '@security.token_storage'
                    session: '@session'
```

After:
```yaml
default:
    suites:
        default:
            contexts:
                - OStark\Context\SonataAdminContext:
                - OStark\Context\SonataAdminUserBundleContext:  
                    userManager: '@sonata.user.user_manager'
                    tokenStorage: '@security.token_storage'
                    session: '@session'
```
