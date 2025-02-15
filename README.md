### To setup a VyOS router for VyConnect, you need to setup the REST API:

### Execute in VyOS CLI:
set service https api keys id vyconnect key YOUR_SECRET_KEY
set service https api rest
commit && save

Also make sure to have Ethernet interfaces, speed and duplex set

https://docs.vyos.io/en/equuleus/automation/vyos-api.html#show

https://docs.vyos.io/en/latest/configuration/service/https.html

⚠️ Make sure to copy "default.config.json" to "config.json" and put in your desired configuration values!

Default pass: vyconnect