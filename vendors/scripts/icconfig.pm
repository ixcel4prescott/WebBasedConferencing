package ICConfig;

use strict;
use warnings;

use base qw( Exporter );

our @EXPORT_OK = qw( $DEBUG &PERL_BIN &SCRIPT_ROOT &MAIL_SERVER &DEFAULT_FROM_ADDR &debug );
our $DEBUG     = 0;

sub PERL_BIN()          { 'C:/Perl/bin/perl.exe'                                  };
sub SCRIPT_ROOT()       { $DEBUG ? 'X:/myca-dev/vendors/scripts' : 
			      'C:/www/myca/vendors/scripts'                       };
sub MAIL_SERVER()       { 'mail.infiniteconferencing.com'                         };
sub DEFAULT_FROM_ADDR() { 'MyConferenceAdmin<do-not-reply@myconferenceadmin.com>' };

sub debug {
    my $msg = shift;
    print STDERR $msg;
}

1

