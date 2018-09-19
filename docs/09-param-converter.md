## Parameter Conversion

If you are using Symfony's Framework Bundle, a parameter converter is included to
automatically convert an IP string into an IP object.

It's unlikely you'll ever need to get an IP address from the route instead of the
`Request` object (aside from administration dashboards perhaps), but the option
is there if you need it. 

To enabled it, make `Darsyn\IP\ParamConverter` a service in your container
configuration and tag it with `request.param_converter`:

```yaml
services:

    darsyn.ip.param_converter:
        class: 'Darsyn\IP\ParamConverter'
        tags:
            - name: 'request.param_converter'
              converter: 'darsyn_ip_converter'
```
