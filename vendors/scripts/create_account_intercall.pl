#!C:/perl/bin/perl.exe
#--------------------------------------------------------------------------------
#
# create_account_intercall.pl
#  Create an account via Intercall's SOAP webservice
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

use Intercall qw( $SANDBOX $DEBUG &ACCOUNT_NS &INTERCALL_BRIDGEID &ACCOUNT_API_ROOT &USERNAME &PASSWORD %ANNOUNCEMENTS 
                  %STATUSES debug extract_owner_info error_message fetch_account_number );

my $ic = DBI->connect("DBI:ODBC:icapp1_ic","djackson_ic","wakj3n8y") 
    or die "Connect failed:  $DBI::errstr\n";

sub fetch_company {
    return $ic->selectrow_array('SELECT company_number FROM intercall_companies WHERE dialinNoid=?', undef, @_);
}

sub do_request {
    my ($company_number, $opts) = @_;

    my $soap = SOAP::Lite
	->proxy(ACCOUNT_API_ROOT)
	->uri(ACCOUNT_NS)
	->autotype(0)
	->on_debug(\&debug)
	->call(SOAP::Data->name('add-account-request')->attr({'xmlns' => ACCOUNT_NS}) => 
	       SOAP::Data->name('login-info' => 
				\SOAP::Data->value(
				    SOAP::Data->name('user-name'      => USERNAME),
				    SOAP::Data->name('password'       => PASSWORD),
				    SOAP::Data->name('company-number' => $company_number)
				)
	       ),
	       SOAP::Data->name('add-account' => 
				\SOAP::Data->value(
				    SOAP::Data->name('general-info' => 
						     \SOAP::Data->value(
							 SOAP::Data->name('company-name'         => $opts->{'acctgrpid'}),
							 SOAP::Data->name('account-name'         => $opts->{'acctgrpid'}),
							 SOAP::Data->name('siccode'              => '1'),
							 SOAP::Data->name('owner-email-required' => 'false')
						     )
				    ),
				    SOAP::Data->name('account-contact-info' => 
						     \SOAP::Data->value(
							 SOAP::Data->name('first-name'          => $opts->{'acctgrpid'}),
							 SOAP::Data->name('last-name'           => $opts->{'acctgrpid'}),
							 SOAP::Data->name('address1'            => '56 Main St'),
							 SOAP::Data->name('city'                => 'Millburn'), 
							 SOAP::Data->name('state'               => 'NJ'), 
							 SOAP::Data->name('country'             => 'US'),
							 SOAP::Data->name('zip'                 => '07041'), 
							 SOAP::Data->name('phone'               => '8882037900'),
							 SOAP::Data->name('email'               => 'intercall@infiniteconferencing.com'),
						     )
				    ),
				    SOAP::Data->name('bill-contact-info' => 
						     \SOAP::Data->value(
							 SOAP::Data->name('first-name'          => $opts->{'acctgrpid'}),
							 SOAP::Data->name('last-name'           => $opts->{'acctgrpid'}),
							 SOAP::Data->name('address1'            => '56 Main St'),
							 SOAP::Data->name('city'                => 'Millburn'), 
							 SOAP::Data->name('state'               => 'NJ'), 
							 SOAP::Data->name('country'             => 'US'),
							 SOAP::Data->name('zip'                 => '07041'), 
							 SOAP::Data->name('phone'               => '8882037900'),
							 SOAP::Data->name('email'               => 'intercall@infiniteconferencing.com'),
						     )
				    )
				)
	       )
	); 

    return $soap;      
}

sub insert_account {
    return $ic->do('INSERT INTO intercall_accounts(company_number, acctgrpid, account_number) VALUES(?,?,?)', undef, @_);
}

sub main {

    my %opts = ();
    GetOptions( \%opts, 
		'debug|D',
		'sandbox|S',
		'acctgrpid|a=s', 
		'dialinNoid=s');

    if(!defined($opts{'acctgrpid'}) || !defined($opts{'dialinNoid'})) {
	die "USAGE: $0 [-D] [-S] -a acctgrpid --dailinNoid dialinNoid ";
    }
    
    $DEBUG   = defined($opts{'debug'});
    $SANDBOX = defined($opts{'sandbox'});

    #print Dumper \%opts if $DEBUG;

    my $company_number = fetch_company($opts{'dialinNoid'});
    if(!defined($company_number)) {
	die "Unknown Intercall branding, is the appropriate record in the 'intercall_companies' table?";
    }

    my $rv = do_request($company_number, \%opts);

    #print Dumper $rv if $DEBUG;

    if(!$rv->match('//error-Information')) {
	my $account        = $rv->valueof('//account');
	my $account_number = $account->{'account-number'};
	insert_account($company_number, $opts{'acctgrpid'}, $account_number);
    } else {
	die error_message($rv);
    }

}
main();
