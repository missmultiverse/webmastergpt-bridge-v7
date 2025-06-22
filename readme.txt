
📘 README: WebMasterGPT Bridge (2025+ Lean Mode)
What Does This Plugin Do?
Provisions a dedicated AI (GPT) user for automation, coding, publishing, and admin tasks.

Manages secure API key(s) or token(s) for AI access.

Exposes a single REST endpoint for AI-to-WP actions, with 100% capability enforcement via native WordPress roles.

Lets you control all capabilities via standard user/role plugins (User Role Editor, Members, etc.).

Provides health/status dashboard and one-click repair if the AI user or caps break.

Nothing else. No shadow ACL, no custom restriction logic, no duplicated mapping.

How To Use
Install plugin.

Visit “WebMasterGPT Bridge” in your WP admin.

Sync or repair the GPT user if needed.

Grant or restrict capabilities using your favorite role manager plugin.

Give the API key to your AI agent.

All AI actions are checked natively by WordPress.

Why This Way?
No vendor lock-in: Any role manager works.

Future-proof: Follows core WP standards.

Secure: No new access vectors beyond what you allow.

Auditable: All actions are logged; only the admin sets the rules.

⚡ Minimal Viable Code: Structure
php
Copy
Edit
// --- WebMasterGPT Bridge Loader ---
// 1. Boot identity & keys
require_once __DIR__ . '/includes/identity.php';

// 2. REST endpoint for universal AI access
require_once __DIR__ . '/includes/rest-endpoint.php';

// 3. Health dashboard and repair button
require_once __DIR__ . '/includes/admin-ui.php';

// --- identity.php ---
// Provision AI user, role, and API key. Health/status check.


// --- rest-endpoint.php ---
// One endpoint: /wp-json/webmastergpt/v1/action
// Checks API key → impersonates user → does_action()
// All permissions enforced by WP roles/caps.


// --- admin-ui.php ---
// Tab: Show user/role/caps health. “Repair Identity” button if needed.

🦾 How does access work?
Admin: Full control (manage_options, edit_plugins, etc.)

Webmaster.GPT: Whatever you grant via WP roles/capabilities.

Editor/Publisher: Whatever you grant via WP roles/capabilities.

Restrict: Only via User Role Editor, Members, etc.


WebMasterGPT Bridge: The Only Jobs That Matter
1. Identity Provisioning
Create/maintain the AI user account (WebMaster.GPT, etc.).

Secure credential management:

Issue or rotate API keys, JWT, or other tokens as needed.

Show “boot”/“sync” buttons in admin for one-click fixes (for when the AI user is deleted or broken).

2. Universal Access API Point
Expose a single, well-documented REST API endpoint (or function-calling schema) for all actions.

Authenticate and impersonate as the AI user on every API call.

Pass ALL permissions/capabilities to the native WordPress system—don’t duplicate or shadow anything.

Log activity for security/auditing, but do not block actions yourself.

3. Monitoring & Minimal Oversight
Display real-time status/health of the AI account (is it active, are all required roles/caps present).

Log failed attempts and anomalies, not to block, but to inform the human admin if something breaks or changes unexpectedly.

Everything Else = OUT OF SCOPE
Access control? Use User Role Editor, Members, or whatever the admin likes.

Capability mapping or restriction? Not the plugin’s job—just sync/give “ALL” and let the admin handle the rest.

Custom approval workflows? Only if explicitly requested—otherwise, it’s just bloat.

README Philosophy (for the plugin):
This plugin exists to provision AI agents and broker secure universal access.
Access and restrictions are managed by your WordPress user/role system.
No internal access matrices, capability maps, or approval flows are coded here by default—because that’s what WordPress and your favorite role manager plugins are for!

