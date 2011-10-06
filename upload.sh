#!/bin/bash

curl -i -X POST -d "`cat /var/www/sync/example.json`" http://localhost/sync/upload.php
