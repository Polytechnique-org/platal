#! /usr/bin/perl -w

use strict;
my $dir = $ARGV[0];
my %classes;

open LINES,"grep -r 'class=[^ ]*' $dir |";
while(<LINES>) {
	my @sub = split /</,$_;
	foreach my $tag (@sub) {
		if($tag =~ /([a-zA-Z0-9]+)[^>]*class=['"]?([^ '">]*).*/) {
			my $index = lc("$1.$2");
			$classes{$index} = 1;
		}
	}
}
close LINES;

print join "\n",sort(keys(%classes));
