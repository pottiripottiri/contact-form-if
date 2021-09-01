#!/usr/bin/env bash

cd `dirname $0`

rm -rf ../svn/includes
rm -rf  ../svn/languages
rm -f  ../svn/contact-form-if.php
rm -f  ../svn/LICENSE
rm -f  ../svn/readme.txt
rm -f  ../svn/screenshot-1.png

cp -prf ../includes ../svn/includes
cp -prf ../languages ../svn/languages
cp -pf ../contact-form-if.php ../svn/contact-form-if.php
cp -pf ../LICENSE ../svn/LICENSE
cp -pf ../readme.txt ../svn/readme.txt
cp -pf ../screenshot-1.png ../svn/screenshot-1.png
