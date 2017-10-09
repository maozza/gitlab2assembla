# GitLab Assembla integration
Use GitLab webhook for integrating push events to Assembla.
### Abstract:
assembla_push_webhook.php is a small web page that trigger by gitlab webhooks "Push Events" and "Tag Push Events" requests and send the commits messages info to Assembla.

## Documentation:
### Gitlab-webhook
https://docs.gitlab.com/ce/user/project/integrations/webhooks.html

### Assembla API
http://api-docs.assembla.cc/

### How does it work:
When push event is sent to the web page, it will search for #assembla-ticket-number (hash sign follow by digits) in all comments, if it finds one or more accordance of tickets, it will collect all the information from all commit message until the push event and post it to assembla 
using the Assembla API.

### How to use it.
1. start web servers with this code
1. move the config.example.php to config.php edit it and configure it.
1. On Gitlab go to webhooks and enter the server URL, on Trigger checkbox add "Push events" and "Tag push events" and click on add webhook