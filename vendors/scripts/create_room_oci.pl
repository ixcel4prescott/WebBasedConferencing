#!C:/perl/bin/perl.exe
#--------------------------------------------------------------------------------
#
# create_room_oci.pl
#  Create a room on the OCI bridge
#
#--------------------------------------------------------------------------------

use strict;
use warnings;

use DBI;
use Getopt::Long;
use Data::Dumper;

use ICConfig qw( PERL_BIN SCRIPT_ROOT );

use constant ORG_ID_IC    => 3;
use constant ORG_ID_MB    => 6;
use constant OCI_BRIDGEID => 3;
use constant BOT_USERNAME => "mycabot";
use constant BOT_PASSWORD => "676767";

my %feature_defaults = (
    'startmode'             => 0,
    'namerecording'         => 0,
    'endonchairhangup'      => 0,
    'dialout'               => 0,
    'record_playback'       => 1,
    'digitentry1'           => 0,
    'confirmdigitentry1'    => 0,
    'digitentry2'           => 0,
    'confirmdigitentry2'    => 0,
    'muteallduringplayback' => 0,
    'entryannouncement'     => 0,
    'exitannouncement'      => 0,
    'endingsignal'          => 1,
    'dtmfsignal'            => 2,
    'recordingsignal'       => 2,
    'securitytype'          => 0,
    'lang'                  => 0, 
);

my $octave = DBI->connect("DBI:ODBC:icapp1","djackson_ic","wakj3n8y") 
    or die "Connect failed:  $DBI::errstr\n";

my $ic = DBI->connect("DBI:ODBC:icapp1_ic","djackson_ic","wakj3n8y") 
    or die "Connect failed:  $DBI::errstr\n";

sub build_confname {
    my ($acctgrpid, $contact, $company) = @_;
    my $confname;
    
    my $prefix = fetch_reseller_prefix($acctgrpid) or 
	die "Could not fetch reseller prefix for $acctgrpid";
   
    if(!$company) {
	my $base_company = fetch_company($acctgrpid) or 
	    die "could not fetch company for $acctgrpid";

	$company = sprintf("%.12s", $base_company);
    }
    
    $confname = sprintf("%28.28s", "$prefix-$company-$contact");
    $confname =~ s/^\s*//;
    $confname =~ s/\s//;

    return $confname;
}

sub fetch_reseller_prefix {
    my ($acctgrpid) = @_;

    my $sql = qq{ SELECT reseller.racctprefix
                  FROM accountgroup
                  JOIN salesperson ON accountgroup.salespid = salesperson.salespid
                  JOIN reseller ON salesperson.resellerid = reseller.resellerid 
                  WHERE acctgrpid = ? };
    
    return $ic->selectrow_array($sql, undef, $acctgrpid);
}

sub fetch_company {
    my ($acctgrpid) = @_;
    my $sql = 'SELECT bCompany FROM accountgroup WHERE acctgrpid = ?';    
    return $ic->selectrow_array($sql, undef, $acctgrpid);    
}

sub fetch_bot_userid {
    my ($bot_loginname) = @_;
    my $sql = 'SELECT UserId FROM AppUser Where LoginName=?';
    return $octave->selectrow_array($sql, undef, $bot_loginname);
}

sub update_bot_org { 
    my ($bot_userid, $org_id) = @_;    
    return $octave->do('UPDATE UserOrg SET OrganizationId=? WHERE UserId=?', undef, $org_id, $bot_userid);
}

sub validate_code {
    my ($code) = @_;

    my $rv = 1;

    if(length($code)>15) { 
 	print "Entry code $code is too long";
	$rv = 0;
    }

    if($code =~ /\D/) { 
 	print "Entry code $code is not numeric";
	$rv = 0;
    }

    if(is_code_taken($code)) {
 	print "Entry code $code exists on bridge";
	$rv = 0;
    }

    return $rv;
}

sub is_code_taken {
    return $octave->selectrow_array('SELECT * FROM ConferenceEntryCode WHERE EntryCodeDTMF=?', undef, @_);
}

sub fetch_confirmation_number {
    my ($cec, $pec) = @_;

    my $sql = qq{ SELECT ConfirmationNumber 
                  FROM Conference
                  LEFT OUTER JOIN ConfSchedule AS ConfSchedule WITH (NOLOCK) ON ConfSchedule.ConferenceId = Conference.ConferenceId
                  LEFT OUTER JOIN ConferenceEntryCode AS cec WITH (NOLOCK) ON cec.ConferenceId = Conference.ConferenceId AND cec.CECEnum=3
                  LEFT OUTER JOIN ConferenceEntryCode AS pec WITH (NOLOCK) ON pec.ConferenceId = Conference.ConferenceId AND pec.CECEnum=1
                  WHERE cec.EntryCodeDTMF=? AND pec.EntryCodeDTMF=? };

    return $octave->selectrow_array($sql, undef, $cec, $pec);
}

sub insert_account {
    my ($accountid, $data) = @_;

    my @parts = (
	'accountid',
	'acctgrpid',
	'bridgeid', 
	'cec',
	'pec', 
	'contact', 
	'company', 
	'maximumconnections', 
	'note1', 
	'note2', 
	'note3', 
	'note4'
    );

    my @params = (
	$accountid, 
	$data->{'acctgrpid'},
	OCI_BRIDGEID,
	$data->{'cec'}, 
	$data->{'pec'}, 
	$data->{'contact'}, 
	$data->{'company'}, 
	$data->{'maxparticipants'}, 
	$data->{'note'}->{'note1'}, 
	$data->{'note'}->{'note2'}, 
	$data->{'note'}->{'note3'}, 
	$data->{'note'}->{'note4'}
    );
    
    foreach my $k (keys %feature_defaults) {
	push @parts, $k;

	if(defined($data->{'feature'}->{$k})) {
	    push @params, $data->{'feature'}->{$k};
	} else {
	    push @params, $feature_defaults{$k};
	}
    }

    my $sql = sprintf('INSERT INTO account(%s) VALUES(%s)', join(',', @parts), join(',', map('?', @parts)));

    return $ic->do($sql, undef, @params);
}

sub update_notes {
    my $sql = 'UPDATE Conference SET AuxData1=?,AuxData2=?,AuxData3=?,AuxData4=? WHERE ConferenceId IN (SELECT ConferenceId FROM ConfSchedule WHERE ConfirmationNumber=?)';
    return $octave->do($sql, undef, @_);
}

sub update_bot_preferences {
    my ($user_id, $features) = @_;

    my %oci_features = (
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
    );

    my @parts;
    my @params;
    foreach my $k (keys %oci_features) {
	push @parts, sprintf('%s=?', $oci_features{$k});
	
	if(defined($features->{$k})) {
	    push @params, $features->{$k};
	} else {
	    push @params, $feature_defaults{$k};
	}
    }

    push @parts, 'bBypassAutoDelete=?';
    if(defined($features->{'isopassist'})) { 
	push @params, $features->{'isopassist'};
    } elsif(defined($features->{'isevent'})) {
	push @params, $features->{'isevent'};
    } else {
	push @params, 0;
    }
    
    push @params, $user_id;

    my $sql = sprintf('UPDATE SchedulerPreferences SET %s WHERE UserId=?', join(',', @parts));

    return $octave->do($sql, undef, @params);
}

sub main {
    my %opts;
    GetOptions( \%opts, 
		'debug|D',
		'acctgrpid|a=s', 
		'company|c=s',
		'cec=s', 
		'pec=s',
		'confname|r=s',
		'contact|t=s',
		'maxparticipants|m=i',
		'feature|f=s%',
	        'note|n=s%' );

    my $username = BOT_USERNAME;
    my $password = BOT_PASSWORD;

    if(!defined($opts{'acctgrpid'}) || !defined($opts{'contact'}) || !defined($opts{'cec'}) || !defined($opts{'pec'})) {
	die "usage: $0 -a acctgrpid -t contact --cec cec_code --pec pec_code [-c companyname] [-r confname] [-n note1=abc...] [-f feature=...]";
    }
    
    my $bot_userid = fetch_bot_userid($username);
    if(!defined($bot_userid)) { 
	die "Could not fetch UserId for bot username $username";
    }

    if(!defined($opts{'maxparticipants'})) {
	$opts{'maxparticipants'} = 100;
    }
     
    for my $n (qw(note1 note2 note3 note4)) {
	if(!defined($opts{'note'}->{$n})) {
	    $opts{'note'}->{$n} = undef;
	}
    }
   
    validate_code($opts{'cec'}) or die "CEC $opts{'cec'} failed validation";
    validate_code($opts{'pec'}) or die "PEC $opts{'pec'} failed validation";

    my $confname = defined($opts{'confname'}) ? $opts{'confname'} : 
	build_confname($opts{'acctgrpid'}, $opts{'contact'}, $opts{'company'});

    update_bot_org($bot_userid, ($opts{'acctgrpid'} =~ /^MB/) ? ORG_ID_MB : ORG_ID_IC);
    update_bot_preferences($bot_userid, $opts{'feature'});

    system(SCRIPT_ROOT . "/schedconf210.exe -h 38.101.211.98 -a $opts{'acctgrpid'} -p $opts{'pec'} -c $opts{'cec'} -n \"$confname\" -L $username -P $password");
    if(($? >> 8) == 0) { 
	update_bot_org($bot_userid, ORG_ID_IC);

	my $accountid = fetch_confirmation_number($opts{'cec'}, $opts{'pec'});

	insert_account($accountid, \%opts);

	if($opts{'note'}){
	    update_notes($opts{'note'}->{'note1'}, $opts{'note'}->{'note2'}, $opts{'note'}->{'note3'}, $opts{'note'}->{'note4'}, $accountid);
	}

    } else {
	die "schedconf210 failed: /schedconf210.exe -h 38.101.211.98 -a $opts{'acctgrpid'} -p $opts{'pec'} -c $opts{'cec'} -n \"$confname\" -L $username -P $password";
    }
}
main();

