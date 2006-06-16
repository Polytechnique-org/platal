#! /usr/bin/perl -w
#***************************************************************************
#*  Copyright (C) 2003-2006 Polytechnique.org                              *
#*  http://opensource.polytechnique.org/                                   *
#*                                                                         *
#*  This program is free software; you can redistribute it and/or modify   *
#*  it under the terms of the GNU General Public License as published by   *
#*  the Free Software Foundation; either version 2 of the License, or      *
#*  (at your option) any later version.                                    *
#*                                                                         *
#*  This program is distributed in the hope that it will be useful,        *
#*  but WITHOUT ANY WARRANTY; without even the implied warranty of         *
#*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the          *
#*  GNU General Public License for more details.                           *
#*                                                                         *
#*  You should have received a copy of the GNU General Public License      *
#*  along with this program; if not, write to the Free Software            *
#*  Foundation, Inc.,                                                      *
#*  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA                *
#***************************************************************************


use strict;
my %classes;
my %styles;

my $red="[1;37;31m";
my $yel="[1;37;33m";
my $blu="[1;37;34m";
my $whi="[1;37;16m";
my $gra="[0m";


sub parse_dir($) {
    my $dir = shift;
    my @dirs;
    opendir DIR,$dir;
    while(my $a = readdir DIR) {
        if( -d $dir."/".$a ) {
            push @dirs,$dir."/".$a unless($a eq '.' or $a eq '..' or $a eq 'CVS');
        } else {
            &parse_file($dir."/".$a) if($a =~ /\.(php|tpl|htm[l]?)$/i);
        }
    }
    closedir DIR;
    foreach $dir (@dirs) { &parse_dir($dir); }
}

sub parse_file($) {
    my $file = shift;
    open FILE,"<$file";
    my $text = join '',(<FILE>);
    $text =~ s/[\s\t\n]+/ /g;
    &get_classes($file,$text);
    close FILE;
}

sub get_classes($$) {
    my $file = shift;
    my $text = shift;
    while ($text =~ /<( *[^\/](?:->|[^>])*) *>(.*)$/) {
        &parse_tag($file,$1);
        $text = $2;
    }
}

sub class_add($$) {
    my $file = shift;
    my $tag = shift;
    my $class = shift;
    if (defined($classes{"$tag.$class"})) {
        $classes{"$tag.$class"} .= " $file";
    } else {
        $classes{"$tag.$class"} = $file;
    }
}

sub parse_tag($$) {
    my $file = shift;
    my $tag = shift;

    # tag interdits en xhtml
    print STDERR "${red}XHTML error: ${yel}<$1> (majuscules) ${blu}($file)${gra}\n"
        if($tag =~ /^((?:[A-Z]+[a-z]*)+)( |$)/);
    print STDERR "${red}XHTML error: ${yel}<$1> ${blu}($file)${gra}\n"
        if($tag =~ /^(b|i|u|big|small|font|center)( |$)/);
    print STDERR "${red}XHTML error: ${yel}<$1> sans '/' ${blu}($file)${gra}\n"
        if($tag =~ /^(br|hr|img|link|input)( [^\/]*)?$/);

    print STDERR "${red}XHTML error: ${yel}attribut $1 sans = ${blu}($file)${gra}\n"
        if($tag =~ / (checked|disabled|multiple|readonly)( |$)/);
    print STDERR "${red}XHTML error: ${yel}attribut $1 ${blu}($file)${gra}\n"
        if($tag =~ / (align|width|border|color|valign)=/);
   
    # récupération des classes utilisées ...
    if($tag =~ /^(\w+).* class=('{[^}]*}'|"{[^}]*}"|'[^{}']*'|"[^{}"]*")/) {
        my $t = lc($1);
        $2 =~ /^['"](.*)['"]$/;
        my $c = lc($1);
        if($c =~ /^{ ?cycle.* values=('[^']*'|"[^"]*")/) {
            my @cycle = split /['",]/,$1;
            foreach my $cl (@cycle) {
                    &class_add($file,$t,$cl) if($cl);
            }
        } else {   
            &class_add($file,$t,$c);
        }
    }

    #récupération des styles utilisés ...
    if($tag =~ /^(\w+).* style=('{[^}]*}'|"{[^}]*}"|'[^{}']*'|"[^{}"]*")/) {
        my $t = lc($1);
        $2 =~ /^['"](.*)['"]$/;
        my $s = lc($1);
        if (defined($styles{"$t => $s"})) {
            $styles{"$t => $s"} .= " $file";
        } else {
            $styles{"$t => $s"} = $file;
        }
    }
}

foreach my $dir (@ARGV) {
    &parse_dir($dir);
}

print "\n$blu..:: Classes ::..$gra\n\n";
foreach my $key (sort(keys(%classes))) {
    print $key,"\n";
}

print "\n$blu..:: Styles ::..$gra\n\n";
foreach my $key (sort(keys(%styles))) {
    print $key,"\t",$whi,$styles{$key},$gra,"\n";
}

print "\n";

# vim:set et ts=4 sts=4 sw=4:
