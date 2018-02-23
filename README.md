[![Build Status](https://travis-ci.org/darsyn/ip.svg?branch=master)](https://travis-ci.org/darsyn/ip)

IP is an immutable value object for (both version 4 and 6) IP addresses. Several
helper methods are provided for ranges, broadcast and network addresses, subnet
masks, whether an IP is a certain type (defined by RFC's), etc.

# Where's the documentation?

This is the alpha release for `4.0.0` before the stable release. It is not
intended for production. Please see the
[`master` branch](https://github.com/darsyn/ip/tree/master) for the latest
stable release and documentation.

Full documentation will be written for the stable release of `4.0.0`. You can
follow along with the progress of documentation in [`docs/`](docs/).

### Todo

- [ ] Write complete documentation
- [ ] Write more unit tests.

# I want to test `4.0.0-alpha`!

Use this library as you did with `3.3.1` using the class
[`Darsyn\IP\IP`](src/IP.php) since the defaults should behave *mostly* the same
(some methods have been renamed and the default for representing IPv4 addresses
internally has changed to IPv4-mapped).

Otherwise, play around with the following classes:
[`IPv4`](src/Version/IPv4.php), [`IPv6`](src/Version/IPv6.php), and
[`Multi`](src/Version/Multi.php).

And check out the classes in the following namespaces:

- [`Darsyn\IP\Formatter`](src/Formatter)
- [`Darsyn\IP\Strategy`](src/Strategy)

# License

Please see the [separate license file](LICENSE.md) included in this repository
for a full copy of the MIT license, which this project is licensed under.

# Authors

- [Zan Baldwin](https://zanbaldwin.com)
- [Jaume Casado Ruiz](http://jau.cat)
- [Pascal Hofmann](http://pascalhofmann.de)

If you make a contribution (submit a pull request), don't forget to add your
name here!
