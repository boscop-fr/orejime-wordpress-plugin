# Purpose IDs

## Context

An effort was put in separating the IDs of integrations and terms.
This was done to keep the architecture clean and decoupled, and to avoid disclosing integrations on the front-end through their deterministic, human-readable identifiers.

This works well almost all of the time, but breaks in edge cases.
For example, the matomo plugin would sometimes regenerate tracking codes very early, at a time when the integration wouldn't have its purpose id set, thus breaking the output.

## Notes

* Integrations are already shouting their names within HTML comments, so there is probably no point in hiding their ids. Also, we could provide a filter to obfuscate them if needed.
* Introducing timing issues is a slippery slope in an event-driven architecture.
* Removing the link between those identifiers would further decouple parts of the code.

## Decision

We're decoupling integrations and terms.
Terms would be referencing integrations by slug, instead of integrations holding a reference to term ids.
