* MyCA Development Notes

** Setting up xampp(php/apache)
   
   The DLL file ntwdblib.dll shipped with xampp does not work by default.  You 
   must replace it with a newer version from SQL Server for mssql to work.  The 
   version of the dll being used is 2000.80.194.0 and can be found in the 
   following locations:
   
   C:\Windows\System32
   $XAMPP_ROOT\apache\bin
   $XAMPP_ROOT\php

   Make sure magic quotes are off!!!

** MSSQL connectivity issues
   
   DO NOT USE PERSISTENT CONNECTIONS. Connections will disconnect and apache/php will try to reuse them resulting in a failed query.  
   This seemes to be caused by mssq.max_procs, attempts to disable the setting did not work.

** Other MSSQL issues
   
   PHP by default reuses db connections when an attempt is made to connect (with say mssql_connect) with the same host/user settings. 
   This will result in cakephp possibly trying to use the wrong db profile when connecting to the same db host twice but using different databases (IC and Octave on icapp1)
