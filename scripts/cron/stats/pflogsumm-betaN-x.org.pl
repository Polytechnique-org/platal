#!/usr/bin/perl -w
eval 'exec perl -S $0 "$@"'
    if 0;

use strict;
use locale;
use Getopt::Long;
# ---Begin: SMTPD_STATS_SUPPORT---
use Date::Calc qw(Delta_DHMS);
# ---End: SMTPD_STATS_SUPPORT---

my $mailqCmd = "mailq";

# Variables and constants used throughout pflogsumm
use vars qw(
    $progName
    $usageMsg
    %opts
    $divByOneKAt $divByOneMegAt $oneK $oneMeg
    @monthNames %monthNums $thisYr $thisMon
    $msgCntI $msgSizeI $msgDfrsI $msgDlyAvgI $msgDlyMaxI
    $isoDateTime
);

# Some constants used by display routines.  I arbitrarily chose to
# display in kilobytes and megabytes at the 512k and 512m boundaries,
# respectively.  Season to taste.
$divByOneKAt   = 524288;	# 512k
$divByOneMegAt = 536870912;	# 512m
$oneK          = 1024;		# 1k
$oneMeg        = 1048576;	# 1m

# Constants used throughout pflogsumm
@monthNames = qw(Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec);
%monthNums = qw(
    Jan  0 Feb  1 Mar  2 Apr  3 May  4 Jun  5
    Jul  6 Aug  7 Sep  8 Oct  9 Nov 10 Dec 11);
($thisMon, $thisYr) = (localtime(time()))[4,5];
$thisYr += 1900;

#
# Variables used only in main loop
#
# Per-user data
my (%recipUser, $recipUserCnt);
my (%sendgUser, $sendgUserCnt);
# Per-domain data
my (%recipDom, $recipDomCnt);	# recipient domain data
my (%sendgDom, $sendgDomCnt);	# sending domain data
# Indexes for arrays in above
$msgCntI    = 0;	# message count
$msgSizeI   = 1;	# total messages size
$msgDfrsI   = 2;	# number of defers
$msgDlyAvgI = 3;	# total of delays (used for averaging)
$msgDlyMaxI = 4;	# max delay

my (
    $cmd, $qid, $addr, $size, $relay, $status, $delay,
    $dateStr,
    %panics, %fatals, %warnings, %masterMsgs,
    %msgSizes,
    %deferred, %bounced,
    %noMsgSize, %msgDetail,
    $msgsRcvd, $msgsDlvrd, $sizeRcvd, $sizeDlvrd,
    $msgMonStr, $msgMon, $msgDay, $msgTimeStr, $msgHr, $msgMin, $msgSec,
    $msgYr,
    $revMsgDateStr, $dayCnt, %msgsPerDay,
    %rejects, $msgsRjctd,
    %rcvdMsg, $msgsFwdd, $msgsBncd,
    $msgsDfrdCnt, $msgsDfrd, %msgDfrdFlgs,
    %connTime, %smtpPerDay, %smtpPerDom, $smtpConnCnt, $smtpTotTime
);

# Messages received and delivered per hour
my @rcvPerHr = qw(0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0);
my @dlvPerHr = @rcvPerHr;
my @dfrPerHr = @rcvPerHr;	# defers per hour
my @bncPerHr = @rcvPerHr;	# bounces per hour
my @rejPerHr = @rcvPerHr;	# rejects per hour
my $lastMsgDay = 0;

# "doubly-sub-scripted array: cnt, total and max time per-hour
# Gag - some things, Perl doesn't do well :-(
my @smtpPerHr;
$smtpPerHr[0]  = [0,0,0]; $smtpPerHr[1]  = [0,0,0]; $smtpPerHr[2]  = [0,0,0];
$smtpPerHr[3]  = [0,0,0]; $smtpPerHr[4]  = [0,0,0]; $smtpPerHr[5]  = [0,0,0];
$smtpPerHr[6]  = [0,0,0]; $smtpPerHr[7]  = [0,0,0]; $smtpPerHr[8]  = [0,0,0];
$smtpPerHr[9]  = [0,0,0]; $smtpPerHr[10] = [0,0,0]; $smtpPerHr[11] = [0,0,0];
$smtpPerHr[12] = [0,0,0]; $smtpPerHr[13] = [0,0,0]; $smtpPerHr[14] = [0,0,0];
$smtpPerHr[15] = [0,0,0]; $smtpPerHr[16] = [0,0,0]; $smtpPerHr[17] = [0,0,0];
$smtpPerHr[18] = [0,0,0]; $smtpPerHr[19] = [0,0,0]; $smtpPerHr[20] = [0,0,0];
$smtpPerHr[21] = [0,0,0]; $smtpPerHr[22] = [0,0,0]; $smtpPerHr[23] = [0,0,0];

$progName = "pflogsumm.pl";
$usageMsg =
    "usage: $progName -[eq] [-d <today|yesterday>] [-h <cnt>] [-u <cnt>]
       [--verp_mung[=<n>]] [--verbose_msg_detail] [--iso_date_time]
       [-m|--uucp_mung] [-i|--ignore_case] [--smtpd_stats] [--mailq]
       [--help] [file1 [filen]]";

# Some pre-inits for convenience
$isoDateTime = 0;	# Don't use ISO date/time formats
GetOptions(
    "d=s"                => \$opts{'d'},
    "e"                  => \$opts{'e'},
    "help"               => \$opts{'help'},
    "h=i"                => \$opts{'h'},
    "i"                  => \$opts{'i'},
    "ignore_case"        => \$opts{'i'},
    "iso_date_time"      => \$isoDateTime,
    "m"                  => \$opts{'m'},
    "uucp_mung"          => \$opts{'m'},
    "mailq"              => \$opts{'mailq'},
    "q"                  => \$opts{'q'},
    "smtpd_stats"        => \$opts{'smtpdStats'},
    "u=i"                => \$opts{'u'},
    "verbose_msg_detail" => \$opts{'verbMsgDetail'},
    "verp_mung:i"        => \$opts{'verpMung'}
) || die "$usageMsg\n";

# internally: 0 == none, undefined == -1 == all
$opts{'h'} = -1 unless(defined($opts{'h'}));
$opts{'u'} = -1 unless(defined($opts{'u'}));

if(defined($opts{'help'})) {
    print "$usageMsg\n";
    exit;
}

$dateStr = get_datestr($opts{'d'}) if(defined($opts{'d'}));

# debugging
#open(UNPROCD, "> unprocessed") ||
#    die "couldn't open \"unprocessed\": $!\n";

while(<>) {
    next if(defined($dateStr) && ! /^$dateStr/o);
    ($msgMonStr, $msgDay, $msgTimeStr, $cmd, $qid) =
	m#^(...)\s+([0-9]+)\s(..:..:..)\s.*?(?:vmailer|postfix)[-/]([^\[:]*).*?: ([^:]+)#o;
    ($msgMonStr, $msgDay, $msgTimeStr, $cmd, $qid) =
	m#^(...)\s+([0-9]+)\s(..:..:..)\s.*?(vmailer|postfix[^\[:]*).*?: ([^:]+)#o unless($cmd);
    next unless($cmd);
    chomp;

    # snatch out log entry date & time
    ($msgHr, $msgMin, $msgSec) = split(/:/, $msgTimeStr);
    $msgMon = $monthNums{$msgMonStr};
    $msgYr = $thisYr; --$msgYr if($msgMon > $thisMon);

    # the following test depends on one getting more than one message a
    # month--or at least that successive messages don't arrive on the
    # same month-day in successive months :-)
    unless($msgDay == $lastMsgDay) {
	$lastMsgDay = $msgDay;
	$revMsgDateStr = sprintf "%d%02d%02d", $msgYr, $msgMon, $msgDay;
	++$dayCnt;
    }

    if($qid eq 'warning') {
	# regexp rejects happen in "cleanup"
	if(my($rejTyp, $rejReas, $rejRmdr) =
	    /^.*\/(cleanup)\[.*reject: ([^\s]+) (.*)$/o)
	{
	    $rejRmdr = string_trimmer($rejRmdr, 64, $opts{'verbMsgDetail'});
	    ++$rejects{$rejTyp}{$rejReas}{$rejRmdr};
	    ++$msgsRjctd;
	    ++$rejPerHr[$msgHr];
	    ++${$msgsPerDay{$revMsgDateStr}}[4];
	} else {
	    (my $warnReas = $_) =~ s/^.*warning: //o;
	    $warnReas = string_trimmer($warnReas, 66, $opts{'verbMsgDetail'});
	    ++$warnings{$cmd}{$warnReas};
	}
    } elsif($qid eq 'fatal') {
	(my $fatalReas = $_) =~ s/^.*fatal: //o;
	$fatalReas = string_trimmer($fatalReas, 66, $opts{'verbMsgDetail'});
	++$fatals{$cmd}{$fatalReas};
    } elsif($qid eq 'panic') {
	(my $panicReas = $_) =~ s/^.*panic: //o;
	$panicReas = string_trimmer($panicReas, 66, $opts{'verbMsgDetail'});
	++$panics{$cmd}{$panicReas};
    } elsif($qid eq 'reject') {
	# This could get real ugly!
	# First: get everything following the "reject: " token
	my ($rejTyp, $rejFrom, $rejRmdr) =
	    /^.* reject: ([^ ]+) from ([^:]+): (.*)$/o;
	# Next: get the reject "reason"
	my $rejReas = $rejRmdr;
	unless(defined($opts{'verbMsgDetail'})) {
	    if($rejTyp eq "RCPT") {	# special treatment :-(
#		$rejReas = (split(/[;:] /, $rejReas))[-2];
#		$rejReas =~ s/\[[0-9\.]+\]//o;
		$rejReas =~ s/^(?:.*?[:;] )(?:\[[^\]]+\] )?([^;]+);.*$/$1/oi;
#		$rejReas =~ s/^(?:.*?[:;] )(?:\[.* blocked using )?([^;]+);.*$/$1/oi;
#		$rejReas =~ s/^(?:.*?[:;] )([^;]+);.*$/$1/oi;
	    } else {
		$rejReas =~ s/^(?:.*[:;] )?([^,]+).*$/$1/o;
	    }
	}
	# stash in "triple-subscripted-array"
	++$rejects{$rejTyp}{$rejReas}{gimme_domain($rejFrom)};
	++$msgsRjctd;
	++$rejPerHr[$msgHr];
	++${$msgsPerDay{$revMsgDateStr}}[4];
    } elsif($cmd eq 'master') {
	++$masterMsgs{(split(/^.*master.*: /))[1]};
    } elsif($cmd eq 'smtpd') {
	if(/: client=/o) {
	    #
	    # Warning: this code in two places!
	    #
	    ++$rcvPerHr[$msgHr];
	    ++${$msgsPerDay{$revMsgDateStr}}[0];
	    ++$msgsRcvd;
	    ++$rcvdMsg{$qid};	# quick-set a flag
	}
# ---Begin: SMTPD_STATS_SUPPORT---
	else {
	    next unless(defined($opts{'smtpdStats'}));
	    if(/: connect from /o) {
		/\/smtpd\[([0-9]+)\]: /o;
		@{$connTime{$1}} =
		    ($msgYr, $msgMon + 1, $msgDay, $msgHr, $msgMin, $msgSec);
	    } elsif(/: disconnect from /o) {
		my ($pid, $hostID) = /\/smtpd\[([0-9]+)\]: disconnect from (.+)$/o;
		if(exists($connTime{$pid})) {
		    $hostID = gimme_domain($hostID);
		    my($d, $h, $m, $s) = Delta_DHMS(@{$connTime{$pid}},
			$msgYr, $msgMon + 1, $msgDay, $msgHr, $msgMin, $msgSec);
		    delete($connTime{$pid});	# dispose of no-longer-needed item
		    my $tSecs = (86400 * $d) + (3600 * $h) + (60 * $m) + $s;

		    ++$smtpPerHr[$msgHr][0];
		    $smtpPerHr[$msgHr][1] += $tSecs;
		    $smtpPerHr[$msgHr][2] = $tSecs if($tSecs > $smtpPerHr[$msgHr][2]);

		    unless(${$smtpPerDay{$revMsgDateStr}}[0]++) {
			${$smtpPerDay{$revMsgDateStr}}[1] = 0;
			${$smtpPerDay{$revMsgDateStr}}[2] = 0;
		    }
		    ${$smtpPerDay{$revMsgDateStr}}[1] += $tSecs;
		    ${$smtpPerDay{$revMsgDateStr}}[2] = $tSecs
			if($tSecs > ${$smtpPerDay{$revMsgDateStr}}[2]);

		    unless(${$smtpPerDom{$hostID}}[0]++) {
			${$smtpPerDom{$hostID}}[1] = 0;
			${$smtpPerDom{$hostID}}[2] = 0;
		    }
		    ${$smtpPerDom{$hostID}}[1] += $tSecs;
		    ${$smtpPerDom{$hostID}}[2] = $tSecs
			if($tSecs > ${$smtpPerDom{$hostID}}[2]);

		    ++$smtpConnCnt;
		    $smtpTotTime += $tSecs;
		}
	    }
	}
# ---End: SMTPD_STATS_SUPPORT---
    } else {
	my $toRmdr;
	if((($addr, $size) = /from=<([^>]*)>, size=([0-9]+)/o) == 2)
	{
	    next if($msgSizes{$qid});	# avoid double-counting!
	    if($addr) {
		if($opts{'m'} && $addr =~ /^(.*!)*([^!]+)!([^!@]+)@([^\.]+)$/o) {
		    $addr = "$4!" . ($1? "$1" : "") . $3 . "\@$2";
		}
		$addr =~ s/(@.+)/\L$1/o unless($opts{'i'});
		$addr = lc($addr) if($opts{'i'});

		# Hack for VERP (?) - convert address from somthing like
		# "list-return-36-someuser=someplace.com@lists.domain.com"
		# to "list-return-ID-someuser=someplace.com@lists.domain.com"
		# to prevent per-user listing "pollution."  More aggressive
		# munging converts to something like "list@lists.domain.com"
		if(defined($opts{'verpMung'})) {
		    if($opts{'verpMung'} > 1) {
			$addr =~ s/^(.+)-return-\d+-[^\@]+(\@.+)$/$1$2/o;
		    } else {
			$addr =~ s/-return-\d+-/-return-ID-/o;
		    }
		}
	    } else {
		$addr = "from=<>"
	    }
	    $msgSizes{$qid} = $size;
	    push(@{$msgDetail{$qid}}, $addr) if($opts{'e'});
	    # Avoid counting forwards
	    if($rcvdMsg{$qid}) {
		(my $domAddr = $addr) =~ s/^[^@]+\@//o;	# get domain only
		++$sendgDomCnt
		    unless(${$sendgDom{$domAddr}}[$msgCntI]);
		++${$sendgDom{$domAddr}}[$msgCntI];
		${$sendgDom{$domAddr}}[$msgSizeI] += $size;
	        ++$sendgUserCnt unless(${$sendgUser{$addr}}[$msgCntI]);
		++${$sendgUser{$addr}}[$msgCntI];
		${$sendgUser{$addr}}[$msgSizeI] += $size;
		$sizeRcvd += $size;
		delete($rcvdMsg{$qid});		# limit hash size
	    }
	}
	elsif((($addr, $relay, $delay, $status, $toRmdr) =
		/to=<([^>]*)>, relay=([^,]+), delay=([^,]+), status=([^ ]+)(.*)$/o) >= 4)
	{
	    if($opts{'m'} && $addr =~ /^(.*!)*([^!]+)!([^!@]+)@([^\.]+)$/o) {
		$addr = "$4!" . ($1? "$1" : "") . $3 . "\@$2";
	    }
	    $addr =~ s/(@.+)/\L$1/o unless($opts{'i'});
	    $addr = lc($addr) if($opts{'i'});
	    (my $domAddr = $addr) =~ s/^[^@]+\@//o;	# get domain only
	    if($status eq 'sent') {
		# was it actually forwarded, rather than delivered?
		if($toRmdr =~ /forwarded as /o) {
		    ++$msgsFwdd;
		    next;
		}
		++$recipDomCnt unless(${$recipDom{$domAddr}}[$msgCntI]);
		++${$recipDom{$domAddr}}[$msgCntI];
		${$recipDom{$domAddr}}[$msgDlyAvgI] += $delay;
		if(! ${$recipDom{$domAddr}}[$msgDlyMaxI] ||
		   $delay > ${$recipDom{$domAddr}}[$msgDlyMaxI])
		{
		    ${$recipDom{$domAddr}}[$msgDlyMaxI] = $delay
		}
		++$recipUserCnt unless(${$recipUser{$addr}}[$msgCntI]);
		++${$recipUser{$addr}}[$msgCntI];
		++$dlvPerHr[$msgHr];
		++${$msgsPerDay{$revMsgDateStr}}[1];
		++$msgsDlvrd;
		if($msgSizes{$qid}) {
		    ${$recipDom{$domAddr}}[$msgSizeI] += $msgSizes{$qid};
		    ${$recipUser{$addr}}[$msgSizeI] += $msgSizes{$qid};
		    $sizeDlvrd += $msgSizes{$qid};
		} else {
		    ${$recipDom{$domAddr}}[$msgSizeI] += 0;
		    ${$recipUser{$addr}}[$msgSizeI] += 0;
		    $noMsgSize{$qid} = $addr;
		    push(@{$msgDetail{$qid}}, "(sender not in log)") if($opts{'e'});
		    # put this back later? mebbe with -v?
		    # msg_warn("no message size for qid: $qid");
		}
		push(@{$msgDetail{$qid}}, $addr) if($opts{'e'});
	    } elsif($status eq 'deferred') {
		my ($deferredReas) = /, status=deferred \(([^\)]+)/o;
		if (!defined($deferredReas)) 
			{
			$deferredReas = "---[0.0.0.0] connect to 0.0.0.0";
			}
		unless(defined($opts{'verbMsgDetail'})) {
		    $deferredReas = said_string_trimmer($deferredReas, 65);
		    $deferredReas =~ s/^[0-9]{3} //o;
		    $deferredReas =~ s/^connect to //o;
		}
		++$deferred{$cmd}{$deferredReas};
                ++$dfrPerHr[$msgHr];
		++${$msgsPerDay{$revMsgDateStr}}[2];
		++$msgsDfrdCnt;
		++$msgsDfrd unless($msgDfrdFlgs{$qid}++);
		++${$recipDom{$domAddr}}[$msgDfrsI];
		if(! ${$recipDom{$domAddr}}[$msgDlyMaxI] ||
		   $delay > ${$recipDom{$domAddr}}[$msgDlyMaxI])
		{
		    ${$recipDom{$domAddr}}[$msgDlyMaxI] = $delay
		}
	    } elsif($status eq 'bounced') {
		my ($bounceReas) = /, status=bounced \((.+)\)/o;
		unless(defined($opts{'verbMsgDetail'})) {
		    $bounceReas = said_string_trimmer($bounceReas, 66);
		    $bounceReas =~ s/^[0-9]{3} //o;
		}
		++$bounced{$relay}{$bounceReas};
                ++$bncPerHr[$msgHr];
		++${$msgsPerDay{$revMsgDateStr}}[3];
		++$msgsBncd;
	    } else {
#		print UNPROCD "$_\n";
	    }
	}
	elsif($cmd eq 'pickup' && /: (sender|uid)=/o) {
	    #
	    # Warning: this code in two places!
	    #
	    ++$rcvPerHr[$msgHr];
	    ++${$msgsPerDay{$revMsgDateStr}}[0];
	    ++$msgsRcvd;
	    ++$rcvdMsg{$qid};	# quick-set a flag
	}
	else
	{
#	    print UNPROCD "$_\n";
	}
    }
}

# -------------------------------------------------------------------------------------------------
# -------------------------------------------------------------------------------------------------
# debugging
#close(UNPROCD) ||
#    die "problem closing \"unprocessed\": $!\n";

print_recip_domain_summary(\%recipDom, $opts{'h'});

# print "per-recipient-domain" traffic summary
# (done in a subroutine only to keep main-line code clean)
sub print_recip_domain_summary {
    use vars '$hashRef';
    local($hashRef) = $_[0];
    my($cnt) = $_[1];
    return if($cnt == 0);
    my $topCnt = $cnt > 0? "(top $cnt)" : "";
    my $avgDly;
    my $parity = 0;

    foreach (sort by_count_then_size keys(%$hashRef)) {
	# there are only delay values if anything was sent
	if(${$hashRef->{$_}}[$msgCntI]) {
	    $avgDly = (${$hashRef->{$_}}[$msgDlyAvgI] /
		       ${$hashRef->{$_}}[$msgCntI]);
	} else {
	    $avgDly = 0;
	}

        printf "<tr class=\"%s\"><td>%s</td><td align=\"center\">%.1f%s</td><td align=\"center\">%.1f%s</td><td align=\"center\">%d%s</td><td align=\"center\">%d%s</td></tr>\n",
	    ($parity?"pair":"impair"),
	    $_,
	    adj_time_units($avgDly),
	    adj_time_units(${$hashRef->{$_}}[$msgDlyMaxI]),
	    adj_int_units(${$hashRef->{$_}}[$msgCntI]),
	    adj_int_units(${$hashRef->{$_}}[$msgDfrsI]);
	$parity=!$parity;
	last if --$cnt == 0;
    }
}

# Subroutine used by host/domain reports to sort by count, then size.
# We "fix" un-initialized values here as well.  Very ugly and un-
# structured to do this here - but it's either that or the callers
# must run through the hashes twice :-(.
sub by_count_then_size {
    ${$hashRef->{$a}}[$msgCntI] = 0 unless(${$hashRef->{$a}}[$msgCntI]);
    ${$hashRef->{$b}}[$msgCntI] = 0 unless(${$hashRef->{$b}}[$msgCntI]);
    return ((${$hashRef->{$b}}[$msgCntI]) <=> (${$hashRef->{$a}}[$msgCntI]));
}

# if there's a real domain: uses that.  Otherwise uses the first
# three octets of the IP addr.  (In the latter case: usually pretty
# safe to assume it's a dialup with a class C IP addr.)  Lower-
# cases returned domain name.
sub gimme_domain {
    $_ = $_[0];
 
    # split domain/ipaddr into separates
    my($domain, $ipAddr) = /^([^\[\(]+)[\[\(]([^\]\)]+)[\]\)]:?\s*$/o;
 
#    print STDERR "dbg: in=\"$_\", domain=\"$domain\", ipAddr=\"$ipAddr\"\n";
    # now re-order "mach.host.dom"/"mach.host.do.co" to
    # "host.dom.mach"/"host.do.co.mach"
    if($domain eq 'unknown') {
        $domain = $ipAddr;
	# For identifying the host part on a Class C network (commonly
	# seen with dial-ups) the following is handy.
        # $domain =~ s/\.[0-9]+$//o;
    } else {
        $domain =~
            s/^(.*)\.([^\.]+)\.([^\.]{3}|[^\.]{2,3}\.[^\.]{2})$/\L$2.$3/o;
    }
 
    return $domain;
}

# Return (value, units) for integer
sub adj_int_units {
    my $value = $_[0];
    my $units = ' ';
    $value = 0 unless($value);
    if($value > $divByOneMegAt) {
	$value /= $oneMeg;
	$units = 'm'
    } elsif($value > $divByOneKAt) {
	$value /= $oneK;
	$units = 'k'
    }
    return($value, $units);
}

# Return (value, units) for time
sub adj_time_units {
    my $value = $_[0];
    my $units = 's';
    $value = 0 unless($value);
    if($value > 3600) {
	$value /= 3600;
	$units = 'h'
    } elsif($value > 60) {
	$value /= 60;
	$units = 'm'
    }
    return($value, $units);
}

# Trim a "said:" string, if necessary.  Add elipses to show it.
sub said_string_trimmer {
    my($trimmedString, $maxLen) = @_;

    while(length($trimmedString) > $maxLen) {
	if($trimmedString =~ /^.* said: /o) {
	    $trimmedString =~ s/^.* said: //o;
	} elsif($trimmedString =~ /^.*: */o) {
	    $trimmedString =~ s/^.*?: *//o;
	} else {
	    $trimmedString = substr($trimmedString, 0, $maxLen - 3) . "...";
	    last;
	}
    }

    return $trimmedString;
}

# Trim a string, if necessary.  Add elipses to show it.
sub string_trimmer {
    my($trimmedString, $maxLen, $doNotTrim) = @_;

    $trimmedString = substr($trimmedString, 0, $maxLen - 3) . "..." 
	if(! $doNotTrim && (length($trimmedString) > $maxLen));
    return $trimmedString;
}

# Emit warning message to stderr
sub msg_warn {
    warn "warning: $progName: $_[0]\n";
}

