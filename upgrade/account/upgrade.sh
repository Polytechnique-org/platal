#!/bin/sh

echo "Upgrading database"
mysql x4dat < *.sql

echo "Updating birthday date"
php birthday.php
