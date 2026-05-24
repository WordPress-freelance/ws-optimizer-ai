=== WS SEO Title AI ===
Contributors: webstrategy
Tags: seo, ai, title, claude, artificial-intelligence
Requires at least: 6.7
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Analyze your SEO titles with Claude AI directly in the WordPress post editor. Score, verdict, strengths, issues and recommendations in one click.

== Description ==

**WS SEO Title AI** adds a metabox to your post editor (Gutenberg and Classic Editor) that analyses your SEO titles using Claude AI via the WordPress AI Client (WordPress 7.0+).

**Features:**

* Score out of 100, verdict, strengths, issues and actionable recommendations
* Supports Yoast SEO and Rank Math focus keywords
* Results cached in post meta — no API call on page load
* Configurable Claude model (Opus 4.6 / Sonnet 4.6)
* Compatible with all public post types

**Requirements:**

* WordPress 7.0+ with the AI Client module enabled
* Anthropic API key configured under Settings → AI Client

== Installation ==

1. Upload the plugin ZIP via WP Admin → Plugins → Add New → Upload Plugin.
2. Activate the plugin.
3. Go to Settings → AI Client and enter your Anthropic API key.
4. Open any post or page — the "SEO Title Analysis (AI)" metabox appears in the sidebar.
5. Optionally configure post types and model under Settings → WS SEO Title AI.

== Frequently Asked Questions ==

= Does this plugin work without WordPress 7.0? =
No. The plugin relies on `wp_ai_client_prompt()`, introduced in WordPress 7.0. It will display a notice if the function is unavailable.

= Which Claude models are supported? =
Claude Opus 4.6 (default) and Claude Sonnet 4.6. Both can be selected under Settings → WS SEO Title AI.

= Is the analysis run on every page load? =
No. The last analysis result is cached in post meta. A new API call is only made when you click the "Analyze" or "Re-analyze" button.

= Does it support Yoast SEO and Rank Math? =
Yes. If a focus keyword is set in either plugin, it is passed to Claude as part of the analysis context.

== Screenshots ==

1. Metabox in the post editor showing score, verdict and recommendations.
2. Detailed analysis with strengths, issues and improvement suggestions.
3. Settings page — post types and Claude model selection.

== Changelog ==

= 1.0.0 =
* Initial release.
