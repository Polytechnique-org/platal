#! /bin/perl -w
#
use strict;

my $rcfile = shift;
my $list   = shift;
my $spool  = "/var/spool/platal/archives/";
my $tmpbox = "mytmpbox.mbox";

my %conv;
$conv{'Jan'} = "01";
$conv{'Feb'} = "02";
$conv{'Mar'} = "03";
$conv{'Apr'} = "04";
$conv{'May'} = "05";
$conv{'Jun'} = "06";
$conv{'Jul'} = "07";
$conv{'Aug'} = "08";
$conv{'Sep'} = "09";
$conv{'Oct'} = "10";
$conv{'Nov'} = "11";
$conv{'Dec'} = "12";

my $mail  = "";
my $line  = "";
my $odir  = "";

my $y = '';
my $m = '';


while(<>) {
    $line = $_;
    if($line =~ /^From +[^@ ]*@[^@ ]* +[a-z]* +([a-z]*) +\d* +\d*:\d*:\d* +(\d*)$/i) {
        if ($conv{$1} ne $m || $y ne $2) {
            if($odir) {
                close FILE;
                $odir = $spool.$list."/$y/$m";
                system("mkdir -p $odir") unless (-d $odir);
                system("mhonarc -add -outdir $odir -rcfile $rcfile $tmpbox");
            } else {
                $odir = 1;
            }
                
            $m = $conv{$1};
            $y = $2;
            open FILE,"> $tmpbox";
        }
    }
    print FILE $line;
}

if($odir) {
    close FILE;
    unlink $tmpbox;
}
