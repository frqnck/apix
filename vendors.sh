#!/bin/sh

if [ ! -d ./vendor/php ]
  then
    phing build-vendor
fi
