---
name: virtual-patch
description: Temporarily mitigate an identified PHP risk through a Go reverse proxy, implementing the protocol, identity, or transformation support required by the affected feature during migration.
---

# Analysis-Driven Virtual Patching

Invoke this Skill only when an identified PHP risk needs temporary mitigation during migration. It is not a required migration stage or a generic routing task. Reuse valid analysis, contracts, and fixtures for the affected feature; obtain missing evidence without running every Skill.

Read [references/patch-plan.md](references/patch-plan.md), which owns capability planning, production readiness, and patch lifecycle. Read the [Evidence Protocol](../equivalence-check/references/evidence-protocol.md) for artifact reuse, verdicts, and security change records. Proxy source/configuration and tests are target-project artifacts; no shared proxy binary from this repository is required.

## Procedure

1. Select the feature and finding. Link existing source/data-flow evidence, contract, and fixtures, and record the intended mitigation and permanent Go fix.
2. Inventory the feature's protocol, origin, identity, and state requirements. Identify existing library/ingress support and missing integration, and turn gaps into implementation tasks under the Patch Plan. Requirements beyond ordinary HTTP are not grounds to exclude the feature.
3. Prepare a Patch Plan selecting the required actions, route matching, expected behavior delta, capability work, and lifecycle decisions. Assess legitimate requests affected by the mitigation.
4. Implement the selected mitigation and capabilities needed to preserve the feature. Start with simple forwarding/routing where sufficient and reuse existing components for specialized behavior. Avoid unrelated gateway subsystems, but do not stop necessary feature work merely because it exceeds the first increment.
5. Execute the relevant verification matrix in isolation, reusing valid fixtures. Attach comparison results, security change records, and the production readiness decision. Resolve capability gaps before cutover while preserving a verified existing path where possible.
6. Prepare the tested implementation, configuration, and manual rollback procedure for review. Deploy only within the user's authorized scope; invoking this Skill does not authorize live infrastructure changes.
7. When the protected feature's Go replacement is ready, verify the permanent fix and perform the Patch Plan's cutover and joint rule/PHP retirement procedure. That later work need not be completed to deliver an interim patch.

## Deliverables

Deliver a feature/finding-linked Patch Plan, selected action and implementation, reusable evidence references, support gaps and rollout decision, and a removal task tied to the permanent Go fix. The plan owns lifecycle requirements; this Skill does not add a second policy checklist.
