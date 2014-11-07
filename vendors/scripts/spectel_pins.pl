#!c:/perl/bin/Perl.exe

use strict;

use ICConfig qw( PERL_BIN SCRIPT_ROOT debug );

use Getopt::Std;
use DBI;
use Data::Dumper;

# process and validate options
my %opts;
getopts("m:a:f:l:p:u:", \%opts);

# connect to the informix db on the bridge
my $dbh = DBI->connect('DBI:ODBC:Spectel Bridge DB', 'brdgdbo', 'brdgdbo') 
    or die "Connect failed:  $DBI::errstr\n";

# Connect to IC
my $ic = DBI->connect("DBI:ODBC:icapp1_ic","djackson_ic","wakj3n8y") 
    or die "Connect failed:  $DBI::errstr\n";

# used by create/update stored proc
my $user = 'spectel_pins.pl';

#-------------------------------------------------------------------------------
# Utilities
#-------------------------------------------------------------------------------

sub trim {
    my $str = shift;
    $str =~ s/^\s+//;
    $str =~ s/\s+$//;
    return $str;
}

sub build_filter { 
    my %opts     = @_;

    my @criteria = ();
    my @values   = ();

    if(exists $opts{'a'}) {
	push(@criteria, 'clientcompanyname=?');
	push(@values, $opts{'a'});	
    }

    if(exists $opts{'p'}) {
	push(@criteria, 'clientmainpincode=?');
	push(@values, $opts{'p'});	
    }

    if(exists $opts{'f'}) {
	push(@criteria, 'clientfirstname=?');
	push(@values, $opts{'f'});	
    }

    if(exists $opts{'l'}) {
	push(@criteria, 'clientlastname=?');
	push(@values, $opts{'l'});	
    }

    unless(scalar @criteria) {
	push(@criteria, "clientmainpincode != '                '");
    }

    return (join(' AND ', @criteria), @values);
}

sub get_client_by_pin {
    my $pin     = shift;
    my $pad_len = 16; # field is CHAR not VARCHAR so values are padded

    my $stmt = $dbh->prepare('SELECT * FROM client WHERE clientmainpincode = ?');
    $stmt->execute(sprintf("%-${pad_len}s", $pin));

    return $stmt->fetchrow_hashref();
}

sub get_client_by_ref {
    my $ref = shift;

    my $stmt = $dbh->prepare('SELECT * FROM client WHERE clientref = ?');
    $stmt->execute($ref);

    return $stmt->fetchrow_hashref();
}

sub insert_pin {
    my $sql = qq{ INSERT INTO pin_codes(bridgeid, external_id, first_name, last_name, company, pin, active)
                  VALUES(4, ?, ?, ?, ?, ?, 1) };
    return $ic->do($sql, undef, @_);
}

sub update_pin {
    my $sql = qq{ UPDATE pin_codes SET first_name=?, last_name=?, company=?, pin=? WHERE bridgeid=4 AND external_id=? };
    return $ic->do($sql, undef, @_);    
}

sub deactivate_pin {
    my $sql = qq{ UPDATE pin_codes SET active=0 WHERE bridgeid=4 AND external_id=? };
    return $ic->do($sql, undef, @_);    
}

#-------------------------------------------------------------------------------
# Main Routines
#-------------------------------------------------------------------------------

sub create_client {
    my ($acctgrpid, $first_name, $last_name, $pin) = @_;

    my $stmt = $dbh->prepare("EXECUTE PROCEDURE 'brdgdbo'.p_addclient(1, NULL, NULL, ?, ?, NULL, ?, NULL, NULL, NULL, NULL, ?, NULL, NULL, NULL, NULL, NULL, NULL, ?)");
    $stmt->execute(($first_name, $last_name, $acctgrpid, $pin, $user)) or die 'Error creating client: ' . $stmt->errstr;
    my @rv = $stmt->fetchrow_array();

    return $rv[0];
}

sub delete_client {
    my $clientref = shift;
    my $stmt = $dbh->prepare("EXECUTE PROCEDURE 'brdgdbo'.p_delclient(1, ?)");
    $stmt->execute($clientref) or die 'Error deleting client: ' . $stmt->errstr;
}

sub read_clients {
    my %opts = @_;
    my ($criteria, @values) = build_filter(%opts);

    my $stmt = $dbh->prepare("SELECT clientref,clientfirstname,clientlastname,clientcompanyname,clientmainpincode FROM client WHERE ${criteria}");
    $stmt->execute(@values);

    while(my $r = $stmt->fetchrow_hashref()) {
	print ' ClientRef: ' . $r->{'clientref'} . "\n";
	print 'First Name: ' . $r->{'clientfirstname'} . "\n";
	print ' Last Name: ' . $r->{'clientlastname'} . "\n";
	print '   Company: ' . $r->{'clientcompanyname'} . "\n";
	print '       PIN: ' . $r->{'clientmainpincode'} . "\n";
	print "\n";
    }
}

sub dump_clients {
    my %opts = @_;
    my ($criteria, @values) = build_filter(%opts);

    my $stmt = $dbh->prepare("SELECT clientref,clientfirstname,clientlastname,clientcompanyname,clientmainpincode FROM client WHERE ${criteria}");
    $stmt->execute(@values);

    print "clientref,first,last,company,pin\n";
    
    while(my $r = $stmt->fetchrow_hashref()) {
	printf("'%s','%s','%s','%s','%s'\n", 
	       trim($r->{'clientref'}), 
	       trim($r->{'clientfirstname'}), 
	       trim($r->{'clientlastname'}), 
	       trim($r->{'clientcompanyname'}), 
	       trim($r->{'clientmainpincode'}));
    }
}

sub update_client {
    my ($userid, $acctgrpid, $first_name, $last_name, $pin) = @_;

    my $stmt = $dbh->prepare("EXECUTE PROCEDURE 'brdgdbo'.p_updclient(1, ?, NULL, NULL, ?, ?, NULL, ?, NULL, NULL, NULL, NULL, ?, NULL, NULL, NULL, NULL, NULL, NULL, ?)");
    $stmt->execute(($userid, $first_name, $last_name, $acctgrpid, $pin, $user)) or die 'Error updating client: ' . $stmt->errstr;
}

#-------------------------------------------------------------------------------
# Entry Point
#-------------------------------------------------------------------------------

sub main {
    my %opts = @_;
    
    my %dispatch = (
	'read' => sub {
	    read_clients(%opts);
	}, 

	'dump' => sub {
	    dump_clients(%opts);
	},

	'create' => sub {
	    die "USAGE: $0 -m create -a acctgrpid -f first_name -l last_name -p pin" unless (exists $opts{'a'} && exists $opts{'f'} && exists $opts{'l'} && exists $opts{'p'});

	    my $rv = get_client_by_pin($opts{'p'});
	    if(!$rv) {
		$rv = create_client($opts{'a'}, $opts{'f'}, $opts{'l'}, $opts{'p'});
		print 'PIN Id: ' . $rv;
 		insert_pin($rv, $opts{'f'}, $opts{'l'}, $opts{'a'}, $opts{'p'}) or die sprintf("Could not insert pin id %d", $rv);
	    } else {
		die 'Pin ' . $opts{'p'} . ' found belonging to clientref ' . $rv->{'clientref'};
	    }	    
	}, 

	'delete' => sub {
	    die "USAGE: $0 -m delete -u userid" unless (exists $opts{'u'});

	    my $rv = get_client_by_ref($opts{'u'});
	    if($rv) {
		delete_client($opts{'u'});
		deactivate_pin($opts{'u'}) or die sprintf("Could not deactivate pin id %d", $opts{'u'});
	    } else {
		die 'No client found by clientref ' . $opts{'u'};
	    }
	},

	'update' => sub {
	    die "USAGE: $0 -m update -u userid -a acctgrpid -f first_name -l last_name -p pin" unless (exists $opts{'u'} && exists $opts{'a'} && exists $opts{'f'} && exists $opts{'l'} && exists $opts{'p'});

	    my $rv = get_client_by_ref($opts{'u'});
	    if($rv) {
		my $existing_pin = get_client_by_pin($opts{'p'});

		if($rv->{'clientmainpincode'} == $opts{'p'} || !$existing_pin) {
		    update_client($opts{'u'}, $opts{'a'}, $opts{'f'}, $opts{'l'}, $opts{'p'});
		    update_pin($opts{'f'}, $opts{'l'}, $opts{'a'}, $opts{'p'}, $opts{'u'}) or die sprintf("Could not update pin id %d", $opts{'u'});
		} else {
		    die 'Pin ' . $opts{'p'} . ' found belonging to clientref ' . $existing_pin->{'clientref'};
		}
	    } else {
		die 'No client found by clientref ' . $opts{'u'};
	    }
	}
    );

    if(exists $opts{'m'} && exists $dispatch{$opts{'m'}}) {
	$dispatch{$opts{'m'}}();
    } else {
	die "USAGE: $0 -m create|read|delete|dump|update [-a acctgrpid] [-f first_name] [-l last_name] [-p pin] [-u userid]";
    }
    
}
main(%opts);
