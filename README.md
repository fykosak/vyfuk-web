# vyfuk-web
Dokuwiki powered website built for Výfuk contest

### Instalation:
1. Download and install Apache enviroment
2. Set domain in `/etc/hosts` (Linux) or `C:\Windows\System32\drivers\etc\hosts` (Windows), e.g.:
```
127.0.0.1 vyfuk.local
```
3. Configure virtual hosts in `/etc/apache/sites-enabled` (Linux) or `XAMPP\Apache\conf\extra\httpd-vhosts.conf` (Windows), e.g.:
```
<VirtualHost *:80>
    DocumentRoot "Enter root path"
    ServerName vyfuk.local
</VirtualHost>
```
4. Enter *http://vyfuk.local* in your browser
5. Profit
