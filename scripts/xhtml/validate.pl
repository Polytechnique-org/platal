#!/usr/bin/perl

use LWP::UserAgent;
use HTTP::Request::Common 'POST';

print LWP::UserAgent
  ->new
  ->request(
            POST 'http://amaretto.inria.fr:8080/w3c-markup-validator/check',
            Content_Type => 'form-data',
            Content      => [
                             output => 'xml',
                             uploaded_file => [$ARGV[0]],
                            ]
           )->as_string;

