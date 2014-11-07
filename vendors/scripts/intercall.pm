package Intercall;

use strict;
use warnings;

use base qw( Exporter );

our @EXPORT_OK = qw( $DEBUG $SANDBOX &NS &ACCOUNT_NS &INTERCALL_BRIDGEID &API_ROOT &ACCOUNT_API_ROOT &USERNAME &PASSWORD 
                     %ANNOUNCEMENTS %STATUSES debug extract_owner_info error_message fetch_account_number );

our $DEBUG   = 0;
our $SANDBOX = 0;

sub NS()                 {'http://intercall.com/ownerAPI' };
sub ACCOUNT_NS()         {'http://intercall.com/AccountAPI' };
sub INTERCALL_BRIDGEID() { 10 };
sub ACCOUNT()            { 625106 };
sub API_ROOT()           { $SANDBOX ? 'https://betaoapi1.intercall.com/SynchOwnerAPIWebService/SynchOwnerAPIWebService' : 
			       'https://oapi6ssl.intercall.com/SynchOwnerAPIWebService/SynchOwnerAPIWebService?wsdl' };
sub ACCOUNT_API_ROOT()   { $SANDBOX ? 'https://betaoapi1.intercall.com/AccountWebService/AccountWebService?WSDL' : 
			       'https://oapi6ssl.intercall.com/AccountWebService/AccountWebService?WSDL' };
sub USERNAME()           { $SANDBOX ? 'Infinite-Beta' : 'Infinite-Conferencing' };
sub PASSWORD()           { $SANDBOX ? '1nf1n1t3' : '1nf1n1t3' };

our %ANNOUNCEMENTS = (
    'SILENCE'   => 0,
    'TONE'      => 1,
    'NAME'      => 2,
    'TONE_NAME' => 3
);

our %STATUSES = (
    'ENABLED'    => 0,
    'DISABLED'   => 1,
    'TERMINATED' => 2
);

sub debug {
    print STDERR @_ if $DEBUG;
}

sub trim {
    my ($str) = @_;

    if(defined($str)) { 
	$str =~ s/^\s+//;
	$str =~ s/\s+$//;
    }

    return $str;
}

sub fetch_account_number {

    my ($dbh, $acctgrpid, $dialinNoid) = @_;

    my $sql = qq{
      SELECT TOP 1 account_number
      FROM intercall_accounts
      JOIN intercall_companies ON intercall_companies.company_number = intercall_accounts.company_number
      WHERE intercall_accounts.acctgrpid = ? AND intercall_companies.dialinNoid = ?
    };

    return $dbh->selectrow_array($sql, undef, $acctgrpid, $dialinNoid);    
}

sub extract_owner_info {
    my ($soap) = @_;
    
    my $owner_data = $soap->valueof('//owner');

    my %owner = (
	'intercall_account'  => trim($owner_data->{'account-number'}),
	'accountid'          => 'I' . trim($owner_data->{'owner-number'}),
	'bridgeid'           => INTERCALL_BRIDGEID,
	'contact'            => trim($owner_data->{'owner-info'}{'first-name'}),
	'acctgrpid'          => trim($owner_data->{'owner-info'}{'last-name'}),
	'dialin'             => trim($owner_data->{'numbers'}{'number'}),
	'maximumconnections' => trim($owner_data->{'numbers'}{'max-participants'}),
	'web-pin'            => trim($owner_data->{'owner-info'}{'web-pin'}),
	'cec'                => trim($owner_data->{'numbers'}{'pin'}),
	'pec'                => trim($owner_data->{'numbers'}{'conference-code'})
    );

    my $status = $soap->valueof('//status');
    if(defined($status)) {
	$owner{'roomstat'} = $STATUSES{trim($status)};
    }

    for my $f ($soap->dataof('//feature')) {

	my $type  = $f->attr()->{'feature-type'};
	my $value = $f->value()->{'feature-value'};

	if(defined($type) && defined($value)) { 

	    $type  = trim($type);
	    $value = trim($value);

	    if($type eq 'entry-announcement' || $type eq 'exit-announcement') {
		$type =~ s/\-//;
		$owner{$type} = $ANNOUNCEMENTS{$value};

	    } elsif($type eq 'quick-start') { 
		$owner{'startmode'} = $value eq 'true' ? 0 : 1;

	    } elsif($type eq 'auto-continuation') { 
		$owner{'endonchairhangup'} = $value eq 'true' ? 0 : 1;

	    } elsif($type eq 'name-record') { 
		$owner{'namerecording'} = $value eq 'YES' ? 1 : 0;

	    } elsif($type eq 'dialout-permission') { 
		$owner{'dialout'} = $value eq 'true' ? 1 : 0;
	    }
	}
    }

    return \%owner;
}

sub error_message {
    my ($soap) = @_;

    my $error = $soap->valueof('//error-Information');

    return sprintf("Webservice responded with an errror: %s(%d): %s", $error->{'category'}, $error->{'error-code'}, 
		   defined($error->{'message'}) ? $error->{'message'} : 'No error message given' );
}

1

