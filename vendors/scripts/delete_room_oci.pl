#!C:/perl/bin/perl.exe
#--------------------------------------------------------------------------------
#
# delete_room_oci.pl
#  Delete a room from OCI
#
#--------------------------------------------------------------------------------

use strict;
use warnings;

use DBI;
use Getopt::Std;
use Data::Dumper;
use POSIX qw(strftime);

use ICConfig qw( PERL_BIN SCRIPT_ROOT );

# connect to Octave
my $dbh = DBI->connect("DBI:ODBC:icapp1","djackson_ic","wakj3n8y") 
    or die "Connect failed:  $DBI::errstr\n";

my $ic = DBI->connect("DBI:ODBC:icapp1_ic","djackson_ic","wakj3n8y") 
    or die "Connect failed:  $DBI::errstr\n";

sub fetch_conf_schedule {
    my ($confirmation_number) = @_;
    return $dbh->selectrow_hashref('SELECT * FROM ConfSchedule WHERE ConfirmationNumber = ?', undef, @_);
}

sub fetch_cec {
    my ($conference_id) = @_;
    return $dbh->selectrow_array('SELECT EntryCodeDTMF FROM ConferenceEntryCode WHERE ConferenceId = ? AND CECEnum=3', undef, @_);    
}

sub delete_octave_records {
    my ($confirmation_number, $conference_id) = @_;
    my $rv;

    # turn on transactions
    $dbh->{AutoCommit} = 0;
    $dbh->{RaiseError} = 1;

    # From DBI manual on how to handle transactions w/ raise error
    eval {
	$dbh->do("DELETE FROM PollingResponse WHERE ConfirmationNumber = ?", undef, $confirmation_number);
	$dbh->do("DELETE FROM Recording WHERE ConfirmationNumber = ?", undef, $confirmation_number);
	$dbh->do("DELETE FROM ConfUser WHERE ConferenceId = ?", undef, $conference_id);
	$dbh->do("DELETE FROM ConfTemplate WHERE ConferenceId = ?", undef, $conference_id);
	$dbh->do("DELETE FROM ConferenceEntryCode WHERE ConferenceId = ?", undef, $conference_id);
	$dbh->do("DELETE FROM ConfSchedule WHERE ConferenceId = ?", undef, $conference_id);
	$dbh->do("DELETE FROM Commitment WHERE ConferenceId = ?", undef, $conference_id);
	$dbh->do("DELETE FROM Conference WHERE ConferenceId = ?", undef, $conference_id);
	$dbh->commit;

	$rv = 1;
    };

    if($@) { 
	eval { $dbh->rollback };
	$rv = 0;
    }

    # reset database handle
    $dbh->{AutoCommit} = 1;
    $dbh->{RaiseError} = 0;

    return $rv;
}

sub mark_as_cancelled {
    my ($accountid) = @_;
    my $pec = 'cancel ' . strftime '%m/%d/%y', localtime;
    return $ic->do("UPDATE account SET cec=?,pec=?,roomstat=2,roomstatdate=CURRENT_TIMESTAMP WHERE accountid=?", undef, '', $pec, $accountid);
}

sub main {
    my %opts;
    getopts("c:D", \%opts);

    if(!defined($opts{'c'})) {
	die "USAGE: $0 -c confirmation_number [-D]";
    }

    my $conf_schedule = fetch_conf_schedule($opts{'c'});

    if(defined($conf_schedule)) { 
	my $cec = fetch_cec($conf_schedule->{'ConferenceId'});
	
	# delete room
	if(!defined($opts{'D'})) {	   	 
	    system(SCRIPT_ROOT."/delroom1.exe -h icapp1 -c $cec -u $opts{'c'}");

	    if(($? >> 8) == 0) {	
		delete_octave_records($opts{'c'}, $conf_schedule->{'ConferenceId'}) or die "record deletion failed"; 
		mark_as_cancelled($opts{'c'}) or die "Marking account $opts{'c'} as cancelled failed";	    
	    } else { 
		die "delroom1 failed: delroom1.exe -h icapp1 -c $cec -u $opts{'c'}";
	    }
	} else {
	    print SCRIPT_ROOT."/delroom1.exe -h icapp1 -c $cec -u $opts{'c'}\n";
	    print "delete_octave_records($opts{'c'}, $conf_schedule->{'ConferenceId'})\n";
	}
             
    } else {
	print "No conference found with confirmation number: $opts{'c'}";
    }
}
main();
