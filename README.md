# SonataAdminBehatContext

[![Build Status](https://travis-ci.org/OskarStark/SonataAdminBehatContext.svg?branch=master)](https://travis-ci.org/OskarStark/SonataAdminBehatContext)

## Installation

```console
composer require oskarstark/sonata-admin-behat-context --dev
```

## Usage
Enable the context in your `behat.yml`:

```yaml
default:
    suites:
        default:
            contexts:
                - OStark\Context\SonataAdminContext:
                    userManager: '@sonata.user.user_manager'
                    tokenStorage: '@security.token_storage'
                    session: '@session'

                # make sure to enable MinkContext!
                - Behat\MinkExtension\Context\MinkContext
```

## Available steps

### Filters

| Step | Regex |
| --- | --- |
| I reset the filters | `/^(?:\|I )reset the filters$/` |
| I filter the list | `/^(?:\|I )filter the list$/` |
| I should see the filters | `/^(?:\|I )should see the filters$/` |
| I should not see the filters | `/^(?:\|I )should not see the filters$/` |
| I click filters | `/^(?:\|I )click filters$/` |
| I select "**Email**" filter | `/^(?:\|I )select "([^"]*)" filter$/` |
| I should see "**Firstname**" filter | `/^(?:\|I )should see "([^"]*)" filter$/` |
| I filter "**ID**" with "**1**" | `/^(?:\|I ) filter "([^"]*)" with "([^"]*)"$/` |

### Checkboxes

| Step | Regex |
| --- | --- |
| I check checkbox in row "**1**" | `/^(?:\|I )check checkbox in row "([^"]*)"$/` |
| I check All-Elements checkbox | `/^(?:\|I )check All-Elements checkbox$/` |

### Flash-Messages

| Step | Regex |
| --- | --- |
| I should see "**success**" flash message with "**Sucessfully updated entity!**" | `/^(?:\|I )should see "([^"]*)" flash message with "(?P<text>(?:[^"]\|\\")*)"$/` |
| I close flash message | `/^(?:\|I )close flash message$/` |

### User & Authentication

| Step | Regex |
| --- | --- |
| I delete last created user | `/^(?:\|I )delete last created user$/` |
| I am authenticated User | `/^I am an authenticated User$/` |
| I have role "**ROLE_SONATA_ADMIN**" | `/^I have role "([^"]*)"$/` |
| I am authenticated as User "**user@example.com**" | `/^I am authenticated as User "([^"]*)"$/` |
| I logout User | `/^(?:\|I )logout User$/` |
| I impersonate user "**user@example.com**" | `/^(?:\|I )impersonate user "([^"]*)"$/` |

### Batch-Actions

| Step | Regex |
| --- | --- |
| I select "**Delete**" from batch actions | `/^(?:\|I )select "([^"]*)" from batch actions$/` |

### List-Columns

| Step | Regex |
| --- | --- |
| I should see "**ID, Firstname, Lastname**" list columns | `/^(?:\|I )should see "([^"]*)" list columns$/` |
| I should not see "**Email, Password**" list columns | `/^(?:\|I )should not see "([^"]*)" list columns$/` |
| I should see "**ID**" list column |`/^(?:\|I )should see "([^"]*)" list column$/`|
| I should not see "**Password**" list column |`/^(?:\|I )should not see "([^"]*)" list column$/`|
| I should see "**user@example.com**" in row "**1**" on column "**Email**"| `/^(?:\|I )should see "([^"]*)" in row "([^"]*)" on column "([^"]*)"$/` |

### Form fields

| Step | Regex |
| --- | --- |
| the field "**Firstname**" should be empty | `/^the "(?P<field>(?:[^"]\|\\")*)" field should be empty$/` |
| the "**Firstname**" field should be empty | `/^the field "(?P<field>(?:[^"]\|\\")*)" should be empty$/` |
| the field "**Password**" should not be empty | `/^the "(?P<field>(?:[^"]\|\\")*)" field should not be empty$/` |
| the "**Password**" field should not be empty | `/^the field "(?P<field>(?:[^"]\|\\")*)" should not be empty$/` |
