@php
    $route = function (...$params) {
        return str_replace('link.','', route(...$params));
    };
    $url = function (...$params) {
        return str_replace('link.','', url(...$params));
    };
@endphp#!/bin/bash
set -exv

#####################################################
# ENV Configuration
#####################################################

export USERAGENT="`echo $USER`@`hostname`:installer"
export DEBIAN_FRONTEND=noninteractive
source /etc/lsb-release

####################################################
# Libraries/Functions
####################################################

apt_install_prereqs() {
    apt-get install -y curl openssh-client jq
}
update_server() {
    source "$HOME/.spork/config.env"
    curl -X PUT -H "Authentication: Bearer $SPORK_TOKEN" -H 'Content-type: application/json' -H 'Accept: application/json' -d  "$1" {{ route('server.update', [$credential->id]) }} --user-agent "$USERAGENT"
}

add_service_to_server() {
    source "$HOME/.spork/config.env"
    curl -X POST -H "Authentication: Bearer $SPORK_TOKEN" -H 'Content-type: application/json' -H 'Accept: application/json' -d  "$1" {{ route('server.service', [$credential->id]) }} --user-agent "$USERAGENT"
}

define_scripts(){
    mkdir -p "$HOME/.spork/scripts"
    cat >"$HOME/.spork/scripts/booted.sh" <<EOF
{!! view('basement-scripts.client.booted', compact('credential')) !!}
EOF
    chmod +x "$HOME/.spork/scripts/booted.sh"

    cat >"$HOME/.spork/scripts/turning-off.sh" <<EOF
{!! view('basement-scripts.client.turning-off', compact('credential')) !!}
EOF
    chmod +x "$HOME/.spork/scripts/turning-off.sh"

    cat >"$HOME/.spork/scripts/ping.sh" <<EOF
{!! view('basement-scripts.client.ping', compact('credential')) !!}
EOF
    chmod +x "$HOME/.spork/scripts/ping.sh"

    cat >"$HOME/.spork/scripts/ssh-reconnect.sh" <<EOF
{!! view('basement-scripts.client.ssh-reconnect', compact('credential')) !!}
EOF
    chmod +x "$HOME/.spork/scripts/ssh-reconnect.sh"
}

# Creation link
SERVER_DATA()
{
    NAME=$(hostname -s | xargs)
    PUBLIC_IP={{request()->ip()}}
    DISK_SIZE=$(df | grep -v run | grep -v sys | awk '{print $2}' | head -1 | tail -1 | xargs)
    MEMORY_KB=$(cat /proc/meminfo | grep memtotal -i | awk '{print $2}')
    MEMORY=$(echo $(($MEMORY_KB / 1024)) | xargs)
    KERNEL=$(uname -r | xargs)
    CPU_THREADS=$(nproc --all  | xargs)
    BOOTED_AT=$(uptime -s)
    source /etc/lsb-release
    cat {!! '<<EOF' !!}
{!! json_encode([
    'name' => '$NAME',
    'ip_address' => '$PUBLIC_IP',
    'memory' => '$MEMORY',
    'disk' => '$DISK_SIZE',
    'host_created_at' => now()->format('Y-m-d H:i:s'),
    'kernel' => '$KERNEL',
    'distro' => '$DISTRIB_ID',
    'threads' => '$CPU_THREADS',
    'status' => 'provisioning',
    'booted_at' => '$BOOTED_AT',
    'os' => '$DISTRIB_DESCRIPTION',
    'server_id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
], JSON_PRETTY_PRINT) !!}
EOF
}

create_server() {
    SERVER=$(curl -X POST -H 'Content-type: application/json' -H "Authentication: Bearer {{ $credential->api_key }}" -H 'Accept: application/json' -d  "$(SERVER_DATA)" "{{ route('server.create') }}" --user-agent "$USERAGENT")

    echo ""
    echo "$SERVER"
    echo ""

    MESSAGE=$(echo $SERVER | jq -r ".message")
    ERRORS=$(echo "$SERVER" | jq -r ".errors")
    if [[ "$MESSAGE" != null ]]; then
        clear
        echo 'Error creating server.'
        echo "$(SERVER_DATA)"
        echo "ERRORS: $MESSAGE"
        echo "ERRORS: $SERVER"
        exit 1;
    fi
    if [[ "$ERRORS" != null ]]; then
        echo 'Error creating server.'
        echo $SERVER;
        echo "$(SERVER_DATA)"
        echo "ERRORS: $ERRORS"
        exit 1;
    fi
    TOKEN=$(echo "$SERVER" | jq -r ".access_token")

    mkdir -p "$HOME/.spork/logs"

    # Is server scoped token, and the server owner's credential_id
    echo "{ \"token\": \"$(echo "$TOKEN")\", \"credential_id\": {{$credential->id}} }" > "$HOME/.spork/config.json"
    cat >"$HOME/.spork/config.env" <<EOL
export SPORK_TOKEN="$(echo $TOKEN)"
export SPORK_CREDENTIAL_ID={{$credential->id}}
EOL

    SSH_PUBLIC_KEY="{{ $credential->getPublicKey() }}"

    mkdir -p /root/.ssh
    touch  /root/.ssh/authorized_keys
    echo $SSH_PUBLIC_KEY >> /root/.ssh/authorized_keys

    update_server {!! escapeshellarg(json_encode(['status' => 'ssh_ready'])) !!}
}

#####################################################
# Pre-installation
#####################################################
apt_install_prereqs

# In case we ever loose connection
if [ ! -f "$HOME/.spork/config.env" ]; then
    echo "No server env found."
    if [ -f "$HOME/.spork/config.json" ]; then
        echo "Migrating server config."
        export SPORK_TOKEN=$(cat "$HOME/.spork/config.json" | jq -r '.token')
        export SPORK_CREDENTIAL_ID=$(cat "$HOME/.spork/config.json" | jq -r '.credential_id')
cat >"$HOME/.spork/config.env" <<EOL
export SPORK_TOKEN="$(echo $SPORK_TOKEN)"
export SPORK_CREDENTIAL_ID="$(echo SPORK_CREDENTIAL_ID)"
EOL

        echo 'Migration complete.';
    else
        echo "No server config found."
    fi
fi

#####################################################
# Create the server, and define the environment vars
#####################################################
if [ ! -f "$HOME/.spork/config.env" ]; then
    create_server
fi

#####################################################
# Detection of services
#####################################################
if [[ "$(which nginx)" != "" ]]; then
    add_service_to_server {!! escapeshellarg(json_encode(['service' => 'nginx'])) !!}
fi

if [[ "$(which apache2)" != "" ]]; then
    add_service_to_server {!! escapeshellarg(json_encode(['service' => 'apache2'])) !!}
fi

if [[ "$(which ruby)" != "" ]]; then
    add_service_to_server {!! escapeshellarg(json_encode(['service' => 'ruby'])) !!}
fi

if [[ "$(which php)" != "" ]]; then
    add_service_to_server {!! escapeshellarg(json_encode(['service' => 'php'])) !!}
fi

if [[ "$(which node)" != "" ]]; then
    add_service_to_server {!! escapeshellarg(json_encode(['service' => 'node'])) !!}
fi

if [[ "$(which supervisord)" != "" ]]; then
    add_service_to_server {!! escapeshellarg(json_encode(['service' => 'supervisord'])) !!}
fi

if [[ "$(which redis)" != "" ]]; then
    add_service_to_server {!! escapeshellarg(json_encode(['service' => 'redis'])) !!}
fi

if [[ "$(which mysql)" != "" ]]; then
    add_service_to_server {!! escapeshellarg(json_encode(['service' => 'mysql'])) !!}
fi

if [[ "$(which postgres)" != "" ]]; then
    add_service_to_server {!! escapeshellarg(json_encode(['service' => 'postgres'])) !!}
fi

if [[ "$(which mongo)" != "" ]]; then
    add_service_to_server {!! escapeshellarg(json_encode(['service' => 'mongo'])) !!}
fi

if [[ "$(which docker)" != "" ]]; then
    add_service_to_server {!! escapeshellarg(json_encode(['service' => 'docker'])) !!}
fi

if [[ "$(which kvm)" != "" ]]; then
    add_service_to_server {!! escapeshellarg(json_encode(['service' => 'kvm'])) !!}
fi

if [[ "$(which vagrant)" != "" ]]; then
    add_service_to_server {!! escapeshellarg(json_encode(['service' => 'vagrant'])) !!}
fi

if [[ "$(which virtualbox)" != "" ]]; then
    add_service_to_server {!! escapeshellarg(json_encode(['service' => 'virtualbox'])) !!}
fi

if [[ "$(which chef)" != "" ]]; then
    add_service_to_server {!! escapeshellarg(json_encode(['service' => 'chef'])) !!}
fi

if [[ "$(which puppet)" != "" ]]; then
    add_service_to_server {!! escapeshellarg(json_encode(['service' => 'puppet'])) !!}
fi

if [ -f /etc/ssh/sshd_config ]; then
    add_service_to_server {!! escapeshellarg(json_encode(['service' => 'sshd'])) !!}
fi

#####################################################
# User configuration
#####################################################
if [ ! -f /root/.ssh/authorized_keys ]; then
    mkdir -p /root/.ssh
    touch  /root/.ssh/authorized_keys
    echo "{{ $credential->getPublicKey() }}" >> /root/.ssh/authorized_keys
fi

if [ ! -f /root/.ssh/id_ed25519 ]; then
    ssh-keygen -t ed25519 -f /root/.ssh/id_ed25519 -N ""
fi


if [[ $(which zerotier-cli) == "zerotier-cli not found" ]]; then
    curl -s https://install.zerotier.com | bash
    zerotier-cli join 0cccb752f75fdc79

    echo "Zerotier joined, please authorize the server on the dashboard"
    sleep 60
fi

IPV4=$(zerotier-cli get 0cccb752f75fdc79 ip4)

INTERNAL=$(echo '{"internal_ip_address": "'$IPV4'"}')
update_server "$INTERNAL"

#####################################################
# Post-installation
#####################################################
update_server {!! escapeshellarg(json_encode(['status' => 'ready'])) !!}

(crontab -l ; echo "@reboot $HOME/.spork/scripts/booted.sh")| crontab -
(crontab -l ; echo "*/15 * * * * $HOME/.spork/scripts/ping.sh")| crontab -
cp $HOME/.spork/scripts/turing-off.sh /etc/rc6.d/K01turning-off

#####################################################
# Cleanup
#####################################################
apt autoremove -y
apt clean -y
rm -rf /var/lib/apt/lists/*
rm -rf /tmp/*
rm -rf /var/tmp/*
truncate -s 0 /var/log/*.log

#####################################################
# Reboot
#####################################################
update_server '{"turned_off_at": "'$(date -u +"%Y-%m-%d %H:%M:%S")'"}'

reboot
