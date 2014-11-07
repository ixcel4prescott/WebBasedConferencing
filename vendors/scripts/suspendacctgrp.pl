#!C:/perl/bin/perl.exe

use strict;
use Getopt::Std;
use DBI;
use Date::Manip;
&Date_Init("TZ=EST5EDT");

my $today = &getnow;
print STDERR "$today\n";

my $Rootdir = "D:/Billing_home/test/scripts/rooms";
select STDOUT; $|=1;

our $opt_a;
our $opt_A;

getopts("a:");
unless (defined($opt_a))
{
	die "usage: $0 -a accountgroup";
}
#$opt_A = "-A" if (defined($opt_A));
my $dbh = DBI->connect("DBI:ODBC:icapp1_ic", "djackson_ic","wakj3n8y") or die "connect BILLING";

my $q = "SELECT * FROM account WHERE acctgrpid = ? AND pec NOT like 'can%'";
my $room_q = $dbh->prepare($q) or die "prepare <$q>";

my @grp = $dbh->selectrow_array("SELECT acctgrpid,bcompany FROM accountgroup WHERE acctgrpid = \'$opt_a\'");
print STDERR "$grp[0] - $grp[1]\n";

$room_q->execute($opt_a);
my $r;

while ($r = $room_q->fetchrow_hashref())
{
	if ($$r{cec} eq "")
	{
		print STDERR "$$r{accountid}: NO CEC - cancel by hand\n";
		next;
	}
	print STDERR "perl ${Rootdir}/suspendroom.pl -c $$r{accountid}\n";
	system("C:/perl/bin/perl ${Rootdir}/suspendroom.pl -c $$r{accountid}");
	print STDERR "grp_u->execute($today,$opt_a)\n";
	}
$room_q->finish;

print STDERR "execute sp_updaccountgroupstat $opt_a, 1)\n";
$dbh->do("execute sp_updaccountgroupstat $opt_a, 1");

print STDOUT "SUCCESS";

sub getnow
{
	my $t = &ParseDate("now");
	my $d = &UnixDate($t, "%D %r");
	return($d);
}
