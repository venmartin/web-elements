#!/bin/bash
# Deploy the web-elements website to the server
# This script is run from the root of the web-elements project
# It assumes that the server is already set up with the correct
# directory structure and permissions
# It also assumes that the server is already set up with the correct
# SSH keys for passwordless login

set -e
set -u

rsync -aP ./ scaleupconsulting.com.au:www/web-elements.scaleupconsulting.com.au/public_html/
