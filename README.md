PHP l2tp vpn server
=========

This project has been started as my research work about l2tp VPN protocol. I've been trying to implement **RFC2661** standart.
The code is published under the GPLv3. Feel free to use it.

Concern:
---
I'm stacking to developing passing PPP frames to pppd daemon, for now it requires TTY device and , therefore
usage such functions as ptsname, posix_openpt, etc. There is no extensions for PHP providing these functions. 

Ongoing actions:
---

- Implement PPP frames forwarding
- Implement packet loss detection
- Write unit test for Host name AVP
- Write unit test for Unrecognized AVP
- Write unit test for Framing Capabilties AVP
- Write unit test for Protocol Version AVP
- Write unit test for Receive Window Size AVP
- Write unit test for Vendor Name AVP
- Implement Challenge AVP
- Write unit test for Challenge AVP
- Implement Tie Breaker AVP
- Write unit test for Tie Breaker AVP
- Implement Challenge Response AVP
- Write unit test for Challenge Response AVP


Toughts:
---
Finally, I came to an experiment. If I can run and controll by the simple PHP script to instance of PPPD, the one 
configured as a server, and the other as a client in notty mode, this project can be accomplished.
