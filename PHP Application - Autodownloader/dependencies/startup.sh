#!/bin/bash
service transmission-daemon start
nodejs /var/www/dependencies/node_socket-io/app.js
