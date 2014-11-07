#!c:/perl/bin/Perl.exe

use strict;

use ICConfig qw( PERL_BIN SCRIPT_ROOT debug );

use Getopt::Std;
use DBI;
use Data::Dumper;

# process and validate options
my %opts;
getopts("m:a:f:l:p:u:", \%opts);

# connect to Octave
my $dbh = DBI->connect("DBI:ODBC:icapp1","djackson_ic","wakj3n8y") 
    or die "Connect failed:  $DBI::errstr\n";

# Connect to IC
my $ic = DBI->connect("DBI:ODBC:icapp1_ic","djackson_ic","wakj3n8y") 
    or die "Connect failed:  $DBI::errstr\n";

#-------------------------------------------------------------------------------
# Utilities
#-------------------------------------------------------------------------------

sub trim {
    my $str = shift;
    $str =~ s/^\s+//;
    $str =~ s/\s+$//;
    return $str;
}

sub escape {
    my $str = shift;
    $str =~ s/'/''/;
    return $str;
}

sub build_filter { 
    my %opts     = @_;

    my @criteria = ();
    my @values   = ();

    if(exists $opts{'a'}) {
	push(@criteria, 'LoginName LIKE ?');
	push(@values, $opts{'a'}.'%');	
    }

    if(exists $opts{'p'}) {
	push(@criteria, 'PIN=?');
	push(@values, $opts{'p'});	
    }

    if(exists $opts{'f'}) {
	push(@criteria, 'FirstName=?');
	push(@values, $opts{'f'});	
    }

    if(exists $opts{'l'}) {
	push(@criteria, 'LastName=?');
	push(@values, $opts{'l'});	
    }

    unless(scalar @criteria) {
	push(@criteria, "1=1");
    }

    return (join(' AND ', @criteria), @values);
}

sub get_user_by_pin {
    my $pin = shift;

    my $stmt = $dbh->prepare('SELECT * FROM AppUser WHERE PIN = ? OR PINDTMF=?');
    $stmt->execute(($pin, $pin));

    return $stmt->fetchrow_hashref();
}

sub get_user_by_id {
    my $id = shift;

    my $stmt = $dbh->prepare('SELECT * FROM AppUser WHERE UserId = ?');
    $stmt->execute($id);

    return $stmt->fetchrow_hashref();
}

sub insert_pin {
    my $sql = qq{ INSERT INTO pin_codes(bridgeid, external_id, first_name, last_name, company, pin, active)
                  VALUES(3, ?, ?, ?, ?, ?, 1) };
    return $ic->do($sql, undef, @_);
}

sub update_pin {
    my $sql = qq{ UPDATE pin_codes SET first_name=?, last_name=?, company=?, pin=? WHERE bridgeid=3 AND external_id=? };
    return $ic->do($sql, undef, @_);    
}

sub deactivate_pin {
    my $sql = qq{ UPDATE pin_codes SET active=0 WHERE bridgeid=3 AND external_id=? };
    return $ic->do($sql, undef, @_);    
}

#-------------------------------------------------------------------------------
# Main Routines
#-------------------------------------------------------------------------------

sub create_user {
    my ($acctgrpid, $first_name, $last_name, $pin) = @_;

    my $stmt = $dbh->prepare("INSERT INTO AppUser(PIN, PINDTMF, FirstName, LastName, LoginName, Address1, Address2, City, State, PostalCode, TimeZonePref, " .
			     "NotifyEnumPref, UserStateEnum, PrivEnum, bNotifyPref, ClueEnumPref, UserClue, DateFormatPrefEnum, TimeFormatPrefEnum, " . 
			     "IVRDateTimePrompt, IVRDurationEndPrompt) VALUES(?, ?, ?, ?, ?, 'n/a', 'n/a', 'n/a', 'n/a', 'n/a', 'Eastern Standard Time', 1, 1, 1, 1, 1, 1, 1, 0, 0, 0)");
    $stmt->execute(($pin, $pin, $first_name, $last_name, $acctgrpid . '_' . $pin)) or die 'Error creating user: ' . $stmt->errstr;

    my $stmt = $dbh->prepare('SELECT @@IDENTITY AS UserId') or die 'Error pulling id of newly created user: ' . $stmt->errstr;
    $stmt->execute();

    my @rv = $stmt->fetchrow_array();
    my $user_id = $rv[0];

    my $stmt = $dbh->prepare("INSERT INTO UserPhone VALUES(4, ?, 'n/a')");
    $stmt->execute($user_id) or die 'Error inserting row in UserPhone: ' . $stmt->errstr;

    my $stmt = $dbh->prepare("INSERT INTO UserOrg Values (?, 3)");
    $stmt->execute($user_id) or die 'Error inserting row in UserOrg: ' . $stmt->errstr;

    return $user_id;
}

sub delete_user {
    my $user_id = shift;

    # Need to delete UserPhone/UserOrg first because of constraints
    my $stmt = $dbh->prepare("DELETE FROM UserPhone WHERE UserId=?");
    $stmt->execute($user_id) or die 'Error deleting row in UserPhone: ' . $stmt->errstr;

    my $stmt = $dbh->prepare("DELETE FROM UserOrg WHERE UserId=?");
    $stmt->execute($user_id) or die 'Error deleting row in UserOrg: ' . $stmt->errstr;

    my $stmt = $dbh->prepare("DELETE FROM AppUser WHERE UserId=?");
    $stmt->execute($user_id) or die 'Error deleting user: ' . $stmt->errstr;
}

sub read_users {
    my %opts = @_;
    my ($criteria, @values) = build_filter(%opts);

    my $stmt = $dbh->prepare("SELECT UserId,FirstName,LastName,LoginName,PIN FROM AppUser WHERE ${criteria}");
    $stmt->execute(@values);

    while(my $r = $stmt->fetchrow_hashref()) {
	print '    Userid: ' . $r->{'UserId'} . "\n";
	print 'First Name: ' . $r->{'FirstName'} . "\n";
	print ' Last Name: ' . $r->{'LastName'} . "\n";
	print 'Login Name: ' . $r->{'LoginName'} . "\n";
	print '       PIN: ' . $r->{'PIN'} . "\n";
	print "\n";
    }
}

sub dump_users {
    my %opts = @_;
    my ($criteria, @values) = build_filter(%opts);

    my $stmt = $dbh->prepare("SELECT UserId,FirstName,LastName,LoginName,BillingCode,PIN FROM AppUser WHERE ${criteria}");
    $stmt->execute(@values);

    print "userid,first,last,login,billingcode,pin\n";
    
    while(my $r = $stmt->fetchrow_hashref()) {
	printf("'%s', '%s', '%s', '%s', '%s'\n", 
	       escape(trim($r->{'UserId'})), 
	       escape(trim($r->{'FirstName'})), 
	       escape(trim($r->{'LastName'})), 
	       #escape(trim($r->{'LoginName'})), 
	       escape(trim($r->{'BillingCode'})), 
	       escape(trim($r->{'PIN'})));
    }
}

sub update_user {
    my $opts = @_;
    
    my @fields = ();
    my @values = ();

    if(exists $opts{'a'}) {
	push(@fields, 'LoginName=?');
	push(@values, $opts{'a'});	
    }

    if(exists $opts{'p'}) {
	push(@fields, 'PIN=?');
	push(@values, $opts{'p'});	
	push(@fields, 'PINDTMF=?');
	push(@values, $opts{'p'});	
    }

    if(exists $opts{'f'}) {
	push(@fields, 'FirstName=?');
	push(@values, $opts{'f'});	
    }

    if(exists $opts{'l'}) {
	push(@fields, 'LastName=?');
	push(@values, $opts{'l'});	
    }

    die "USAGE: $0 -m update -u userid [-a acctgrpid] [-f first_name] [-l last_name] [-p pin]" unless scalar @fields;
    
    my $fields_str = join(', ', @fields);
    push(@values, $opts{'u'});

    my $stmt = $dbh->prepare("UPDATE AppUser SET ${fields_str} WHERE UserId=?");
    $stmt->execute(@values) or die 'Error updating client: ' . $stmt->errstr;
}

#-------------------------------------------------------------------------------
# Entry Point
#-------------------------------------------------------------------------------

sub main {
    my %opts = @_;
    
    my %dispatch = (
	'read' => sub {
	    read_users(%opts);
	}, 

	'dump' => sub {
	    dump_users(%opts);
	},

	'create' => sub {
	    die "USAGE: $0 -m create -a acctgrpid -f first_name -l last_name -p pin" unless (exists $opts{'a'} && exists $opts{'f'} && exists $opts{'l'} && exists $opts{'p'});

	    my $rv = get_user_by_pin($opts{'p'});
	    if(!$rv) {
		$rv = create_user($opts{'a'}, $opts{'f'}, $opts{'l'}, $opts{'p'});
		print 'PIN Id: ' . $rv;

		insert_pin($rv, $opts{'f'}, $opts{'l'}, $opts{'a'}, $opts{'p'}) or die sprintf("Could not insert pin id %d", $rv);
	    } else {
		die 'Pin ' . $opts{'p'} . ' found belonging to UserId ' . $rv->{'UserId'};
	    }	    
	}, 

	'delete' => sub {
	    die "USAGE: $0 -m delete -u userid" unless (exists $opts{'u'});

	    my $rv = get_user_by_id($opts{'u'});
	    if($rv) {
		delete_user($opts{'u'});
		deactivate_pin($opts{'u'}) or die sprintf("Could not deactivate pin id %d", $opts{'u'});
	    } else {
		die 'No user found by UserId ' . $opts{'u'};
	    }
	},

	'update' => sub {
	    die "USAGE: $0 -m update -u userid [-a acctgrpid] [-f first_name] [-l last_name] [-p pin]" unless (exists $opts{'u'});

	    my $rv = get_user_by_id($opts{'u'});
	    if($rv) {
		update_user(%opts);
		update_pin($opts{'f'}, $opts{'l'}, $opts{'a'}, $opts{'p'}, $opts{'u'}) or die sprintf("Could not update pin id %d", $opts{'u'});
	    } else {
		die 'No user found by UserId ' . $opts{'u'};
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
