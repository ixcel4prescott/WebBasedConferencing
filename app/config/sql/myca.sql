---------------------------------------------------------------------------------
-- 
-- These are the myca specific tables that need to be installed for use 
-- 
---------------------------------------------------------------------------------

-- Users table

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[myca_users]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[myca_users]
GO

if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[myca_users]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
 BEGIN
CREATE TABLE [dbo].[myca_users] (
	[id] [bigint] IDENTITY (1, 1) NOT NULL ,
	[username] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[email] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	[password] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	[name] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	[theme] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	[company_name] [varchar] (100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[layout] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	[level_type] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[level_id] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[active] [bit] NOT NULL ,
	[created] [datetime] NULL ,
	[modified] [datetime] NULL ,
	[verified] [bit] NOT NULL ,
	[logins] [int] NOT NULL CONSTRAINT [D_dbo_myca_users_1] DEFAULT ((0)),
	[verification_code] [varchar] (40) COLLATE SQL_Latin1_General_CP1_CI_AS NULL 
) ON [PRIMARY]
END

GO

-- DiffLog table

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[myca_diff_log]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[myca_diff_log]
GO

if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[myca_diff_log]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
 BEGIN
CREATE TABLE [dbo].[myca_diff_log] (
	[id] [bigint] IDENTITY (1, 1) NOT NULL ,
	[entity] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	[object_id] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	[op] [int] NOT NULL ,
	[userid] [int] NOT NULL ,
	[ip_addr] [varchar] (15) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	[created] [datetime] NOT NULL ,
	[diff] [text] COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[host] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	CONSTRAINT [PK__myca_diff_log__6FE99F9F] PRIMARY KEY  CLUSTERED 
	(
		[id]
	) WITH  FILLFACTOR = 100  ON [PRIMARY] 
) ON [PRIMARY]
END

GO

-- Log Table

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[myca_log]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[myca_log]
GO

if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[myca_log]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
 BEGIN
CREATE TABLE [dbo].[myca_log] (
	[id] [bigint] IDENTITY (1, 1) NOT NULL ,
	[created] [datetime] NOT NULL ,
	[controller] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[action] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[pass] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[url] [varchar] (250) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[userid] [bigint] NULL ,
	[comments] [varchar] (250) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[ip_addr] [varchar] (15) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[category] [varchar] (250) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[method] [varchar] (6) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[host] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL 
) ON [PRIMARY]
END

GO

-- Requests table 

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[requests]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[requests]
GO

if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[requests]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
 BEGIN
CREATE TABLE [dbo].[requests] (
	[id] [int] IDENTITY (1, 1) NOT NULL ,
	[type] [int] NOT NULL ,
	[creator] [int] NOT NULL ,
	[created] [datetime] NOT NULL ,
	[modified] [datetime] NOT NULL ,
	[acctgrpid] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[accountid] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[status] [int] NOT NULL CONSTRAINT [D_dbo_requests_1] DEFAULT ((0)),
	[notes] [text] COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[log] [text] COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[signed_off_by] [int] NULL ,
	[comments] [text] COLLATE SQL_Latin1_General_CP1_CI_AS NULL 
) ON [PRIMARY]
END

GO

-- Request Data table

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[requests_data]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[requests_data]
GO

if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[requests_data]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
 BEGIN
CREATE TABLE [dbo].[requests_data] (
	[requests_id] [int] NOT NULL ,
	[field] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	[value] [varchar] (100) COLLATE SQL_Latin1_General_CP1_CI_AS NULL 
) ON [PRIMARY]
END

GO

-- Request Statuses table

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[requests_status]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[requests_status]
GO

if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[requests_status]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
 BEGIN
CREATE TABLE [dbo].[requests_status] (
	[id] [int] NOT NULL ,
	[name] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	[description] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL 
) ON [PRIMARY]
END

GO

-- Request Types Table

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[requests_types]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[requests_types]
GO

if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[requests_types]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
 BEGIN
CREATE TABLE [dbo].[requests_types] (
	[id] [int] NOT NULL ,
	[name] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	[description] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NULL 
) ON [PRIMARY]
END

GO


-- Request Groups table

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[request_groups]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[request_groups]
GO

if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[request_groups]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
 BEGIN
CREATE TABLE [dbo].[request_groups] (
	[id] [int] IDENTITY (1, 1) NOT NULL ,
	[name] [varchar] (250) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	 PRIMARY KEY  CLUSTERED 
	(
		[id]
	)  ON [PRIMARY] 
) ON [PRIMARY]
END

GO

-- Requests to Request Groups table

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[requests_request_groups]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[requests_request_groups]
GO

if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[requests_request_groups]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
 BEGIN
CREATE TABLE [dbo].[requests_request_groups] (
	[type] [int] NULL ,
	[status] [int] NULL ,
	[request_group_id] [int] NULL 
) ON [PRIMARY]
END

GO

-- Requests Groups to Users table

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[request_groups_users]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[request_groups_users]
GO

if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[request_groups_users]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
 BEGIN
CREATE TABLE [dbo].[request_groups_users] (
	[request_group_id] [int] NULL ,
	[user_id] [int] NULL 
) ON [PRIMARY]
END

GO

-- Default Bridge table

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[default_bridge]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[default_bridge]
GO

if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[default_bridge]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
 BEGIN
CREATE TABLE [dbo].[default_bridge] (
	[acctgrpid] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	[bridge_id] [int] NOT NULL CONSTRAINT [DF__default_b__bridg__02084FDA] DEFAULT ((0)),
	 PRIMARY KEY  CLUSTERED 
	(
		[acctgrpid]
	)  ON [PRIMARY] 
) ON [PRIMARY]
END

GO

-- Reseller Groups table

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[reseller_groups]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[reseller_groups]
GO

if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[reseller_groups]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
 BEGIN
CREATE TABLE [dbo].[reseller_groups] (
	[user_id] [int] NOT NULL ,
	[name] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL 
) ON [PRIMARY]
END

GO

-- Reseller Groups to Resellers table

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[reseller_groups_to_resellers]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[reseller_groups_to_resellers]
GO

if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[reseller_groups_to_resellers]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
 BEGIN
CREATE TABLE [dbo].[reseller_groups_to_resellers] (
	[user_id] [int] NOT NULL ,
	[reseller_id] [int] NOT NULL 
) ON [PRIMARY]
END

GO

-- Salesperson Groups Table

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[salesperson_groups]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[salesperson_groups]
GO

if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[salesperson_groups]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
 BEGIN
CREATE TABLE [dbo].[salesperson_groups] (
	[user_id] [int] NOT NULL ,
	[name] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL 
) ON [PRIMARY]
END

GO

-- Salespeople to Salesperson Groups table

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[salespeople_to_salesperson_groups]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[salespeople_to_salesperson_groups]
GO

if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[salespeople_to_salesperson_groups]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
 BEGIN
CREATE TABLE [dbo].[salespeople_to_salesperson_groups] (
	[user_id] [int] NOT NULL ,
	[salesperson_id] [int] NOT NULL 
) ON [PRIMARY]
END

GO


-- Services table, used for clientside to map user/pass pairs 

if exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[services]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
drop table [dbo].[services]
GO

if not exists (select * from dbo.sysobjects where id = object_id(N'[dbo].[services]') and OBJECTPROPERTY(id, N'IsUserTable') = 1)
 BEGIN
CREATE TABLE [dbo].[services] (
	[id] [int] NOT NULL ,
	[user_id] [int] NOT NULL CONSTRAINT [DF__services__user_i__04E4BC85] DEFAULT ((0)),
	[name] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	[username] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	[password] [varchar] (50) COLLATE SQL_Latin1_General_CP1_CI_AS NOT NULL ,
	 PRIMARY KEY  CLUSTERED 
	(
		[id]
	)  ON [PRIMARY] 
) ON [PRIMARY]
END

GO


