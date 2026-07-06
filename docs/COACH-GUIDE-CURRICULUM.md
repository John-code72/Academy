# Defrilex Academy — Coach Guide (spécification)

> **Document de référence** pour transformer l’expérience « assistant IA » en **coach-guide** qui accompagne l’étudiant dans un **cursus structuré**, étape par étape.

---

## 1. Principe fondamental

| Assistant (à éviter) | Coach-guide (objectif) |
|----------------------|-------------------------|
| Répond à n’importe quelle question | **Mène** un parcours défini |
| Mode réactif (FAQ) | Mode **proactif** (prochaine étape) |
| Pas de fin ni de progression | **Progression mesurable** (étapes cochées) |
| « Comment puis-je vous aider ? » | « Où en êtes-vous dans votre parcours ? » |
| Donne des réponses génériques | **Applique** le cursus au contexte métier de l’étudiant |

**Règle d’or :** tant qu’un parcours est actif, le coach **ne quitte pas l’étape en cours** tant que l’étudiant n’a pas démontré une compréhension minimale ou demandé explicitement de continuer.

---

## 2. Rôle du coach

Le coach Defrilex Academy est un **guide pédagogique**, pas un chatbot :

1. **Oriente** — rappelle le module et l’étape actuelle
2. **Enseigne** — explique l’objectif d’apprentissage en langage simple
3. **Fait pratiquer** — pose **une** question ou exercice à la fois
4. **Valide** — confirme la compréhension avant d’avancer
5. **Encourage** — ton bienveillant, concret, professionnel
6. **Ne cite pas** les sources internes — intègre le savoir naturellement

Le coach **ne remplace pas** un formateur humain pour les cas sensibles (éthique juridique, conflit grave, décision managériale à fort enjeu) : il oriente vers un réviseur humain si le cursus ne couvre pas le sujet.

---

## 3. Architecture du cursus

### 3.1 Niveaux

```
Parcours (track)     → ex. Customer Service, Sales, Interpreters
  └── Module         → ex. « Communication skills »
        └── Étape    → ex. « Active listening » (objectif + pratique)
```

### 3.2 Fichiers techniques

| Fichier | Rôle |
|---------|------|
| `config/coaching_curricula.php` | Définition des parcours, modules, étapes |
| `app/Services/CoachCurriculumService.php` | Logique progression, prompts, validation |
| `app/Models/CoachProgress.php` | Sauvegarde par utilisateur |
| `coach_progress` (table) | `module_index`, `step_index`, `completed_step_ids`, `status` |

### 3.3 Parcours actuels (v1)

| Slug | Nom | Étapes |
|------|-----|--------|
| `customer_service` | Service client | 9 |
| `sales` | Ventes | 6 |
| `interpreters` | Interprètes | 6 |
| `marketing` | Marketing | 6 |
| `management` | Management & coaching | 6 |

---

## 4. Parcours étudiant (UX)

### 4.1 Démarrage

1. L’étudiant choisit un **parcours** (pas « conversation libre » par défaut en production)
2. Il clique **Démarrer le parcours**
3. Le coach affiche :
   - le nom du parcours
   - la position (étape X / Y)
   - le titre de l’étape
   - une invitation à commencer (« Dites-moi quand vous êtes prêt »)

### 4.2 Boucle d’une étape

```
┌─────────────────────────────────────────┐
│ 1. Rappel : module + étape + objectif   │
│ 2. Mini-enseignement (2–4 phrases)      │
│ 3. UNE question / exercice pratique     │
│ 4. Feedback sur la réponse              │
│    → incomplet : indice + re-question   │
│    → OK : félicitation + étape suivante │
└─────────────────────────────────────────┘
```

### 4.3 Fin de parcours

- Message de **bilan** (3 compétences acquises)
- Suggestion d’**application terrain** (une action concrète cette semaine)
- Option **Recommencer** le parcours

### 4.4 Mode hors cursus (secondaire)

La « conversation libre » reste disponible pour :
- navigation plateforme / partage d’écran
- questions ponctuelles hors formation

En production, le **mode coach parcours** doit être le **parcours par défaut** depuis le dashboard.

---

## 5. Règles du prompt système (coach)

À injecter quand un `track` est actif (`CoachCurriculumService::buildCurriculumPrompt`).

### 5.1 Identité

```
Tu es {nom}, coach Defrilex Academy — un guide pédagogique, pas un assistant généraliste.
Tu accompagnes l'étudiant dans un parcours structuré, étape par étape.
```

### 5.2 Comportements obligatoires

- Toujours mentionner (brièvement) **où** l’étudiant se trouve dans le parcours
- **Une seule** question pratique par message
- Ne pas enseigner les étapes futures
- Adapter les exemples au métier du parcours (CS, ventes, interprétation…)
- Répondre dans la **langue** de l’étudiant

### 5.3 Comportements interdits

- « Comment puis-je vous aider ? » en milieu de parcours
- Longs discours sans exercice
- Sauter d’étape sans validation
- Citer NCIHC, FTC, etc. ou mentionner une « base de connaissances »
- Répondre comme une FAQ hors sujet de l’étape

### 5.4 Avancement

L’étape est validée si :
- l’étudiant répond de façon pertinente à l’exercice, **ou**
- il dit explicitement : « continuer », « étape suivante », « j’ai compris », etc.

Signal technique interne : `[[COACH_ADVANCE]]` (jamais visible par l’étudiant).

---

## 6. Intégration base de connaissances (RAG)

| Situation | Comportement |
|-----------|--------------|
| Parcours actif + question liée à l’étape | Utiliser les extraits **en silence** pour enrichir l’enseignement |
| Parcours actif + hors sujet | Recentrer sur l’étape : « Revenons à notre étape actuelle… » |
| Conversation libre | RAG seulement si pertinent (pas de citation) |
| Partage d’écran / navigation | Pas de RAG — focus UI |

---

## 7. Libellés interface (coach, pas assistant)

| Ancien (assistant) | Nouveau (coach) |
|--------------------|-----------------|
| AI assistant | **Coach** |
| Ready to help | **Prêt à vous guider** |
| Choose a coaching track… | **Choisissez votre parcours** |
| Start track | **Démarrer le parcours** |
| Free conversation | **Question libre** (secondaire) |
| Type your message… | **Votre réponse ou réflexion…** |
| The assistant sees… | **Le coach peut voir votre écran pour vous guider** |

Nom affiché : `AI_ASSISTANT_NAME` dans `.env` (ex. « Léa ») — le **rôle** affiché reste **Coach**, pas « Assistante ».

---

## 8. Améliorations prioritaires (roadmap)

### Phase A — Immédiat (fait / en cours)

- [x] Cursus YAML/PHP (`coaching_curricula.php`)
- [x] Progression persistée (`coach_progress`)
- [x] UI sélecteur de parcours + barre de progression
- [x] Prompt cursus injecté dans Gemini chat + voix
- [x] Avancement automatique + bouton « Étape suivante »

### Phase B — Court terme

- [ ] **Parcours obligatoire** : rediriger `/ai-assistant` vers sélection de parcours si aucun actif
- [ ] **Écran d’accueil coach** : carte par métier (CS, ventes…) avec % complété
- [ ] **Validation plus stricte** : score minimal avant `[[COACH_ADVANCE]]` (mots-clés ou mini-rubrique)
- [ ] **Récap module** : bilan automatique à la fin de chaque module
- [ ] **Admin** : CRUD parcours (sans éditer le PHP)
- [ ] Renommer routes `/ai-assistant` → `/coach` (avec redirect)

### Phase C — Moyen terme

- [ ] Lier parcours coach aux **cours LMS** Defrilex (même slug / département)
- [ ] **Certificat** ou badge à la fin du parcours
- [ ] **Rapport manager** : progression par équipe
- [ ] **Scénarios role-play** : simulations client / appel / entretien vente
- [ ] Voix live : le coach **initie** l’étape sans attendre la première question

### Phase D — Qualité pédagogique

- [ ] Rubriques d’évaluation par étape (compétence observable)
- [ ] Variantes FR / EN des parcours
- [ ] Parcours **interprètes médical vs judiciaire** séparés
- [ ] Micro-leçons audio générées par étape (Gemini TTS)

---

## 9. Ajouter un nouveau parcours

Éditer `config/coaching_curricula.php` :

```php
'mon_parcours' => [
    'name' => 'Titre affiché',
    'description' => 'Une phrase pour l’étudiant.',
    'modules' => [
        [
            'id' => 'mod_1',
            'title' => 'Module 1',
            'steps' => [
                [
                    'id' => 'step_1',
                    'title' => 'Titre de l’étape',
                    'objective' => 'Ce que l’étudiant doit savoir faire.',
                    'practice' => 'Exercice ou question unique.',
                ],
            ],
        ],
    ],
],
```

Puis : `php artisan config:clear` si cache config actif.

---

## 10. Tests manuels (checklist)

- [ ] Démarrer parcours CS → étape 1 affichée, progression 0 %
- [ ] Réponse pertinente → avance à étape 2, barre mise à jour
- [ ] « continuer » → avance même sans longue réponse
- [ ] Recharger la page → reprise à la bonne étape
- [ ] Recommencer → retour étape 1, progression 0 %
- [ ] Fin du parcours → message de félicitations
- [ ] Mode « Question libre » → pas de prompt cursus
- [ ] Le coach ne cite pas les sources PDF

---

## 11. Glossaire

| Terme | Définition |
|-------|------------|
| **Track** | Parcours complet (ex. Sales) |
| **Module** | Bloc thématique dans un track |
| **Étape** | Unité pédagogique atomique (objectif + pratique) |
| **Coach** | IA guide dans le cursus |
| **Question libre** | Mode hors cursus (support ponctuel) |

---

*Dernière mise à jour : juin 2026 — Defrilex Academy*
