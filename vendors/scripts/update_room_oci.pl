#!C:/perl/bin/perl.exe
#--------------------------------------------------------------------------------
#
# update_room_oci.pl
#  Update a room on the OCI bridge
# 
#--------------------------------------------------------------------------------

use strict;
use warnings;

use Getopt::Long;
use DBI;
use Data::Dumper;

my $octave = DBI->connect("DBI:ODBC:icapp1","djackson_ic","wakj3n8y") 
    or die "Connect failed:  $DBI::errstr\n";

my $ic = DBI->connect("DBI:ODBC:icapp1_ic","djackson_ic","wakj3n8y") 
    or die "Connect failed:  $DBI::errstr\n";

sub update_conference {
    my ($confirmation_number, $features) = @_;
            
    my %oci_features = (
	'confname'              => 'ConfName',
	'startmode'             => 'StartProcEnum',
	'namerecording'         => 'bRecordNames',
	'endonchairhangup'      => 'bChairHangup',
	'dialout'               => 'bAllowChairDialout',
	'record_playback'       => 'bAllowUARecordPlayback',
	'digitentry1'           => 'eCDRDigitRequired',
	'confirmdigitentry1'    => 'bBypassCDRDigitConfirmation',
	'digitentry2'           => 'eCDRDigitRequired2',
	'confirmdigitentry2'    => 'bBypassCDRDigitConfirmation2',
	'muteallduringplayback' => 'bMutePlayback',
	'entryannouncement'     => 'EntryProcEnum',
	'exitannouncement'      => 'ExitProcEnum',
	'endingsignal'          => 'WarningProcEnum',
	'dtmfsignal'            => 'OtherProcEnum',
	'recordingsignal'       => 'RecordingProcEnum',
	'securitytype'          => 'SecurityMethodEnum',
	'lang'                  => 'LanguageTypeEnum',
	'isopassist'            => 'bBypassAutoDelete',
	'isevent'               => 'bBypassAutoDelete',
	'maximumconnections'    => 'MaxParticipants',
	'confname'              => 'ConfName', 
	'scheduletype'          => 'SchedTypeEnum', 
	'note1'                 => 'AuxData1',
	'note2'                 => 'AuxData2',
	'note3'                 => 'AuxData3',
	'note4'                 => 'AuxData4'
    );

    my @parts;
    my @params;
    foreach my $k (keys %$features) {
	if(defined($oci_features{$k})) {
	    push @parts, sprintf('%s=?', $oci_features{$k});

	    if($k eq 'confname') {
		$features->{$k} = substr($features->{$k}, 0, 30);
	    }

	    push @params, $features->{$k};
	}
    }
    
    push @params, $confirmation_number;

    my $sql = sprintf('UPDATE Conference SET %s WHERE ConferenceId IN (SELECT ConferenceId FROM ConfSchedule WHERE ConfirmationNumber=?)', join(',', @parts));

    return $octave->do($sql, undef, @params);
}

sub update_record {
    my ($accountid, $features) = @_;

    my @parts;
    my @params;
    foreach my $k (keys %$features) {
	if($k ne 'confname' && $k ne 'startdate') { 
	    push @parts, sprintf('%s=?', $k);
	    push @params, $features->{$k};
	}
    }
    
    push @params, $accountid;

    my $sql = sprintf('UPDATE account SET %s WHERE accountid=?', join(',', @parts));

    print STDERR sprintf("%s\n", $sql);

    return $ic->do($sql, undef, @params);
}

sub main {

    my %opts;
    GetOptions( \%opts, 
		'debug|D',
		'acctgrpid|a=s', 
		'accountid|c=s',
		'feature|f=s%' );

    if(!defined($opts{'acctgrpid'}) || !defined($opts{'accountid'})) {
	die "USAGE: $0 [-D] -a acctgrpid -c accountid -t contact --cec cec --pec pec -f foo=bar...";
    }

    if(!$opts{'accountid'} =~ /^20(\d+)$/) {
	die "Invalid confirmation number: " . $opts{'accountid'};
    }

    update_conference($opts{'accountid'}, $opts{'feature'}) or 
	die "Could not update conference: " . $opts{'accountid'};

    update_record($opts{'accountid'}, $opts{'feature'}) or 
	die "Could not update account: " . $opts{'accountid'};

}
main();
