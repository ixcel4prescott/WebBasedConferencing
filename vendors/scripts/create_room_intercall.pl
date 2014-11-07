#!C:/perl/bin/perl.exe
#--------------------------------------------------------------------------------
#
# create_room_intercall.pl
#  Create a room via Intercall's SOAP webservice
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

use ICConfig qw( PERL_BIN SCRIPT_ROOT );

use Intercall qw( $SANDBOX $DEBUG &NS &INTERCALL_BRIDGEID &API_ROOT &USERNAME &PASSWORD %ANNOUNCEMENTS 
                  %STATUSES debug extract_owner_info error_message fetch_account_number );

my $ic = DBI->connect("DBI:ODBC:icapp1_ic","djackson_ic","wakj3n8y") 
    or die "Connect failed:  $DBI::errstr\n";

sub do_request {
    my ($account_number, $opts) = @_;

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
	->call(SOAP::Data->name('add-owner-request')->attr({'xmlns' => NS}) => 
	       SOAP::Data->name('login-info' => 
				\SOAP::Data->value(
				    SOAP::Data->name('user-name'      => USERNAME),
				    SOAP::Data->name('password'       => PASSWORD),
				    SOAP::Data->name('account-number' => $account_number)
				)
	       ),
	       SOAP::Data->name('add-owner' => 
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
							 SOAP::Data->name('email'               => 'intercall-confirmations@infiniteconferencing.com'),
							 SOAP::Data->name('confirmation_format' => 'D') # for North America, per documentation
						     )
				    ),
				    SOAP::Data->name('features' => 
						     \SOAP::Data->value(
							 @features
						     )
				    )->attr({'features-type' => 'reslessplus'}),
				    SOAP::Data->name('add-numbers' => 
						     \SOAP::Data->value(				    
							 SOAP::Data->name('conference-code'  => $opts->{'pec'}),
							 SOAP::Data->name('pin'              => $opts->{'cec'}),
							 SOAP::Data->name('max-participants' => $opts->{'participants'})
						     )
				    )->attr({'number-type' => 'reslessplus'}), 
				    SOAP::Data->name('add-numbers')->attr({'number-type' => 'reslessplus-intl'})
				)
	       ),
	); 

    return $soap;
}

sub insert_record {
    my ($owner, $account_number, $dialinNoid) = @_;

    my @params = (
	$owner->{'accountid'},
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
	$dialinNoid,
	sprintf("Account Number: %s | WebPin: %s", $account_number, $owner->{'web-pin'})
    );
    
    my $sql = 'INSERT INTO account(accountid,acctgrpid,contact,cec,pec,bridgeid,startmode,endonchairhangup,namerecording,dialout,' . 
	'entryannouncement,exitannouncement,maximumconnections,dialinNoid,note3,roomstat) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,0)';

    return $ic->do($sql, undef, @params);
}

sub main {

    my %opts = ();
    GetOptions( \%opts, 
		'debug|D',
		'sandbox|S',
		'acctgrpid|a=s', 
		'contact|t=s',
		'cec=s', 
		'pec=s',
		'participants|m=i', 
		'dialinNoid=s',
		'feature|f=s%' );

    if(!defined($opts{'acctgrpid'}) || !defined($opts{'contact'}) || !defined($opts{'cec'}) || !defined($opts{'pec'}) || !defined($opts{'dialinNoid'})) {
	die "USAGE: $0 [-D] [-S] -a acctgrpid -t contact --cec cec --pec pec --dialinNoid dialinNoid -f foo=bar...";
    }
    
    if(!defined($opts{'participants'})) { 
	$opts{'participants'} = 100;
    }

    $DEBUG   = defined($opts{'debug'});
    $SANDBOX = defined($opts{'sandbox'});

    #print Dumper \%opts if $DEBUG;

    my $account_number = fetch_account_number($ic, $opts{'acctgrpid'}, $opts{'dialinNoid'});
    if(!defined($account_number)) { 

	my $account_cmd = SCRIPT_ROOT . "/create_account_intercall.pl -a $opts{'acctgrpid'} --dialinNoid $opts{'dialinNoid'}";
	if($DEBUG) {
	    $account_cmd .= ' -D';
	}

	if($SANDBOX) {
	    $account_cmd .= ' -S';
	}

	system($account_cmd);
	if(($? >> 8) == 0) { 
	    $account_number = fetch_account_number($ic, $opts{'acctgrpid'}, $opts{'dialinNoid'});
	} else {
	    die "Could not create account for acctgrpid: $opts{'acctgrpid'} / dialinNoid: $opts{'dialinNoid'}";
	}
    }

    my $rv = do_request($account_number, \%opts);
    #print Dumper $rv if $DEBUG;
    
    if(!$rv->match('//error-Information')) {
	my $owner = extract_owner_info($rv);

	insert_record($owner, $account_number, $opts{'dialinNoid'})
	    or die "Could not insert record for newly created room: " . $owner->{'accountid'};

	printf("Created room: %s\n", $owner->{'accountid'});

    } else {
	printf(STDERR "Account Number: %s | DialinNoid: %s | CEC: %s | PEC: %s\n", $account_number, $opts{'dialinNoid'}, $opts{'cec'}, $opts{'pec'});
	die error_message($rv);
    }
}
main();
