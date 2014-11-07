#!C:/perl/bin/perl.exe

require "getopts.pl";
use XML::Simple;
use LWP::UserAgent;
use DBI;
use MD5;

our $opt_c;
our $opt_u;
&Getopts('c:u:');

#my $dbh = DBI->connect("DBI:ODBC:BILLING") or die "couldn't connect to billing";
my $dbh = DBI->connect("DBI:ODBC:icapp1_ic","djackson_ic","wakj3n8y") or die "couldn't connect to billing";

my $u = "UPDATE account SET webinterpoint = 0 WHERE cec = ?";
my $upd = $dbh->prepare($u) or die "prepare failed";
$u = "UPDATE account SET webinterpoint = 0 WHERE accountid = ?";
my $upd2 = $dbh->prepare($u) or die "prepare failed";


my @url = ('http://www.conferenceservers.com/register/api/main.asp?xml=');
my $customer = 'INF';

# this portion fills in the values required
my $i = 0;
my @content;
print STDOUT "cec\n" unless defined ($opt_c);
if (defined($opt_c))
{
	&widel($opt_c);
}
else
{
	print STDERR "use QUIT if EOF fails\n";
	while ($line = <STDIN>)
	{
		chomp($line);
		last if ($line eq "QUIT");
		next if ($line =~ /^\#/);
		($cec) = split(/\t/, $line);

		&widel($cec);
	}

}
$dbh->disconnect;

sub make_check_code
{
	my $modid = shift;

	my $context = new MD5;

	$context->add($modid, 'BlormTip922');
	my $digest = $context->hexdigest();
	return($digest);
}

sub widel
{

	my $cec = shift;

	$cec =~ s/-//g;

	$i = 0;
	$xmlstr = '<WDAPI type="call" name="deleteSubscriber"><PARAMETERS><SUBSCRIBER';

	$xmlstr .= " svc-prov=\"$customer\"";
	$xmlstr .= " subscriber-id=\"$cec\"";
	$xmlstr .= " check=\"" . &make_check_code($cec) . "\"";
	$xmlstr .= '/></PARAMETERS></WDAPI>';

	foreach $url (@url)
	{
		my $nurl = $url . $xmlstr;
#print STDERR "$nurl\n";

		my $ua = new LWP::UserAgent;
		#$ua->agent("Webdialogs/0.1 " . $ua->agent);

		my $req = new HTTP::Request GET => $nurl;
		$req->content_type('application/x-www-form-urlencoded');
		$req->content($content);

		my $res = $ua->request($req);

		if ($res->is_success)
		{
			print STDERR $res->content;
			print STDERR "\nGOT IT!$accountid\n";
			my $xml = XMLin($res->content);
			if ($xml->{STATUS}->{result} =~ /OK/i)
			{
				print STDOUT "GOT OKAY BACK\n";
				if (defined($opt_u))
				{	
					$upd2->execute($opt_u) or die "execute: $accountid\n";
					$upd2->finish;
				}
				else
				{
					$upd->execute($cec) or die "execute: $accountid\n";
					$upd->finish;
				}
			}
			else
			{
				print STDOUT "GOT $xml->{ERROR}->{description} BACK\n"
			}
		}
		else
		{
			print STDOUT "oops:$line\n";
		}
	}
}
