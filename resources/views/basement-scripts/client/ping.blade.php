#!/bin/bash
set -exv
source "\$HOME/.spork/config.env"
DATA=\$(echo '{"last_ping_at": "'\$(date -u +"%Y-%m-%d %H:%M:%S")'"}')
curl -X PUT -H "Authentication: Bearer \$SPORK_TOKEN" \
-H 'Content-type: application/json' -H 'Accept: application/json' \
-d  "\$DATA" {{ route('server.update', [$credential->id]) }} \
--user-agent "$USERAGENT"
echo "";
