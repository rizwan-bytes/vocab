# CLAUDE.md

## ⚡ Build Status (2026-08-21)

**Phases 1–5 built and working locally, plus an IELTS Tutor module beyond the original spec** (user-requested addition — Essay Feedback, Speaking Practice, Vocabulary Practice). Phase 6 (the spec's own "IELTS mode / AI exercises") is effectively superseded by the Tutor module's Vocabulary Practice feature — revisit whether the original Phase 6 items still add anything on top before building them separately.

- **Stack:** Laravel 13, Livewire 4 (classic class-based components, not the new single-file-component style — matches AgencyBrain's convention), Tailwind v4 (Vite plugin), MySQL. Local: `https://vocab.test` (Laragon), DB `vocab`.
- **Done:** foundation + layout/nav, vocabulary schema (`words`/`word_reviews`/`daily_progress`), Gemini generation service + prompt + validation, Add Word flow, vocabulary card UI, library (search/filter/edit/delete), spaced repetition + review mode, progress dashboard with streaks, and the **IELTS Tutor module** (`tutor_sessions` table; `GeminiClient` — extracted shared transport reused by both `GeminiVocabularyService` and the new `IeltsTutorService`; Essay Feedback with band breakdown + corrections; Speaking Practice with AI-generated questions + graded responses, pronunciation deliberately never assessed since it's typed practice; Vocabulary Practice that pulls the user's own "learning"/"reviewing" words into a generated writing exercise). 109 automated tests passing (`php artisan test`), Pint-formatted.
- **500-word IELTS vocabulary seed loaded** (2026-08-21): `php artisan vocab:seed` imports `database/seeders/data/ielts-500-words.json` (500 unique words, hand-written across 12 thematic batches in `database/seeders/data/batches/` — environment, tech/education, health/society, crime/media, culture/travel, work/business, science/personality, general academic vocab, linking words, lifestyle/housing) directly into the `words` table with `source='ielts'`. Written by Claude directly rather than through `GeminiVocabularyService`, since this sandbox blocks Gemini's `generateContent` call (see below). Each entry is validated against `GeminiVocabularyService::validationRules()` (now public, shared by both the live service and the seed command) and duplicates are skipped, so re-running the command is safe. Already run against the local `vocab` DB — confirmed 500/500 created, 0 skipped, 0 invalid.
- **Deferred:** importing the user's own Google Drive IELTS 1000-word-list PDF (`IELTS-academic-wordlist.pdf`, untracked in git) into the vocabulary section — explicitly postponed by the user ("rehne do abhi, phir kabhi plan krein gay"). Don't resume without being asked again; the PDF's format (messy, 3-section layout, no rich definitions) will need its own parsing plan when that happens.
- **No auth/login UI** — deliberate MVP choice (confirmed with the user): `words`/`daily_progress`/`tutor_sessions` have a nullable `user_id` so real auth is additive later, not a rewrite.
- **Gemini API key is already in `.env`** (real key, Flash-Lite model) — **not live-tested end-to-end from this build session**, because the sandboxed tool environment used to build this timed out specifically on the `generateContent` POST action (confirmed: `countTokens` POST on the same host/key worked instantly, so it's an environment egress-policy quirk, not a code bug or bad key). All four Gemini flows (vocabulary + essay + speaking + vocab-practice) are covered by `Http::fake()` tests instead. **First things to verify in a real browser: Add Word → Generate, and each of the three Tutor tools, actually return real Gemini output.**
- **Gotchas worth knowing:**
  - Livewire 4's default `component_layout` config (`layouts::app`) doesn't resolve against this project's Blade-component-style layout (`resources/views/components/layouts/app.blade.php`) — every full-page Livewire component needs an explicit `#[Layout('components.layouts.app')]` attribute, or it 500s with "No hint path defined for [layouts]". Already applied everywhere; keep doing it for any new full-page component.
  - Laravel's `required` validation rule fails on an *empty* array (not just a missing key) — bit the essay/speaking feedback validation, where a genuinely strong submission might have zero `improvements`. Use `present` instead of `required` for array fields that may legitimately be empty.
  - In tests, `Http::fake([...])` only honors the **most recently registered** stub set for the rest of that test — calling it a second time mid-test (e.g. to fake a *different* response for a second Gemini call in the same flow) silently does nothing; the first fake keeps answering. Workaround used throughout the Tutor tests: `$component->set(...)` the intermediate state directly (e.g. `question`/`instructions`) instead of also exercising the first Gemini call in a test that's really about the second one.
- **Not yet done:** a real timer for `study_minutes` (currently always 0 — no UI writes to it yet), git remote/push (repo is initialized + committed locally only).

---

## Project Overview

Build a personal web application for improving English vocabulary and IELTS performance.

The app is primarily for one user initially, but the architecture should be clean enough to support authentication and multiple users later.

The core idea:

> When the user encounters an unfamiliar English word while reading books, IELTS material, articles, etc., they enter the word. The app uses the Gemini API to generate a structured vocabulary card, saves the word to the database, and later uses spaced repetition and AI-generated exercises to turn passive vocabulary into active vocabulary.

The application must remain simple, fast, distraction-free, and highly practical.

---

# 1. Primary Goals

The application must help the user:

1. Capture unfamiliar words quickly.
2. Understand the word deeply.
3. Remember the word through spaced repetition.
4. Learn how to use the word naturally.
5. Build IELTS-relevant vocabulary.
6. Track daily vocabulary progress.
7. Practice previously learned vocabulary.
8. Gradually convert vocabulary from recognition → recall → active usage.

Do NOT build a generic dictionary application.

This is a personal English-learning and IELTS vocabulary system.

---

# 2. Technology Stack

Use:

- Laravel
- Livewire
- Blade
- Tailwind CSS
- MySQL
- Laravel migrations
- Laravel Eloquent ORM
- Laravel validation
- Laravel queues/jobs where appropriate
- Gemini API for AI generation
- Vite
- JavaScript only where necessary

Avoid unnecessary frameworks and dependencies.

Do NOT introduce React, Vue, Next.js, Inertia, or a separate API backend unless explicitly requested.

Prefer server-side Laravel + Livewire architecture.

---

# 3. Development Philosophy

Follow these principles:

### Simple > Clever

Prefer straightforward Laravel code over abstractions that do not provide real value.

### MVP First

Do not implement future features before the core workflow works.

### Reusable Components

Use Livewire components and Blade components where they improve maintainability.

### Database First

Important application state must live in MySQL, not browser localStorage.

### AI Is a Service

Gemini should be isolated behind a dedicated service class.

Example:

`app/Services/GeminiVocabularyService.php`

Do not call Gemini directly from Livewire components.

### No Hardcoded Secrets

API keys must come from `.env`.

Example:

`GEMINI_API_KEY=`

Never commit secrets.

---

# 4. Core User Flow

The primary workflow must be extremely fast.

User opens dashboard:

1. Click "Add Word"
2. Enter word
3. Submit
4. Backend validates word
5. Gemini generates structured vocabulary information
6. Application displays vocabulary card
7. User confirms/saves
8. Word becomes available for future reviews

Target experience:

> Enter word → Generate → Save

The workflow should require minimal clicks.

---

# 5. MVP Features

Implement these features first.

## 5.1 Dashboard

Dashboard should show:

- Today's new words
- Words due for review
- Total vocabulary
- Mastered words
- Current learning streak
- Study/review activity
- Quick Add Word button
- Start Review button

Example:

```text
TODAY

12 New Words
24 Reviews Due
8 Words Mastered

[ + Add Word ]

[ Start Review ]
```

Keep dashboard visually clean.

---

# 6. Add Word

Create a Livewire component for adding words.

Input:

```text
Word
```

Optional:

```text
Context / sentence where the word was found
```

Example:

```text
Word:
meticulous

Context:
He was meticulous about checking every detail.
```

Context should be sent to Gemini because it can improve meaning detection.

---

# 7. Gemini Vocabulary Generation

Gemini should return structured JSON.

Expected structure:

```json
{
  "word": "meticulous",
  "pronunciation": "/məˈtɪkjələs/",
  "part_of_speech": "adjective",
  "simple_definition": "Very careful and precise about details.",
  "urdu_meaning": "بہت محتاط، باریک بینی سے کام کرنے والا",
  "detailed_definition": "Showing great attention to detail and accuracy.",
  "examples": [
    "She is meticulous about her work.",
    "He kept meticulous records of the experiment."
  ],
  "synonyms": [
    "careful",
    "precise",
    "thorough"
  ],
  "antonyms": [
    "careless",
    "sloppy"
  ],
  "common_collocations": [
    "meticulous planning",
    "meticulous attention to detail",
    "meticulous research"
  ],
  "ielts_relevance": "Useful for describing careful research, work, analysis and processes.",
  "difficulty_level": "C1"
}
```

The exact schema may evolve, but the response must always be validated before saving.

Never blindly trust AI output.

---

# 8. Gemini Service

Create:

`app/Services/GeminiVocabularyService.php`

Responsibilities:

- Send vocabulary prompt
- Include word
- Include optional context
- Request JSON
- Parse response
- Validate structure
- Handle API failures
- Handle malformed JSON
- Return predictable application data

The Livewire component should not contain Gemini API implementation details.

---

# 9. Database Design

Create migrations and models.

## words

Fields:

```text
id
word
normalized_word
pronunciation
part_of_speech
simple_definition
detailed_definition
urdu_meaning
examples
synonyms
antonyms
common_collocations
ielts_relevance
difficulty_level
context
source
status
created_at
updated_at
```

Use JSON columns where appropriate for arrays such as:

- examples
- synonyms
- antonyms
- common_collocations

Possible statuses:

```text
learning
reviewing
mastered
```

Add appropriate indexes.

`normalized_word` should help prevent accidental duplicate words.

---

# 10. Word Reviews

Create:

`word_reviews`

Fields:

```text
id
word_id
review_date
rating
next_review_at
correct_count
wrong_count
created_at
updated_at
```

Use foreign key relationship to `words`.

---

# 11. Spaced Repetition

Implement a simple spaced-repetition system first.

Do NOT implement a complicated academic algorithm in MVP.

Initial schedule:

```text
First learning
↓
1 day
↓
3 days
↓
7 days
↓
14 days
↓
30 days
↓
60 days
```

The review result should influence the next review.

Ratings:

```text
Again
Hard
Good
Easy
```

Basic behavior:

### Again

Schedule very soon / same day.

### Hard

Short interval.

### Good

Move to next normal interval.

### Easy

Increase interval more aggressively.

Create a dedicated service:

`app/Services/SpacedRepetitionService.php`

Do not put scheduling logic directly into Blade or Livewire views.

---

# 12. Review System

Create a distraction-free review page.

One word at a time.

Example:

```text
METICULOUS

What does this word mean?

[ Reveal Answer ]
```

After reveal:

```text
Very careful and precise about details.

Urdu:
بہت محتاط، باریک بینی سے کام کرنے والا

Example:
She is meticulous about her work.

[ Again ] [ Hard ] [ Good ] [ Easy ]
```

Do not show all answers immediately.

The goal is active recall.

---

# 13. Future AI Exercises

Design the architecture so AI exercises can be added later.

Examples:

### Fill in the blank

```text
The scientist kept ______ records of the experiment.
```

### Sentence creation

```text
Write a sentence using "meticulous".
```

### Synonym challenge

```text
Which word is closest to meticulous?
```

### Urdu → English

```text
بہت محتاط / باریک بینی سے کام کرنے والا
```

User must recall:

`meticulous`

### IELTS usage

Generate a realistic IELTS-style sentence or mini exercise using previously learned vocabulary.

Do not implement all of these in the first MVP unless explicitly requested.

---

# 14. IELTS Mode

The application should eventually have a dedicated IELTS vocabulary mode.

It should use the user's existing vocabulary instead of generating random vocabulary.

Possible modules:

- IELTS Writing vocabulary
- IELTS Speaking vocabulary
- Academic vocabulary
- Synonym practice
- Sentence improvement
- Fill-in-the-blank
- AI-generated mini tasks

Important:

The app should teach the user how to USE vocabulary, not merely recognize definitions.

---

# 15. Progress Tracking

Track:

```text
Total words
New words today
Reviews today
Mastered words
Learning words
Current streak
Longest streak
Accuracy
Study/review minutes
```

Daily progress table:

```text
daily_progress

id
date
new_words
reviewed_words
mastered_words
correct_answers
wrong_answers
study_minutes
created_at
updated_at
```

Use database aggregation rather than storing redundant values unnecessarily.

---

# 16. Vocabulary Status

A word should progress approximately through:

```text
New
↓
Learning
↓
Reviewing
↓
Mastered
```

"Mastered" should not mean the user saw the word many times.

It should require repeated successful recall.

---

# 17. Word Library

Create a searchable vocabulary page.

Features:

- Search
- Filter by status
- Filter by difficulty
- Filter by IELTS relevance
- Sort newest/oldest
- Open vocabulary card
- Review word
- Edit word
- Delete word

Keep the interface fast.

---

# 18. Vocabulary Card UI

Each card should contain:

```text
WORD

Pronunciation
Part of Speech

Simple Meaning
Urdu Meaning

Definition

Examples

Synonyms
Antonyms

Common Collocations

IELTS Usage

Difficulty

Review Status
Next Review
```

Do not overcrowd the card.

Use clear typography and visual hierarchy.

---

# 19. Reading Integration

The first version does NOT need PDF/book integration.

The user can manually add words while reading.

However, design the database with:

```text
source
context
```

so later we can support:

```text
Book
IELTS
Article
YouTube
Conversation
Other
```

Example:

```text
source = Book
context = "Full sentence in which the word appeared"
```

---

# 20. Authentication

Initially, authentication may be simple.

But structure the database so words and progress can eventually belong to a user.

If authentication is implemented, use Laravel's standard authentication approach.

Avoid custom authentication systems.

---

# 21. UI/UX Requirements

The application should feel like a personal productivity tool, not an enterprise dashboard.

Design principles:

- Minimal
- Fast
- Clean
- Mobile responsive
- Excellent typography
- Strong whitespace
- Low cognitive load
- Clear CTA
- Dark mode optional later

The most important action is:

> Add Word

The second most important action is:

> Review Due Words

Do not add unnecessary navigation.

Suggested navigation:

```text
Dashboard
Vocabulary
Review
Progress
Settings
```

---

# 22. Error Handling

Handle:

- Gemini API unavailable
- Invalid API response
- Rate limits
- Timeout
- Duplicate word
- Empty input
- Network failure
- Database failure

Show human-friendly messages.

Never expose API keys or internal exceptions to the user.

Log technical errors appropriately.

---

# 23. Gemini Cost Control

The Gemini API is a limited/free-tier resource.

Optimize API usage.

Rules:

1. Do not call Gemini unnecessarily.
2. Cache generated vocabulary.
3. Do not regenerate a word unless explicitly requested.
4. Prevent accidental duplicate requests.
5. Keep prompts concise.
6. Request structured JSON.
7. Do not send unnecessary conversation history.

If a word already exists, show the existing vocabulary instead of calling Gemini again.

---

# 24. Prompt Design

Keep Gemini prompts centralized.

Create something similar to:

`app/Prompts/VocabularyPrompt.php`

The prompt should tell Gemini:

- Explain for an English learner
- Prefer natural definitions
- Give accurate Urdu meaning
- Provide realistic examples
- Identify part of speech
- Provide useful collocations
- Explain IELTS relevance
- Avoid unnecessarily difficult explanations
- Return JSON only

Prompts should be versionable and easy to modify.

---

# 25. Code Organization

Prefer:

```text
app/
├── Livewire/
│   ├── Dashboard/
│   ├── Vocabulary/
│   └── Review/
│
├── Models/
│   ├── Word.php
│   ├── WordReview.php
│   └── DailyProgress.php
│
├── Services/
│   ├── GeminiVocabularyService.php
│   └── SpacedRepetitionService.php
│
└── Prompts/
    └── VocabularyPrompt.php
```

Keep business logic out of views.

---

# 26. Testing

Write tests for critical functionality.

Minimum tests:

### Vocabulary

- Can create a word
- Duplicate words are handled
- Word validation works
- Gemini response parsing works

### Review

- Due words are retrieved correctly
- Review rating changes next review date
- Mastery progression works

### Progress

- Daily statistics calculate correctly

Do not spend excessive time testing visual details in MVP.

---

# 27. Security

Follow Laravel security best practices.

- Validate all input
- Escape output
- Protect forms with CSRF
- Never expose API keys
- Authorize database records
- Avoid mass-assignment vulnerabilities
- Sanitize/validate AI output
- Do not execute AI-generated content

---

# 28. Git Workflow

Use small commits.

Recommended:

```text
feat: create vocabulary database schema
feat: add word workflow
feat: integrate Gemini vocabulary generation
feat: build vocabulary cards
feat: implement spaced repetition
feat: build review mode
feat: add progress dashboard
```

Do not make one enormous commit containing the entire application.

---

# 29. Development Order

Build in this exact order.

## Phase 1 — Foundation

- Laravel setup
- MySQL connection
- Tailwind
- Basic layout
- Database migrations
- Models

## Phase 2 — Core Vocabulary

- Add Word
- Gemini service
- Gemini prompt
- JSON parsing
- Save word
- Vocabulary card

## Phase 3 — Library

- Vocabulary list
- Search
- Filters
- Word detail
- Edit/delete

## Phase 4 — Review

- Review database
- Spaced repetition service
- Due words
- Review UI
- Again/Hard/Good/Easy

## Phase 5 — Progress

- Dashboard statistics
- Daily progress
- Streak
- Accuracy
- Mastered vocabulary

## Phase 6 — IELTS

Only after the above is stable:

- IELTS mode
- AI exercises
- Sentence creation
- Fill-in-the-blanks
- Vocabulary activation exercises

---

# 30. Important Constraint

DO NOT overengineer this application.

This is a personal learning tool.

Do not add:

- Microservices
- Redis unless actually needed
- Kubernetes
- Separate frontend
- Complex event architecture
- GraphQL
- Elasticsearch
- Multiple AI providers in MVP
- Complex gamification
- Social features

Start with a clean Laravel monolith.

---

# 31. Definition of Done

The MVP is complete when the user can:

1. Open the application.
2. Enter an unfamiliar word.
3. Optionally provide the sentence/context.
4. Generate vocabulary information using Gemini.
5. See a polished vocabulary card.
6. Save the word.
7. Find it later in the vocabulary library.
8. See when it is due for review.
9. Review it using active recall.
10. Rate the review.
11. Receive the next review date automatically.
12. See daily and overall progress.

If these work reliably, the MVP is successful.

---

# 32. Claude Code Instructions

Before writing code:

1. Inspect the existing project.
2. Understand the current Laravel version.
3. Check installed dependencies.
4. Check database configuration.
5. Do not overwrite existing work.
6. Create a short implementation plan.
7. Implement one phase at a time.
8. Run relevant tests after each major phase.
9. Fix errors before moving forward.
10. Keep the code production-quality.

When uncertain between two approaches:

> Prefer the simpler Laravel-native solution.

Do not invent requirements.

Do not build future functionality unless it helps the current architecture remain extensible.

The primary objective is:

> Build a fast, reliable personal vocabulary system that turns everyday reading into measurable IELTS vocabulary growth.