# Configurando SMTP

### Tópicos 

:small_blue_diamond: [Configure Postfix](#configure-postfix)

:small_blue_diamond: [Configure Dovecot](#configure-dovecot)

:small_blue_diamond: [Configure Mail Aliases ](#configure-mail-aliases )

:small_blue_diamond: [Testando Postfix ](#testing-postfix  )



NOTA:
Nos containers CentOs seguir o Link específico para instalação do Postfix+Mysql+Dovecot. Nesta instalação deverá ser utilizada a mesma base de dados do servidor FTP. Vide abaixo a sugestão de comandos SQL para referência da configuração do banco de dados.

ATENÇÃO:
Não coloque em "myhostname" no arquivo "main.cf" nenhum dos nomes constantes na tabela "domains" do seu banco de dados!

## Configurar o MySQL para Domínios Virtuais e Usuários

Acessar o danco de dados MySQL, configure de acordo com os comando abaixo;
```
CREATE TABLE domains (domain VARCHAR(50) NOT NULL, PRIMARY KEY (domain) );
CREATE TABLE forwardings (source VARCHAR(80) NOT NULL, destination TEXT NOT NULL, PRIMARY KEY (source) );
CREATE TABLE transport ( domain VARCHAR(128) NOT NULL DEFAULT '', transport VARCHAR(128) NOT NULL DEFAULT '', UNIQUE KEY domain (domain) );

INSERT INTO domains (domain) VALUES ('pugmail.d.local');

INSERT INTO ftpusers(nome,login,senha,uid,gid,ativo,dir,shell,email) VALUES ('Joao - Teste de Correio','joao','senhajoao',12346,100,'s','/home/joao/','/bin/bash','joao@asa30.ifrn.local');
INSERT INTO ftpusers(nome,login,senha,uid,gid,ativo,dir,shell,email) values ('Teste – at4','mail1','atividade',15001,15000,'s','/home/mail1','/bin/bash','at@pugmail.d.local');
INSERT INTO ftpusers(nome,login,senha,uid,gid,ativo,dir,shell,email) values ('Teste – at5','mail2','atividade',15002,15000,'s','/home/mail2','/bin/bash','at@f.mail57.local');


insert into ftpgroups(groupname,gid,members) values ('mail',15001,'at@pugmail.d.local,at@f.mail57.local');
```

## Configure Postfix

1. Create a virtual domain configuration file for Postfix called /etc/postfix/mysql-virtual_domains.cf:
```
File: /etc/postfix/mysql-virtual_domains.cf
user = container57
password = 1F(044480)
dbname = ASA57
query = SELECT domain AS virtual FROM domains WHERE domain='%s'
hosts = 192.168.102.100
```
2. Create a virtual forwarding file for Postfix called /etc/postfix/mysql-virtual_forwardings.cf:
```
File: /etc/postfix/mysql-virtual_forwardings.cf
user = container57
password = 1F(044480)
dbname = ASA57
query = SELECT destination FROM forwardings WHERE source='%s'
hosts = 192.168.102.100
```
3. Create a virtual mailbox configuration file for Postfix called /etc/postfix/mysql-virtual_mailboxes.cf:
```
File: /etc/postfix/mysql-virtual_mailboxes.cf
user = container57
password = 1F(044480)
dbname = ASA57
query = SELECT CONCAT(SUBSTRING_INDEX(email,'@',-1),'/',SUBSTRING_INDEX(email,'@',1),'/') FROM ftpusers WHERE email='%s'
hosts = 192.168.102.100
```
4. Create a virtual email mapping file for Postfix called /etc/postfix/mysql-virtual_email2email.cf:
```
File: /etc/postfix/mysql-virtual_email2email.cf
user = container57
password = 1F(044480)
dbname = ASA57
query = SELECT email FROM ftpusers WHERE email='%s'
hosts = 192.168.102.100
```
5. Set proper permissions and ownership for these configuration files:
```
chmod o= /etc/postfix/mysql-virtual_*.cf
chgrp postfix /etc/postfix/mysql-virtual_*.cf
```
6. Create a user and group for mail handling. All virtual mailboxes will be stored under this user’s home directory:
```
groupadd -g 5000 vmail
useradd -g vmail -u 5000 vmail -d /home/vmail -m
```
7.Complete the remaining steps required for Postfix configuration. Please be sure to replace server.example.com with the Linode’s fully qualified domain name. If you are planning on using your own SSL certificate and key, replace /etc/pki/dovecot/private/dovecot.pem with the appropriate path:
```
postconf -e 'myhostname = c57.ifrn.local'
postconf -e 'mynetworks = 127.0.0.0/8'
postconf -e 'inet_interfaces = all'
postconf -e 'message_size_limit = 30720000'

postconf -e 'virtual_alias_domains ='
postconf -e 'virtual_alias_maps = proxy:mysql:/etc/postfix/mysql-virtual_forwardings.cf, mysql:/etc/postfix/mysql-virtual_email2email.cf'
postconf -e 'virtual_mailbox_domains = proxy:mysql:/etc/postfix/mysql-virtual_domains.cf'
postconf -e 'virtual_mailbox_maps = proxy:mysql:/etc/postfix/mysql-virtual_mailboxes.cf'
postconf -e 'virtual_mailbox_base = /home/vmail'
postconf -e 'virtual_uid_maps = static:5000'
postconf -e 'virtual_gid_maps = static:5000'

postconf -e 'smtpd_sasl_type = dovecot'
postconf -e 'smtpd_sasl_path = private/auth'
postconf -e 'smtpd_sasl_auth_enable = yes'
postconf -e 'broken_sasl_auth_clients = yes'
postconf -e 'smtpd_sasl_authenticated_header = yes'

postconf -e 'smtpd_recipient_restrictions = permit_mynetworks, permit_sasl_authenticated, reject_unauth_destination'
postconf -e 'smtpd_use_tls = yes'
postconf -e 'smtpd_tls_cert_file = /etc/pki/dovecot/certs/dovecot.pem'
postconf -e 'smtpd_tls_key_file = /etc/pki/dovecot/private/dovecot.pem'
postconf -e 'virtual_create_maildirsize = yes'
postconf -e 'virtual_maildir_extended = yes'
postconf -e 'proxy_read_maps = $local_recipient_maps $mydestination $virtual_alias_maps $virtual_alias_domains $virtual_mailbox_maps $virtual_mailbox_domains $relay_recipient_maps $relay_domains $canonical_maps $sender_canonical_maps $recipient_canonical_maps $relocated_maps $transport_maps $mynetworks $virtual_mailbox_limit_maps'
postconf -e 'virtual_transport = dovecot'
postconf -e 'dovecot_destination_recipient_limit = 1'
```
8. Edit the file /etc/postfix/master.cf and add the Dovecot service to the bottom of the file:
```
File: /etc/postfix/master.cf
dovecot   unix  -       n       n       -       -       pipe
    flags=DRhu user=vmail:vmail argv=/usr/libexec/dovecot/deliver -f ${sender} -d ${recipient}
```
9. Configure Postfix to start on boot and start the service for the first time:
```
chkconfig postfix on
service postfix start

```
Isso conclui a configuração do Postfix.


## Configure Dovecot

1. Move /etc/dovecot/dovecot.conf to a backup file:
```
mv /etc/dovecot/dovecot.conf /etc/dovecot/dovecot.conf-backup
```
2. Copy the following into the now-empty dovecot.conf file, substituting your system’s domain name for example.com in line 37:
```
File: /etc/dovecot/dovecot.conf
protocols = imap pop3
log_timestamp = "%Y-%m-%d %H:%M:%S "
mail_location = maildir:/home/vmail/%d/%n/Maildir

ssl_cert = </etc/pki/dovecot/certs/dovecot.pem
ssl_key = </etc/pki/dovecot/private/dovecot.pem

namespace {
    type = private
    separator = .
    prefix = INBOX.
    inbox = yes
}

service auth {
    unix_listener auth-master {
        mode = 0600
        user = vmail
    }

    unix_listener /var/spool/postfix/private/auth {
        mode = 0666
        user = postfix
        group = postfix
    }

user = root
}

service auth-worker {
    user = root
}

protocol lda {
    log_path = /home/vmail/dovecot-deliver.log
    auth_socket_path = /var/run/dovecot/auth-master
    postmaster_address = postmaster@pugmail.d.local
}

protocol pop3 {
    pop3_uidl_format = %08Xu%08Xv
}

passdb {
    driver = sql
    args = /etc/dovecot/dovecot-sql.conf.ext
}

userdb {
    driver = static
    args = uid=5000 gid=5000 home=/home/vmail/%d/%n allow_all_users=yes
}

```

3. MySQL will be used to store password information, so /etc/dovecot/dovecot-sql.conf.ext must be created. Insert the following contents into the file, making sure to replace mail_admin_password with your mail password:

```
File: /etc/dovecot/dovecot-sql.conf.ext
driver = mysql
connect = host=192.168.102.100 dbname=ASA57 user=container57 password=1F(044480)
default_pass_scheme = PLAIN
password_query = SELECT email as user, senha as password FROM ftpusers WHERE email='%u';
```
4. Restrict access to the file by changing the permissions to allow users in the dovecot group to access it, while denying access to others:
```
chgrp dovecot /etc/dovecot/dovecot-sql.conf.ext
chmod o= /etc/dovecot/dovecot-sql.conf.ext
```
5. Configure Dovecot to start on boot, and start it for the first time:
```
chkconfig dovecot on
service dovecot start
```
6. Check /var/log/maillog to make sure Dovecot started without errors. Your log should have lines similar to the following:
```
File: /var/log/maillog

tail -100 /var/log/maillog

```



### Configure Mail Aliases  


1. Edit the file /etc/aliases, making sure the postmaster and root directives are set properly for your organization:

```
File: /etc/aliases
postmaster: root
root: postmaster@pugmail.d.local
```
2. Update aliases and restart Postfix:
```
newaliases
service postfix restart
```

Isso conclui a configuração do alias. Em seguida, teste o Postfix para se certificar de que ele está funcionando corretamente.



### Testing Postfix  

Test Postfix for SMTP-AUTH and TLS:
```
telnet localhost 25
While still connected, issue the following command:

ehlo localhost
You should see output similar to the following:

250-hostname.example.com
250-PIPELINING
250-SIZE 30720000
250-VRFY
250-ETRN
250-STARTTLS
250-AUTH PLAIN
250-AUTH=PLAIN
250-ENHANCEDSTATUSCODES
250-8BITMIME
250 DSN
Issue the command quit to terminate the telnet connection.
```
Em seguida, preencha o banco de dados MySQL com domínios e usuários de e-mail.



