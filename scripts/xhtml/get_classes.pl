#! /usr/bin/perl -w

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
    opendir DIR,$dir;
    while(my $a = readdir DIR) {
        if(-d $a) {
            &parse_dir($dir."/".$a) unless($a eq '.' or $a eq '..' or $a eq 'CVS');
        } else {
            &parse_file($dir."/".$a) if($a =~ /\.(php|tpl|htm[l]?)$/i);
        }
    }
    closedir DIR;
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
    print STDERR "${red}XHTML error: ${yel}<$1> ${blu}($file)${gra}\n"
        if($tag =~ /^(b|i|u|center)( |$)/);
    print STDERR "${red}XHTML error: ${yel}<$1> sans '/' ${blu}($file)${gra}\n"
        if($tag =~ /^(br|hr|img|link|input)( [^\/]*)?$/);
   
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
