#!/usr/bin/env bash

set -ex

rm -rf .plugin
mkdir .plugin
curl -s https://downloads.wordpress.org/plugin/contact-form-7.5.5.1.zip -o plugin.zip
unzip plugin.zip -d .plugin
rm -f plugin.zip