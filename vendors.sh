#!/bin/sh

# /www/dev/tools/php sismo.php build --local --verbose --force

if [ ! -d ./vendor/php ]
  then
    phing build-vendor
fi
