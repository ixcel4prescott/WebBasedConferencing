#!C:/perl/bin/perl.exe

use strict;
require "getopts.pl";
use DBI;
use Date::Manip;
&Date_Init("TZ=EST5EDT");

# Include common constants
use ICConfig qw( PERL_BIN SCRIPT_ROOT );

my $Rootdir = SCRIPT_ROOT;
#select STDOUT; $|=1;

our $opt_a;
our $opt_n;
our $opt_C;
&Getopts("a:nC:");
unless (defined($opt_a))
{
	die "usage: $0 -a accountgroup";
}

my $dbh = DBI->connect("DBI:ODBC:icapp1_ic", "djackson_ic","wakj3n8y") or die "connect BILLING";

my $q = "SELECT * FROM account WHERE acctgrpid = ? AND pec NOT like 'can%'";
my $room_q = $dbh->prepare($q) or die "prepare <$q>";

my @grp = $dbh->selectrow_array("SELECT acctgrpid,bcompany,acctstat,acctstatdate FROM accountgroup WHERE acctgrpid = \'$opt_a\'");
print STDERR "$grp[0] - $grp[1]\n";

if ($grp[2] != 0)
{
	print STDERR "\tAccount status is non-zero ($grp[2] - $grp[3])\n";
}
sleep 3; # just a little pause to allow this to be interrupted


$room_q->execute($opt_a);
my $r;

while ($r = $room_q->fetchrow_hashref())
{
#	if ($$r{cec} eq "")
#	{
#		print "$$r{accountid}: NO CEC - cancel by hand\n";
#		next;
#	}
	print STDERR PERL_BIN . " ${Rootdir}/delroom.pl -c $$r{accountid}\n";
	if (defined($opt_n))
	{
		system(PERL_BIN . " ${Rootdir}/delroom.pl -c $$r{accountid} -n");
	}
	else
	{
		system(PERL_BIN . " ${Rootdir}/delroom.pl -c $$r{accountid}");
	}
}
$room_q->finish;
print STDERR "execute sp_updaccountgroupstat $opt_a, 2)\n";
$dbh->do("execute sp_updaccountgroupstat $opt_a, 2");
if (defined($opt_C))
{
	$dbh->do("execute sp_addcollections $opt_a, $opt_C");
}

print STDOUT "SUCCESS";
