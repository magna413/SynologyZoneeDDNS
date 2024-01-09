# Synology Zone.ee DDNS Script 📜

The is a script to be used to add [Zone.ee](https://www.zone.ee/) as a DDNS to [Synology](https://www.synology.com/) NAS. The script uses Zone.eu API [ZoneID API (2.0)](https://api.zone.eu/v2)

## How to use

### Access Synology via SSH

1. Login to your DSM
2. Go to Control Panel > Terminal & SNMP > Enable SSH service
3. Use your client to access Synology via SSH.
4. Use your Synology admin account to connect.

### Run commands in Synology

1. Download `zone_ee.php` from this repository to `/usr/syno/bin/ddns/zone_ee.php`

```
wget https://raw.githubusercontent.com/magna413/SynologyZoneeDDNS/main/zone_ee.php -O /usr/syno/bin/ddns/zone_ee.php
```

It is not a must, you can put I whatever you want. If you put the script in other name or path, make sure you use the right path.

2. Give others execute permission

```
chmod +x /usr/syno/bin/ddns/zone_ee.php
```

3. Add `zone_ee.php` to Synology

```
cat >> /etc.defaults/ddns_provider.conf << 'EOF'
[Zone.ee]
        modulepath=/usr/syno/bin/ddns/zone_ee.php
        queryurl=https://api.zone.eu/v2/dns
EOF
```

`queryurl` does not matter because we are going to use our script but it is needed.

### Get Zone.ee parameters

1. Go to your account security settings and generate API key.

### Setup DDNS

1. Login to your DSM
2. Go to Control Panel > External Access > DDNS > Add
3. Enter the following:
   - Service provider: `Zone.ee`
   - Hostname: `www.example.com`
   - Username/Email: `<Zone.ee username>`
   - Password Key: `<API key>`

### Limitations

1. Only 1 subdomain is allowed:
- example.example.com
- or example.example.co.uk
 
