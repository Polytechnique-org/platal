#! /usr/bin/perl -w

my $mode = shift;
#mode 1 = mise à jour obligatoire
#mode autre = mise à jour si flag

my @args = ("mysql x4dat <recherche.sql");
my @args2 = ("php recherche.php");

if ($mode==1) {
    system(@args);
    system(@args2);
}
else {
    open(INFILE,'</tmp/flag_recherche');
    $_ = <INFILE>;
    if (/1/) {
        system(@args);
        system(@args2);
    }
    close INFILE;
    open(OUTFILE,'>/tmp/flag_recherche');
    print OUTFILE "0";
    close OUTFILE;
}
