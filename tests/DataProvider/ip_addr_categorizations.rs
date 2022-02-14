#![feature(ip)]
use std::net::Ipv4Addr;
use std::net::Ipv6Addr;
use std::net::Ipv6MulticastScope;

/*******************************************************\
| WHY?                                                  |
| Because I trust the Rust developer team a lot more    |
| than my ability to correctly understand complex RFCs. |
\*******************************************************/

fn get_ipv4_addresses() -> Vec<&'static str> {
    vec![
        "10.9.8.7",
        "127.1.2.3",
        "172.31.254.253",
        "169.254.253.242",
        "192.0.2.183",
        "192.1.2.183",
        "192.168.254.253",
        "198.51.100.0",
        "203.0.113.0",
        "203.2.113.0",
        "255.255.255.255",
        "198.18.0.0",
        "198.18.54.2",
        "198.19.255.255",
        "224.0.0.0",
        "239.255.255.255",
        "0.0.0.0",
        "10.0.0.0",
        "10.255.255.255",
        "172.16.0.0",
        "172.31.255.255",
        "192.168.0.0",
        "192.168.255.255",
        "127.0.0.0",
        "127.255.255.255",
        "169.254.0.0",
        "169.254.255.255",
        // Randomly generated.
        "129.129.154.203",
        "239.248.153.114",
        "85.101.159.135",
        "72.64.156.77",
        "162.199.210.167",
        "2.12.191.95",
        "83.125.176.74",
        "224.0.65.129",
    ]
}

fn get_ipv6_addresses() -> Vec<&'static str> {
    vec![
        "::",
        "::0",
        "::1",
        "::0.0.0.2",
        "1::",
        "fc00::",
        "fdff:ffff::",
        "fe80:ffff::",
        "fe80::",
        "febf:ffff::",
        "febf::",
        "febf:ffff:ffff:ffff:ffff:ffff:ffff:ffff",
        "fe80::ffff:ffff:ffff:ffff",
        "fe80:0:0:1::",
        "fec0::",
        "ff01::",
        "ff02::",
        "ff03::",
        "ff04::",
        "ff05::",
        "ff08::",
        "ff0e::",
        "2001:db8:85a3::8a2e:370:7334",
        "2001:2::ac32:23ff:21",
        "102:304:506:708:90a:b0c:d0e:f10",
        "fd00::",
        "fdff:ffff:ffff:ffff:ffff:ffff:ffff:ffff",
        "ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff",
        "::ffff:1:0",
        "::ffff:7f00:1",
        "::ffff:1234:5678",
        "0000:0000:0000:0000:0000:ffff:7f00:a001",
        "2002::",
        "2002:7f00:1::",
        "2002:1234:4321:0:00:000:0000::",
        "::7f00:1",
        "::12.34.56.78",
        "0::000:0000:b12:cab",
        // Randomly generated.
        "1cc9:7d7f:2a9f:cabd:9186:2be5:bef1:6a54",
        "b638:cc70:716:c4d4:f69c:4ee3:6c65:a0b2",
        "140c:12f1:6e6f:c0bb:980e:3816:3e52:1193",
        "7a30:bf4:4c6c:8dc1:e340:774d:6487:3822",
        "6af8:1ceb:eaae:104a:829c:e76e:5802:13f8",
        "3e48:c9fd:c569:f5dd:ee36:8075:691b:8234",
        "cab2:4f27:790f:cf03:5241:9eff:aba5:bb5c",
        "e896:8866:872b:bd4f:6d60:7aa8:ebe5:36f1",
    ]
}

fn main() {
    // cargo +nightly run
    println!("<?php");
    println!("return [");
    print_ipv4_config();
    println!("");
    print_ipv6_config();
    println!("];");
}

fn print_ipv4_config() {
    let ipv4_addresses = get_ipv4_addresses();
    println!("    // Ipv4 Addresses");
    for ipv4_str in ipv4_addresses {
        let mut attr: Vec<String> = vec![];
        let ipv4_addr: Ipv4Addr = match ipv4_str.parse::<Ipv4Addr>() {
            Ok(address) => address,
            Err(_) => panic!("\"{}\" is not a valid IPv4 address.", ipv4_str),
        };

        if ipv4_addr.is_unspecified() { attr.push("UNSPECIFIED".to_string()); }
        if ipv4_addr.is_loopback() { attr.push("LOOPBACK".to_string()); }
        if ipv4_addr.is_private() { attr.push("PRIVATE_USE".to_string()); }
        if ipv4_addr.is_link_local() { attr.push("LINK_LOCAL".to_string()); }
        if ipv4_addr.is_global() { attr.push("PUBLIC_USE".to_string()); }
        if ipv4_addr.is_benchmarking() { attr.push("BENCHMARKING".to_string()); }
        if ipv4_addr.is_documentation() { attr.push("DOCUMENTATION".to_string()); }
        if ipv4_addr.is_broadcast() { attr.push("BROADCAST".to_string()); }
        if ipv4_addr.is_multicast() { attr.push("MULTICAST_IPV4".to_string()); }
        if ipv4_addr.is_shared() { attr.push("SHARED".to_string()); }
        if ipv4_addr.is_reserved() { attr.push("RESERVED".to_string()); }

        let constants: Vec<String> = attr.iter().map(| constant | format!("self::{}", constant)).collect();
        let list: String = if constants.len() == 0 { "0".to_string() } else {constants.join(" | ") };
        println!("    '{}' => {},", ipv4_str, list);
    }
}

fn print_ipv6_config() {
    let ipv6_addresses = get_ipv6_addresses();
    println!("    // Ipv6 Addresses");
    for ipv6_str in ipv6_addresses {
        let mut attr: Vec<String> = vec![];
        let ipv6_addr: Ipv6Addr = match ipv6_str.parse::<Ipv6Addr>() {
            Ok(address) => address,
            Err(_) => panic!("\"{}\" is not a valid IPv6 address.", ipv6_str),
        };

        if ipv6_addr.is_unspecified() { attr.push("UNSPECIFIED".to_string()); }
        if ipv6_addr.is_loopback() { attr.push("LOOPBACK".to_string()); }
        match ipv6_addr.segments()[0] & 0xff00 {
            0xfd00 => attr.push("PRIVATE_USE".to_string()),
            _ => (),
        }
        if ipv6_addr.is_unicast_link_local() { attr.push("LINK_LOCAL".to_string()); }
        if ipv6_addr.is_global() { attr.push("PUBLIC_USE".to_string()); }
        if ipv6_addr.is_benchmarking() { attr.push("BENCHMARKING".to_string()); }
        if ipv6_addr.is_documentation() { attr.push("DOCUMENTATION".to_string()); }
        // BROADCAST
        if ipv6_addr == Ipv6Addr::new(0xffff, 0xffff, 0xffff, 0xffff, 0xffff, 0xffff, 0xffff, 0xffff) {
            attr.push("BROADCAST".to_string());
        }
        if ipv6_addr.is_multicast() {
            match ipv6_addr.multicast_scope() {
                Some(Ipv6MulticastScope::InterfaceLocal) => attr.push("MULTICAST_INTERFACE_LOCAL".to_string()),
                Some(Ipv6MulticastScope::LinkLocal) => attr.push("MULTICAST_LINK_LOCAL".to_string()),
                Some(Ipv6MulticastScope::RealmLocal) => attr.push("MULTICAST_REALM_LOCAL".to_string()),
                Some(Ipv6MulticastScope::AdminLocal) => attr.push("MULTICAST_ADMIN_LOCAL".to_string()),
                Some(Ipv6MulticastScope::SiteLocal) => attr.push("MULTICAST_SITE_LOCAL".to_string()),
                Some(Ipv6MulticastScope::OrganizationLocal) => attr.push("MULTICAST_ORGANIZATION_LOCAL".to_string()),
                Some(Ipv6MulticastScope::Global) => attr.push("MULTICAST_GLOBAL".to_string()),
                _ => attr.push("MULTICAST_OTHER".to_string()),
            }
        }
        if ipv6_addr.is_unique_local() { attr.push("UNIQUE_LOCAL".to_string()); }
        if ipv6_addr.is_unicast() {
            if ipv6_addr.is_unicast_global() {
                attr.push("UNICAST_GLOBAL".to_string());
            } else {
                attr.push("UNICAST_OTHER".to_string());
            }
        }

        // Embedding Strategies:
        if ipv6_addr.segments()[0] & 0xffff == 0x2002
            && ipv6_addr.segments()[3] & 0xffff == 0
            && ipv6_addr.segments()[4] & 0xffff == 0
            && ipv6_addr.segments()[5] & 0xffff == 0
            && ipv6_addr.segments()[6] & 0xffff == 0
            && ipv6_addr.segments()[7] & 0xffff == 0 {
            attr.push("DERIVED".to_string());
        }
        if ipv6_addr.segments()[0] & 0xffff == 0
            && ipv6_addr.segments()[1] & 0xffff == 0
            && ipv6_addr.segments()[2] & 0xffff == 0
            && ipv6_addr.segments()[3] & 0xffff == 0
            && ipv6_addr.segments()[4] & 0xffff == 0
            && ipv6_addr.segments()[5] & 0xffff == 0 {
            attr.push("COMPATIBLE".to_string());
        }
        if ipv6_addr.segments()[0] & 0xffff == 0
            && ipv6_addr.segments()[1] & 0xffff == 0
            && ipv6_addr.segments()[2] & 0xffff == 0
            && ipv6_addr.segments()[3] & 0xffff == 0
            && ipv6_addr.segments()[4] & 0xffff == 0
            && ipv6_addr.segments()[5] & 0xffff == 0xffff {
            attr.push("MAPPED".to_string());
        }

        let constants: Vec<String> = attr.iter().map(| constant | format!("self::{}", constant)).collect();
        let list: String = if constants.len() == 0 { "0".to_string() } else {constants.join(" | ") };
        println!("    '{}' => {},", ipv6_str, list);
    }
}
