"""Génère le .pot et les .po/.mo pour de_DE, en_US, es_ES.

Usage : python3 generate_translations.py
"""
import re, subprocess
from pathlib import Path

PLUGIN_ROOT = Path(__file__).parent
SLUG        = "ws-optimizer-ai"
LANGUAGES   = PLUGIN_ROOT / "languages"
LANGUAGES.mkdir(exist_ok=True)

LOCALES = {
    "de_DE": "Allemand",
    "en_US": "Anglais",
    "es_ES": "Espagnol",
}

TRANSLATIONS = {
    "Automatique (réglage WordPress)": {
        "de_DE": "Automatisch (WordPress-Einstellung)",
        "en_US": "Automatic (WordPress setting)",
        "es_ES": "Automático (ajuste de WordPress)",
    },
    "Moteur IA": {
        "de_DE": "KI-Engine",
        "en_US": "AI engine",
        "es_ES": "Motor de IA",
    },
    "Réponse IA invalide.": {
        "de_DE": "Ungültige KI-Antwort.",
        "en_US": "Invalid AI response.",
        "es_ES": "Respuesta de IA no válida.",
    },
    "%d entrée(s)": {
        "de_DE": "%d Eintrag(e)",
        "en_US": "%d entry(ies)",
        "es_ES": "%d entrada(s)",
    },
    "AI Logs": {
        "de_DE": "KI-Logs",
        "en_US": "AI Logs",
        "es_ES": "Registros de IA",
    },
    "Aucune entrée. Activez la capture puis lancez une analyse depuis un article.": {
        "de_DE": "Keine Einträge. Aktivieren Sie die Erfassung und starten Sie dann eine Analyse in einem Beitrag.",
        "en_US": "No entries. Enable capture, then run an analysis from a post.",
        "es_ES": "Sin entradas. Activa la captura y luego ejecuta un análisis desde una entrada.",
    },
    "Capturer les logs IA": {
        "de_DE": "KI-Logs erfassen",
        "en_US": "Capture AI logs",
        "es_ES": "Capturar registros de IA",
    },
    "Documentation": {
        "de_DE": "Dokumentation",
        "en_US": "Documentation",
        "es_ES": "Documentación",
    },
    "Enregistrer": {
        "de_DE": "Speichern",
        "en_US": "Save",
        "es_ES": "Guardar",
    },
    "Réglage enregistré.": {
        "de_DE": "Einstellung gespeichert.",
        "en_US": "Setting saved.",
        "es_ES": "Ajuste guardado.",
    },
    "Réglages": {
        "de_DE": "Einstellungen",
        "en_US": "Settings",
        "es_ES": "Ajustes",
    },
    "Réglages sauvegardés.": {
        "de_DE": "Einstellungen gespeichert.",
        "en_US": "Settings saved.",
        "es_ES": "Ajustes guardados.",
    },
    "Sauvegarder les réglages": {
        "de_DE": "Einstellungen speichern",
        "en_US": "Save settings",
        "es_ES": "Guardar ajustes",
    },
    "Vider les logs": {
        "de_DE": "Logs leeren",
        "en_US": "Clear logs",
        "es_ES": "Vaciar registros",
    },
    "Analyse Titre (Claude)": {
        "de_DE": "Titel-Analyse (Claude)",
        "en_US": "Title Analysis (Claude)",
        "es_ES": "Análisis de título (Claude)",
    },
    "Ajoute un titre pour l'analyser.": {
        "de_DE": "Füge einen Titel hinzu, um ihn zu analysieren.",
        "en_US": "Add a title to analyze it.",
        "es_ES": "Añade un título para analizarlo.",
    },
    "Analyser le titre": {
        "de_DE": "Titel analysieren",
        "en_US": "Analyze title",
        "es_ES": "Analizar título",
    },
    "Ré-analyser": {
        "de_DE": "Neu analysieren",
        "en_US": "Re-analyze",
        "es_ES": "Volver a analizar",
    },
    "Analyse en cours…": {
        "de_DE": "Analyse läuft…",
        "en_US": "Analyzing…",
        "es_ES": "Analizando…",
    },
    "Erreur lors de l'analyse.": {
        "de_DE": "Fehler bei der Analyse.",
        "en_US": "Error during analysis.",
        "es_ES": "Error durante el análisis.",
    },
    "Forces": {
        "de_DE": "Stärken",
        "en_US": "Strengths",
        "es_ES": "Puntos fuertes",
    },
    "Problèmes": {
        "de_DE": "Probleme",
        "en_US": "Issues",
        "es_ES": "Problemas",
    },
    "Recommandations": {
        "de_DE": "Empfehlungen",
        "en_US": "Recommendations",
        "es_ES": "Recomendaciones",
    },
    "WordPress 7.0 avec AI Client requis": {
        "de_DE": "WordPress 7.0 mit AI Client erforderlich",
        "en_US": "WordPress 7.0 with AI Client required",
        "es_ES": "Se requiere WordPress 7.0 con AI Client",
    },
    "Réponse Claude invalide": {
        "de_DE": "Ungültige Claude-Antwort",
        "en_US": "Invalid Claude response",
        "es_ES": "Respuesta de Claude inválida",
    },
    "Nonce invalide.": {
        "de_DE": "Ungültiges Nonce.",
        "en_US": "Invalid nonce.",
        "es_ES": "Nonce inválido.",
    },
    "Permission refusée.": {
        "de_DE": "Berechtigung verweigert.",
        "en_US": "Permission denied.",
        "es_ES": "Permiso denegado.",
    },
    "Le titre est requis.": {
        "de_DE": "Der Titel ist erforderlich.",
        "en_US": "Title is required.",
        "es_ES": "El título es obligatorio.",
    },
    "Erreur lors de l'appel à Claude.": {
        "de_DE": "Fehler beim Claude-Aufruf.",
        "en_US": "Error calling Claude.",
        "es_ES": "Error al llamar a Claude.",
    },
    "WS SEO Title AI": {
        "de_DE": "WS SEO Title AI",
        "en_US": "WS SEO Title AI",
        "es_ES": "WS SEO Title AI",
    },
    "Types de contenu analysés": {
        "de_DE": "Analysierte Inhaltstypen",
        "en_US": "Analyzed content types",
        "es_ES": "Tipos de contenido analizados",
    },
    "Modèle Claude": {
        "de_DE": "Claude-Modell",
        "en_US": "Claude model",
        "es_ES": "Modelo Claude",
    },
    "Réglages — WS SEO Title AI": {
        "de_DE": "Einstellungen — WS SEO Title AI",
        "en_US": "Settings — WS SEO Title AI",
        "es_ES": "Ajustes — WS SEO Title AI",
    },
    "Enregistrer les modifications": {
        "de_DE": "Änderungen speichern",
        "en_US": "Save Changes",
        "es_ES": "Guardar cambios",
    },
    "Ce plugin nécessite WordPress 7.0+ avec le module AI Client activé et une clé API Anthropic configurée.": {
        "de_DE": "Dieses Plugin erfordert WordPress 7.0+ mit aktiviertem AI-Client-Modul und konfiguriertem Anthropic-API-Schlüssel.",
        "en_US": "This plugin requires WordPress 7.0+ with the AI Client module enabled and an Anthropic API key configured.",
        "es_ES": "Este plugin requiere WordPress 7.0+ con el módulo AI Client activado y una clave API de Anthropic configurada.",
    },
    "Configurer le AI Client": {
        "de_DE": "AI Client konfigurieren",
        "en_US": "Configure AI Client",
        "es_ES": "Configurar AI Client",
    },
    "Accès refusé.": {
        "de_DE": "Zugriff verweigert.",
        "en_US": "Access denied.",
        "es_ES": "Acceso denegado.",
    },
    "Analyse Titre SEO (IA)": {
        "de_DE": "SEO-Titel-Analyse (KI)",
        "en_US": "SEO Title Analysis (AI)",
        "es_ES": "Análisis de título SEO (IA)",
    },
    "Analysé : « %s »": {
        "de_DE": 'Analysiert: \u201e%s\u201c',
        "en_US": 'Analyzed: "%s"',
        "es_ES": "Analizado: \u00ab%s\u00bb",
    },
    "Atouts": {
        "de_DE": "Stärken",
        "en_US": "Strengths",
        "es_ES": "Puntos fuertes",
    },
    "Ce plugin nécessite WordPress avec le module AI Client activé et un compte Anthropic configuré (Claude).": {
        "de_DE": "Dieses Plugin erfordert WordPress mit aktiviertem AI-Client-Modul und konfiguriertem Anthropic-Konto (Claude).",
        "en_US": "This plugin requires WordPress with the AI Client module enabled and an Anthropic account configured (Claude).",
        "es_ES": "Este plugin requiere WordPress con el módulo AI Client activado y una cuenta Anthropic configurada (Claude).",
    },
    "Configuration générale": {
        "de_DE": "Allgemeine Einstellungen",
        "en_US": "General Settings",
        "es_ES": "Configuración general",
    },
    "Documentation →": {
        "de_DE": "Dokumentation →",
        "en_US": "Documentation →",
        "es_ES": "Documentación →",
    },
    "Enregistrer les réglages": {
        "de_DE": "Einstellungen speichern",
        "en_US": "Save Settings",
        "es_ES": "Guardar ajustes",
    },
    "Le titre est vide.": {
        "de_DE": "Der Titel ist leer.",
        "en_US": "Title is empty.",
        "es_ES": "El título está vacío.",
    },
    "Non définie": {
        "de_DE": "Nicht definiert",
        "en_US": "Not defined",
        "es_ES": "No definida",
    },
    "Prérequis": {
        "de_DE": "Voraussetzungen",
        "en_US": "Requirements",
        "es_ES": "Requisitos previos",
    },
    "Réponse Claude invalide.": {
        "de_DE": "Ungültige Claude-Antwort.",
        "en_US": "Invalid Claude response.",
        "es_ES": "Respuesta de Claude inválida.",
    },
    "Types de publication": {
        "de_DE": "Beitragstypen",
        "en_US": "Post types",
        "es_ES": "Tipos de entrada",
    },
    "WordPress AI Client requis (WordPress 7.0+).": {
        "de_DE": "WordPress AI Client erforderlich (WordPress 7.0+).",
        "en_US": "WordPress AI Client required (WordPress 7.0+).",
        "es_ES": "Se requiere WordPress AI Client (WordPress 7.0+).",
    },
    "Modèle utilisé pour l'analyse via WordPress AI Client.": {
        "de_DE": "Modell für die Analyse über WordPress AI Client.",
        "en_US": "Model used for analysis via WordPress AI Client.",
        "es_ES": "Modelo utilizado para el análisis mediante WordPress AI Client.",
    },
    "Analysez vos titres SEO avec Claude directement dans l'éditeur.": {
        "de_DE": "Analysieren Sie Ihre SEO-Titel mit Claude direkt im Editor.",
        "en_US": "Analyze your SEO titles with Claude directly in the editor.",
        "es_ES": "Analice sus títulos SEO con Claude directamente en el editor.",
    },
    "Ajoutez d'abord un titre.": {
        "de_DE": "Fügen Sie zuerst einen Titel hinzu.",
        "en_US": "Add a title first.",
        "es_ES": "Añade primero un título.",
    },
    "Erreur lors de l'appel à Claude : %s": {
        "de_DE": "Fehler beim Claude-Aufruf: %s",
        "en_US": "Error calling Claude: %s",
        "es_ES": "Error al llamar a Claude: %s",
    },
}


def extract_strings():
    patterns = [
        r"__\(\s*['\"]([^'\"]+)['\"]\s*,\s*['\"]" + re.escape(SLUG) + r"['\"]\s*\)",
        r"_e\(\s*['\"]([^'\"]+)['\"]\s*,\s*['\"]" + re.escape(SLUG) + r"['\"]\s*\)",
        r"esc_html__\(\s*['\"]([^'\"]+)['\"]\s*,\s*['\"]" + re.escape(SLUG) + r"['\"]\s*\)",
        r"esc_html_e\(\s*['\"]([^'\"]+)['\"]\s*,\s*['\"]" + re.escape(SLUG) + r"['\"]\s*\)",
    ]
    found = set()
    for php_file in PLUGIN_ROOT.rglob("*.php"):
        if "vendor/" in str(php_file) or "/tests/" in str(php_file):
            continue
        content = php_file.read_text(encoding="utf-8", errors="ignore")
        for pat in patterns:
            for m in re.finditer(pat, content):
                found.add(m.group(1))
    return sorted(found)


def write_pot(strings):
    pot = LANGUAGES / f"{SLUG}.pot"
    lines = [
        f"# Translation template for {SLUG}",
        'msgid ""', 'msgstr ""',
        f'"Project-Id-Version: {SLUG}\\n"',
        '"Content-Type: text/plain; charset=UTF-8\\n"',
        f'"X-Domain: {SLUG}\\n"', '',
    ]
    for s in strings:
        e = s.replace('\\', '\\\\').replace('"', '\\"')
        lines += [f'msgid "{e}"', 'msgstr ""', '']
    pot.write_text('\n'.join(lines), encoding='utf-8')


def write_po(locale, strings):
    po = LANGUAGES / f"{SLUG}-{locale}.po"
    lines = [
        f"# {LOCALES[locale]} translation of {SLUG}",
        'msgid ""', 'msgstr ""',
        f'"Project-Id-Version: {SLUG}\\n"',
        f'"Language: {locale}\\n"',
        '"Content-Type: text/plain; charset=UTF-8\\n"',
        '"MIME-Version: 1.0\\n"',
        '"Content-Transfer-Encoding: 8bit\\n"', '',
    ]
    for s in strings:
        t = TRANSLATIONS.get(s, {}).get(locale, "")
        if not t:
            print(f"  ⚠️  Manquant [{locale}]: {s!r}")
        es = s.replace('\\', '\\\\').replace('"', '\\"')
        et = t.replace('\\', '\\\\').replace('"', '\\"')
        lines += [f'msgid "{es}"', f'msgstr "{et}"', '']
    po.write_text('\n'.join(lines), encoding='utf-8')
    return po


def compile_mo(po_path):
    mo_path = po_path.with_suffix('.mo')
    try:
        subprocess.run(['msgfmt', str(po_path), '-o', str(mo_path)], check=True, capture_output=True)
    except (FileNotFoundError, subprocess.CalledProcessError):
        import polib
        po = polib.pofile(str(po_path))
        po.save_as_mofile(str(mo_path))
    return mo_path


def main():
    strings = extract_strings()
    print(f"→ {len(strings)} chaînes extraites")
    write_pot(strings)
    print("→ .pot généré")
    for locale in LOCALES:
        po = write_po(locale, strings)
        mo = compile_mo(po)
        print(f"  ✓ {locale}: {po.name} + {mo.name}")
    print(f"\n✅ Traductions dans {LANGUAGES}")


if __name__ == "__main__":
    main()
