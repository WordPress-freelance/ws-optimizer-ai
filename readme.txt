=== WS SEO Title AI ===
Contributors: webstrategy
Tags: seo, ai, title, claude, artificial intelligence
Requires at least: 6.7
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Analysez vos titres SEO avec Claude directement dans l'éditeur WordPress et obtenez un score, des forces, des problèmes et des recommandations.

== Description ==

WS SEO Title AI intègre l'intelligence artificielle Claude dans votre workflow de rédaction WordPress. Sans quitter l'éditeur, obtenez en un clic une analyse complète de votre titre : score sur 100, verdict synthétique, points forts, problèmes détectés et recommandations d'amélioration.

**Fonctionnalités**

* Metabox native dans l'éditeur d'articles et de pages
* Score SEO de 0 à 100 avec code couleur
* Détection des mots-puissance, chiffres, parenthèses
* Vérification de la longueur optimale (50-60 caractères)
* Compatibilité Yoast SEO et Rank Math (lecture de la keyword focus)
* Résultat mis en cache pour ne pas reconsommer l'API
* Compatible Gutenberg et Classic Editor
* Interface sombre palette WebStrategy

**Prérequis**

Ce plugin utilise WordPress AI Client (`wp_ai_client_prompt`) pour communiquer avec Claude (Anthropic). WordPress AI Client doit être installé, activé et configuré avec votre clé API Anthropic.

== Installation ==

1. Téléverser le plugin via Extensions → Téléverser une extension.
2. Activer via Extensions → Extensions installées.
3. Configurer les types de publication concernés dans Réglages → WS SEO Title AI.
4. S'assurer que WordPress AI Client est configuré avec une clé API Anthropic valide.
5. Ouvrir n'importe quel article ou page — la metabox "Analyse Titre SEO (IA)" apparaît dans la colonne latérale.

== Frequently Asked Questions ==

= Quel modèle Claude est utilisé ? =

Par défaut Claude Opus 4.6. Vous pouvez passer à Claude Sonnet 4.6 dans Réglages → WS SEO Title AI.

= L'analyse est-elle refaite à chaque ouverture de l'article ? =

Non. Le résultat est mis en cache en tant que méta du post. Cliquez sur "Ré-analyser" pour déclencher une nouvelle analyse.

= Le plugin est-il compatible avec Yoast SEO et Rank Math ? =

Oui. Si une de ces extensions est active, le plugin récupère automatiquement la keyword focus et l'inclut dans le prompt envoyé à Claude.

= Pourquoi le bouton ne fonctionne-t-il pas ? =

Vérifiez que WordPress AI Client est installé et qu'une clé API Anthropic valide est renseignée. En cas d'erreur, le message s'affiche directement dans la metabox.

== Screenshots ==

1. Metabox "Analyse Titre SEO" dans l'éditeur d'articles avec score et recommandations.
2. Page de réglages (Réglages → WS SEO Title AI) — types de publication et modèle.
3. Résultat détaillé avec atouts, problèmes et recommandations.

== Changelog ==

= 1.0.0 =
* Version initiale.
