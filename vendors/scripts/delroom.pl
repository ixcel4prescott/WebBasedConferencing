#!C:/perl/bin/perl.exe

use strict;

require "getopts.pl";

use DBI;
use Data::Dumper;
use Date::Manip;
&Date_Init("TZ=EST5EDT");

# Include common constants
use ICConfig qw( PERL_BIN SCRIPT_ROOT );

my $today = &gettoday;

print STDERR "today: <$today>\n";

our $opt_c; # confirmation number
our $opt_O;
our $opt_n; # no delete confirmation necessary
our $opt_D; # don't run delroom1.exe

my $Rootdir = SCRIPT_ROOT;
#select STDOUT; $|=1;

&Getopts("c:OnD");
unless (defined($opt_c))
{	
	die "usage: $0 -c <confirmation number> [-O]";
}

my $bdb = DBI->connect("DBI:ODBC:icapp1_ic", "djackson_ic","wakj3n8y")
	or die "connect BILLING";
my $idb = DBI->connect("DBI:ODBC:icapp1", "djackson_ic","wakj3n8y", {
	AutoCommit => 0,
	RaiseError => 1 })
		or die "connect icapp1";

my $atok = 1;
#my $adb = DBI->connect("DBI:ODBC:atcas1", "djackson_ic","wakj3n8y", {
#	AutoCommit => 0,
#	RaiseError => 1 })
#		or $atok = 0;

#my $adb2 = DBI->connect("DBI:ODBC:atcas2", "djackson_ic","wakj3n8y", {
#	AutoCommit => 0,
#	RaiseError => 1 })
#		or $atok = 0;


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

# Get the ConfSchedule Record

$q = "SELECT * FROM ConfSchedule WHERE ConfirmationNumber = ?";
print STDERR "$q <$opt_c>\n";

my $csg = $idb->prepare($q) or die "prepare: $q";

$csg->execute($opt_c) or die "csg execute";

my $cs = $csg->fetchrow_hashref(); # there can be only one
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

my $id = $$cs{ConferenceId};
print STDERR "id = <$id>\n";
print STDERR "\t\tcontact is: <$$acct{contact}>\n";

my $rc;

if ($id){

	my $cecrec;
	if (defined($cs))
	{
		$q = "SELECT * FROM ConferenceEntryCode WHERE ConferenceId = $id AND CECEnum = 3";
		print STDERR "Running [$q].\n";

		my $cecg = $idb->prepare($q) or die "$q";
		$cecg->execute or die "Failed:$q";
		$cecrec = $cecg->fetchrow_hashref();
		$cecg->finish;
		print STDERR "CEC is: <$$cecrec{EntryCodeDTMF}> -- account rec says: <$$acct{cec}>\n";

	}


	## Have the CEC & confirmation number, so do two things:
	unless (defined($opt_n))
	{
		print STDERR "CEC=$$cecrec{EntryCodeDTMF}.  Delete? ";
		my $ans = <>;
		chomp $ans;
		exit 1 if ($ans !~  /^y/i);
	}

	# 1. suspend the room so that it's inaccessible from the bridge
	system("${Rootdir}/delroom1.exe -h icapp1 -c $$cecrec{EntryCodeDTMF} -u $opt_c") unless (defined($opt_D));

	# 2. delete the room for WebInterpoint
	if ($$cecrec{EntryCodeDTMF} ne "")
	{
		system(PERL_BIN . " ${Rootdir}/delcec.pl -c $$cecrec{EntryCodeDTMF}");
	}


	if (defined($cs))
	{
		print STDERR "octave_rmroom($idb, $opt_c, $id)\n";
		&octave_rmroom($idb, $opt_c, $id);
	}

#	if ($atok > 0)
#	{
#		my $q;
#		my $confid;
#		my $confirm;
#		if ($$cecrec{EntryCodeDTMF} ne "")
#		{
#		print STDERR "ATSIDE\n";
#			$q = "SELECT * FROM ConferenceEntryCode WHERE CECEnum = 3 AND EntryCodeDTMF = ?";
#			my $aq = $adb->prepare_cached($q);
#			$aq->execute($$cecrec{EntryCodeDTMF});
#			my $r;
#			my $idcount = 0;
#			my @id;
#			while ($r = $aq->fetchrow_hashref())
#			{
#				$id[$idcount++] = $$r{ConferenceId};
#			}
#			$aq->finish;
#
#			my $found = 0;
#			# find the Confschedule record and see if the ConfirmationNumber matches
#			for (my $i= 0; $i < @id; $i++)
#			{
#				$q = "SELECT * FROM ConfSchedule WHERE ConferenceId = ?";
#				my $aq = $adb->prepare_cached($q);
#				$aq->execute($id[$i]);
#				$r = $aq->fetchrow_hashref();
#				$aq->finish;
#
#				if ($$r{ConfirmationNumber} eq $opt_c)
#				{
#					$found = 1;
#					$confid = $id[$i];
#					last;
#				}
#			}	
#
#			if ($found > 0)
#			{
#				print STDERR "octave_rmroom($adb, $opt_c, $confid)\n";
#				&octave_rmroom($adb, $opt_c, $confid);
#			}
#		}
#
#		if ($$cecrec{EntryCodeDTMF} ne "")
#		{
#		print STDERR "AT2SIDE\n";
#			$q = "SELECT * FROM ConferenceEntryCode WHERE CECEnum = 3 AND EntryCodeDTMF = ?";
#			my $aq = $adb2->prepare_cached($q);
#			$aq->execute($$cecrec{EntryCodeDTMF});
#			my $r;
#			my $idcount = 0;
#			my @id;
#			while ($r = $aq->fetchrow_hashref())
#			{
#				$id[$idcount++] = $$r{ConferenceId};
#			}
#			$aq->finish;
#
#			my $found = 0;
			# find the Confschedule record and see if the ConfirmationNumber matches
#			for (my $i= 0; $i < @id; $i++)
#			{
#				$q = "SELECT * FROM ConfSchedule WHERE ConferenceId = ?";
#				my $aq = $adb2->prepare_cached($q);
#				$aq->execute($id[$i]);
#				$r = $aq->fetchrow_hashref();
#				$aq->finish;
#
#				if ($$r{ConfirmationNumber} eq $opt_c)
#				{
#					$found = 1;
#					$confid = $id[$i];
#					last;
#				}
#			}	
#
#			if ($found > 0)
#			{
#				print STDERR "octave_rmroom($adb2, $opt_c, $confid)\n";
#				&octave_rmroom($adb2, $opt_c, $confid);
#			}
#		}
#
#	}
}else{
	print STDERR ("Conference was not found on the bridge, deleting from ICAM...\n");
}

$q = "UPDATE account SET cec=NULL,pec=\'cancel $today\',roomstat=2,roomstatdate=\'$today\' WHERE accountid=\'$opt_c\'";

$rc = $bdb->do($q);

if (!$rc){
	if ($DBI::errstr =~ /deadlock/){
		$rc = $bdb->do($q);
		if (!$rc ){
			print STDERR "DB ERROR: $DBI::errstr";
			die($q);
		}
	}else{
		print STDERR "DB ERROR: $DBI::errstr";
		die($q);
	}
}

print STDERR "Update cec/pec: $rc\n";
print STDOUT "SUCCESS";

&dbdisconnect;

exit 0;

sub dbdisconnect
{
	$idb->disconnect;
#	$adb->disconnect if ($atok > 0);
#	$adb2->disconnect if ($atok > 0);
	$bdb->disconnect;
}

sub gettoday
{
	my $t = &ParseDate("midnight today");
	my $d = &UnixDate($t, "%D");
	return($d);
}

sub dbquit
{
	my $idb = shift;
	my $q = shift;
	$idb->rollback(); 
	die "$q";
}


sub octave_rmroom
{
	my $idb = shift;
	my $opt_c = shift;
	my $id = shift;

	my $q;
	my $rc;

	my $noprint = 1;
	
	print STDERR "octave_rmroom($idb, $opt_c, $id)\n";
	$idb->rollback();

	
	$q = "DELETE FROM PollingResponse WHERE ConfirmationNumber = \'$opt_c\'";
	$rc = $idb->do($q) or  &dbquit($q);
	print STDERR "PollingResponse: $rc\n" unless ($noprint == 1);
	
	$q = "DELETE FROM PollingQuestion WHERE ConfirmationNumber = \'$opt_c\'";
	print STDERR "$q\n";

	$rc = $idb->do($q) or &dbquit($q);
	print STDERR "PollingQuestion: $rc\n" unless ($noprint == 1);
	$q = "DELETE FROM Recording WHERE ConfirmationNumber = \'$opt_c\'";
	$rc = $idb->do($q) or  &dbquit($q);
	print STDERR "Recording: $rc\n" unless ($noprint == 1);

	$q = "DELETE FROM ConfUser WHERE ConferenceId = $id";
	$rc = $idb->do($q) or &dbquit($q);
	print STDERR "ConfUser: $rc\n" unless ($noprint == 1);
	$q = "DELETE FROM ConfTemplate WHERE ConferenceId = $id";
	$rc = $idb->do($q) or  &dbquit($q);
	print STDERR "ConfTemplate: $rc\n" unless ($noprint == 1);
	$q = "DELETE FROM ConferenceEntryCode WHERE ConferenceId = $id";
	$rc = $idb->do($q) or  &dbquit($q);
	print STDERR "ConferenceEntryCode: $rc\n" unless ($noprint == 1);
	$q = "DELETE FROM ConfSchedule WHERE ConferenceId = $id";
	$rc = $idb->do($q) or  &dbquit($q);
	print STDERR "ConfSchedule: $rc\n" unless ($noprint == 1);
	$q = "DELETE FROM Commitment WHERE ConferenceId = $id";
	$rc = $idb->do($q) or  &dbquit($q);
	print STDERR "Commitment: $rc\n" unless ($noprint == 1);
	$q = "DELETE FROM Conference WHERE ConferenceId = $id";
	$rc = $idb->do($q) or  &dbquit($q);
	print STDERR "Conference: $rc\n" unless ($noprint == 1);

	$idb->commit();
}
