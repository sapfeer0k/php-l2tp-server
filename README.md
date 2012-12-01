PHP l2tp vpn server
=========

This project has been started as my research work about l2tp VPN protocol. I've tried to implement **RFC2661** standart. The code is published under the GPLv3. Feel free to use it.

Ongoing actions:
---

- Create class l2tp_assigned_tunnel_id_avp
- Create class for Receive Window Size AVP
- Create class for Challenge AVP
- Create class for Tie Breaker AVP
- Create class for Vendor Name
- Create class for Firmware Revision
- Implement encode method for the erlier mentioned classes
- Write Unit-tests for all these classes

Global actions:
---

- Implement classes for **ALL** RFC's defined AVPs
- Implement handling control messages for tunnels
- Implement handling control messages for session
- Implement handling data messages
- Implement PPP-frames transmission/retransmission
