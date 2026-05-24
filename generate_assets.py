"""Génère les assets PNG de ws-optimizer-ai via cairosvg.

Usage : python3 generate_assets.py
"""
import os
from pathlib import Path
import cairosvg

ASSETS = Path(__file__).parent / "assets"
ASSETS.mkdir(exist_ok=True)

BG       = "#14121C"
BG_ALT   = "#1A1724"
BG_DEEP  = "#221D32"
ACCENT   = "#7C5CBF"
ACC_MID  = "#9B8EC4"
ACC_L    = "#A899D4"
TEXT     = "#F0EDE8"
TEXT_S   = "#C4BFDA"
BORDER   = "#2E2B38"
WP_SIDE  = "#1d2327"
WP_BODY  = "#f0f0f1"


def write_png(svg: str, name: str, w: int, h: int) -> None:
    out = ASSETS / name
    cairosvg.svg2png(bytestring=svg.encode("utf-8"), output_width=w, output_height=h,
                     write_to=str(out))
    print(f"  ✓ {name} ({w}×{h})")


# Icônes — logo "WSAI" sur fond violet
icon_svg = f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256">
  <rect width="256" height="256" rx="32" fill="{BG}"/>
  <circle cx="128" cy="128" r="88" fill="{ACCENT}" opacity="0.15"/>
  <circle cx="128" cy="128" r="64" fill="{ACCENT}"/>
  <text x="128" y="118" text-anchor="middle" font-family="Georgia,serif"
        font-weight="700" font-size="38" fill="{TEXT}">WS</text>
  <text x="128" y="158" text-anchor="middle" font-family="Georgia,serif"
        font-weight="700" font-size="38" fill="{TEXT}">AI</text>
</svg>"""
write_png(icon_svg, "icon-128x128.png", 128, 128)
write_png(icon_svg, "icon-256x256.png", 256, 256)

# Banner
banner_svg = f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1544 500">
  <rect width="1544" height="500" fill="{BG}"/>
  <defs>
    <linearGradient id="g" x1="0" x2="1" y1="0" y2="1">
      <stop offset="0" stop-color="{ACCENT}" stop-opacity="0.2"/>
      <stop offset="1" stop-color="{BG}" stop-opacity="0"/>
    </linearGradient>
  </defs>
  <rect width="1544" height="500" fill="url(#g)"/>
  <!-- Score badge décoratif -->
  <circle cx="1300" cy="250" r="140" fill="{ACCENT}" opacity="0.08"/>
  <circle cx="1300" cy="250" r="100" fill="{ACCENT}" opacity="0.12"/>
  <text x="1300" y="230" text-anchor="middle" font-family="Georgia,serif"
        font-weight="700" font-size="64" fill="{ACCENT}">92</text>
  <text x="1300" y="280" text-anchor="middle" font-family="Arial,sans-serif"
        font-size="22" fill="{ACC_L}">/100</text>
  <!-- Titre -->
  <text x="80" y="190" font-family="Georgia,serif" font-weight="700"
        font-size="72" fill="{TEXT}">WS SEO Title AI</text>
  <text x="80" y="260" font-family="Arial,sans-serif" font-weight="400"
        font-size="30" fill="{TEXT_S}">Analyse vos titres SEO avec Claude AI</text>
  <text x="80" y="310" font-family="Arial,sans-serif" font-size="22" fill="{ACC_L}">
    Score · Verdict · Recommandations — directement dans l'éditeur
  </text>
  <!-- Tag WP7 -->
  <rect x="80" y="350" width="200" height="44" rx="8" fill="{ACCENT}"/>
  <text x="180" y="378" text-anchor="middle" font-family="Arial,sans-serif"
        font-weight="600" font-size="18" fill="{TEXT}">WordPress 7.0+</text>
</svg>"""
write_png(banner_svg, "banner-1544x500.png", 1544, 500)
write_png(banner_svg, "banner-772x250.png", 772, 250)


def screenshot(title: str, body_inner: str, filename: str) -> None:
    svg = f"""<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 900">
      <rect width="1200" height="900" fill="{WP_BODY}"/>
      <!-- WP Admin sidebar -->
      <rect x="0" y="0" width="160" height="900" fill="{WP_SIDE}"/>
      <rect x="0" y="0" width="1200" height="32" fill="#23282d"/>
      <text x="20" y="22" font-family="Arial,sans-serif" font-size="13" fill="#aaa">Tableau de bord</text>
      <!-- Contenu plugin dark -->
      <rect x="160" y="32" width="1040" height="868" fill="{BG}"/>
      <text x="200" y="95" font-family="Georgia,serif" font-weight="700"
            font-size="32" fill="{TEXT}">{title}</text>
      <line x1="200" y1="110" x2="1160" y2="110" stroke="{BORDER}" stroke-width="1"/>
      {body_inner}
    </svg>"""
    write_png(svg, filename, 1200, 900)


# Screenshot 1 — Metabox dans l'éditeur
screenshot("Analyse de titre SEO — Metabox", f"""
  <!-- Éditeur simulé -->
  <rect x="200" y="130" width="720" height="680" rx="6" fill="{BG_ALT}" stroke="{BORDER}"/>
  <text x="220" y="165" font-family="Arial,sans-serif" font-size="13" fill="{TEXT_S}">Titre de l'article</text>
  <rect x="220" y="175" width="680" height="44" rx="4" fill="{BG}" stroke="{ACCENT}"/>
  <text x="232" y="203" font-family="Arial,sans-serif" font-size="15" fill="{TEXT}">WordPress 2024 : 10 Meilleurs Plugins Gratuits</text>
  <!-- Sidebar metabox -->
  <rect x="940" y="130" width="260" height="340" rx="6" fill="{BG_ALT}" stroke="{BORDER}"/>
  <rect x="940" y="130" width="260" height="44" rx="6" fill="{BG_DEEP}"/>
  <text x="960" y="157" font-family="Arial,sans-serif" font-weight="600" font-size="14" fill="{TEXT}">Analyse Titre (Claude)</text>
  <!-- Score -->
  <text x="960" y="215" font-family="Georgia,serif" font-weight="700" font-size="40" fill="{ACCENT}">88</text>
  <text x="1010" y="215" font-family="Arial,sans-serif" font-size="18" fill="{TEXT_S}">/100</text>
  <text x="960" y="245" font-family="Arial,sans-serif" font-size="13" fill="{TEXT_S}">✅ Excellent titre SEO</text>
  <text x="960" y="270" font-family="Arial,sans-serif" font-size="12" fill="{ACC_L}">Forces :</text>
  <text x="960" y="288" font-family="Arial,sans-serif" font-size="11" fill="{TEXT_S}">• Chiffre présent (10)</text>
  <text x="960" y="304" font-family="Arial,sans-serif" font-size="11" fill="{TEXT_S}">• Mots-puissance</text>
  <text x="960" y="320" font-family="Arial,sans-serif" font-size="11" fill="{TEXT_S}">• Longueur optimale (48c)</text>
  <rect x="960" y="420" width="220" height="36" rx="6" fill="{ACCENT}"/>
  <text x="1070" y="443" text-anchor="middle" font-family="Arial,sans-serif" font-weight="600" font-size="13" fill="{TEXT}">Ré-analyser</text>
""", "screenshot-1.png")

# Screenshot 2 — Résultat d'analyse détaillé
screenshot("Résultat d'analyse complet", f"""
  <rect x="200" y="130" width="940" height="680" rx="6" fill="{BG_ALT}" stroke="{BORDER}"/>
  <!-- Score header -->
  <rect x="220" y="150" width="900" height="90" rx="6" fill="{BG_DEEP}"/>
  <text x="260" y="200" font-family="Georgia,serif" font-weight="700" font-size="52" fill="{ACCENT}">72</text>
  <text x="330" y="200" font-family="Arial,sans-serif" font-size="24" fill="{TEXT_S}">/100</text>
  <text x="420" y="185" font-family="Arial,sans-serif" font-weight="600" font-size="18" fill="{TEXT}">⚠️ Titre à améliorer</text>
  <text x="420" y="210" font-family="Arial,sans-serif" font-size="13" fill="{TEXT_S}">Le titre manque de mots-puissance et la keyword est absente.</text>
  <!-- Issues -->
  <text x="240" y="270" font-family="Arial,sans-serif" font-weight="600" font-size="14" fill="{TEXT}">Problèmes détectés</text>
  <rect x="240" y="285" width="860" height="44" rx="4" fill="{BG}" stroke="#7c3a3a"/>
  <text x="258" y="312" font-family="Arial,sans-serif" font-size="13" fill="#e88">🚨 Keyword focus absente du titre</text>
  <rect x="240" y="336" width="860" height="44" rx="4" fill="{BG}" stroke="#7c5c2a"/>
  <text x="258" y="363" font-family="Arial,sans-serif" font-size="13" fill="#eb9">⚠️ Titre trop long (68 caractères, optimal 50-60)</text>
  <!-- Recommandations -->
  <text x="240" y="415" font-family="Arial,sans-serif" font-weight="600" font-size="14" fill="{TEXT}">Recommandations</text>
  <text x="240" y="438" font-family="Arial,sans-serif" font-size="13" fill="{ACC_L}">• Intégrer la keyword focus en début de titre</text>
  <text x="240" y="460" font-family="Arial,sans-serif" font-size="13" fill="{ACC_L}">• Raccourcir à moins de 60 caractères</text>
  <text x="240" y="482" font-family="Arial,sans-serif" font-size="13" fill="{ACC_L}">• Ajouter un chiffre ou un mot-puissance (Guide, Complet…)</text>
""", "screenshot-2.png")

# Screenshot 3 — Settings page
screenshot("Réglages — WS SEO Title AI", f"""
  <rect x="200" y="130" width="760" height="600" rx="6" fill="{BG_ALT}" stroke="{BORDER}"/>
  <!-- Section types de contenu -->
  <text x="220" y="175" font-family="Arial,sans-serif" font-weight="600" font-size="15" fill="{TEXT}">Types de contenu</text>
  <rect x="220" y="188" width="720" height="1" fill="{BORDER}"/>
  <rect x="225" y="205" width="16" height="16" rx="3" fill="{ACCENT}"/>
  <text x="250" y="218" font-family="Arial,sans-serif" font-size="13" fill="{TEXT_S}">Articles (post)</text>
  <rect x="225" y="232" width="16" height="16" rx="3" fill="{ACCENT}"/>
  <text x="250" y="245" font-family="Arial,sans-serif" font-size="13" fill="{TEXT_S}">Pages (page)</text>
  <rect x="225" y="259" width="16" height="16" rx="3" fill="{BG}" stroke="{BORDER}"/>
  <text x="250" y="272" font-family="Arial,sans-serif" font-size="13" fill="{TEXT_S}">Produits (product)</text>
  <!-- Section modèle -->
  <text x="220" y="320" font-family="Arial,sans-serif" font-weight="600" font-size="15" fill="{TEXT}">Modèle Claude</text>
  <rect x="220" y="333" width="720" height="1" fill="{BORDER}"/>
  <rect x="220" y="350" width="360" height="40" rx="4" fill="{BG}" stroke="{BORDER}"/>
  <text x="235" y="375" font-family="Arial,sans-serif" font-size="13" fill="{TEXT_S}">claude-opus-4-6</text>
  <!-- Info box -->
  <rect x="220" y="430" width="720" height="80" rx="6" fill="{BG_DEEP}" stroke="{BORDER}"/>
  <text x="240" y="460" font-family="Arial,sans-serif" font-size="13" fill="{ACC_L}">ℹ️ Ce plugin utilise WordPress AI Client (WP 7.0+)</text>
  <text x="240" y="482" font-family="Arial,sans-serif" font-size="12" fill="{TEXT_S}">Configurez votre clé API Anthropic dans Réglages → AI Client</text>
  <!-- Save button -->
  <rect x="220" y="560" width="160" height="42" rx="6" fill="{ACCENT}"/>
  <text x="300" y="586" text-anchor="middle" font-family="Arial,sans-serif" font-weight="600" font-size="15" fill="{TEXT}">Enregistrer</text>
""", "screenshot-3.png")

print(f"\n✅ Assets générés dans {ASSETS}")
