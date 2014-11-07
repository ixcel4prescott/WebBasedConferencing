#!C:/perl/bin/perl.exe
#--------------------------------------------------------------------------------
#
# send_email.pl
#   Build and send an email message
# 
#--------------------------------------------------------------------------------

use strict;
use warnings;

use Getopt::Long;
use Data::Dumper;
use MIME::Lite;
use LWP::MediaTypes qw(guess_media_type);

use ICConfig qw( PERL_BIN SCRIPT_ROOT MAIL_SERVER DEFAULT_FROM_ADDR );

sub main {

    my %opts = ();
    GetOptions( \%opts, 
		'debug|D',
		'subject|s=s',
		'from|f=s',
		'to|t=s@',
		'body|b=s',
		'body-path=s',
		'bcc=s@',
		'cc=s@', 
		'attach|a=s@');

    if(!defined($opts{'subject'}) && 
       (!defined($opts{'body'}) || !defined($opts{'body-path'})) && 
       (!defined($opts{'to'}) || !defined($opts{'cc'}) || !defined($opts{'bcc'}))) {
	die "USAGE: $0 [-D] -s SUBJECT [-f FROM] -b BODY|-body-path PATH -to ... -bcc ... -cc ... -attach ...";
    }

    print Dumper \%opts if $opts{'debug'};
    
    my $mail = MIME::Lite->new(
	Type    => 'multipart/mixed',
	From    => defined($opts{'from'}) ? $opts{'from'} : DEFAULT_FROM_ADDR,
	Subject => $opts{'subject'},
	To      => defined($opts{'to'}) ? join(', ', @{$opts{'to'}}) : undef,
	Cc      => defined($opts{'cc'}) ? join(', ', @{$opts{'cc'}}) : undef,
	Bcc     => defined($opts{'bcc'}) ? join(', ', @{$opts{'bcc'}}) : undef
    );

    if(defined($opts{'body'})) {
	$mail->attach( 
	    Type => 'TEXT', 
	    Data => $opts{'body'}
	);
    }

    if(defined($opts{'body-path'})) {
	if(-e $opts{'body-path'}) {

	    local( $/, *DATA);
	    open(DATA, '<', $opts{'body-path'}) or die "Could not open body data: $!";

	    $mail->attach( 
		Type => guess_media_type($opts{'body-path'}),
		Data => <DATA>
    	    );
	} else {
	    die "Body data does not exist: $opts{'body-path'}";
	}
    }

    if(defined($opts{'attach'})) {	
	foreach my $f (@{$opts{'attach'}}) {
	    if(-e $f) { 
		$mail->attach(
		    Type        => guess_media_type($f), 
		    Disposition => 'attachment',
		    Path        => $f
		);
	    } else {
		die "Attachment does not exist: $f";
	    }
	}
    }

    $mail->send('smtp', MAIL_SERVER, Debug=>$opts{'debug'});
}
main();
