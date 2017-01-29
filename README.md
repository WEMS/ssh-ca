# CA Signer

---

[![Build Status](https://scrutinizer-ci.com/g/WEMS/ssh-ca/badges/build.png?b=master)](https://scrutinizer-ci.com/g/WEMS/ssh-ca/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/WEMS/ssh-ca/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/WEMS/ssh-ca/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/WEMS/ssh-ca/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/WEMS/ssh-ca/?branch=master)
[![Code Climate](https://codeclimate.com/github/WEMS/ssh-ca/badges/gpa.svg)](https://codeclimate.com/github/WEMS/ssh-ca)

---

HTTP server for signing SSH keys. Receives a public key, returns a signed certificate that can then be used to SSH into
systems that trust the CA.

## Usage

Spin it up and POST requests to it.

Example using the PHP built in server:

```
php -S 0.0.0.0:8002 -t public
```

POST with CURL:

```
 curl -F "user=wems" -F "key=@/home/ben/.ssh/id_rsa.pub" http://localhost:8002/request-cert
```

## Configuration

The configuration is done by a YAML file at `config.config.yml`. A sample configuration is present under `config/config.sample.yml`.

The only required configuration option is the `ca_path`.

### Expiry Times

Expiry times are set with the config option `default_expiry`. It's named `default_expiry` and not just `expiry` as the 
intention is to allow a user to optionally request an expiry time of their own, subject to some checks. For example, we
may allow users with a particular ldap group membership to request a month's certificate.

From the ssh-keygen manual:

> Specify a validity interval when signing a certificate.  A validity interval may consist of a single time, indicating that the certificate is valid beginning now and expiring at that time,
 or may consist of two times separated by a colon to indicate an explicit time interval.  The start time may be specified as a date in YYYYMMDD format, a time in YYYYMMDDHHMMSS format or a
 relative time (to the current time) consisting of a minus sign followed by a relative time in the format described in the TIME FORMATS section of sshd_config(5).  The end time may be spec‐
 ified as a YYYYMMDD date, a YYYYMMDDHHMMSS time or a relative time starting with a plus character.

> For example: “+52w1d” (valid from now to 52 weeks and one day from now), “-4w:+4w” (valid from four weeks ago to four weeks from now), “20100101123000:20110101123000” (valid from 12:30 PM,
 January 1st, 2010 to 12:30 PM, January 1st, 2011), “-1d:20110101” (valid from yesterday to midnight, January 1st, 2011).
