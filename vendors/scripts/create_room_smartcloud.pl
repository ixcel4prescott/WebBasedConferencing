#!C:/perl/bin/perl.exe
#--------------------------------------------------------------------------------
#
# create_room_webdialogs.pl
#  Create a room via Webdialog's Webservice
#
#--------------------------------------------------------------------------------

use strict;
use warnings;

use Getopt::Std;
use XML::Simple;
use LWP::UserAgent;
use HTTP::Request::Common qw(POST);
use MD5;
use Data::Dumper;
use DBI;

use constant API_ROOT => 'http://www.conferenceservers.com/register/api/main.asp';
use constant SRV_PROV => 'INF';
use constant SALT     => 'BlormTip922';

my $ic = DBI->connect("DBI:ODBC:icapp1_ic","djackson_ic","wakj3n8y") 
    or die "Connect failed:  $DBI::errstr\n";

sub do_request {
    my ($payload) = @_;
   
    my $data = {
	'WDAPI' => {
	    'type' => 'call', 
	    'name' => 'addSubscriber',
	    'PARAMETERS' => [{
		'SUBSCRIBER' => $payload
	     }]
	}
    };

    my $xml = XMLout($data, NoIndent=>1, KeepRoot=>1);
    
    my $ua = new LWP::UserAgent;
    my $request = POST(API_ROOT, ['xml' => $xml]);

    my $response = $ua->request($request);
    if($response->is_success) {
	my $response_data = XMLin($response->content);

	if($response_data->{STATUS}->{result} eq 'OK') {
	    return 1;
	} else {
	    die $response_data->{ERROR}->{description};
	}
    } else {
	die "Request did not succeed";
    }
}

sub generate_check {
    my ($data) = @_;

    my $context = new MD5;
    $context->reset();
    $context->add($data, SALT);
    
    return $context->hexdigest();
}

sub update_account { 
    my ($accountid) = @_;
    return $ic->do('UPDATE account set webinterpoint=1 WHERE accountid=?', undef, $accountid);
}

sub insert_record {
    my $sql = "INSERT INTO webinterpoint_accounts(accountid, cec, pec, max_ports, service_provider, billing_type) VALUES(?, ?, ?, ?, ?, 'm')";
    return $ic->do($sql, undef, @_);
}

sub main {
    my %opts;
    getopts("a:c:C:P:n:p:", \%opts);

    if(!defined($opts{'a'}) || !defined($opts{'c'}) || !defined($opts{'C'}) || 
       !defined($opts{'P'}) || !defined($opts{'p'})) {
	die "USAGE: $0 -a accountgroup -c confirmation number -C cec -P pec -p max ports";
    }

    my %request = (
	'svc-prov'      => SRV_PROV, 
	'subscriber-id' => $opts{'C'},
	'passcode'      => $opts{'P'},
	'info1'         => $opts{'a'},
	'info2'         => $opts{'c'},
	'port-number'   => $opts{'p'}, 
	'check'         => generate_check($opts{'C'}),
	'billing-type'  => 'm'
    );

    my $rv = do_request(\%request);
    if($rv) { 
	update_account($opts{'c'});
	insert_record($opts{'c'}, $opts{'C'}, $opts{'P'}, $opts{'p'}, SRV_PROV);
    }
}
main();
