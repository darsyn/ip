## Introduction

IP is an immutable value object for (both version 4 and 6) IP addresses. Several
helper methods are provided for ranges, broadcast and network addresses, subnet
masks, whether an IP is a certain type (defined by RFC's), etc.

Internally, the library converts IP addresses to a binary sequence for easy
mathematical operations and consistency.
You can choose to work with IPv6 addresses as 16-byte binary sequences, IPv4
addresses as 4-byte binary sequences, or work with both interchangeably by
representing IPv4 addresses as 16-byte binary sequences (which is recommended
for most applications).

## License

Please see the [separate license file](../LICENSE.md) included in this repository
for a full copy of the MIT license, which this project is licensed under.

## Authors

- [Zan Baldwin](https://zanbaldwin.com)
- [Jaume Casado Ruiz](http://jau.cat)
- [Pascal Hofmann](http://pascalhofmann.de)

If you make a contribution (submit a pull request), don't forget to add your name here!
