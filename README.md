# What is spork?
Spork is a personal multi-tool, a place to let my creativity run wild and automate random tasks.

# How do I run it?
If you'd like to get this project up and running, it's pretty straight forward using docker and Laravel sail. Other deployment methods are not supported officially, but will likely work with little issue. I deploy this personally using Laravel Forge.

(This bin/sail script will automatically run composer install the first time if sail isn't installed already)
```bash
$ ./bin/sail up -d 
```
(On Mac or Linux)

# What does it do? Features?
 - [x] Syncing domains from Namecheap
 - [x] Syncing servers from Laravel Forge
 - [x] Syncing registrar domains from Cloudflare
 - [x] Syncing zones from Cloudflare
 - [x] RSS Syncing & updating
 - [x] DNS Validation/Verification
 - [x] Projects, with relations to domains, servers, rss feeds, pages (and redirects), and people.
 - [x] SSL configuration via forge.
 - [x] Adding and configuring purchased domains to Cloudflare & Laravel Forge, and provision out the routing automatically. (Still in progress)
 - [x] Plaid integration for asset syncing
 - [x] Task management, tasks are created per project
-  [x] Email syncing via IMAP
-  [x] Tagging for Transactions based on manually defined rules
-  [x] Fetching Weather for a location
-  [x] A programmatic code editing system, which allows for mass refactoring based on programmatic logic; and a limited interface for controlling event listeners (controlling container bindings is a WIP). 
-  [x] Jira task syncing
-  [x] Running scripts on servers remotely
-  [x] Beeper authentication via Email Token


## Planned 
 - [ ] Built in [matrix.org client](https://matrix.org) I actually have code written in `docker/matrix-bot` to facilitate this, but I've been unsuccessful getting end-to-end encryption working.
 - [ ] Dynamic routes and domains via a page editor
 - [ ] IFTTT inspired Dynamic Automations
 - [ ] Budgeting per project
 - [ ] Built-in project researching for Scholarly articles and browsing the web.
 - [ ] Calendar integration for project and site wide events
 - [ ] Personal Dashboard 
   - [ ] List tasks from all projects
   - [ ] List events from all projects
   - [ ] List budget usage -- if applicable.
 - [ ] Domain Purchasing and Renewals

## Screenshots

![screenshot-dashboard-2024-02-25.png](/resources/screenshots/screenshot-dashboard-2024-02-25.png)
