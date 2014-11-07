USE [MyCA]
GO

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[myca_users_salesperson]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[myca_users_salesperson](
	[userid] [bigint] NOT NULL,
	[salespid] [bigint] NOT NULL,
 CONSTRAINT [IX_myca_users_salesperson] UNIQUE CLUSTERED 
(
	[userid] ASC,
	[salespid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[myca_users_salesperson]') AND name = N'IX_myca_users_salesperson_1')
CREATE NONCLUSTERED INDEX [IX_myca_users_salesperson_1] ON [dbo].[myca_users_salesperson] 
(
	[salespid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[myca_diff_log]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[myca_diff_log](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[entity] [varchar](50) NOT NULL,
	[object_id] [varchar](50) NOT NULL,
	[op] [int] NOT NULL,
	[userid] [int] NOT NULL,
	[ip_addr] [varchar](15) NOT NULL,
	[created] [datetime] NOT NULL,
	[diff] [text] NULL,
	[host] [varchar](50) NOT NULL,
 CONSTRAINT [PK__myca_diff_log__6FE99F9F] PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[myca_diff_log]') AND name = N'IX_ENTITY')
CREATE NONCLUSTERED INDEX [IX_ENTITY] ON [dbo].[myca_diff_log] 
(
	[entity] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[myca_diff_log]') AND name = N'IX_USERID')
CREATE NONCLUSTERED INDEX [IX_USERID] ON [dbo].[myca_diff_log] 
(
	[userid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[request_groups]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[request_groups](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[name] [varchar](250) NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[icbilltab_curmonth]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[icbilltab_curmonth](
	[billid] [int] NOT NULL,
	[confid] [int] NULL,
	[invoicenum] [nvarchar](50) NULL,
	[acctgrpid] [nvarchar](50) NULL,
	[accountid] [nvarchar](50) NULL,
	[billingcode] [nvarchar](50) NULL,
	[confname] [nvarchar](50) NULL,
	[participant_type] [nvarchar](50) NULL,
	[starttime_t] [int] NULL,
	[startdate] [nvarchar](12) NULL,
	[starttime] [nvarchar](20) NULL,
	[minutes] [int] NULL,
	[dnis] [nvarchar](75) NULL,
	[ani] [nvarchar](50) NULL,
	[calltype] [nvarchar](10) NOT NULL,
	[calltype_text] [nvarchar](50) NULL,
	[confstart_t] [int] NULL,
	[confstartdate] [nvarchar](12) NULL,
	[confstarttime] [nvarchar](20) NULL,
	[confminutes] [int] NULL,
	[conflegs] [int] NULL,
	[ppm] [nvarchar](50) NULL,
	[callcost] [decimal](18, 2) NULL,
	[confcost] [decimal](18, 2) NULL,
	[suppress] [int] NULL,
	[rppm] [decimal](18, 2) NULL,
	[rcallcost] [decimal](18, 2) NULL,
	[rconfcost] [decimal](18, 2) NULL,
	[auxdata1] [nvarchar](63) NULL,
	[auxdata2] [nvarchar](63) NULL,
	[auxdata3] [nvarchar](63) NULL,
	[auxdata4] [nvarchar](63) NULL,
	[ldval] [int] NULL,
	[sysname] [nvarchar](22) NULL,
	[username] [nvarchar](50) NULL,
	[created] [datetime] NULL,
 CONSTRAINT [IX_icbilltab_curmonth] UNIQUE CLUSTERED 
(
	[billid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[icbilltab_curmonth]') AND name = N'IX_icbilltab_curmonth_1')
CREATE NONCLUSTERED INDEX [IX_icbilltab_curmonth_1] ON [dbo].[icbilltab_curmonth] 
(
	[accountid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[icbilltab_curmonth]') AND name = N'IX_icbilltab_curmonth_2')
CREATE NONCLUSTERED INDEX [IX_icbilltab_curmonth_2] ON [dbo].[icbilltab_curmonth] 
(
	[acctgrpid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[icbilltab_curmonth]') AND name = N'IX_icbilltab_curmonth_3')
CREATE NONCLUSTERED INDEX [IX_icbilltab_curmonth_3] ON [dbo].[icbilltab_curmonth] 
(
	[ani] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[icbilltab_curmonth]') AND name = N'IX_icbilltab_curmonth_4')
CREATE NONCLUSTERED INDEX [IX_icbilltab_curmonth_4] ON [dbo].[icbilltab_curmonth] 
(
	[confid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[icbilltab_curmonth]') AND name = N'IX_icbilltab_curmonth_5')
CREATE NONCLUSTERED INDEX [IX_icbilltab_curmonth_5] ON [dbo].[icbilltab_curmonth] 
(
	[billingcode] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[icbilltab_curmonth]') AND name = N'IX_icbilltab_curmonth_6')
CREATE NONCLUSTERED INDEX [IX_icbilltab_curmonth_6] ON [dbo].[icbilltab_curmonth] 
(
	[invoicenum] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[icbilltab_curmonth]') AND name = N'IX_icbilltab_curmonth_7')
CREATE NONCLUSTERED INDEX [IX_icbilltab_curmonth_7] ON [dbo].[icbilltab_curmonth] 
(
	[confstart_t] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[icbilltab_curmonth]') AND name = N'IX_icbilltab_curmonth_8')
CREATE NONCLUSTERED INDEX [IX_icbilltab_curmonth_8] ON [dbo].[icbilltab_curmonth] 
(
	[confstartdate] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[icbilltab_curmonth]') AND name = N'IX_icbilltab_curmonth_9')
CREATE NONCLUSTERED INDEX [IX_icbilltab_curmonth_9] ON [dbo].[icbilltab_curmonth] 
(
	[confstartdate] ASC,
	[acctgrpid] ASC,
	[calltype] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[myca_users]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[myca_users](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[username] [varchar](50) NULL,
	[email] [varchar](50) NOT NULL,
	[password] [varchar](50) NOT NULL,
	[name] [varchar](50) NOT NULL,
	[theme] [varchar](50) NOT NULL,
	[company_name] [varchar](100) NULL,
	[layout] [varchar](50) NOT NULL,
	[level_type] [varchar](50) NULL,
	[level_id] [varchar](50) NULL,
	[active] [bit] NOT NULL,
	[created] [datetime] NULL,
	[modified] [datetime] NULL,
	[verified] [bit] NOT NULL,
	[logins] [int] NOT NULL CONSTRAINT [D_dbo_myca_users_1]  DEFAULT ((0)),
	[verification_code] [varchar](40) NULL
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[servicerates]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[servicerates](
	[id] [int] NOT NULL,
	[name] [nvarchar](50) NULL,
	[rsvless] [int] NULL,
	[rsvlesstoll] [int] NULL,
	[meetmetollfree] [int] NULL,
	[meetmetoll] [int] NULL,
	[operdialout] [int] NULL,
	[operodakhi] [int] NULL,
	[operodint] [int] NULL,
	[eventmeetme] [int] NULL,
	[eventmeetmetoll] [int] NULL,
	[eventdialout] [int] NULL,
	[participationreport] [int] NULL,
	[audiostreaming] [int] NULL,
	[digitalreplay] [int] NULL,
	[digreplayparticrpt] [int] NULL,
	[tapecd] [int] NULL,
	[duplicatetaping] [int] NULL,
	[callnotification] [int] NULL,
	[rsvpsetup] [int] NULL,
	[rsvp] [int] NULL,
	[transcription] [int] NULL,
	[faxemailbcast] [int] NULL,
	[webinterpointppm] [int] NULL,
	[eslides] [int] NULL,
	[dtmfrec] [int] NULL,
	[altplatform] [int] NULL,
	[sysname] [nvarchar](22) NULL,
 CONSTRAINT [IX_servicerates] UNIQUE CLUSTERED 
(
	[id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[luacctstat]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[luacctstat](
	[acctstat] [int] NOT NULL,
	[description] [varchar](10) NULL
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[ldval2countryname]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[ldval2countryname](
	[ldval] [int] NULL,
	[countryname] [nvarchar](50) NULL
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[lubridge]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[lubridge](
	[id] [bigint] NOT NULL,
	[name] [varchar](20) NOT NULL,
	[ip] [varchar](15) NULL,
	[url] [varchar](100) NULL
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[luinvdelmeth]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[luinvdelmeth](
	[invdelmeth] [int] NOT NULL,
	[description] [varchar](15) NULL
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[luInvoiceType]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[luInvoiceType](
	[invoicetype] [int] NOT NULL,
	[description] [nchar](30) NULL
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[luTerms]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[luTerms](
	[terms] [nvarchar](35) NOT NULL
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[default_bridge]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[default_bridge](
	[acctgrpid] [varchar](50) NOT NULL,
	[bridge_id] [int] NOT NULL DEFAULT ((0)),
PRIMARY KEY CLUSTERED 
(
	[acctgrpid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[services]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[services](
	[id] [int] NOT NULL,
	[user_id] [int] NOT NULL DEFAULT ((0)),
	[name] [varchar](50) NOT NULL,
	[username] [varchar](50) NOT NULL,
	[password] [varchar](50) NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[rates]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[rates](
	[rateid] [int] NOT NULL,
	[countrycode] [int] NULL,
	[npa] [int] NULL,
	[carrierrate] [int] NULL,
	[clientrate] [int] NULL,
	[countryname] [nvarchar](50) NULL,
	[description] [nvarchar](50) NULL
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[myca_users_resellers]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[myca_users_resellers](
	[userid] [bigint] NOT NULL,
	[resellerid] [bigint] NOT NULL,
 CONSTRAINT [IX_myca_users_resellers] UNIQUE CLUSTERED 
(
	[userid] ASC,
	[resellerid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[myca_users_resellers]') AND name = N'IX_myca_users_resellers_1')
CREATE NONCLUSTERED INDEX [IX_myca_users_resellers_1] ON [dbo].[myca_users_resellers] 
(
	[resellerid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[reseller]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[reseller](
	[resellerid] [int] NOT NULL,
	[name] [nvarchar](50) NULL,
	[contact] [nvarchar](50) NULL,
	[raddress1] [nvarchar](50) NULL,
	[raddress2] [nvarchar](50) NULL,
	[rcity] [nvarchar](50) NULL,
	[rstate] [nvarchar](50) NULL,
	[rzip] [nvarchar](50) NULL,
	[rphone] [nvarchar](50) NULL,
	[rfax] [nvarchar](50) NULL,
	[remail] [nvarchar](80) NULL,
	[servicerateid] [int] NULL,
	[billingincr] [int] NULL,
	[rdesc] [nvarchar](50) NULL,
	[racctprefix] [nvarchar](6) NULL,
	[rcontact] [nvarchar](50) NULL,
	[rateid] [int] NULL,
	[agent] [bit] NULL,
	[discountid] [int] NULL,
	[reporttype] [nvarchar](10) NULL,
	[secondaryservicerate] [int] NULL,
	[directory] [nvarchar](50) NULL,
	[emailusagerpts] [bit] NULL,
	[usagerptfrom] [varchar](100) NULL,
	[logo] [varchar](100) NULL,
	[logoref] [varchar](100) NULL,
	[logowidth] [int] NULL,
	[logoheight] [int] NULL,
	[customcolor] [varchar](6) NULL,
	[tstamp] [varchar](50) NULL,
	[invoicetype] [varchar](10) NULL,
	[ecpWebHostURL] [varchar](100) NULL,
	[agidgen] [bit] NULL,
	[agidlen] [smallint] NULL,
	[agidlast] [int] NULL,
	[default_rateid] [int] NULL,
	[ecpCustomFtr] [varchar](100) NULL,
	[opsEmail] [varchar](100) NULL,
	[uifn] [int] NULL,
	[rateprefix] [varchar](4) NULL,
 CONSTRAINT [PK_reseller] PRIMARY KEY CLUSTERED 
(
	[resellerid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.views WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_daily_view]'))
EXEC dbo.sp_executesql @statement = N'CREATE VIEW [dbo].[icsummary_daily_view]
AS
SELECT     TOP 100 PERCENT salesperson.resellerid, salesperson.salespid, icsummary_daily.[date] AS date, COALESCE (accountgroup.bcompany, ''--noname--'') AS company, 
                      icsummary_daily.acctgrpid AS acctgrpid, COALESCE (icsummary_daily.rl_confcount, 0) AS rl_count, COALESCE (icsummary_daily.rl_minutes, 0) 
                      AS rl_minutes, CASE WHEN salesperson.resellerid = 1 THEN COALESCE (icsummary_daily.rl_cost, 0) ELSE COALESCE (icsummary_daily.rl_rcost, 0) 
                      END AS rl_cost, COALESCE (icsummary_daily.oa_confcount, 0) AS oa_count, COALESCE (icsummary_daily.oa_minutes, 0) AS oa_minutes, 
                      CASE WHEN salesperson.resellerid = 1 THEN COALESCE (icsummary_daily.oa_cost, 0) ELSE COALESCE (icsummary_daily.oa_rcost, 0) 
                      END AS oa_cost, COALESCE (icsummary_daily.wb_confcount, 0) AS wb_count, COALESCE (icsummary_daily.wb_minutes, 0) AS wb_minutes, 
                      CASE WHEN salesperson.resellerid = 1 THEN COALESCE (icsummary_daily.wb_cost, 0) ELSE COALESCE (icsummary_daily.wb_rcost, 0) 
                      END AS wb_cost, COALESCE (icsummary_daily.rl_confcount, 0) + COALESCE (icsummary_daily.oa_confcount, 0) 
                      + COALESCE (icsummary_daily.wb_confcount, 0) AS total_count, COALESCE (icsummary_daily.rl_minutes, 0) 
                      + COALESCE (icsummary_daily.oa_minutes, 0) + COALESCE (icsummary_daily.wb_minutes, 0) AS total_minutes, 
                      CASE WHEN salesperson.resellerid = 1 THEN COALESCE (icsummary_daily.oa_cost, 0) + COALESCE (icsummary_daily.oa_cost, 0) 
                      + COALESCE (icsummary_daily.wb_cost, 0) ELSE COALESCE (icsummary_daily.oa_rcost, 0) + COALESCE (icsummary_daily.oa_rcost, 0) 
                      + COALESCE (icsummary_daily.wb_rcost, 0) END AS total_cost, COALESCE (icsummary_daily.enhanced_count, 0) AS enhanced_count, 
                      COALESCE (icsummary_daily.enhanced_cost, 0) AS enhanced_cost, 
                      CASE WHEN salesperson.resellerid = 1 THEN COALESCE (icsummary_daily.rl_cost, 0) + COALESCE (icsummary_daily.oa_cost, 0) 
                      + COALESCE (icsummary_daily.wb_cost, 0) + COALESCE (icsummary_daily.enhanced_cost, 0) ELSE COALESCE (icsummary_daily.rl_rcost, 0) 
                      + COALESCE (icsummary_daily.oa_rcost, 0) + COALESCE (icsummary_daily.wb_rcost, 0) + COALESCE (icsummary_daily.enhanced_cost, 0) 
                      END AS grandtotal_cost
FROM         salesperson INNER JOIN
                      accountgroup ON salesperson.salespid = accountgroup.salespid INNER JOIN
                      icsummary_daily ON accountgroup.acctgrpid = icsummary_daily.acctgrpid
ORDER BY company' 
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[salesperson]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[salesperson](
	[salespid] [int] NOT NULL,
	[resellerid] [int] NULL,
	[name] [nvarchar](50) NULL,
	[address1] [nvarchar](50) NULL,
	[address2] [nvarchar](50) NULL,
	[city] [nvarchar](50) NULL,
	[state] [nvarchar](50) NULL,
	[zip] [nvarchar](50) NULL,
	[phone] [nvarchar](50) NULL,
	[fax] [nvarchar](50) NULL,
	[email] [nvarchar](50) NULL,
	[rpttype] [int] NULL,
	[notes] [nvarchar](50) NULL,
	[tstamp] [binary](8) NULL,
 CONSTRAINT [PK_salesperson] PRIMARY KEY CLUSTERED 
(
	[salespid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[salesperson]') AND name = N'IX_resellerid')
CREATE NONCLUSTERED INDEX [IX_resellerid] ON [dbo].[salesperson] 
(
	[resellerid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[accountgroup]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[accountgroup](
	[acctgrpid] [nvarchar](50) NOT NULL,
	[salespid] [int] NOT NULL,
	[bcontact] [nvarchar](50) NULL,
	[bcompany] [nvarchar](50) NULL,
	[baddr1] [nvarchar](50) NULL,
	[baddr2] [nvarchar](50) NULL,
	[city] [nvarchar](50) NULL,
	[state] [nvarchar](50) NULL,
	[zip] [nvarchar](50) NULL,
	[phone] [nvarchar](50) NULL,
	[fax] [nvarchar](50) NULL,
	[email] [nvarchar](80) NULL,
	[invoicetype] [int] NULL,
	[billcc] [bit] NULL,
	[optdesc1] [nvarchar](15) NULL,
	[optval1] [nvarchar](25) NULL,
	[terms] [nvarchar](35) NULL,
	[suppressmins] [int] NULL,
	[creditcard] [nvarchar](24) NULL,
	[ccexpire] [nvarchar](10) NULL,
	[cctype] [nvarchar](24) NULL,
	[invdelmeth] [int] NULL,
	[discountid] [int] NULL,
	[taxexempt] [bit] NULL,
	[name2billcode] [bit] NULL,
	[tstamp] [binary](8) NULL,
	[acctstat] [int] NULL,
	[acctstatdate] [datetime] NULL,
	[default_rateid] [int] NULL,
	[corpcontact] [nvarchar](50) NULL,
	[corpphone] [nvarchar](50) NULL,
	[corpemail] [nvarchar](80) NULL,
	[ccholdername] [nvarchar](50) NULL,
	[ccholderstreet] [nvarchar](50) NULL,
	[ccholderzip] [nvarchar](10) NULL,
	[cvv2] [nvarchar](4) NULL,
	[default_servicerate] [int] NULL,
	[default_canada] [int] NULL,
	[dialinNoid] [bigint] NULL,
	[auth_udbclabel] [nvarchar](50) NULL,
	[auth_urlbrand] [nvarchar](120) NULL,
	[sicCode] [varchar](4) NULL,
	[cosizeid] [int] NULL,
	[default_uifn] [int] NULL,
 CONSTRAINT [PK_accountgroup] PRIMARY KEY CLUSTERED 
(
	[acctgrpid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[accountgroup]') AND name = N'salespid')
CREATE NONCLUSTERED INDEX [salespid] ON [dbo].[accountgroup] 
(
	[salespid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[account]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[account](
	[accountid] [nvarchar](50) NOT NULL,
	[acctgrpid] [nvarchar](50) NULL,
	[cec] [nvarchar](16) NULL,
	[pec] [nvarchar](16) NULL,
	[contact] [nvarchar](50) NULL,
	[company] [nvarchar](50) NULL,
	[addr1] [nvarchar](50) NULL,
	[addr2] [nvarchar](50) NULL,
	[city] [nvarchar](50) NULL,
	[state] [nvarchar](50) NULL,
	[zip] [nvarchar](50) NULL,
	[phone] [nvarchar](50) NULL,
	[fax] [nvarchar](50) NULL,
	[email] [nvarchar](200) NULL,
	[isopassist] [bit] NULL,
	[billingcode] [nvarchar](20) NULL,
	[rateid] [int] NULL,
	[servicerate] [int] NULL,
	[ratemultiplier] [int] NULL,
	[webinterpoint] [bit] NULL,
	[webconfvisppm] [int] NULL,
	[discountid] [int] NULL,
	[canada] [int] NULL,
	[emailrpt] [bit] NULL,
	[isevent] [bit] NULL,
	[prec] [nvarchar](16) NULL,
	[maximumconnections] [int] NULL,
	[scheduletype] [int] NULL,
	[securitytype] [int] NULL,
	[lang] [int] NULL,
	[startmode] [int] NULL,
	[namerecording] [bit] NULL,
	[endonchairhangup] [bit] NULL,
	[dialout] [bit] NULL,
	[record_playback] [bit] NULL,
	[entryannouncement] [int] NULL,
	[exitannouncement] [int] NULL,
	[endingsignal] [int] NULL,
	[dtmfsignal] [int] NULL,
	[recordingsignal] [int] NULL,
	[digitentry1] [int] NULL,
	[confirmdigitentry1] [bit] NULL,
	[digitentry2] [int] NULL,
	[confirmdigitentry2] [bit] NULL,
	[muteallduringplayback] [bit] NULL,
	[reservationcomments] [nvarchar](128) NULL,
	[note1] [nvarchar](63) NULL,
	[note2] [nvarchar](63) NULL,
	[note3] [nvarchar](63) NULL,
	[note4] [nvarchar](63) NULL,
	[roomstat] [int] NULL,
	[roomstatdate] [datetime] NULL,
	[tstamp] [binary](8) NULL,
	[uifn] [int] NULL,
	[bridgeid] [bigint] NULL,
	[deptid] [bigint] NULL,
	[dialinNoid] [bigint] NULL DEFAULT ((107)),
 CONSTRAINT [PK_account] PRIMARY KEY CLUSTERED 
(
	[accountid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[account]') AND name = N'IX_acctgrpid')
CREATE NONCLUSTERED INDEX [IX_acctgrpid] ON [dbo].[account] 
(
	[acctgrpid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[account]') AND name = N'IX_cec')
CREATE NONCLUSTERED INDEX [IX_cec] ON [dbo].[account] 
(
	[cec] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[account]') AND name = N'IX_contact')
CREATE NONCLUSTERED INDEX [IX_contact] ON [dbo].[account] 
(
	[contact] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[account]') AND name = N'IX_pec')
CREATE NONCLUSTERED INDEX [IX_pec] ON [dbo].[account] 
(
	[pec] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[account]') AND name = N'IX_roomstat')
CREATE NONCLUSTERED INDEX [IX_roomstat] ON [dbo].[account] 
(
	[roomstat] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[account]') AND name = N'IX_roomstatdate')
CREATE NONCLUSTERED INDEX [IX_roomstatdate] ON [dbo].[account] 
(
	[roomstatdate] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.views WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_daily_total_mtd]'))
EXEC dbo.sp_executesql @statement = N'CREATE VIEW [dbo].[icsummary_daily_total_mtd]
AS
SELECT     TOP 100 PERCENT dbo.icsummary_daily_mtd.[date], dbo.icsummary_daily_mtd.rl_count + dbo.icsummary_daily_reseller_mtd.rl_count AS rl_count, 
                      dbo.icsummary_daily_mtd.rl_minutes + dbo.icsummary_daily_reseller_mtd.rl_minutes AS rl_minutes, 
                      dbo.icsummary_daily_mtd.rl_cost + dbo.icsummary_daily_reseller_mtd.rl_cost AS rl_cost, 
                      dbo.icsummary_daily_mtd.oa_count + dbo.icsummary_daily_reseller_mtd.oa_count AS oa_count, 
                      dbo.icsummary_daily_mtd.oa_minutes + dbo.icsummary_daily_reseller_mtd.oa_minutes AS oa_minutes, 
                      dbo.icsummary_daily_mtd.oa_cost + dbo.icsummary_daily_reseller_mtd.oa_cost AS oa_cost, 
                      dbo.icsummary_daily_mtd.wb_count + dbo.icsummary_daily_reseller_mtd.wb_count AS wb_count, 
                      dbo.icsummary_daily_mtd.wb_minutes + dbo.icsummary_daily_reseller_mtd.wb_minutes AS wb_minutes, 
                      dbo.icsummary_daily_mtd.wb_cost + dbo.icsummary_daily_reseller_mtd.wb_cost AS wb_cost, 
                      dbo.icsummary_daily_mtd.rl_count + dbo.icsummary_daily_reseller_mtd.rl_count + dbo.icsummary_daily_mtd.oa_count + dbo.icsummary_daily_reseller_mtd.oa_count
                       + dbo.icsummary_daily_mtd.wb_count + dbo.icsummary_daily_reseller_mtd.wb_count AS total_count, 
                      dbo.icsummary_daily_mtd.rl_minutes + dbo.icsummary_daily_reseller_mtd.rl_minutes + dbo.icsummary_daily_mtd.oa_minutes + dbo.icsummary_daily_reseller_mtd.oa_minutes
                       + dbo.icsummary_daily_mtd.wb_minutes + dbo.icsummary_daily_reseller_mtd.wb_minutes AS total_minutes, 
                      dbo.icsummary_daily_mtd.rl_cost + dbo.icsummary_daily_reseller_mtd.rl_cost + dbo.icsummary_daily_mtd.oa_cost + dbo.icsummary_daily_reseller_mtd.oa_cost
                       + dbo.icsummary_daily_mtd.wb_cost + dbo.icsummary_daily_reseller_mtd.wb_cost AS total_cost, 
                      dbo.icsummary_daily_mtd.enhanced_count + dbo.icsummary_daily_reseller_mtd.enhanced_count AS enhanced_count, 
                      dbo.icsummary_daily_mtd.enhanced_cost + dbo.icsummary_daily_reseller_mtd.enhanced_cost AS enhanced_cost, 
                      dbo.icsummary_daily_mtd.rl_cost + dbo.icsummary_daily_reseller_mtd.rl_cost + dbo.icsummary_daily_mtd.oa_cost + dbo.icsummary_daily_reseller_mtd.oa_cost
                       + dbo.icsummary_daily_mtd.wb_cost + dbo.icsummary_daily_reseller_mtd.wb_cost + dbo.icsummary_daily_mtd.enhanced_cost AS grandtotal_cost
FROM         dbo.icsummary_daily_mtd INNER JOIN
                      dbo.icsummary_daily_reseller_mtd ON dbo.icsummary_daily_mtd.[date] = dbo.icsummary_daily_reseller_mtd.[date]
WHERE     (dbo.icsummary_daily_mtd.resellerid = 1)
ORDER BY dbo.icsummary_daily_mtd.[date]' 
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.views WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_daily_total_ytd]'))
EXEC dbo.sp_executesql @statement = N'CREATE VIEW [dbo].[icsummary_daily_total_ytd]
AS
SELECT     TOP 100 PERCENT dbo.icsummary_daily_ytd.[date], dbo.icsummary_daily_ytd.rl_count + dbo.icsummary_daily_reseller_ytd.rl_count AS rl_count,
                       dbo.icsummary_daily_ytd.rl_minutes + dbo.icsummary_daily_reseller_ytd.rl_minutes AS rl_minutes, 
                      dbo.icsummary_daily_ytd.rl_cost + dbo.icsummary_daily_reseller_ytd.rl_cost AS rl_cost, 
                      dbo.icsummary_daily_ytd.oa_count + dbo.icsummary_daily_reseller_ytd.oa_count AS oa_count, 
                      dbo.icsummary_daily_ytd.oa_minutes + dbo.icsummary_daily_reseller_ytd.oa_minutes AS oa_minutes, 
                      dbo.icsummary_daily_ytd.oa_cost + dbo.icsummary_daily_reseller_ytd.oa_cost AS oa_cost, 
                      dbo.icsummary_daily_ytd.wb_count + dbo.icsummary_daily_reseller_ytd.wb_count AS wb_count, 
                      dbo.icsummary_daily_ytd.wb_minutes + dbo.icsummary_daily_reseller_ytd.wb_minutes AS wb_minutes, 
                      dbo.icsummary_daily_ytd.wb_cost + dbo.icsummary_daily_reseller_ytd.wb_cost AS wb_cost, 
                      dbo.icsummary_daily_ytd.rl_count + dbo.icsummary_daily_reseller_ytd.rl_count + dbo.icsummary_daily_ytd.oa_count + dbo.icsummary_daily_reseller_ytd.oa_count
                       + dbo.icsummary_daily_ytd.wb_count + dbo.icsummary_daily_reseller_ytd.wb_count AS total_count, 
                      dbo.icsummary_daily_ytd.rl_minutes + dbo.icsummary_daily_reseller_ytd.rl_minutes + dbo.icsummary_daily_ytd.oa_minutes + dbo.icsummary_daily_reseller_ytd.oa_minutes
                       + dbo.icsummary_daily_ytd.wb_minutes + dbo.icsummary_daily_reseller_ytd.wb_minutes AS total_minutes, 
                      dbo.icsummary_daily_ytd.rl_cost + dbo.icsummary_daily_reseller_ytd.rl_cost + dbo.icsummary_daily_ytd.oa_cost + dbo.icsummary_daily_reseller_ytd.oa_cost
                       + dbo.icsummary_daily_ytd.wb_cost + dbo.icsummary_daily_reseller_ytd.wb_cost AS total_cost, 
                      dbo.icsummary_daily_ytd.enhanced_count + dbo.icsummary_daily_reseller_ytd.enhanced_count AS enhanced_count, 
                      dbo.icsummary_daily_ytd.enhanced_cost + dbo.icsummary_daily_reseller_ytd.enhanced_cost AS enhanced_cost, 
                      dbo.icsummary_daily_ytd.rl_cost + dbo.icsummary_daily_reseller_ytd.rl_cost + dbo.icsummary_daily_ytd.oa_cost + dbo.icsummary_daily_reseller_ytd.oa_cost
                       + dbo.icsummary_daily_ytd.wb_cost + dbo.icsummary_daily_reseller_ytd.wb_cost + dbo.icsummary_daily_ytd.enhanced_cost AS grandtotal_cost
FROM         dbo.icsummary_daily_ytd INNER JOIN
                      dbo.icsummary_daily_reseller_ytd ON dbo.icsummary_daily_ytd.[date] = dbo.icsummary_daily_reseller_ytd.[date]
WHERE dbo.icsummary_daily_ytd.resellerid = 1
ORDER BY dbo.icsummary_daily_ytd.[date]' 
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_yearly]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[icsummary_yearly](
	[date] [datetime] NOT NULL,
	[acctgrpid] [varchar](50) NOT NULL,
	[resellerid] [int] NOT NULL CONSTRAINT [DF_icsummary_yearly_resellerid]  DEFAULT ((0)),
	[rl_confcount] [int] NOT NULL,
	[rl_minutes] [int] NOT NULL,
	[rl_cost] [decimal](18, 2) NOT NULL,
	[rl_rcost] [decimal](18, 2) NOT NULL,
	[oa_confcount] [int] NOT NULL,
	[oa_minutes] [int] NOT NULL,
	[oa_cost] [decimal](18, 2) NOT NULL,
	[oa_rcost] [decimal](18, 2) NOT NULL,
	[wb_confcount] [int] NOT NULL,
	[wb_minutes] [int] NOT NULL,
	[wb_cost] [decimal](18, 2) NOT NULL,
	[wb_rcost] [decimal](18, 2) NOT NULL,
	[enhanced_count] [int] NOT NULL,
	[enhanced_cost] [decimal](18, 2) NOT NULL,
	[enhanced_rcost] [decimal](18, 2) NOT NULL,
 CONSTRAINT [PK_icsummary_yearly] PRIMARY KEY CLUSTERED 
(
	[date] ASC,
	[acctgrpid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[Results]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[Results](
	[date] [datetime] NOT NULL,
	[acctgrpid] [varchar](50) NOT NULL,
	[resellerid] [int] NULL,
	[rl_confcount] [int] NOT NULL,
	[rl_minutes] [int] NOT NULL,
	[rl_cost] [decimal](18, 2) NOT NULL,
	[rl_rcost] [decimal](18, 2) NOT NULL,
	[oa_confcount] [int] NOT NULL,
	[oa_minutes] [int] NOT NULL,
	[oa_cost] [decimal](18, 2) NOT NULL,
	[oa_rcost] [decimal](18, 2) NOT NULL,
	[wb_confcount] [int] NOT NULL,
	[wb_minutes] [int] NOT NULL,
	[wb_cost] [decimal](18, 2) NOT NULL,
	[wb_rcost] [decimal](18, 2) NOT NULL,
	[enhanced_count] [int] NOT NULL,
	[enhanced_cost] [decimal](18, 2) NOT NULL,
	[enhanced_rcost] [decimal](18, 2) NOT NULL
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[confirmtranslate]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[confirmtranslate](
	[f_confirmationnumber] [nvarchar](50) NOT NULL,
	[t_confirmationnumber] [nvarchar](50) NULL,
	[tstamp] [binary](8) NULL
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[pci]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[pci](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[acctgrpid] [nvarchar](50) NOT NULL,
	[active] [int] NOT NULL CONSTRAINT [DF__pci__active__59C55456]  DEFAULT ((1)),
	[created] [datetime] NULL,
	[deactivated] [datetime] NULL,
	[ccholdername] [nvarchar](50) NULL,
	[ccholderstreet] [nvarchar](50) NULL,
	[ccholderzip] [nvarchar](10) NULL,
	[creditcard] [varbinary](8000) NULL,
	[cclastfour] [nvarchar](4) NULL,
	[cctype] [nvarchar](24) NULL,
	[ccexpire] [nvarchar](10) NULL
) ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[pci]') AND name = N'IX_acctgrpid')
CREATE NONCLUSTERED INDEX [IX_acctgrpid] ON [dbo].[pci] 
(
	[acctgrpid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[welcome_email_log]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[welcome_email_log](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[creator] [int] NULL DEFAULT ((0)),
	[sent] [datetime] NULL,
	[templateid] [int] NULL DEFAULT ((0)),
	[accountid] [nvarchar](50) NULL,
	[from] [nvarchar](100) NULL,
	[to] [ntext] NULL,
	[bcc] [text] NULL,
	[acctgrpid] [nvarchar](50) NULL,
PRIMARY KEY CLUSTERED 
(
	[id] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[welcome_email_log]') AND name = N'IX_ACCOUNTID')
CREATE NONCLUSTERED INDEX [IX_ACCOUNTID] ON [dbo].[welcome_email_log] 
(
	[accountid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[welcome_email_log]') AND name = N'IX_ACCTGRPID')
CREATE NONCLUSTERED INDEX [IX_ACCTGRPID] ON [dbo].[welcome_email_log] 
(
	[acctgrpid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[welcome_email_log]') AND name = N'IX_CREATOR')
CREATE NONCLUSTERED INDEX [IX_CREATOR] ON [dbo].[welcome_email_log] 
(
	[creator] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[backend_log]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[backend_log](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[message] [text] NULL,
	[created] [datetime] NOT NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[reseller_groups]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[reseller_groups](
	[user_id] [int] NOT NULL,
	[name] [varchar](50) NOT NULL
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[salespeople_to_salesperson_groups]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[salespeople_to_salesperson_groups](
	[user_id] [int] NOT NULL,
	[salesperson_id] [int] NOT NULL
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[reseller_groups_to_resellers]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[reseller_groups_to_resellers](
	[user_id] [int] NOT NULL,
	[reseller_id] [int] NOT NULL
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[salesperson_groups]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[salesperson_groups](
	[user_id] [int] NOT NULL,
	[name] [varchar](50) NOT NULL
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[myca_log]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[myca_log](
	[id] [bigint] IDENTITY(1,1) NOT NULL,
	[created] [datetime] NOT NULL,
	[controller] [varchar](50) NULL,
	[action] [varchar](50) NULL,
	[pass] [varchar](50) NULL,
	[url] [varchar](250) NULL,
	[userid] [bigint] NULL,
	[comments] [varchar](250) NULL,
	[ip_addr] [varchar](15) NULL,
	[category] [varchar](250) NULL,
	[method] [varchar](6) NULL,
	[host] [varchar](50) NULL
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[request_groups_users]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[request_groups_users](
	[request_group_id] [int] NULL,
	[user_id] [int] NULL
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[requests_data]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[requests_data](
	[requests_id] [int] NOT NULL,
	[field] [varchar](50) NOT NULL,
	[value] [varchar](100) NULL
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[requests_request_groups]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[requests_request_groups](
	[type] [int] NULL,
	[status] [int] NULL,
	[request_group_id] [int] NULL
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[requests_types]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[requests_types](
	[id] [int] NOT NULL,
	[name] [varchar](50) NOT NULL,
	[description] [varchar](50) NULL
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[requests_status]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[requests_status](
	[id] [int] NOT NULL,
	[name] [varchar](50) NOT NULL,
	[description] [varchar](50) NULL
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[requests]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[requests](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[type] [int] NOT NULL,
	[creator] [int] NOT NULL,
	[created] [datetime] NOT NULL,
	[modified] [datetime] NOT NULL,
	[acctgrpid] [varchar](50) NULL,
	[accountid] [varchar](50) NULL,
	[status] [int] NOT NULL CONSTRAINT [D_dbo_requests_1]  DEFAULT ((0)),
	[notes] [text] NULL,
	[log] [text] NULL,
	[signed_off_by] [int] NULL,
	[comments] [text] NULL
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_monthly]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[icsummary_monthly](
	[date] [datetime] NOT NULL,
	[acctgrpid] [varchar](50) NOT NULL,
	[resellerid] [int] NOT NULL CONSTRAINT [DF_icsummary_monthly_resellerid]  DEFAULT ((0)),
	[rl_confcount] [int] NOT NULL,
	[rl_minutes] [int] NOT NULL,
	[rl_cost] [decimal](18, 2) NOT NULL,
	[rl_rcost] [decimal](18, 2) NOT NULL,
	[oa_confcount] [int] NOT NULL,
	[oa_minutes] [int] NOT NULL,
	[oa_cost] [decimal](18, 2) NOT NULL,
	[oa_rcost] [decimal](18, 2) NOT NULL,
	[wb_confcount] [int] NOT NULL,
	[wb_minutes] [int] NOT NULL,
	[wb_cost] [decimal](18, 2) NOT NULL,
	[wb_rcost] [decimal](18, 2) NOT NULL,
	[enhanced_count] [int] NOT NULL,
	[enhanced_cost] [decimal](18, 2) NOT NULL,
	[enhanced_rcost] [decimal](18, 2) NOT NULL,
 CONSTRAINT [PK_icsummary_monthly] PRIMARY KEY CLUSTERED 
(
	[date] ASC,
	[acctgrpid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
END
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_daily]') AND type in (N'U'))
BEGIN
CREATE TABLE [dbo].[icsummary_daily](
	[date] [datetime] NOT NULL,
	[acctgrpid] [varchar](50) NOT NULL,
	[resellerid] [int] NOT NULL CONSTRAINT [DF_icsummary_daily_resellerid]  DEFAULT ((0)),
	[rl_confcount] [int] NOT NULL CONSTRAINT [DF_icsummary_daily_rl_confcount]  DEFAULT ((0)),
	[rl_minutes] [int] NOT NULL CONSTRAINT [DF_icsummary_daily_rl_minutes]  DEFAULT ((0)),
	[rl_cost] [decimal](18, 2) NOT NULL CONSTRAINT [DF_icsummary_daily_rl_cost]  DEFAULT ((0)),
	[rl_rcost] [decimal](18, 2) NOT NULL CONSTRAINT [DF_icsummary_daily_rl_rcost]  DEFAULT ((0)),
	[oa_confcount] [int] NOT NULL CONSTRAINT [DF_icsummary_daily_oa_confcount]  DEFAULT ((0)),
	[oa_minutes] [int] NOT NULL CONSTRAINT [DF_icsummary_daily_oa_minutes]  DEFAULT ((0)),
	[oa_cost] [decimal](18, 2) NOT NULL CONSTRAINT [DF_icsummary_daily_oa_cost]  DEFAULT ((0)),
	[oa_rcost] [decimal](18, 2) NOT NULL CONSTRAINT [DF_icsummary_daily_oa_rcost]  DEFAULT ((0)),
	[wb_confcount] [int] NOT NULL CONSTRAINT [DF_icsummary_daily_wb_confcount]  DEFAULT ((0)),
	[wb_minutes] [int] NOT NULL CONSTRAINT [DF_icsummary_daily_wb_minutes]  DEFAULT ((0)),
	[wb_cost] [decimal](18, 2) NOT NULL CONSTRAINT [DF_icsummary_daily_wb_cost]  DEFAULT ((0)),
	[wb_rcost] [decimal](18, 2) NOT NULL CONSTRAINT [DF_icsummary_daily_wb_rcost]  DEFAULT ((0)),
	[enhanced_count] [int] NOT NULL CONSTRAINT [DF_icsummary_daily_enhanced_count]  DEFAULT ((0)),
	[enhanced_cost] [decimal](18, 2) NOT NULL CONSTRAINT [DF_icsummary_daily_enhanced_cost]  DEFAULT ((0)),
	[enhanced_rcost] [decimal](18, 2) NOT NULL CONSTRAINT [DF_icsummary_daily_enhanced_rcost]  DEFAULT ((0)),
 CONSTRAINT [PK_ic_dailysummary] PRIMARY KEY CLUSTERED 
(
	[date] ASC,
	[acctgrpid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
END
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_daily]') AND name = N'IC_resellerid')
CREATE NONCLUSTERED INDEX [IC_resellerid] ON [dbo].[icsummary_daily] 
(
	[resellerid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_daily]') AND name = N'IX_acctgrpid')
CREATE NONCLUSTERED INDEX [IX_acctgrpid] ON [dbo].[icsummary_daily] 
(
	[acctgrpid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_daily]') AND name = N'IX_date-resellerid-acctgrpid')
CREATE NONCLUSTERED INDEX [IX_date-resellerid-acctgrpid] ON [dbo].[icsummary_daily] 
(
	[date] ASC,
	[resellerid] ASC,
	[acctgrpid] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO

IF NOT EXISTS (SELECT * FROM sys.indexes WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_daily]') AND name = N'IX_resellerid-date')
CREATE NONCLUSTERED INDEX [IX_resellerid-date] ON [dbo].[icsummary_daily] 
(
	[resellerid] ASC,
	[date] ASC
)WITH (IGNORE_DUP_KEY = OFF) ON [PRIMARY]
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.views WHERE object_id = OBJECT_ID(N'[dbo].[icbilltab_daily]'))
EXEC dbo.sp_executesql @statement = N'CREATE VIEW [dbo].[icbilltab_daily]
AS
SELECT     TOP 100 PERCENT dbo.salesperson.resellerid, dbo.salesperson.salespid, dbo.accountgroup.bcompany AS company, dbo.icbilltab_curmonth.acctgrpid, 
                      dbo.icbilltab_curmonth.confstartdate AS [date], dbo.icbilltab_curmonth.accountid, dbo.icbilltab_curmonth.confname, dbo.icbilltab_curmonth.ppm, 
                      dbo.icbilltab_curmonth.starttime, dbo.icbilltab_curmonth.minutes, dbo.icbilltab_curmonth.dnis, dbo.icbilltab_curmonth.ani, 
                      dbo.icbilltab_curmonth.calltype_text, dbo.icbilltab_curmonth.conflegs, COALESCE (dbo.icbilltab_curmonth.callcost, 0) AS callcost, 
                      dbo.icbilltab_curmonth.sysname, dbo.icbilltab_curmonth.confid
FROM         dbo.icbilltab_curmonth INNER JOIN
                      dbo.accountgroup ON dbo.icbilltab_curmonth.acctgrpid = dbo.accountgroup.acctgrpid INNER JOIN
                      dbo.salesperson ON dbo.accountgroup.salespid = dbo.salesperson.salespid
ORDER BY dbo.icbilltab_curmonth.confstartdate' 
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.views WHERE object_id = OBJECT_ID(N'[dbo].[room_view]'))
EXEC dbo.sp_executesql @statement = N'CREATE VIEW [dbo].[room_view]
AS
SELECT     dbo.account.accountid, dbo.account.acctgrpid, dbo.account.cec, dbo.account.pec, dbo.account.contact, dbo.account.company, dbo.account.email, 
                      dbo.account.isopassist, dbo.account.billingcode, dbo.account.servicerate, dbo.account.rateid, dbo.account.canada, dbo.account.webinterpoint, 
                      dbo.account.emailrpt, dbo.account.isevent, dbo.account.maximumconnections, dbo.account.namerecording, dbo.account.endonchairhangup, 
                      dbo.account.dialout, dbo.account.roomstatdate, dbo.account.bridgeid, dbo.servicerates.name AS rate_name, dbo.servicerates.rsvless AS rate_rlf, dbo.servicerates.rsvlesstoll AS rate_rlt, 
                      dbo.servicerates.operdialout AS rate_oa, dbo.servicerates.eventmeetme AS rate_event, dbo.servicerates.webinterpointppm AS rate_web, 
                      dbo.luacctstat.description AS roomstat
FROM         dbo.account INNER JOIN
                      dbo.servicerates ON dbo.account.servicerate = dbo.servicerates.id INNER JOIN
                      dbo.luacctstat ON dbo.account.roomstat = dbo.luacctstat.acctstat' 
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.views WHERE object_id = OBJECT_ID(N'[dbo].[accountgroup_view]'))
EXEC dbo.sp_executesql @statement = N'CREATE VIEW [dbo].[accountgroup_view]
AS
SELECT     dbo.accountgroup.acctgrpid AS acctgrpid, dbo.accountgroup.bcompany AS company, 
                      dbo.accountgroup.bcontact AS contact, dbo.accountgroup.baddr1 AS addr1, 
                      dbo.accountgroup.baddr2 AS addr2, dbo.accountgroup.city AS city, dbo.accountgroup.state AS state, dbo.accountgroup.zip AS zip, 
                      dbo.accountgroup.phone AS phone, dbo.accountgroup.fax AS fax, dbo.accountgroup.email AS email, dbo.luInvoiceType.description AS invoicetype, 
                      dbo.accountgroup.billcc AS billcc, dbo.accountgroup.terms AS terms, dbo.accountgroup.creditcard AS creditcard, 
                      dbo.accountgroup.ccexpire AS ccexpire, dbo.accountgroup.cctype AS cctype, dbo.luinvdelmeth.description AS inv_delmethod, 
                      dbo.accountgroup.taxexempt AS taxexempt, dbo.accountgroup.tstamp AS tstamp, dbo.luacctstat.description AS acctstatus, 
                      dbo.accountgroup.acctstatdate AS acctstatdate, dbo.accountgroup.default_rateid AS default_rateid, 
                      dbo.accountgroup.default_canada AS default_canada, dbo.servicerates.name AS rate_name, dbo.servicerates.rsvless AS rate_rlf, 
                      dbo.servicerates.rsvlesstoll AS rate_rlt, dbo.servicerates.operdialout AS rate_oa, dbo.servicerates.eventmeetme AS rate_event, 
                      dbo.servicerates.webinterpointppm AS rate_web, 
                      dbo.salesperson.salespid AS salespid, dbo.salesperson.name AS salesperson_name, dbo.salesperson.email AS salesperson_email, dbo.reseller.resellerid AS resellerid, dbo.reseller.name AS reseller_name
FROM         dbo.accountgroup INNER JOIN
                      dbo.salesperson ON dbo.accountgroup.salespid = dbo.salesperson.salespid INNER JOIN
                      dbo.reseller ON dbo.salesperson.resellerid = dbo.reseller.resellerid INNER JOIN
                      dbo.servicerates ON dbo.accountgroup.default_servicerate = dbo.servicerates.id INNER JOIN
                      dbo.luacctstat ON dbo.accountgroup.acctstat = dbo.luacctstat.acctstat INNER JOIN
                      dbo.luinvdelmeth ON dbo.accountgroup.invdelmeth = dbo.luinvdelmeth.invdelmeth INNER JOIN
                      dbo.luInvoiceType ON dbo.accountgroup.invoicetype = dbo.luInvoiceType.invoicetype' 
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.views WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_daily_ytd_totalResellers]'))
EXEC dbo.sp_executesql @statement = N'CREATE VIEW [dbo].[icsummary_daily_ytd_totalResellers]
AS
SELECT     TOP (100) PERCENT ics.date, COALESCE (SUM(ics.rl_confcount), 0) AS rl_count, COALESCE (SUM(ics.rl_minutes), 0) AS rl_minutes, 
                      COALESCE (SUM(ics.rl_rcost), 0) AS rl_cost, COALESCE (SUM(ics.oa_confcount), 0) AS oa_count, COALESCE (SUM(ics.oa_minutes), 0) AS oa_minutes, 
                      COALESCE (SUM(ics.oa_rcost), 0) AS oa_cost, COALESCE (SUM(ics.wb_confcount), 0) AS wb_count, COALESCE (SUM(ics.wb_minutes), 0) 
                      AS wb_minutes, COALESCE (SUM(ics.wb_rcost), 0) AS wb_cost, COALESCE (SUM(ics.rl_confcount), 0) + COALESCE (SUM(ics.oa_confcount), 0) 
                      + COALESCE (SUM(ics.wb_confcount), 0) AS total_count, COALESCE (SUM(ics.rl_minutes), 0) + COALESCE (SUM(ics.oa_minutes), 0) 
                      + COALESCE (SUM(ics.wb_minutes), 0) AS total_minutes, COALESCE (SUM(ics.rl_rcost), 0) + COALESCE (SUM(ics.oa_rcost), 0) 
                      + COALESCE (SUM(ics.wb_rcost), 0) AS total_cost, COALESCE (SUM(ics.enhanced_count), 0) AS enhanced_count, COALESCE (SUM(ics.enhanced_rcost),
                       0) AS enhanced_cost, COALESCE (SUM(ics.rl_rcost), 0) + COALESCE (SUM(ics.oa_rcost), 0) + COALESCE (SUM(ics.wb_rcost), 0) 
                      + COALESCE (SUM(ics.enhanced_rcost), 0) AS grandtotal_cost
FROM         dbo.salesperson INNER JOIN
                      dbo.accountgroup ON dbo.salesperson.salespid = dbo.accountgroup.salespid INNER JOIN
                      dbo.icsummary_monthly AS ics ON dbo.accountgroup.acctgrpid = ics.acctgrpid INNER JOIN
                      dbo.reseller ON dbo.salesperson.resellerid = dbo.reseller.resellerid
WHERE     (ics.resellerid NOT IN (1, 3)) AND (dbo.reseller.agent = 0)
GROUP BY ics.date
ORDER BY ics.date' 
GO
EXEC sys.sp_addextendedproperty @name=N'MS_DiagramPane1', @value=N'[0E232FF0-B466-11cf-A24F-00AA00A3EFFF, 1.00]
Begin DesignProperties = 
   Begin PaneConfigurations = 
      Begin PaneConfiguration = 0
         NumPanes = 4
         Configuration = "(H (1[40] 4[20] 2[20] 3) )"
      End
      Begin PaneConfiguration = 1
         NumPanes = 3
         Configuration = "(H (1 [50] 4 [25] 3))"
      End
      Begin PaneConfiguration = 2
         NumPanes = 3
         Configuration = "(H (1 [50] 2 [25] 3))"
      End
      Begin PaneConfiguration = 3
         NumPanes = 3
         Configuration = "(H (4 [30] 2 [40] 3))"
      End
      Begin PaneConfiguration = 4
         NumPanes = 2
         Configuration = "(H (1 [56] 3))"
      End
      Begin PaneConfiguration = 5
         NumPanes = 2
         Configuration = "(H (2 [66] 3))"
      End
      Begin PaneConfiguration = 6
         NumPanes = 2
         Configuration = "(H (4 [50] 3))"
      End
      Begin PaneConfiguration = 7
         NumPanes = 1
         Configuration = "(V (3))"
      End
      Begin PaneConfiguration = 8
         NumPanes = 3
         Configuration = "(H (1[56] 4[18] 2) )"
      End
      Begin PaneConfiguration = 9
         NumPanes = 2
         Configuration = "(H (1 [75] 4))"
      End
      Begin PaneConfiguration = 10
         NumPanes = 2
         Configuration = "(H (1[66] 2) )"
      End
      Begin PaneConfiguration = 11
         NumPanes = 2
         Configuration = "(H (4 [60] 2))"
      End
      Begin PaneConfiguration = 12
         NumPanes = 1
         Configuration = "(H (1) )"
      End
      Begin PaneConfiguration = 13
         NumPanes = 1
         Configuration = "(V (4))"
      End
      Begin PaneConfiguration = 14
         NumPanes = 1
         Configuration = "(V (2))"
      End
      ActivePaneConfig = 0
   End
   Begin DiagramPane = 
      Begin Origin = 
         Top = 0
         Left = 0
      End
      Begin Tables = 
         Begin Table = "salesperson"
            Begin Extent = 
               Top = 6
               Left = 38
               Bottom = 114
               Right = 205
            End
            DisplayFlags = 280
            TopColumn = 0
         End
         Begin Table = "accountgroup"
            Begin Extent = 
               Top = 6
               Left = 243
               Bottom = 114
               Right = 433
            End
            DisplayFlags = 280
            TopColumn = 0
         End
         Begin Table = "ics"
            Begin Extent = 
               Top = 114
               Left = 38
               Bottom = 222
               Right = 214
            End
            DisplayFlags = 280
            TopColumn = 0
         End
         Begin Table = "reseller"
            Begin Extent = 
               Top = 114
               Left = 252
               Bottom = 222
               Right = 452
            End
            DisplayFlags = 280
            TopColumn = 0
         End
      End
   End
   Begin SQLPane = 
   End
   Begin DataPane = 
      Begin ParameterDefaults = ""
      End
   End
   Begin CriteriaPane = 
      Begin ColumnWidths = 12
         Column = 1440
         Alias = 900
         Table = 1170
         Output = 720
         Append = 1400
         NewValue = 1170
         SortType = 1350
         SortOrder = 1410
         GroupBy = 1350
         Filter = 1350
         Or = 1350
         Or = 1350
         Or = 1350
      End
   End
End
' ,@level0type=N'SCHEMA', @level0name=N'dbo', @level1type=N'VIEW', @level1name=N'icsummary_daily_ytd_totalResellers'

GO
EXEC sys.sp_addextendedproperty @name=N'MS_DiagramPaneCount', @value=1 ,@level0type=N'SCHEMA', @level0name=N'dbo', @level1type=N'VIEW', @level1name=N'icsummary_daily_ytd_totalResellers'

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.views WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_daily_ytd_byReseller]'))
EXEC dbo.sp_executesql @statement = N'CREATE VIEW [dbo].[icsummary_daily_ytd_byReseller]
AS
SELECT     TOP (100) PERCENT ics.date, ics.resellerid, COALESCE (SUM(ics.rl_confcount), 0) AS rl_count, COALESCE (SUM(ics.rl_minutes), 0) 
                      AS rl_minutes, COALESCE (SUM(ics.rl_rcost), 0) AS rl_cost, COALESCE (SUM(ics.oa_confcount), 0) AS oa_count, COALESCE (SUM(ics.oa_minutes), 0) 
                      AS oa_minutes, COALESCE (SUM(ics.oa_rcost), 0) AS oa_cost, COALESCE (SUM(ics.wb_confcount), 0) AS wb_count, COALESCE (SUM(ics.wb_minutes), 
                      0) AS wb_minutes, COALESCE (SUM(ics.wb_rcost), 0) AS wb_cost, COALESCE (SUM(ics.rl_confcount), 0) + COALESCE (SUM(ics.oa_confcount), 0) 
                      + COALESCE (SUM(ics.wb_confcount), 0) AS total_count, COALESCE (SUM(ics.rl_minutes), 0) + COALESCE (SUM(ics.oa_minutes), 0) 
                      + COALESCE (SUM(ics.wb_minutes), 0) AS total_minutes, COALESCE (SUM(ics.rl_rcost), 0) + COALESCE (SUM(ics.oa_rcost), 0) 
                      + COALESCE (SUM(ics.wb_rcost), 0) AS total_cost, COALESCE (SUM(ics.enhanced_count), 0) AS enhanced_count, COALESCE (SUM(ics.enhanced_rcost),
                       0) AS enhanced_cost, COALESCE (SUM(ics.rl_rcost), 0) + COALESCE (SUM(ics.oa_rcost), 0) + COALESCE (SUM(ics.wb_rcost), 0) 
                      + COALESCE (SUM(ics.enhanced_rcost), 0) AS grandtotal_cost, dbo.reseller.name, dbo.reseller.rdesc
FROM         dbo.salesperson INNER JOIN
                      dbo.accountgroup ON dbo.salesperson.salespid = dbo.accountgroup.salespid INNER JOIN
                      dbo.icsummary_monthly AS ics ON dbo.accountgroup.acctgrpid = ics.acctgrpid INNER JOIN
                      dbo.reseller ON dbo.salesperson.resellerid = dbo.reseller.resellerid
WHERE     (dbo.salesperson.resellerid NOT IN (1, 3)) AND (dbo.reseller.agent = 0)
GROUP BY ics.date, ics.resellerid, dbo.reseller.name, dbo.reseller.rdesc
ORDER BY ics.date, ics.resellerid' 
GO
EXEC sys.sp_addextendedproperty @name=N'MS_DiagramPane1', @value=N'[0E232FF0-B466-11cf-A24F-00AA00A3EFFF, 1.00]
Begin DesignProperties = 
   Begin PaneConfigurations = 
      Begin PaneConfiguration = 0
         NumPanes = 4
         Configuration = "(H (1[40] 4[20] 2[20] 3) )"
      End
      Begin PaneConfiguration = 1
         NumPanes = 3
         Configuration = "(H (1 [50] 4 [25] 3))"
      End
      Begin PaneConfiguration = 2
         NumPanes = 3
         Configuration = "(H (1 [50] 2 [25] 3))"
      End
      Begin PaneConfiguration = 3
         NumPanes = 3
         Configuration = "(H (4 [30] 2 [40] 3))"
      End
      Begin PaneConfiguration = 4
         NumPanes = 2
         Configuration = "(H (1 [56] 3))"
      End
      Begin PaneConfiguration = 5
         NumPanes = 2
         Configuration = "(H (2 [66] 3))"
      End
      Begin PaneConfiguration = 6
         NumPanes = 2
         Configuration = "(H (4 [50] 3))"
      End
      Begin PaneConfiguration = 7
         NumPanes = 1
         Configuration = "(V (3))"
      End
      Begin PaneConfiguration = 8
         NumPanes = 3
         Configuration = "(H (1[56] 4[18] 2) )"
      End
      Begin PaneConfiguration = 9
         NumPanes = 2
         Configuration = "(H (1 [75] 4))"
      End
      Begin PaneConfiguration = 10
         NumPanes = 2
         Configuration = "(H (1[66] 2) )"
      End
      Begin PaneConfiguration = 11
         NumPanes = 2
         Configuration = "(H (4 [60] 2))"
      End
      Begin PaneConfiguration = 12
         NumPanes = 1
         Configuration = "(H (1) )"
      End
      Begin PaneConfiguration = 13
         NumPanes = 1
         Configuration = "(V (4))"
      End
      Begin PaneConfiguration = 14
         NumPanes = 1
         Configuration = "(V (2))"
      End
      ActivePaneConfig = 0
   End
   Begin DiagramPane = 
      Begin Origin = 
         Top = 0
         Left = 0
      End
      Begin Tables = 
         Begin Table = "salesperson"
            Begin Extent = 
               Top = 6
               Left = 38
               Bottom = 114
               Right = 205
            End
            DisplayFlags = 280
            TopColumn = 0
         End
         Begin Table = "accountgroup"
            Begin Extent = 
               Top = 6
               Left = 243
               Bottom = 114
               Right = 433
            End
            DisplayFlags = 280
            TopColumn = 0
         End
         Begin Table = "ics"
            Begin Extent = 
               Top = 114
               Left = 38
               Bottom = 222
               Right = 214
            End
            DisplayFlags = 280
            TopColumn = 0
         End
         Begin Table = "reseller"
            Begin Extent = 
               Top = 114
               Left = 252
               Bottom = 222
               Right = 452
            End
            DisplayFlags = 280
            TopColumn = 0
         End
      End
   End
   Begin SQLPane = 
   End
   Begin DataPane = 
      Begin ParameterDefaults = ""
      End
   End
   Begin CriteriaPane = 
      Begin ColumnWidths = 12
         Column = 1440
         Alias = 900
         Table = 1170
         Output = 720
         Append = 1400
         NewValue = 1170
         SortType = 1350
         SortOrder = 1410
         GroupBy = 1350
         Filter = 1350
         Or = 1350
         Or = 1350
         Or = 1350
      End
   End
End
' ,@level0type=N'SCHEMA', @level0name=N'dbo', @level1type=N'VIEW', @level1name=N'icsummary_daily_ytd_byReseller'

GO
EXEC sys.sp_addextendedproperty @name=N'MS_DiagramPaneCount', @value=1 ,@level0type=N'SCHEMA', @level0name=N'dbo', @level1type=N'VIEW', @level1name=N'icsummary_daily_ytd_byReseller'

GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.views WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_daily_ytd_bySP]'))
EXEC dbo.sp_executesql @statement = N'CREATE VIEW [dbo].[icsummary_daily_ytd_bySP]
AS
SELECT     TOP 100 PERCENT SUBSTRING(CONVERT(CHAR(10), dbo.icsummary_daily.[date], 103), 4, 7) AS [date],dbo.icsummary_daily.resellerid,  
                      SUM(dbo.icsummary_daily.rl_confcount) AS rl_count, SUM(dbo.icsummary_daily.rl_minutes) AS rl_minutes, SUM(dbo.icsummary_daily.rl_cost) 
                      AS rl_cost, SUM(dbo.icsummary_daily.oa_confcount) AS oa_count, SUM(dbo.icsummary_daily.oa_minutes) AS oa_minutes, 
                      SUM(dbo.icsummary_daily.oa_cost) AS oa_cost, SUM(dbo.icsummary_daily.wb_confcount) AS wb_count, SUM(dbo.icsummary_daily.wb_minutes) 
                      AS wb_minutes, SUM(dbo.icsummary_daily.wb_cost) AS wb_cost, SUM(dbo.icsummary_daily.rl_confcount) + SUM(dbo.icsummary_daily.oa_confcount) 
                      + SUM(dbo.icsummary_daily.wb_confcount) AS total_count, SUM(dbo.icsummary_daily.rl_minutes) + SUM(dbo.icsummary_daily.oa_minutes) 
                      + SUM(dbo.icsummary_daily.wb_minutes) AS total_minutes, SUM(dbo.icsummary_daily.rl_cost) + SUM(dbo.icsummary_daily.oa_cost) 
                      + SUM(dbo.icsummary_daily.wb_cost) AS total_cost, SUM(dbo.icsummary_daily.enhanced_count) AS enhanced_count, 
                      SUM(dbo.icsummary_daily.enhanced_cost) AS enhanced_cost, SUM(dbo.icsummary_daily.rl_cost) + SUM(dbo.icsummary_daily.oa_cost) 
                      + SUM(dbo.icsummary_daily.wb_cost) + SUM(dbo.icsummary_daily.enhanced_cost) AS grandtotal_cost, dbo.salesperson.salespid, dbo.salesperson.name as salesperson_name
FROM         dbo.salesperson INNER JOIN
                      dbo.accountgroup ON dbo.salesperson.salespid = dbo.accountgroup.salespid INNER JOIN
                      dbo.icsummary_daily ON dbo.accountgroup.acctgrpid = dbo.icsummary_daily.acctgrpid
GROUP BY SUBSTRING(CONVERT(CHAR(10), dbo.icsummary_daily.[date], 103), 4, 7), dbo.icsummary_daily.resellerid, dbo.salesperson.salespid, dbo.salesperson.name
ORDER BY SUBSTRING(CONVERT(CHAR(10), dbo.icsummary_daily.[date], 103), 4, 7)' 
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.views WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_daily_ytd_byacctgrpid_bySP]'))
EXEC dbo.sp_executesql @statement = N'CREATE VIEW [dbo].[icsummary_daily_ytd_byacctgrpid_bySP]
AS
SELECT     TOP 100 PERCENT SUBSTRING(CONVERT(CHAR(10), dbo.icsummary_daily.[date], 103), 4, 7) AS date, dbo.salesperson.salespid AS salespid, dbo.salesperson.name as salesperson_name,
                      dbo.icsummary_daily.acctgrpid AS acctgrpid, SUM(dbo.icsummary_daily.rl_confcount) AS rl_count, SUM(dbo.icsummary_daily.rl_minutes) 
                      AS rl_minutes, SUM(dbo.icsummary_daily.rl_cost) AS rl_cost, SUM(dbo.icsummary_daily.oa_confcount) AS oa_count, 
                      SUM(dbo.icsummary_daily.oa_minutes) AS oa_minutes, SUM(dbo.icsummary_daily.oa_cost) AS oa_cost, SUM(dbo.icsummary_daily.wb_confcount) 
                      AS wb_count, SUM(dbo.icsummary_daily.wb_minutes) AS wb_minutes, SUM(dbo.icsummary_daily.wb_cost) AS wb_cost, 
                      SUM(dbo.icsummary_daily.rl_confcount) + SUM(dbo.icsummary_daily.oa_confcount) + SUM(dbo.icsummary_daily.wb_confcount) AS total_count, 
                      SUM(dbo.icsummary_daily.rl_minutes) + SUM(dbo.icsummary_daily.oa_minutes) + SUM(dbo.icsummary_daily.wb_minutes) AS total_minutes, 
                      SUM(dbo.icsummary_daily.rl_cost) + SUM(dbo.icsummary_daily.oa_cost) + SUM(dbo.icsummary_daily.wb_cost) AS total_cost, 
                      SUM(dbo.icsummary_daily.enhanced_count) AS enhanced_count, SUM(dbo.icsummary_daily.enhanced_cost) AS enhanced_cost, 
                      SUM(dbo.icsummary_daily.rl_cost) + SUM(dbo.icsummary_daily.oa_cost) + SUM(dbo.icsummary_daily.wb_cost) 
                      + SUM(dbo.icsummary_daily.enhanced_cost) AS grandtotal_cost
FROM         dbo.salesperson INNER JOIN
                      dbo.accountgroup ON dbo.salesperson.salespid = dbo.accountgroup.salespid INNER JOIN
                      dbo.icsummary_daily ON dbo.accountgroup.acctgrpid = dbo.icsummary_daily.acctgrpid
GROUP BY SUBSTRING(CONVERT(CHAR(10), dbo.icsummary_daily.[date], 103), 4, 7), dbo.salesperson.salespid, dbo.salesperson.name, dbo.icsummary_daily.acctgrpid
ORDER BY SUBSTRING(CONVERT(CHAR(10), dbo.icsummary_daily.[date], 103), 4, 7), dbo.salesperson.salespid, dbo.salesperson.name, dbo.icsummary_daily.acctgrpid' 
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.views WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_daily_mtd_bySP]'))
EXEC dbo.sp_executesql @statement = N'CREATE VIEW [dbo].[icsummary_daily_mtd_bySP]
AS
SELECT     TOP 100 PERCENT dbo.icsummary_daily.[date], dbo.icsummary_daily.resellerid, COALESCE (SUM(dbo.icsummary_daily.rl_confcount), 0) AS rl_count, 
                      COALESCE (SUM(dbo.icsummary_daily.rl_minutes), 0) AS rl_minutes, COALESCE (SUM(dbo.icsummary_daily.rl_cost), 0) AS rl_cost, 
                      COALESCE (SUM(dbo.icsummary_daily.oa_confcount), 0) AS oa_count, COALESCE (SUM(dbo.icsummary_daily.oa_minutes), 0) AS oa_minutes, 
                      COALESCE (SUM(dbo.icsummary_daily.oa_cost), 0) AS oa_cost, COALESCE (SUM(dbo.icsummary_daily.wb_confcount), 0) AS wb_count, 
                      COALESCE (SUM(dbo.icsummary_daily.wb_minutes), 0) AS wb_minutes, COALESCE (SUM(dbo.icsummary_daily.wb_cost), 0) AS wb_cost, 
                      COALESCE (SUM(dbo.icsummary_daily.rl_confcount), 0) + COALESCE (SUM(dbo.icsummary_daily.oa_confcount), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.wb_confcount), 0) AS total_count, COALESCE (SUM(dbo.icsummary_daily.rl_minutes), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.oa_minutes), 0) + COALESCE (SUM(dbo.icsummary_daily.wb_minutes), 0) AS total_minutes, 
                      COALESCE (SUM(dbo.icsummary_daily.rl_cost), 0) + COALESCE (SUM(dbo.icsummary_daily.oa_cost), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.wb_cost), 0) AS total_cost, COALESCE (SUM(dbo.icsummary_daily.enhanced_count), 0) AS enhanced_count, 
                      COALESCE (SUM(dbo.icsummary_daily.enhanced_cost), 0) AS enhanced_cost, COALESCE (SUM(dbo.icsummary_daily.rl_cost), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.oa_cost), 0) + COALESCE (SUM(dbo.icsummary_daily.wb_cost), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.enhanced_cost), 0) AS grandtotal_cost, dbo.salesperson.salespid, dbo.salesperson.name as salesperson_name
FROM         dbo.salesperson INNER JOIN
                      dbo.accountgroup ON dbo.salesperson.salespid = dbo.accountgroup.salespid INNER JOIN
                      dbo.icsummary_daily ON dbo.accountgroup.acctgrpid = dbo.icsummary_daily.acctgrpid
GROUP BY dbo.icsummary_daily.[date], dbo.icsummary_daily.resellerid, dbo.salesperson.salespid, dbo.salesperson.name
ORDER BY dbo.icsummary_daily.[date]' 
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.views WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_daily_mtd_byacctgrpid_bySP]'))
EXEC dbo.sp_executesql @statement = N'CREATE VIEW [dbo].[icsummary_daily_mtd_byacctgrpid_bySP]
AS
SELECT     TOP 100 PERCENT dbo.icsummary_daily.[date], dbo.salesperson.salespid, dbo.salesperson.name as salesperson_name, dbo.icsummary_daily.acctgrpid, 
                      COALESCE (SUM(dbo.icsummary_daily.rl_confcount), 0) AS rl_count, COALESCE (SUM(dbo.icsummary_daily.rl_minutes), 0) AS rl_minutes, 
                      COALESCE (SUM(dbo.icsummary_daily.rl_cost), 0) AS rl_cost, COALESCE (SUM(dbo.icsummary_daily.oa_confcount), 0) AS oa_count, 
                      COALESCE (SUM(dbo.icsummary_daily.oa_minutes), 0) AS oa_minutes, COALESCE (SUM(dbo.icsummary_daily.oa_cost), 0) AS oa_cost, 
                      COALESCE (SUM(dbo.icsummary_daily.wb_confcount), 0) AS wb_count, COALESCE (SUM(dbo.icsummary_daily.wb_minutes), 0) AS wb_minutes, 
                      COALESCE (SUM(dbo.icsummary_daily.wb_cost), 0) AS wb_cost, COALESCE (SUM(dbo.icsummary_daily.rl_confcount), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.oa_confcount), 0) + COALESCE (SUM(dbo.icsummary_daily.wb_confcount), 0) AS total_count, 
                      COALESCE (SUM(dbo.icsummary_daily.rl_minutes), 0) + COALESCE (SUM(dbo.icsummary_daily.oa_minutes), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.wb_minutes), 0) AS total_minutes, COALESCE (SUM(dbo.icsummary_daily.rl_cost), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.oa_cost), 0) + COALESCE (SUM(dbo.icsummary_daily.wb_cost), 0) AS total_cost, 
                      COALESCE (SUM(dbo.icsummary_daily.enhanced_count), 0) AS enhanced_count, COALESCE (SUM(dbo.icsummary_daily.enhanced_cost), 0) 
                      AS enhanced_cost, COALESCE (SUM(dbo.icsummary_daily.rl_cost), 0) + COALESCE (SUM(dbo.icsummary_daily.oa_cost), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.wb_cost), 0) + COALESCE (SUM(dbo.icsummary_daily.enhanced_cost), 0) AS grandtotal_cost
FROM         dbo.salesperson INNER JOIN
                      dbo.accountgroup ON dbo.salesperson.salespid = dbo.accountgroup.salespid INNER JOIN
                      dbo.icsummary_daily ON dbo.accountgroup.acctgrpid = dbo.icsummary_daily.acctgrpid
GROUP BY dbo.icsummary_daily.[date], dbo.salesperson.salespid, dbo.salesperson.name, dbo.icsummary_daily.acctgrpid
ORDER BY dbo.icsummary_daily.[date], dbo.salesperson.salespid, dbo.salesperson.name, dbo.icsummary_daily.acctgrpid' 
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.views WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_daily_ytd]'))
EXEC dbo.sp_executesql @statement = N'CREATE VIEW [dbo].[icsummary_daily_ytd]
AS
SELECT     TOP 100 PERCENT ics.resellerid AS resellerid, SUBSTRING(CONVERT(CHAR(10), ics.[date], 103), 4, 7) AS date, SUM(ics.rl_confcount) 
                      AS rl_count, SUM(ics.rl_minutes) AS rl_minutes, SUM(ics.rl_cost) AS rl_cost, SUM(ics.oa_confcount) AS oa_count, SUM(ics.oa_minutes) AS oa_minutes, 
                      SUM(ics.oa_cost) AS oa_cost, SUM(ics.wb_confcount) AS wb_count, SUM(ics.wb_minutes) AS wb_minutes, SUM(ics.wb_cost) AS wb_cost, 
                      SUM(ics.rl_confcount) + SUM(ics.oa_confcount) + SUM(ics.wb_confcount) AS total_count, SUM(ics.rl_minutes) + SUM(ics.oa_minutes) 
                      + SUM(ics.wb_minutes) AS total_minutes, SUM(ics.rl_cost) + SUM(ics.oa_cost) + SUM(ics.wb_cost) AS total_cost, SUM(ics.enhanced_count) 
                      AS enhanced_count, SUM(ics.enhanced_cost) AS enhanced_cost, SUM(ics.rl_cost) + SUM(ics.oa_cost) + SUM(ics.wb_cost) + SUM(ics.enhanced_cost) 
                      AS grandtotal_cost
FROM         icsummary_monthly ics
GROUP BY ics.resellerid, SUBSTRING(CONVERT(CHAR(10), ics.[date], 103), 4, 7)
ORDER BY SUBSTRING(CONVERT(CHAR(10), ics.[date], 103), 4, 7)' 
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.views WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_daily_reseller_ytd]'))
EXEC dbo.sp_executesql @statement = N'CREATE VIEW [dbo].[icsummary_daily_reseller_ytd]
AS
SELECT     TOP 100 PERCENT SUBSTRING(CONVERT(CHAR(10), ics.[date], 103), 4, 7) AS date, COALESCE (SUM(ics.rl_confcount), 0) AS rl_count, 
                      COALESCE (SUM(ics.rl_minutes), 0) AS rl_minutes, COALESCE (SUM(ics.rl_rcost), 0) AS rl_cost, COALESCE (SUM(ics.oa_confcount), 0) AS oa_count, 
                      COALESCE (SUM(ics.oa_minutes), 0) AS oa_minutes, COALESCE (SUM(ics.oa_rcost), 0) AS oa_cost, COALESCE (SUM(ics.wb_confcount), 0) 
                      AS wb_count, COALESCE (SUM(ics.wb_minutes), 0) AS wb_minutes, COALESCE (SUM(ics.wb_rcost), 0) AS wb_cost, COALESCE (SUM(ics.rl_confcount), 
                      0) + COALESCE (SUM(ics.oa_confcount), 0) + COALESCE (SUM(ics.wb_confcount), 0) AS total_count, COALESCE (SUM(ics.rl_minutes), 0) 
                      + COALESCE (SUM(ics.oa_minutes), 0) + COALESCE (SUM(ics.wb_minutes), 0) AS total_minutes, COALESCE (SUM(ics.rl_rcost), 0) 
                      + COALESCE (SUM(ics.oa_rcost), 0) + COALESCE (SUM(ics.wb_rcost), 0) AS total_cost, COALESCE (SUM(ics.enhanced_count), 0) AS enhanced_count, 
                      COALESCE (SUM(ics.enhanced_cost), 0) AS enhanced_cost, COALESCE (SUM(ics.rl_rcost), 0) + COALESCE (SUM(ics.oa_rcost), 0) 
                      + COALESCE (SUM(ics.wb_rcost), 0) + COALESCE (SUM(ics.enhanced_rcost), 0) AS grandtotal_cost
FROM         icsummary_monthly ics
WHERE     (ics.resellerid NOT IN (1, 3))
GROUP BY SUBSTRING(CONVERT(CHAR(10), ics.[date], 103), 4, 7)
ORDER BY SUBSTRING(CONVERT(CHAR(10), ics.[date], 103), 4, 7)' 
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.views WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_daily_mtd_byacctgrpid]'))
EXEC dbo.sp_executesql @statement = N'CREATE VIEW [dbo].[icsummary_daily_mtd_byacctgrpid]
AS
SELECT     TOP 100 PERCENT dbo.icsummary_daily.[date], dbo.icsummary_daily.resellerid, dbo.icsummary_daily.acctgrpid, 
                      COALESCE (SUM(dbo.icsummary_daily.rl_confcount), 0) AS rl_count, COALESCE (SUM(dbo.icsummary_daily.rl_minutes), 0) AS rl_minutes, 
                      COALESCE (SUM(dbo.icsummary_daily.rl_cost), 0) AS rl_cost, COALESCE (SUM(dbo.icsummary_daily.oa_confcount), 0) AS oa_count, 
                      COALESCE (SUM(dbo.icsummary_daily.oa_minutes), 0) AS oa_minutes, COALESCE (SUM(dbo.icsummary_daily.oa_cost), 0) AS oa_cost, 
                      COALESCE (SUM(dbo.icsummary_daily.wb_confcount), 0) AS wb_count, COALESCE (SUM(dbo.icsummary_daily.wb_minutes), 0) AS wb_minutes, 
                      COALESCE (SUM(dbo.icsummary_daily.wb_cost), 0) AS wb_cost, COALESCE (SUM(dbo.icsummary_daily.rl_confcount), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.oa_confcount), 0) + COALESCE (SUM(dbo.icsummary_daily.wb_confcount), 0) AS total_count, 
                      COALESCE (SUM(dbo.icsummary_daily.rl_minutes), 0) + COALESCE (SUM(dbo.icsummary_daily.oa_minutes), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.wb_minutes), 0) AS total_minutes, COALESCE (SUM(dbo.icsummary_daily.rl_cost), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.oa_cost), 0) + COALESCE (SUM(dbo.icsummary_daily.wb_cost), 0) AS total_cost, 
                      COALESCE (SUM(dbo.icsummary_daily.enhanced_count), 0) AS enhanced_count, COALESCE (SUM(dbo.icsummary_daily.enhanced_cost), 0) 
                      AS enhanced_cost, COALESCE (SUM(dbo.icsummary_daily.rl_cost), 0) + COALESCE (SUM(dbo.icsummary_daily.oa_cost), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.wb_cost), 0) + COALESCE (SUM(dbo.icsummary_daily.enhanced_cost), 0) AS grandtotal_cost
FROM         dbo.icsummary_daily
GROUP BY dbo.icsummary_daily.[date], dbo.icsummary_daily.resellerid, dbo.icsummary_daily.acctgrpid
ORDER BY dbo.icsummary_daily.[date], dbo.icsummary_daily.resellerid, dbo.icsummary_daily.acctgrpid' 
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.views WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_daily_reseller_mtd]'))
EXEC dbo.sp_executesql @statement = N'CREATE VIEW [dbo].[icsummary_daily_reseller_mtd]
AS
SELECT     TOP 100 PERCENT dbo.icsummary_daily.[date], COALESCE (SUM(dbo.icsummary_daily.rl_confcount), 0) AS rl_count, 
                      COALESCE (SUM(dbo.icsummary_daily.rl_minutes), 0) AS rl_minutes, COALESCE (SUM(dbo.icsummary_daily.rl_rcost), 0) AS rl_cost, 
                      COALESCE (SUM(dbo.icsummary_daily.oa_confcount), 0) AS oa_count, COALESCE (SUM(dbo.icsummary_daily.oa_minutes), 0) AS oa_minutes, 
                      COALESCE (SUM(dbo.icsummary_daily.oa_rcost), 0) AS oa_cost, COALESCE (SUM(dbo.icsummary_daily.wb_confcount), 0) AS wb_count, 
                      COALESCE (SUM(dbo.icsummary_daily.wb_minutes), 0) AS wb_minutes, COALESCE (SUM(dbo.icsummary_daily.wb_rcost), 0) AS wb_cost, 
                      COALESCE (SUM(dbo.icsummary_daily.rl_confcount), 0) + COALESCE (SUM(dbo.icsummary_daily.oa_confcount), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.wb_confcount), 0) AS total_count, COALESCE (SUM(dbo.icsummary_daily.rl_minutes), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.oa_minutes), 0) + COALESCE (SUM(dbo.icsummary_daily.wb_minutes), 0) AS total_minutes, 
                      COALESCE (SUM(dbo.icsummary_daily.rl_rcost), 0) + COALESCE (SUM(dbo.icsummary_daily.oa_rcost), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.wb_rcost), 0) AS total_cost, COALESCE (SUM(dbo.icsummary_daily.enhanced_count), 0) AS enhanced_count, 
                      COALESCE (SUM(dbo.icsummary_daily.enhanced_cost), 0) AS enhanced_cost, COALESCE (SUM(dbo.icsummary_daily.rl_rcost), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.oa_rcost), 0) + COALESCE (SUM(dbo.icsummary_daily.wb_rcost), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.enhanced_rcost), 0) AS grandtotal_cost
FROM       dbo.icsummary_daily
WHERE     (dbo.icsummary_daily.resellerid NOT IN (1, 3))
GROUP BY dbo.icsummary_daily.[date]
ORDER BY dbo.icsummary_daily.[date]' 
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.views WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_daily_mtd]'))
EXEC dbo.sp_executesql @statement = N'CREATE VIEW [dbo].[icsummary_daily_mtd]
AS
SELECT     TOP 100 PERCENT dbo.icsummary_daily.resellerid, dbo.icsummary_daily.[date], COALESCE (SUM(dbo.icsummary_daily.rl_confcount), 0) AS rl_count, 
                      COALESCE (SUM(dbo.icsummary_daily.rl_minutes), 0) AS rl_minutes, COALESCE (SUM(dbo.icsummary_daily.rl_cost), 0) AS rl_cost, 
                      COALESCE (SUM(dbo.icsummary_daily.oa_confcount), 0) AS oa_count, COALESCE (SUM(dbo.icsummary_daily.oa_minutes), 0) AS oa_minutes, 
                      COALESCE (SUM(dbo.icsummary_daily.oa_cost), 0) AS oa_cost, COALESCE (SUM(dbo.icsummary_daily.wb_confcount), 0) AS wb_count, 
                      COALESCE (SUM(dbo.icsummary_daily.wb_minutes), 0) AS wb_minutes, COALESCE (SUM(dbo.icsummary_daily.wb_cost), 0) AS wb_cost, 
                      COALESCE (SUM(dbo.icsummary_daily.rl_confcount), 0) + COALESCE (SUM(dbo.icsummary_daily.oa_confcount), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.wb_confcount), 0) AS total_count, COALESCE (SUM(dbo.icsummary_daily.rl_minutes), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.oa_minutes), 0) + COALESCE (SUM(dbo.icsummary_daily.wb_minutes), 0) AS total_minutes, 
                      COALESCE (SUM(dbo.icsummary_daily.rl_cost), 0) + COALESCE (SUM(dbo.icsummary_daily.oa_cost), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.wb_cost), 0) AS total_cost, COALESCE (SUM(dbo.icsummary_daily.enhanced_count), 0) AS enhanced_count, 
                      COALESCE (SUM(dbo.icsummary_daily.enhanced_cost), 0) AS enhanced_cost, COALESCE (SUM(dbo.icsummary_daily.rl_cost), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.oa_cost), 0) + COALESCE (SUM(dbo.icsummary_daily.wb_cost), 0) 
                      + COALESCE (SUM(dbo.icsummary_daily.enhanced_cost), 0) AS grandtotal_cost
FROM dbo.icsummary_daily
GROUP BY dbo.icsummary_daily.resellerid, dbo.icsummary_daily.[date]
ORDER BY dbo.icsummary_daily.resellerid, dbo.icsummary_daily.[date]' 
GO
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
IF NOT EXISTS (SELECT * FROM sys.views WHERE object_id = OBJECT_ID(N'[dbo].[icsummary_daily_ytd_byacctgrpid]'))
EXEC dbo.sp_executesql @statement = N'CREATE VIEW [dbo].[icsummary_daily_ytd_byacctgrpid]
AS
SELECT     TOP 100 PERCENT SUBSTRING(CONVERT(CHAR(10), dbo.icsummary_daily.[date], 103), 4, 7) AS date, dbo.icsummary_daily.resellerid as resellerid, dbo.icsummary_daily.acctgrpid AS acctgrpid, 
                      SUM(dbo.icsummary_daily.rl_confcount) AS rl_count, SUM(dbo.icsummary_daily.rl_minutes) AS rl_minutes, SUM(dbo.icsummary_daily.rl_cost) 
                      AS rl_cost, SUM(dbo.icsummary_daily.oa_confcount) AS oa_count, SUM(dbo.icsummary_daily.oa_minutes) AS oa_minutes, 
                      SUM(dbo.icsummary_daily.oa_cost) AS oa_cost, SUM(dbo.icsummary_daily.wb_confcount) AS wb_count, SUM(dbo.icsummary_daily.wb_minutes) 
                      AS wb_minutes, SUM(dbo.icsummary_daily.wb_cost) AS wb_cost, SUM(dbo.icsummary_daily.rl_confcount) + SUM(dbo.icsummary_daily.oa_confcount) 
                      + SUM(dbo.icsummary_daily.wb_confcount) AS total_count, SUM(dbo.icsummary_daily.rl_minutes) + SUM(dbo.icsummary_daily.oa_minutes) 
                      + SUM(dbo.icsummary_daily.wb_minutes) AS total_minutes, SUM(dbo.icsummary_daily.rl_cost) + SUM(dbo.icsummary_daily.oa_cost) 
                      + SUM(dbo.icsummary_daily.wb_cost) AS total_cost, SUM(dbo.icsummary_daily.enhanced_count) AS enhanced_count, 
                      SUM(dbo.icsummary_daily.enhanced_cost) AS enhanced_cost, SUM(dbo.icsummary_daily.rl_cost) + SUM(dbo.icsummary_daily.oa_cost) 
                      + SUM(dbo.icsummary_daily.wb_cost) + SUM(dbo.icsummary_daily.enhanced_cost) AS grandtotal_cost
FROM         dbo.icsummary_daily
GROUP BY SUBSTRING(CONVERT(CHAR(10), dbo.icsummary_daily.[date], 103), 4, 7), dbo.icsummary_daily.resellerid, dbo.icsummary_daily.acctgrpid
ORDER BY SUBSTRING(CONVERT(CHAR(10), dbo.icsummary_daily.[date], 103), 4, 7), dbo.icsummary_daily.acctgrpid' 
