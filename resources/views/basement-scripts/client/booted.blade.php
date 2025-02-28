#!/bin/bash
set -exv
source "\$HOME/.spork/config.env"

DATA=\$(echo '{"booted_at": "'\$(uptime -s)'", "turned_off_at": null}')
curl -X PUT \
-H "Authentication: Bearer \$SPORK_TOKEN" \
-H 'Content-type: application/json' \
-H 'Accept: application/json' \
-d "\$DATA" \
{{ route('server.update', [$credential->id]) }} --user-agent "$USERAGENT"
echo "";
