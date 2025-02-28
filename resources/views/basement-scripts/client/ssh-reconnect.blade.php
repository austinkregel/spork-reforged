#!/bin/bash
set -exv
source "\$HOME/.spork/config.env"
SSH_PUBLIC_KEY="{{ $credential->getPublicKey() }}"
mkdir -p "\$HOME"/.ssh
touch  "\$HOME"/.ssh/authorized_keys
echo \$SSH_PUBLIC_KEY >> "\$HOME"/.ssh/authorized_keys
DATA=\$(echo '{"last_ping_at": "'\$(date -u +"%Y-%m-%d %H:%M:%S")'", "status": "reconnected"}')
curl -X PUT -H "Authentication: Bearer \$SPORK_TOKEN" \
-H 'Content-type: application/json' \
-H 'Accept: application/json' \
-d "\$DATA" {{ route('server.update', [$credential->id]) }} --user-agent "$USERAGENT"
echo "";
