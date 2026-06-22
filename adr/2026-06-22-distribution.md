# Distribution

## Context

While developing a first version, Orejime was loaded from a CDN.

This violates 3 plugin guidelines which were brougth up during review:

> **Phoning Home / Collecting User Data Without Opt-In Consent**: Frontend visitors are forced to load Orejime JS/CSS from the external jsDelivr CDN via hardcoded URLs with no local fallback or explicit opt-in, causing unsolicited third-party requests.

> **Calling files remotely**:
Please remove external dependencies from your plugin and, if possible, include all files within the plugin (that is not called remotely). If instead you feel you are providing a service, please re-write your readme.txt in a manner that explains the service, the servers being called, and if any account is needed to connect.

> **Undocumented use of a 3rd Party / external service**:
Plugins are permitted to require the use of third party/external services as long as they are clearly documented.
When your plugin reach out to external services, you must disclose it. This is true even if you are the one providing that service.

## Decision

We're distributing the build files with the plugin so we don't rely on a CDN anymore.
