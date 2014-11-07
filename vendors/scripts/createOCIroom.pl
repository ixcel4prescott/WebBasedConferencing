#!c:/perl/bin/Perl.exe

# Include common constants
use ICConfig qw( PERL_BIN SCRIPT_ROOT );

##  CODE: Check entry codes for alphanumerics and length

use Getopt::Std;
use DBI;
use Data::Dumper;

getopts("a:c:t:C:P:pr:m:n:");

die "usage: $0 -a acctgrpid -t contact -C cec_code -P pec_code [-c companyname -p publish]" unless ((defined($opt_a) && defined($opt_t)) && (defined($opt_C) || defined($opt_P)));

if (!-e 'C:/SUA'){
	mkdir 'C:/SUA';
}

$dbh = DBI->connect("DBI:ODBC:icapp1","djackson_ic","wakj3n8y") or die "Connect failed:  $DBI::errstr\n";

$billdb  = DBI->connect("DBI:ODBC:icapp1_ic", "djackson_ic","wakj3n8y") or die "connect icapp1\n";
$billdb->do("use IC;") or die "Could not use IC\n";

my $q = "UPDATE RoomView SET State=1 WHERE ConfirmationNumber = ?";

$icstate = $dbh->prepare($q);

$acctgrpid = $opt_a;
$contact = $opt_t;
$cec = $opt_C;
$pec = $opt_P;

if (defined($opt_m)){
	$maxusers = "";
}else{
	$maxusers = "-m $opt_m";
}

$username = "-L mycabot";
$password = "-P 676767";

&ValidateAcctgrpid($acctgrpid);
&ValidateEntryCodes($cec, $pec);

my $confname = &BuildConfname($acctgrpid, $contact, $opt_c, $opt_r);
print STDERR "<$confname><$cec><$pec>\n";

my $cmd = SCRIPT_ROOT . "/schedconf210.exe -h 38.101.211.98 -a $opt_a -p $pec -c $cec -n \"$confname\" $username $password";
print STDERR "$cmd\n";

if ($opt_p){
	if ($acctgrpid =~ /^MB/){
		&ChangeBotOrg(6);	# Change Org to 6 for Meetingbridge
	}else{
		&ChangeBotOrg(3);	# Change Org to 3 for IC
	}
	
	`$cmd`;

	&ChangeBotOrg(3);	# Change Org back to 3 for IC, to cover our ass

	my $confirmation_number = &GetConfirmationNumber($cec,$pec);
	print STDERR "IC Confirmation Number='$confirmation_number'\n";
	
	if ($confirmation_number eq "")
	{
		die "Insert failed!\n";
	}

	## Oddly, we need to update the state and set it to Pending..(0)
	##
	$icstate->execute($confirmation_number);
	$icstate->finish;
	
	if (defined($opt_n) && $opt_n){
		my @notes = split(/;/, $opt_n);
		foreach $note (@notes){
			my ($field, $value) = split(/:/, $note);
			if ($field =~ /^note/ && $value){
				$field =~ s/note/AuxData/g;
				&UpdateConferenceField($confirmation_number, $field, $value);
			}
		}		
	}
	
	print STDOUT $confirmation_number;
}else{
	die "Stopping, no publish.  Use the -p option to publish.\n";
}

sub readconfirm
{
	open(LOG, "< C:/SUA/CONFNUM") || die "open C:/SUA/CONFNUM error: $!\n";
	my $line = <LOG>;
	close(LOG);
	chomp($line);
	$line =~ s/CONFIRMATION NUMBER://;
	unlink("C:/SUA/CONFNUM");
	return($line);
}

sub ValidateAcctgrpid
{
	my $acctgrpid = shift;
	my $billdb  = DBI->connect("DBI:ODBC:icapp1_ic", "djackson_ic","wakj3n8y") or die "connect icapp1\n";
	$billdb->do("use IC;") or die "Could not use IC\n";

	my $q = "SELECT acctgrpid FROM accountgroup WHERE acctgrpid = ?;";
	my $rsh = $billdb->prepare($q) or die "Couldn't prepare ValidateAcctgrpid: <$acctgrpid>, $q.\n";
	$rsh->execute($acctgrpid) or die "Couldn't execute ValidateAcctgrpid: <$acctgrpid>, $q.\n";
	
	my $rh = $rsh->fetchrow_hashref();
	if ($$rh{acctgrpid}){
		return 1;
	}else{
		die "Account Group not found: <$acctgrpid>, $q.\n"
	}	
}

sub ValidateEntryCodes
{
	my ($cec, $pec) = @_;
	
	if (!($cec && $pec)){
		die "Entry Codes Missing [$cec|$pec].\n";
	}

	if (length($cec)>15 || length($pec)>15){
		die "Entry Code is too long [$cec|$pec].\n";
	}

	if (($cec =~ /\D/) || ($pec =~ /\D/)){
		die "Entry Code is not numeric [$cec|$pec].\n";
	}
	
	if (&havedbcode($cec)){
		die "Found Chair Code, but it's Taken [$cec].\n";
	}

	if (&havedbcode($pec)){
		die "Found Participant Code, but it's Taken [$pec].\n";
	}

	return 1;
}

sub BuildConfname
{
	my ($acctgrpid, $contact, $company, $override_confname) = @_;
	my $billdb  = DBI->connect("DBI:ODBC:icapp1", "djackson_ic","wakj3n8y") or die "connect icapp1\n";
	my $confname;
	
	if ($override_confname){
		$confname=$override_confname;
	}elsif (($contact =~ /^A_/) || ($contact =~ /^FT-/)){
		$confname = $contact;
	}else{
		# Get Prefix and Company Name from database based on acctgrpid
		my $prefix = &GetPrefix($acctgrpid);

		if (!$company){
			$company = sprintf("%.12s", &GetCompany($acctgrpid));
		}

		$confname = sprintf("%28.28s", "$prefix-$company-$contact");
		$confname =~ s/^\s*//;
		$confname =~ s/\s//;
	}	
	return $confname;
}

sub GetPrefix
{
	my $acctgrpid = shift;
	my $billdb  = DBI->connect("DBI:ODBC:icapp1_ic", "djackson_ic","wakj3n8y") or die "connect icapp1\n";
	$billdb->do("use IC;") or die "Could not use IC\n";

	my $q = "SELECT reseller.racctprefix AS prefix FROM accountgroup INNER JOIN salesperson ON accountgroup.salespid = salesperson.salespid INNER JOIN reseller ON salesperson.resellerid = reseller.resellerid WHERE acctgrpid = ?;";

	my $rsh = $billdb->prepare($q) or die "Couldn't prepare GetPrefix: <$acctgrpid>, $q.\n";
	$rsh->execute($acctgrpid) or die "Couldn't execute GetPrefix: <$acctgrpid>, $q.\n";
	
	my $rh = $rsh->fetchrow_hashref();
	if ($$rh{prefix}){
		return $$rh{prefix};
	}else{
		die "Coudln't retrieve hash for GetPrefix: <$acctgrpid>, $q.\n"
	}	
}

sub GetCompany
{
	my $acctgrpid = shift;
	my $billdb  = DBI->connect("DBI:ODBC:icapp1_ic", "djackson_ic","wakj3n8y") or die "connect icapp1\n";
	$billdb->do("use IC;") or die "Could not use IC\n";

	my $q = "SELECT bCompany AS company FROM accountgroup WHERE acctgrpid = ?;";

	my $rsh = $billdb->prepare($q) or die "Couldn't prepare prefix: <$acctgrpid>, $q.\n";
	$rsh->execute($acctgrpid) or die "Couldn't execute GetCompany: <$acctgrpid>, $q.\n";
	
	my $rh = $rsh->fetchrow_hashref();
	if ($$rh{company}){
		return $$rh{company};
	}else{
		die "Coudln't retrieve hash for GetCompany: <$acctgrpid>, $q.\n"
	}	
}

sub havedbcode
{
	my ($code) = @_;
	my ($qcode, @check);

	my $codecheck = $dbh->prepare("SELECT * FROM ConferenceEntryCode where EntryCodeDTMF = ?") or die "codecheck prepare failed\n";
	my $billdb  = DBI->connect("DBI:ODBC:icapp1_ic", "djackson_ic","wakj3n8y") or die "connect icapp1\n";
	$billdb->do("use IC;") or die "Could not use IC\n";
	
	# mvolpe: checking that these codes arent used on OCI only so we can create backup rooms for spectel
	$ceccheck = $billdb->prepare("SELECT cec FROM account where cec = ? AND accountid LIKE '20%' AND bridgeid=3");
	$peccheck = $billdb->prepare("SELECT pec FROM account where pec = ? AND accountid LIKE '20%' AND bridgeid=3");

	$qcode = $code;
	$codecheck->execute($qcode) or return 1;
	$check = $codecheck->fetchrow_hashref();
	$codecheck->finish;
	if ($$check{EntryCodeDTMF} eq $code)
	{
		return 1;
	}

	$ceccheck->execute($qcode) or return 1;
	$check = $ceccheck->fetchrow_hashref();
	$ceccheck->finish;
	if ($$check{cec} eq $code)
	{
		return 1;
	}
	$peccheck->execute($qcode) or return 1;
	$check = $peccheck->fetchrow_hashref();
	$peccheck->finish;
	if ($$check{pec} eq $code)
	{
		return 1;
	}


	return 0;
}

sub GetConfirmationNumber
{
	my ($cec,$pec) = @_;
	my $q = "SELECT RoomView.ConfirmationNumber as ConfirmationNumber FROM RoomView INNER JOIN ConferenceEntryCode ON RoomView.ConferenceId = ConferenceEntryCode.ConferenceId where EntryCodeDTMF IN ('$cec','$pec');";
	my $codecheck = $dbh->prepare($q) or die "codecheck prepare failed\n";
print STDERR "$q\n";

	$codecheck->execute();
	my @rs = $codecheck->fetchrow_array() or die "Could not fetch.\n";
	my ($confirmnum) = @rs;
	
	print STDERR "Found [$confirmnum].\n";

	$codecheck->finish;

	return $confirmnum;
}

sub ChangeBotOrg
{
	my ($orgid) = @_;
	my $q;
	my $rh;
	my $octaveDB;
	
	if ($orgid){
		$octaveDB  = DBI->connect("DBI:ODBC:icapp1", "djackson_ic","wakj3n8y") or die "connect icapp1\n";
		$octaveDB->do("use octave;") or die "Could not use Octave\n";

		$q = "UPDATE UserOrg SET OrganizationId=? WHERE UserId=12733;";
		$rh = $octaveDB->prepare($q) or die "Could not prepare [$orgid]: $q\n";
		$rh->execute($orgid) or die "Could not execute [$orgid]: $q\n";
		$rh->finish;

		$octaveDB->disconnect();
	}
}

sub UpdateConferenceField
{
	my ($confirmation_number, $field, $value) = @_;
	my $q;
	my $octaveDB;
	
	if ($confirmation_number){
		$octaveDB  = DBI->connect("DBI:ODBC:icapp1", "djackson_ic","wakj3n8y") or die "connect icapp1\n";
		$octaveDB->do("use octave;") or die "Could not use Octave\n";

		$q = "UPDATE Conference SET $field='$value' WHERE ConferenceId IN (SELECT ConferenceId FROM ConfSchedule WHERE ConfirmationNumber='$confirmation_number');";
		$octaveDB->do($q) or die "Could not Do [$q]\n";

		$octaveDB->disconnect();
	}
}
