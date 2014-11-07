#!C:/perl/bin/perl.exe
#--------------------------------------------------------------------------------
#
# get_intercall_info.pl
#  Gets Intercall owner info (mainly for debugging)
# 
# NB: To install Crypt::SSLeay for https support in Soap::Lite
#     ppm install http://theoryx5.uwinnipeg.ca/ppms/Crypt-SSLeay.ppd
#
#--------------------------------------------------------------------------------

use strict;
use warnings;

use Getopt::Std;
use Soap::Lite;
use Data::Dumper;
use DBI;

use Intercall qw( $SANDBOX $DEBUG &NS &INTERCALL_BRIDGEID  &API_ROOT &USERNAME &PASSWORD %ANNOUNCEMENTS 
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
	->call(SOAP::Data->name('retrieve-owner-request')->attr({'xmlns' => NS}) => 
	       SOAP::Data->name('login-info' => 
				\SOAP::Data->value(
				    SOAP::Data->name('user-name'      => USERNAME),
				    SOAP::Data->name('password'       => PASSWORD),
				    SOAP::Data->name('account-number' => $account_number)
				)
	       ),
	       SOAP::Data->name('owner-number' => $owner_number)
	);

    return $soap;
}

sub main {
    my %opts;
    getopts("DSc:", \%opts);

    if(!defined($opts{'c'})) {
	die "USAGE: $0 -c confirmation_number [-D] [-S]";
    }

    $DEBUG   = defined($opts{'D'});
    $SANDBOX = defined($opts{'S'});
    
    my $owner_number;
    if($opts{'c'} =~ /^I(\d+)$/) {
	$owner_number = $1;
    } else {
	die "Invalid Intercall accountid: " . $opts{'c'};
    }

    my ($acctgrpid, $dialinNoid) = fetch_account_info($opts{'c'});
    my $account_number           = fetch_account_number($ic, $acctgrpid, $dialinNoid);
    if(!defined($account_number)) { 
	die "Could find find intercall account number for $opts{'c'}"
    }

    my $rv = do_request($account_number, $owner_number);

    #print Dumper $rv if $DEBUG;

    if(!$rv->match('//error-Information')) {
	my $owner = extract_owner_info($rv);
	
	for my $k (keys %$owner) {
	    
	    printf("% 20s: %s\n", $k, defined($owner->{$k}) ? $owner->{$k} : '');
	}
	
    } else {
	die error_message($rv);
    }
}
main();
