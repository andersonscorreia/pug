# Configurando FTP 

## Criando tabelas no Bando de Dados

```
CREATE DATABASE IF NOT EXISTS ASA;
USE ASA;

/*Table structure for table ftpusers */

CREATE TABLE IF NOT EXISTS ftpusers (
nome varchar(255) NOT NULL DEFAULT 'Nome',
login varchar(20) NOT NULL,
senha varchar(20) NOT NULL,
uid int(10) NOT NULL AUTO_INCREMENT,
gid int(10) DEFAULT NULL,
ativo char(1) NOT NULL DEFAULT 's',
dir varchar(255) NOT NULL,
shell varchar(255) NOT NULL,
email varchar(255) DEFAULT NULL,
tipo varchar(255) DEFAULT NULL,
PRIMARY KEY (login),
KEY login (login),
KEY uid (uid) );

/*Table structure for table ftpgroups */
CREATE TABLE IF NOT EXISTS ftpgroups(
groupname varchar(20) NOT NULL,
gid int(10) NOT NULL,
members varchar(255) DEFAULT NULL,
PRIMARY KEY (groupname)
);

CREATE USER 'ftpdbuser'@'localhost' identified by 'ftpdbuserpwd';

GRANT SELECT ON ASA.* TO 'ftpdbuser'@'localhost';

/*Data for the table ftpusers */
insert into ftpusers(nome,login,senha,uid,gid,ativo,dir,shell,email) values ('Teste de FTP','ftpuser','senhaasaftp',12345,100,'s','/home/ftpuser/','/bin/bash','ftpuser@linux49.asa');
insert into ftpusers(nome,login,senha,uid,gid,ativo,dir,shell,email) values ('Teste – at1','at1','atividade',15001,15000,'s','/home/at1','/bin/bash','at@ftp57.local');
insert into ftpusers(nome,login,senha,uid,gid,ativo,dir,shell,email) values ('Teste – at2','at2','atividade',15002,15000,'s','/home/at2','/bin/bash','at@f.ftp57.local');

/*Data for the table ftpgroups */
insert into ftpgroups(groupname,gid,members) values ('atividade',15000,'at@ftp57.local,at@f.ftp57.local');
```

Acesse o arquivo "proftpd.conf" no diretório "/etc" e adicione as diretivas abaixo;
```
nano /etc/proftpd.conf
...
RootLogin on
DefaultRoot ~ users,!root
###
# LOGIN via MYSQL
###
RequireValidShell off
SQLAuthTypes Plaintext Crypt
SQLAuthenticate on
SQLConnectInfo ASA57@192.168.102.100 container57 1F(044480)
SQLUserInfo ftpusers email senha uid gid dir shell
SQLGroupInfo ftpgroups groupname gid members
SQLUserWhereClause "ativo='s'"
CreateHome on
###
# Fim configuração ASA
###
```

