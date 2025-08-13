# Dark Promoters — Official Rules (EN)

> A competitive event-management card game set in the gothic/alt scene.  
> All events happen on the **same day** — compete for Locations, Acts, Sponsors, Marketing, and Audience.

## 0) Objective & Setup
- **Goal:** Highest **Profit** wins. Tie → most **Audience**, then lowest **Total Spend**.
- **Start:** Each player gets **starting budget**, **audience base**, and **ticket price band options** per chosen **Mode** (see §8).

**Card Types**
- **Location** *(unique)* — rent/cost, **capacity** cap. Max 1 per player.
- **Act** *(unique)* — cost, **audience bonus in %** (additive).
- **Sponsor** — **min slots (0–3)**, **payout per slot** (one-time); each player has **6 slots** total.
- **Marketing** — cost, **audience bonus in %** (additive).
- **Sabotage** — one-shot negative effects.
> **Unique**: only **one copy in play** for the entire game (Locations, Acts).

No hand limit. Each physical card may be played **once** per game.

---

## 1) Turn Structure (all events on the same day)
Each game uses **2–3 rounds** depending on Mode (see §8.5). Each round has phases:

1. **Finance (R1)** — play Sponsors (assign slots, get one-time payout).
2. **Location (R1)** — take **exactly one** Location (first come, first served).
3. **Booking (R1)** — book Acts (pay costs). Acts are **unique** globally.
4. **Marketing (R1)** — play Marketing (additive %). **Set your Ticket Price** now (see §3).
5. **Sabotage** — play in **reverse turn order**.
6. **Finance (R2)** — more Sponsors (if slots remain).
7. **Booking (R2)**
8. **Marketing (R2)**
9. **Event (Scoring)** — compute Audience & Profit (see §2, §4).

**Draw/market rules** are house-tunable; default: at the start of **each phase**, each player may draw **1 matching card**.

---

## 2) Audience Math
1) **Base** per Mode (see §8).  
2) **Acts factor** = \(1 + \sum \text{Act %}\)  
3) **Marketing factor** = \(1 + \sum \text{Marketing %}\)  
4) **Price-Band factor** = from §3 (chosen in Marketing R1)  
5) **Preliminary Audience** = Base × Acts × Marketing × Price-Band  
6) **Apply Sabotage** (flat reductions etc.)  
7) **Capacity cap** — final **Effective Audience** = min(above, Location capacity)

**Soft Caps (balancing):**
- **Acts:** first **3 Acts** count **full**, additional Acts count **50%** of their %.
- **Marketing caps:** up to **+60%** total (Club/Party) or **+90%** (1-Day/2-Day Festivals).

---

## 3) Ticket Price
- **Timing:** Choose in **Marketing (R1)**. Stays **fixed** to the end.
- **Default prices:** see §8 per Mode. Players pick a **band**:
  - **Budget:** ×0.80 price → **+15%** audience (factor **1.15**)
  - **Standard:** ×1.00 → **±0%** (factor **1.00**)
  - **Premium:** ×1.20 → **−12%** (factor **0.88**)
  - **Prestige:** ×1.35 → **−25%** (factor **0.75**)

---

## 4) Scoring
**Profit** = (Ticket Price × **Effective Audience**) **+ Sponsoring** − **Total Spend**  
**Total Spend** = Location Rent + Act Costs + Marketing Costs ± other costs.

Tie-breakers:
1) Highest **Audience**  
2) Lowest **Total Spend**  
3) Most **Acts** booked

---

## 5) Sponsors
**Slots & Payout**
- Each player has **6 Sponsor slots**. In both Finance phases, play any number of Sponsors up to your slot limit.
- **Min slots** must be assigned **immediately**; no re-shuffling later (unless a card says so).
- **One-time payout** on play: **(payout/slot) × (assigned slots)**.
- Sponsors can support **multiple events** in parallel (no exclusivity) — **unless** someone claims **Exclusive**.

**Exclusive Bonus**
- Only the **first player** playing that Sponsor may choose **Exclusive**:
  - Must assign **+1 additional slot** (on top of min slots).
  - Gets **+25%** on that Sponsor’s **total payout**.
  - That Sponsor becomes **locked** for others (cannot be played by anyone else).

---

## 6) Uniqueness & Availability
- **Locations & Acts** are globally **unique** — first to book keeps them.
- **Sponsors** are shared across tables **unless Exclusive** was taken.
- Max **1 Location** per player; **unlimited Acts** (subject to budget, caps, capacity).

---

## 7) Sabotage (framework)
- Played in **reverse turn order** during the Sabotage phase.
- Typical examples:
  - **Rumor Mill:** remove the opponent’s strongest Marketing card.
  - **Double-Booked:** **neutralize** one Act (its % doesn’t apply).
  - **Tech Failure:** **−10 audience** (after multipliers, before capacity).
- Costs and failure risks can be introduced as expansions; core remains simple.

---

## 8) Modes & Reference Values (realistic ranges)

### 8.1 Clubnight
- **Rounds:** **2** (30–45 min)
- **Start Budget:** **€8,000**
- **Ticket Price:** **€15**
- **Audience Base:** **150**
- **Sponsor /slot:** **€400–800**

**Locations (rent → cap):**  
Kellerclub €1,000–1,500 → 200–250  
Scene club €1,500–3,000 → 300–450  
Big club €3,500–6,000 → 600–800

**Acts (cost → %):**  
Resident DJ €300–500 → +10%  
Guest DJ €600–900 → +15%  
Small live €1,000–1,600 → +20%

**Marketing (cost → %):**  
Flyer team €250 → +5%  
Social ads €400 → +7%  
City posters (light) €700 → +10%  
Influencer collab €500 → +8%

---

### 8.2 Party (1 Headliner + Support)
- **Rounds:** **2** (optional **Teaser window** before R1)
- **Start Budget:** **€20,000**
- **Ticket Price:** **€28** (25–30)
- **Audience Base:** **300**
- **Sponsor /slot:** **€700–1,500**

**Locations:**  
Hall M €5–8k → 800–1,200  
Hall L €9–12k → 1,500–2,000

**Acts:**  
Headliner €8–15k → +55–70%  
Support €1.2–2k → +12–18%  
Opener €0.5–0.8k → +8–12%

**Marketing:**  
City posters €1.5k → +12%  
PR/mag €800 → +8%  
Teaser video €1k → +10%  
Street team €600 → +7%

**Teaser Window (optional, Party only):**  
Before Phase 1, each player chooses exactly one:  
- Play 1 Sponsor (normal), or  
- Mini-Marketing: **+5%** audience for **€500**.

---

### 8.3 One-Day Festival
- **Rounds:** **3** (**Round 3 = Last Call**: Booking → Marketing → Sabotage; **no** Finance/Sponsors)
- **Start Budget:** **€90,000**
- **Ticket Price:** **€69** (55–79)
- **Audience Base:** **800**
- **Sponsor /slot:** **€1,500–3,000**

**Locations (package incl. basics):**  
Ground S €30k → cap 2,500  
Ground M €50k → cap 5,000  
Ground L €80k → cap 8,000

**Acts:**  
Headliner €20–30k → +70–90%  
Co-Headliner €10–15k → +35–45%  
Mid-tier €3–6k → +15–25%  
Emerging €1–2k → +8–12%

**Marketing:**  
Big city posters €6k → +25%  
Digital campaign €4k → +18%  
PR agency €3k → +12%  
Early-bird push €1.5k → +10%

---

### 8.4 Two-Day Festival
- **Rounds:** **3** (**Last Call** like above)
- **Start Budget:** **€160,000**
- **Ticket Price (2-day):** **€119** (95–140)
- **Audience Base:** **1,500**
- **Sponsor /slot:** **€2,500–5,000**

**Locations (festival package):**  
Ground M €90k → cap 5,000/day  
Ground L €150k → cap 10,000/day  
Ground XL €220k → cap 15,000/day

**Acts (per day; % add up across the event):**  
Headliner A €30–50k → +80–100%  
Headliner B €20–35k → +60–80%  
Co-Headliner €12–20k → +35–45%  
Mid-tier €4–7k → +15–25%  
Emerging €1.2–2.5k → +8–12%

**Marketing (festival-wide):**  
Posters XXL €10k → +30%  
Digital always-on €8k → +22%  
OOH large €12k → +28%  
PR agency €6k → +15%

---

## 9) Glossary
- **Unique:** Only one copy can exist in play (global) for the entire game.
- **Slots:** Personal Sponsor capacity (6 total, no halves).
- **Exclusive (Sponsor):** Costs +1 slot, grants +25% payout, locks that Sponsor for others.

## 10) Changelog
- v0.3: realistic mode values; exclusive sponsor +25% (+1 slot); soft caps; price band timing.
- v0.2: phase structure; uniqueness; sponsor slots.
- v0.1: initial concept.
