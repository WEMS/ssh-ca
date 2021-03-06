#!/bin/bash
USER=root
KEY=$HOME/.ssh/id_rsa
PORT=8002
URL=http://server.name:$PORT/request-cert
KEYTYPE=rsa
KEYBITS=4096

generate_key()
{
    mkdir -p $HOME/.ssh
    chmod 700 $HOME/.ssh
    ssh-keygen -q -t $KEYTYPE -b $KEYBITS -C "$HOSTNAME" -f $HOME/.ssh/id_rsa -N ""
    chmod 600 $KEY
}

sign_key()
{
    rm -f ${KEY}-cert.pub
    curl -s -F "user=$USER" -F "key=@${KEY}.pub" $URL -o ${KEY}-cert.pub
    if [ ! -f ${KEY}-cert.pub ] ; then
        echo failed to sign key
        exit 1
    else
        EXPIRES=$(ssh-keygen -L -f ${KEY}-cert.pub | grep Valid: | awk '{print $NF}')
        echo Access granted to WEMS managed systems until $(date --date="$EXPIRES")
        echo Start a new terminal window or run ${0##*/} to extend access
    fi
}

if [ ! -f $KEY ] ; then
    generate_key
fi

sign_key
