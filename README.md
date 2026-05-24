# WS SEO Title AI

[![Unit Tests](https://github.com/WordPress-freelance/ws-optimizer-ai/actions/workflows/tests.yml/badge.svg)](https://github.com/WordPress-freelance/ws-optimizer-ai/actions/workflows/tests.yml)

Plugin WordPress FREE qui analyse vos titres SEO avec Claude AI directement dans l'éditeur de contenu, via WordPress AI Client (WordPress 7.0+).

## Prérequis

- WordPress 7.0+
- Module AI Client activé avec clé API Anthropic

## Fonctionnalités

- Metabox dans l'éditeur (Gutenberg + Classic Editor) : score /100, verdict, points forts, problèmes, recommandations
- Analyse via `claude-opus-4-6` par défaut (configurable)
- Cache du dernier résultat en post meta — pas d'appel API au chargement
- Prise en charge des keywords focus Yoast SEO et Rank Math
- Page Réglages pour configurer les types de publication et le modèle

## Installation

1. Téléverser le ZIP via WP Admin → Plugins → Téléverser une extension
2. Activer
3. Configurer l'AI Client sous Réglages → AI Client (clé API Anthropic)
4. Ouvrir un article — la metabox "Analyse Titre SEO (IA)" apparaît dans la sidebar

## Tests

### Unitaires (WP_Mock)

```bash
# Dépendances (hors Composer — proxy Claude)
mkdir -p vendor/10up vendor/mockery vendor/antecedent vendor/hamcrest
git clone --depth 1 --branch 0.5.0 https://github.com/10up/wp_mock.git vendor/10up/wp_mock
git clone --depth 1 --branch 1.6.12 https://github.com/mockery/mockery.git vendor/mockery/mockery
git clone --depth 1 --branch 2.1.27 https://github.com/antecedent/patchwork.git vendor/antecedent/patchwork
git clone --depth 1 https://github.com/hamcrest/hamcrest-php.git vendor/hamcrest/hamcrest-php

phpunit -c phpunit.xml
```

**50 tests / 88 assertions**

### Intégration BDD (CI uniquement)

```bash
bash bin/install-wp-tests.sh wp_test root root 127.0.0.1 latest
phpunit -c phpunit-integration.xml
```

## Structure

```
ws-optimizer-ai/
├── admin/               # Classe Admin, CSS, JS, partials
├── includes/            # Orchestrateur, Loader, Analyzer, i18n
├── languages/           # .pot + .po/.mo (de_DE, en_US, es_ES)
├── public/              # Classe Public (front vide)
├── tests/unit/          # PHPUnit + WP_Mock
└── ws-optimizer-ai.php  # Point d'entrée
```
