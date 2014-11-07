#!C:/perl/bin/perl.exe

use strict;

# Include common constants
use ICConfig qw( PERL_BIN SCRIPT_ROOT );

use Getopt::Std;
use DBI;
use Data::Dumper;
use Date::Manip;
&Date_Init("TZ=EST5EDT");

my $today = &gettoday;

print STDERR "today: <$today>\n";
my $Rootdir = SCRIPT_ROOT;
select STDOUT; $|=1;

our $opt_c; # confirmation number
our $opt_O;
our $opt_A;

getopts("c:OA");
unless (defined($opt_c))
{	
	die "usage: $0 -c <confirmation number> [-O] [-A]";
}

my @idb;
my @host;
my $bdb = DBI->connect("DBI:ODBC:icapp1_ic", "djackson_ic","wakj3n8y")
	or die "connect BILLING";
#if (defined($opt_A))
#{
#		$host[0] = "atcas1";
#		$host[1] = "atcas2";
#		$idb[0] = DBI->connect("DBI:ODBC:atcas1", "djackson_ic","wakj3n8y", {
#			AutoCommit => 0,
#			RaiseError => 1 })
#				or die "connect atcas1";
#		
#		$idb[1] = DBI->connect("DBI:ODBC:atcas2", "djackson_ic","wakj3n8y", {
#				AutoCommit => 0,
#				RaiseError => 1 })
#					or die "connect atcas2";
#}
#else
#{
	$host[0] = "icapp1";
	$idb[0] = DBI->connect("DBI:ODBC:icapp1", "djackson_ic","wakj3n8y", {
	AutoCommit => 0,
		RaiseError => 1 })
		or die "connect icapp1";
#}
	


# get the account table record

my $q = "SELECT * FROM account WHERE accountid = ?";
my $ag = $bdb->prepare($q) or die "prepare failed: $q";
$ag->execute($opt_c) or die "ag execute failed";
my $acct = $ag->fetchrow_hashref() or die "fetch ag";
$ag->finish;

unless ($$acct{accountid} eq $opt_c)
{
	die "account record doesn't match $opt_c";
}


my $csg;
my $cs;
my $id;
my $cecrec;
my $i;
for ($i = 0; $i < @idb; $i++)
{
	my $idb = $idb[$i];
	# Get the ConfSchedule Record
	
	$q = "SELECT * FROM ConfSchedule WHERE ConfirmationNumber = ?";
	$csg = $idb->prepare($q) or die "prepare: $q";
	
	$csg->execute($opt_c) or die "csg execute";
	
	$cs = $csg->fetchrow_hashref(); # there can be only one
	$csg->finish;
	unless (defined($cs))
	{
		die "cs hash var is null for $opt_c confschedule get\n"
			unless (defined($opt_O));
	}
	if ($$cs{ConfirmationNumber} ne $opt_c)
	{	
		die "cs hash var confirmation number doesn't match $opt_c!\n"
			unless (defined($opt_O));
	}
	
	$id = $$cs{ConferenceId};
	print STDERR "id = <$id>\n";
	
	$cecrec;
	if (defined($cs))
	{
		$q = "SELECT * FROM ConferenceEntryCode WHERE ConferenceId = $id AND CECEnum = 3";
		my $cecg = $idb->prepare($q) or die "$q";
		$cecg->execute;
		$cecrec = $cecg->fetchrow_hashref();
		$cecg->finish;
		print STDERR "CEC is: <$$cecrec{EntryCodeDTMF}> -- account rec says: <$$acct{cec}>\n";
	
	}
	
	
	## Have the CEC & confirmation number, so do two things:
	
	# 1. suspend the room so that it's inaccessible from the bridge
	system("${Rootdir}/delroom1.exe -h $host[$i] -c $$cecrec{EntryCode} -u $opt_c");
}
# 2. delete the room for WebInterpoint
system("${Rootdir}/delcec.pl -c $$cecrec{EntryCodeDTMF}");

$q = "UPDATE account SET roomstat=1,roomstatdate=\'$today\' WHERE accountid=\'$opt_c\'";
my $rc = $bdb->do($q) or die "$q";
print STDERR "Update roomstat: $rc\n";
print STDOUT "SUCCESS";


sub gettoday
{
	my $t = &ParseDate("midnight today");
	my $d = &UnixDate($t, "%D");
	return($d);
}

sub dbquit
{
	my $idb;
	foreach $idb (@idb)
	{
		$idb->rollback(); 
	}
	die "$q";
}
