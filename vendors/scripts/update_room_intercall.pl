#!C:/perl/bin/perl.exe
#--------------------------------------------------------------------------------
#
# update_room_intercall.pl
#  Update a room via Intercall's SOAP webservice
# 
# NB: To install Crypt::SSLeay for https support in Soap::Lite
#     ppm install http://theoryx5.uwinnipeg.ca/ppms/Crypt-SSLeay.ppd
#
#--------------------------------------------------------------------------------

use strict;
use warnings;

use Getopt::Long;
use Soap::Lite;
use DBI;
use Data::Dumper;

use Intercall qw( $SANDBOX $DEBUG &NS &INTERCALL_BRIDGEID &API_ROOT &USERNAME &PASSWORD %ANNOUNCEMENTS 
                  %STATUSES debug extract_owner_info error_message fetch_account_number );

my $ic = DBI->connect("DBI:ODBC:icapp1_ic","djackson_ic","wakj3n8y") 
    or die "Connect failed:  $DBI::errstr\n";

sub do_request {
    my ($account_number, $owner_number, $opts) = @_;

    my @features;
    foreach my $f (keys %{$opts->{'feature'}}) {
	my $feature = SOAP::Data
	    ->name('feature' => \SOAP::Data->name('feature-value' => $opts->{'feature'}->{$f}))
	    ->attr({'feature-type' => $f});
	
	push @features, $feature;
    }

    my $soap = SOAP::Lite
	->proxy(API_ROOT)
	->uri(NS)
	->autotype(0)
	->on_debug(\&debug)
	->call(SOAP::Data->name('update-owner-request')->attr({'xmlns' => NS}) => 
	       SOAP::Data->name('login-info' => 
				\SOAP::Data->value(
				    SOAP::Data->name('user-name'      => USERNAME), 
				    SOAP::Data->name('password'       => PASSWORD), 
				    SOAP::Data->name('account-number' => $account_number)
				)
	       ),
	       SOAP::Data->name('reference-number' => ''),
	       SOAP::Data->name('owner-number' => $owner_number),
	       SOAP::Data->name('update-owner' => 
				\SOAP::Data->value(
				    SOAP::Data->name('owner-info' => 
						     \SOAP::Data->value(				    
							 SOAP::Data->name('first-name'          => $opts->{'contact'}),
							 SOAP::Data->name('last-name'           => $opts->{'acctgrpid'}),
							 SOAP::Data->name('address1'            => '56 Main St'),
							 SOAP::Data->name('city'                => 'Millburn'), 
							 SOAP::Data->name('state'               => 'NJ'), 
							 SOAP::Data->name('country'             => 'US'),
							 SOAP::Data->name('zip'                 => '07041'), 
							 SOAP::Data->name('phone'               => '8882037900'),
							 SOAP::Data->name('email'               => 'intercall@infiniteconferencing.com'),
							 SOAP::Data->name('confirmation_format' => 'D') # for North America, per documentation
						     )
				    ),
# 				    SOAP::Data->name('add-numbers' => 
# 						     \SOAP::Data->value(				    
# 							 SOAP::Data->name('conference-code'  => $opts->{'pec'}),
# 							 SOAP::Data->name('pin'              => $opts->{'cec'}),
# 							 SOAP::Data->name('max-participants' => $opts->{'participants'})
# 						     )
# 				    )->attr({'number-type' => 'reslessplus'}),
				    SOAP::Data->name('features' => 
						     \SOAP::Data->value(
							 @features
						     )
				    )->attr({'features-type' => 'reslessplus'})
				)
	       )
	); 

    return $soap;
}

sub update_record {
    my ($owner) = @_;

    my @params = (
	$owner->{'acctgrpid'},
	$owner->{'contact'},
	$owner->{'cec'},
	$owner->{'pec'},
	$owner->{'bridgeid'},
	$owner->{'startmode'},
	$owner->{'endonchairhangup'},
	$owner->{'namerecording'},
	$owner->{'dialout'},
	$owner->{'entryannouncement'},
	$owner->{'exitannouncement'},
	$owner->{'maximumconnections'},
	$owner->{'accountid'}
    );

    my $sql = 'UPDATE account SET acctgrpid=?,contact=?,cec=?,pec=?,bridgeid=?,startmode=?,endonchairhangup=?,namerecording=?,dialout=?,' . 
	'entryannouncement=?,exitannouncement=?,maximumconnections=? WHERE accountid=?';

    return $ic->do($sql, undef, @params);
}

sub main {

    my %opts = ();
    GetOptions( \%opts, 
		'debug|D',
		'sandbox|S',
		'acctgrpid|a=s', 
		'accountid|c=s',
		'contact|t=s',
		'dialinNoid=s',
		'cec=s', 
		'pec=s',
		'participants|m=i', 
		'feature|f=s%' );

    if(!defined($opts{'acctgrpid'}) || !defined($opts{'accountid'}) || !defined($opts{'contact'}) || 
       !defined($opts{'cec'}) || !defined($opts{'pec'})) {
	die "USAGE: $0 [-D] [-S] -a acctgrpid -c accountid -t contact --cec cec --pec pec -f foo=bar...";
    }

    my $owner_number;
    if($opts{'accountid'} =~ /^I(\d+)$/) {
	$owner_number = $1;
    } else {
	die "Invalid Intercall accountid: " . $opts{'accountid'};
    }

    if(!defined($opts{'participants'})) { 
	$opts{'participants'} = 100;
    }

    $DEBUG   = defined($opts{'debug'});
    $SANDBOX = defined($opts{'sandbox'});

    #print Dumper \%opts if $DEBUG;

    my $account_number = fetch_account_number($ic, $opts{'acctgrpid'}, $opts{'dialinNoid'});
    if(!defined($account_number)) { 
	die "Could find find intercall account number for acctgrpid: $opts{'acctgrpid'} | dialinNoid: $opts{'dialinNoid'} | accountid: $opts{'accountid'}"
    }

    my $rv = do_request($account_number, $owner_number, \%opts);

    #print Dumper $rv if $DEBUG;

    if(!$rv->match('//error-Information')) {

	my $owner = extract_owner_info($rv);
	update_record($owner) or die "Could not update record for room: " . $owner->{'accountid'};	    
	printf("Updated room: %s\n", $owner->{'accountid'});
 
    } else {
	die error_message($rv);
    }
}
main();
