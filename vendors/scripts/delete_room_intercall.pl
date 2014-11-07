#!C:/perl/bin/perl.exe
#--------------------------------------------------------------------------------
#
# delete_room_intercall.pl
#  Delete a room via Intercall's SOAP webservice
# 
# NB: To install Crypt::SSLeay for https support in Soap::Lite
#     ppm install http://theoryx5.uwinnipeg.ca/ppms/Crypt-SSLeay.ppd
#
#--------------------------------------------------------------------------------

use strict;
use warnings;

use Getopt::Std;
use Soap::Lite;
use DBI;
use Data::Dumper;
use POSIX qw(strftime);

use Intercall qw( $SANDBOX $DEBUG &NS &INTERCALL_BRIDGEID &API_ROOT &USERNAME &PASSWORD %ANNOUNCEMENTS 
                  %STATUSES debug extract_owner_info error_message fetch_account_number );

my $ic = DBI->connect("DBI:ODBC:icapp1_ic","djackson_ic","wakj3n8y") 
    or die "Connect failed:  $DBI::errstr\n";

sub fetch_account_info {
    my ($accountid) = @_;
    return $ic->selectrow_array('SELECT acctgrpid, dialinNoid FROM account WHERE accountid=?', undef, $accountid);
}

sub do_request {
    my ($account_number, $owner_number) = @_;

    my $soap = SOAP::Lite
	->proxy(API_ROOT)
	->uri(NS)
	->autotype(0)
	->on_debug(\&debug)
	->call(SOAP::Data->name('delete-owner-request')->attr({'xmlns' => NS}) => 
	       SOAP::Data->name('login-info' => 
				\SOAP::Data->value(
				    SOAP::Data->name('user-name'      => USERNAME), 
				    SOAP::Data->name('password'       => PASSWORD), 
				    SOAP::Data->name('account-number' => $account_number)
				)
	       ),
	       SOAP::Data->name('delete-owner' => 
				\SOAP::Data->value(
				  SOAP::Data->name('owner-number' => $owner_number)
				)
	       )
	);
    
    return $soap;
}

sub mark_as_cancelled {
    my ($accountid) = @_;
    my $pec = 'cancel ' . strftime '%m/%d/%y', localtime;
    return $ic->do("UPDATE account SET cec=?,pec=?,roomstat=2,roomstatdate=CURRENT_TIMESTAMP WHERE accountid=?", undef, '', $pec, $accountid);
}

sub main {
    my %opts;

    getopts("c:DS", \%opts);

    if(!defined($opts{'c'})) {
	die "USAGE: $0 -c confirmation_number [-D] [-S]";
    }

    my $owner_number;
    if($opts{'c'} =~ /^I(\d+)$/) {
	$owner_number = $1;
    } else {
	die "Invalid Intercall accountid: " . $opts{'c'};
    }

    $DEBUG   = defined($opts{'D'});
    $SANDBOX = defined($opts{'S'});

    my ($acctgrpid, $dialinNoid) = fetch_account_info($opts{'c'});
    my $account_number           = fetch_account_number($ic, $acctgrpid, $dialinNoid);
    if(!defined($account_number)) { 
	die "Could find find intercall account number for $opts{'c'}"
    }

    my $rv = do_request($account_number, $owner_number);

    #print Dumper $rv if $DEBUG;

    if(!$rv->match('//error-Information')) {

	mark_as_cancelled($opts{'c'})
	    or die sprintf("Could not mark %s room as cancelled", $opts{'c'});

	printf("Room deleted: %s\n", $opts{'c'});	

    } else {
	die error_message($rv);
    }
}
main();
