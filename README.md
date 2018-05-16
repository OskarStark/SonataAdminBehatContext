# SonataAdminBehatContext

[![Build Status](https://travis-ci.org/OskarStark/SonataAdminBehatContext.svg?branch=master)](https://travis-ci.org/OskarStark/SonataAdminBehatContext)

## Installation

```console
composer require oskarstark/sonata-admin-behat-context
```

## Usage
Enable the context in your `behat.yml`:

```yaml
default:
    suites:
        default:
            contexts:
                - OStark\Context\SonataAdminContext
```

## Available steps

| Step | Regex |
| --- | --- |
| I reset the filters | `/^(?:\|I )reset the filters$/` |
| I filter the list | `/^(?:\|I )filter the list$/` |
| I check All-Elements checkbox | `/^(?:\|I )check All-Elements checkbox$/` |
| I should see "**success**" flash message with "**Sucessfully updated entity!**" | `/^(?:\|I )should see "([^"]*)" flash message with "(?P<text>(?:[^"]\|\\")*)"$/` |

