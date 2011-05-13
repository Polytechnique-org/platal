#!/bin/sh

mysqldump -h murphy geocoding profile_addresses | sed -e 's/profile_addresses/tmp_profile_addresses/' | mysql x5dat
mysqldump -h murphy geocoding profile_addresses_components | mysql x5dat
mysqldump -h murphy geocoding profile_addresses_components_enum | mysql x5dat

