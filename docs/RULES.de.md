# Dark Promoters — Offizielle Regeln (DE)

> Ein kompetitives Event-Management-Kartenspiel in der Gothic/Alt-Szene.  
> Alle Events finden am **gleichen Tag** statt – ihr konkurriert um Locations, Acts, Sponsoren, Marketing und Publikum.

Beispielkarten liegen unter `/cards`, ein Demo-Starterdeck unter `/starter_decks`. Die englische Version befindet sich in `../RULES.md`.

## 0) Ziel & Setup
- **Ziel:** Höchster **Gewinn** gewinnt. Bei Gleichstand: höchste **Zuschauerzahl**, danach **geringste Gesamtausgaben**.
- **Setup:** Pro **Modus** (siehe §8) gelten Startbudget, Zuschauer-Basis und Ticketpreis-Bänder.

**Kartentypen**
- **Location** *(einzigartig)* – Miete/Kosten, **Kapazität** (harte Obergrenze). Max. 1 pro Spieler:in.
- **Act** *(einzigartig)* – Gage/Kosten, **Publikumsbonus in %** (additiv).
- **Sponsor** – **Mindestslots (0–3)**, **Payout je Slot** (einmalige Auszahlung); jede:r hat **6 Slots** insgesamt.
- **Marketing** – Kosten, **Publikumsbonus in %** (additiv).
- **Sabotage** – Einmaleffekte (stören Gegner, neutralisieren Effekte).

> **Einzigartig** heißt: Diese Karte kann im gesamten Spiel nur **einmal** belegt werden (Locations, Acts).

Kein Handlimit. Jede physische Karte darf pro Partie **nur einmal** ausgespielt werden.

---

## 1) Rundenstruktur (ein Tag, gemeinsame Konkurrenz)
Je nach Modus werden **2–3 Runden** gespielt (siehe §8.5). Jede Runde hat diese Phasen:

1. **Finanzierung (R1)** – Sponsoren ausspielen (Slots belegen, **einmalige Auszahlung**).
2. **Location (R1)** – **genau eine** Location sichern (**Wer zuerst bucht, bekommt sie**).
3. **Booking (R1)** – Acts buchen (Kosten zahlen). Acts sind **global einzigartig**.
4. **Marketing (R1)** – Marketingkarten ausspielen (additive %). **Hier den Ticketpreis festlegen** (siehe §3).
5. **Sabotage** – in **umgekehrter Spielerreihenfolge**.
6. **Finanzierung (R2)** – weitere Sponsoren (falls Slots frei).
7. **Booking (R2)**
8. **Marketing (R2)**
9. **Event (Wertung)** – Zuschauer & Gewinn berechnen (siehe §2, §4).

**Nachzieh-/Markt-Regel (Playtest-Standard):** Zu Beginn **jeder Phase** darf jede:r **1 passende Karte** ziehen (Hausregel anpassbar).

---

## 2) Zuschauer-Berechnung
1) **Basis** je Modus (siehe §8).  
2) **Acts-Faktor** = \(1 + \sum \text{Act-%}\)  
3) **Marketing-Faktor** = \(1 + \sum \text{Marketing-%}\)  
4) **Preisband-Faktor** = aus §3 (in Marketing R1 gewählt)  
5) **Vorläufige Zuschauer** = Basis × Acts × Marketing × Preisband  
6) **Sabotage** anwenden (feste Abzüge etc.)  
7) **Kapazitätsdeckel** – **Effektive Zuschauer** = min(Ergebnis, Location-Kapazität)

**Soft-Caps (Balancing):**
- **Acts:** Die **ersten 3 Acts** zählen **voll**, weitere Acts zählen **50 %** ihres %-Werts.
- **Marketing:** Gesamtbonus max. **+60 %** (Club/Party) bzw. **+90 %** (1-Tag/2-Tage-Festival).

---

## 3) Ticketpreis
- **Zeitpunkt:** In **Marketing (R1)** wählen. Gilt **fix** bis Spielende.
- **Standardpreise:** siehe §8 je Modus. Wähle ein **Preisband**:
  - **Budget:** ×0,80 Preis → **+15 %** Publikum (Faktor **1,15**)
  - **Standard:** ×1,00 → **±0 %** (Faktor **1,00**)
  - **Premium:** ×1,20 → **−12 %** (Faktor **0,88**)
  - **Prestige:** ×1,35 → **−25 %** (Faktor **0,75**)

---

## 4) Wertung
**Gewinn** = (Ticketpreis × **Effektive Zuschauer**) **+ Sponsoring − Gesamtausgaben**  
**Gesamtausgaben** = Locationmiete + Gagen (Acts) + Marketingkosten ± weitere Kosten.

Tie-Breaker:
1) höchste **Zuschauerzahl**  
2) **geringste Gesamtausgaben**  
3) **meiste Acts** gebucht

---

## 5) Sponsoren
**Slots & Auszahlung**
- Jede:r hat **6 Sponsor-Slots**. In beiden Finanzierungsphasen dürfen bis zur Slotgrenze beliebig viele Sponsoren ausgespielt werden.
- **Mindestslots** müssen **sofort** beim Ausspielen zugewiesen werden; **kein Umschichten** (außer eine Karte erlaubt es).
- **Einmalige Auszahlung** beim Ausspielen: **(Payout/Slot) × (zugewiesene Slots)**.
- Sponsoren dürfen **parallel** mehrere Events unterstützen (keine Exklusivität) — **außer** jemand nimmt **Exklusiv**.

**Exklusiv-Bonus**
- Nur die/der **Erste**, die/der diesen Sponsor spielt, darf **Exklusiv** wählen:
  - **+1 zusätzlicher Slot** muss belegt werden (zusätzlich zu den Mindestslots).
  - **+25 %** auf den **Gesamt-Payout** dieser Karte.
  - Dieser Sponsor ist danach für alle anderen **gesperrt**.

---

## 6) Einzigartigkeit & Verfügbarkeit
- **Locations & Acts** sind **global einzigartig** – wer zuerst bucht, behält sie.
- **Sponsoren** sind geteilt, **außer** wenn **Exklusiv** beansprucht wurde.
- Max. **1 Location** pro Spieler:in; **beliebig viele Acts** (Budget, Caps & Kapazität beachten).

---

## 7) Sabotage (Rahmenregeln)
- Gespielt in **umgekehrter Spielerreihenfolge** während der Sabotage-Phase.
- Typische Beispiele:
  - **Gerüchteküche:** Entfernt die **stärkste Marketingkarte** eines Gegners.
  - **Doppelt gebucht:** **Neutralisiert** einen Act (sein % zählt nicht).
  - **Technikausfall:** **−10 Zuschauer** (nach Multiplikatoren, vor Kapazitätsdeckel).
- Kosten/Fehlschlagchancen können in Erweiterungen kommen; **Kernspiel bleibt simpel**.

---

## 8) Modi & Referenzwerte (realistisch)

### 8.1 Clubnacht
- **Runden:** **2** (30–45 Min.)
- **Startbudget:** **8.000 €**
- **Ticketpreis:** **15 €**
- **Zuschauer-Basis:** **150**
- **Sponsor je Slot:** **400–800 €**

**Locations (Miete → Kapazität):**  
Kellerclub 1.000–1.500 € → 200–250  
Szene-Club 1.500–3.000 € → 300–450  
Großraum-Club 3.500–6.000 € → 600–800

**Acts (Gage → %):**  
Resident-DJ 300–500 € → +10 %  
Guest-DJ 600–900 € → +15 %  
Live-Act klein 1.000–1.600 € → +20 %

**Marketing (Kosten → %):**  
Flyer-Team 250 € → +5 %  
Social Ads 400 € → +7 %  
City-Poster light 700 € → +10 %  
Influencer-Koop 500 € → +8 %

---

### 8.2 Party (1 Headliner + Support)
- **Runden:** **2** (optional **Teaser-Fenster** vor Runde 1)
- **Startbudget:** **20.000 €**
- **Ticketpreis:** **28 €** (25–30)
- **Zuschauer-Basis:** **300**
- **Sponsor je Slot:** **700–1.500 €**

**Locations:**  
Halle M 5–8 k € → 800–1.200  
Halle L 9–12 k € → 1.500–2.000

**Acts:**  
Headliner 8–15 k € → +55–70 %  
Support 1,2–2 k € → +12–18 %  
Opener 0,5–0,8 k € → +8–12 %

**Marketing:**  
City-Poster 1,5 k € → +12 %  
PR/Magazin 800 € → +8 %  
Teaser-Video 1 k € → +10 %  
Street-Team 600 € → +7 %

**Teaser-Fenster (optional, nur Party):**  
Vor Phase 1 wählt jede:r genau eine Aktion:  
- **1 Sponsor** regulär ausspielen (Slots, Auszahlung), **oder**  
- **Mini-Marketing:** **+5 %** Publikum für **500 €**.

---

### 8.3 1-Tag-Festival
- **Runden:** **3** (**Runde 3 = Last Call**: Booking → Marketing → Sabotage; **keine** Finanzierung/Sponsoren)
- **Startbudget:** **90.000 €**
- **Ticketpreis (Tagesticket):** **69 €** (55–79)
- **Zuschauer-Basis:** **800**
- **Sponsor je Slot:** **1.500–3.000 €**

**Locations (Paket inkl. Grundtechnik/Security):**  
Gelände S 30 k € → Kap. 2.500  
Gelände M 50 k € → Kap. 5.000  
Gelände L 80 k € → Kap. 8.000

**Acts:**  
Headliner 20–30 k € → +70–90 %  
Co-Headliner 10–15 k € → +35–45 %  
Mid-Tier 3–6 k € → +15–25 %  
Emerging 1–2 k € → +8–12 %

**Marketing:**  
City-Poster groß 6 k € → +25 %  
Digitalkampagne 4 k € → +18 %  
PR-Agentur 3 k € → +12 %  
Early-Bird-Push 1,5 k € → +10 %

---

### 8.4 2-Tage-Festival
- **Runden:** **3** (**Last Call** wie oben)
- **Startbudget:** **160.000 €**
- **Ticketpreis (2-Tages-Pass):** **119 €** (95–140)
- **Zuschauer-Basis:** **1.500**
- **Sponsor je Slot:** **2.500–5.000 €**

**Locations (Festivalpaket, 2 Tage):**  
Gelände M 90 k € → Kap. 5.000/Tag  
Gelände L 150 k € → Kap. 10.000/Tag  
Gelände XL 220 k € → Kap. 15.000/Tag

**Acts (pro Tag; % addieren sich übers Event):**  
Headliner A 30–50 k € → +80–100 %  
Headliner B 20–35 k € → +60–80 %  
Co-Headliner 12–20 k € → +35–45 %  
Mid-Tier 4–7 k € → +15–25 %  
Emerging 1,2–2,5 k € → +8–12 %

**Marketing (festivalweit):**  
Poster XXL 10 k € → +30 %  
Digital Always-On 8 k € → +22 %  
OOH Großflächen 12 k € → +28 %  
PR-Agentur 6 k € → +15 %

---

## 9) Glossar
- **Einzigartig:** Karte kann im gesamten Spiel nur **einmal** im Spiel sein (global).
- **Slots:** Eigene Sponsor-Kapazität (6 gesamt; keine halben Slots).
- **Exklusiv (Sponsor):** Kostet **+1 Slot**, gibt **+25 %** Payout, sperrt diesen Sponsor für andere.

## 10) Changelog
- v0.3: realistische Modus-Werte; Sponsor Exklusiv +25 % (+1 Slot); Soft-Caps; Ticketpreis-Timing.
- v0.2: Phasenstruktur; Einzigartigkeit; Sponsor-Slots.
- v0.1: Grundidee.
