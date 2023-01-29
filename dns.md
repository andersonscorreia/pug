# Configurando DNS

Após instalado o [BIND](https://www.isc.org/bind/) vamos acessar o diretório "/var/named" e criar os arquivos de zonas que serão responsáveis pelo domínio, ftp e correio eletrônico. Aqui utilizamos o editor de texto "nano" para a tarefa.

pug.d.local.zone
```
root@asa:/var/named# nano pug.d.local.zone
$TTL 30	; tempo de vida das respostas fornecidas pelo DNS
$ORIGIN pug.d.local.
@       IN SOA  ns1 root (
                                20230122	; serial para controle de atualizações entre master e slave
                                120			; refresh tempo de atualizações entre master e slave
                                60			; retry tempo de atualizações caso o refresh falhe
                                300			; expiry tempo de expiração do slave caso não se contate com o master
								30 )		; minimum tempo de vida das repostas negativas do servidor

            IN 	NS   	ns1
			IN 	A 		192.168.102.157
			IN 	MX 10	mail
            IN 	MX 0 	pugmail.d.local.

ns1					IN 	A 	192.168.102.157
mail				IN 	A 	192.168.102.157
www					IN 	A 	192.168.102.157
pugmail.d.local.	IN 	A 	192.168.102.157
web					IN 	CNAME www
```


pugmail.d.local.zone
```
ot@asa:/var/named# nano pugmail.d.local.zone
$TTL 30	; tempo de vida das respostas fornecidas pelo DNS
$ORIGIN pugmail.d.local.
@       IN SOA  ns1 postmaster (
                                202311261	; serial para controle de atualizações entre master e slave
                                120			; refresh tempo de atualizações entre master e slave
                                60			; retry tempo de atualizações caso o refresh falhe
                                300			; expiry tempo de expiração do slave caso não se contate com o master
								30 )		; minimum tempo de vida das repostas negativas do servidor

            IN 	NS   	ns1
			IN 	A 		192.168.102.157
            IN 	MX 0 	mail

ns1					IN 	A 	192.168.102.157
mail				IN 	A 	192.168.102.157
www					IN 	A 	192.168.102.157
```

ftppug.d.local.zone
```
root@asa:/var/named# nano ftppug.d.local.zone
$TTL 10
$ORIGIN ftppug.d.local.
@       IN SOA  ns1 hostmaster (
                                20230122	; serial para controle de atualizações entre master e slave
                                120			; refresh tempo de atualizações entre master e slave
                                60			; retry tempo de atualizações caso o refresh falhe
                                300			; expiry tempo de expiração do slave caso não se contate com o master
								30 )		; minimum tempo de vida das repostas negativas do servidor
            IN 	NS   	ns1
			IN 	A 		192.168.102.157
            IN 	MX 0 	mail

ns1		IN A 192.168.102.157
www     IN A 192.168.102.157
mail    IN A 192.168.102.157
ftp     IN CNAME www
```

Em seguida vamos acessar o diretorio "/etc" e anexar ao arquivo "named.conf" nossos blocos de zonas. Não esquecer de incluir ao final do arquivo "named.conf" o arquivo "named.conf.projeto", vamos utilizá-lo no decorrer do processo.
 ```
root@asa:/# nano /etc/named.conf
```
```

...
zone "pug.d.local" IN {
        type master;
        file "pug.d.local.zone";                        # zone file path
        allow-query { any; };
};

zone "pugmail.d.local" IN {
        type master;
        file "pugmail.d.local.zone";                        # zone file path
        allow-query { any; };
};

zone "ftppug.d.local" IN {
        type master;
        file "ftppug.d.local.zone";                        # zone file path
        allow-query { any; };
};
...
include "/etc/named.conf.projeto";
```

## Verificando a sintaxe de configuração do BIND

Execute o comando a seguir para verificar a sintaxe dos arquivos named.conf*:
```
root@asa:/# named-checkconf
```
Se o arquivo de configuração nomeado não contiver erros de sintaxe, você retornará ao  prompt do shell e  nenhuma mensagem de erro será exibida.

O comando named-checkzone pode ser usado para verificar a correção dos arquivos de zona.
Por exemplo, para verificar a configuração da zona, execute o seguinte comando (mude os nomes para que correspodem à sua zona e arquivo .zone):
```
root@asa:/# named-checkzone exemplo.com exemplo.com.zone
```
ou 
```
root@asa:/# named-checkzone exemplo.com /var/named/exemplo.com.zone
```

## Reiniciando o BIND

Reinicie o BIND:
```
root@asa:/# systemctl restart bind9
```
ou
```
service named restart
```
ou
```
rndc reload
```