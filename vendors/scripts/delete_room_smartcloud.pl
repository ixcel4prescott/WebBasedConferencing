#!C:/perl/bin/perl.exe
#--------------------------------------------------------------------------------
#
# delete_room_webdialogs.pl
#  Delete a room via Webdialog's Webservice
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
	    'name' => 'deleteSubscriber',
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
	    die 'Request status returned not OK: ' . $response_data->{ERROR}->{description};
	}
    } else {
	die "Request did not succeed";
    }
}


sub update_account { 
    my ($accountid) = @_;
    return $ic->do('UPDATE account set webinterpoint=0 WHERE accountid=?', undef, $accountid);
}

sub delete_record {
    return $ic->do('DELETE FROM webinterpoint_accounts WHERE cec=? AND pec=?', undef, @_);
}

sub generate_check {
    my ($data) = @_;

    my $context = new MD5;
    $context->reset();
    $context->add($data, SALT);
    
    return $context->hexdigest();
}

sub main {
    my %opts;
    getopts("a:c:C:P:n:p:", \%opts);

    if(!defined($opts{'C'}) || !defined($opts{'P'})) {
	die "USAGE: $0 -C cec -P pec";
    }

    my %request = (
	'svc-prov'      => SRV_PROV, 
	'subscriber-id' => $opts{'C'},
	'passcode'      => $opts{'P'},
	'check'         => generate_check($opts{'C'}),
    );

    my $rv = do_request(\%request);
    if($rv) { 
	update_account($opts{'c'});
	delete_record($opts{'C'}, $opts{'P'});
    }
}
main();
