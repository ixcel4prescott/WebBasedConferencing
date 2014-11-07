 #!/usr/bin/perl
#--------------------------------------------------------------------------------
#
# do_intercall_request.pl
#  Issue a raw soap request reading xml from STDIN/file and writing to STDOUT/file
# 
# NB: To install Crypt::SSLeay for https support in Soap::Lite
#     ppm install http://theoryx5.uwinnipeg.ca/ppms/Crypt-SSLeay.ppd
#
#--------------------------------------------------------------------------------

use strict;
use warnings;

use Getopt::Std;
use Data::Dumper;
use HTTP::Cookies;
use LWP::UserAgent;

use Intercall qw( $DEBUG &NS &INTERCALL_BRIDGEID &API_ROOT &USERNAME &PASSWORD %ANNOUNCEMENTS 
                  %STATUSES debug extract_owner_info error_message );

sub do_request {
    my ($input) = @_;

    my $user_agent = new LWP::UserAgent;
    
    my $headers = new HTTP::Headers (
        'Content-Type'   => 'text/xml; charset=utf-8',
        'SOAPAction'     => NS,
    );

    my $request  = new HTTP::Request('POST', API_ROOT, $headers, $input);
    my $response = $user_agent->request($request);

    return $response->content;
}

sub main {
    my %opts;
    getopts("i:o:", \%opts);

    my $fh;

    if(defined($opts{'i'})) {
	open($fh, '<', $opts{'i'}) 
	    or die "Could not open: " . $opts{'i'};
    } else {
	$fh = <STDIN>;
    }

    undef $/;
    my $input = <$fh>;

    my $output = do_request($input);

    if(defined($opts{'o'})) {
	open($fh, '>', $opts{'o'}) 
	    or die "Could not open: " . $opts{'o'};	
    } else {
	$fh = <STDOUT>;
    }

    print $fh, $output;
}
main();
