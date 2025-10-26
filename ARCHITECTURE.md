# Welcome Tour System Architecture

```
┌─────────────────────────────────────────────────────────────────────────┐
│                          WELCOME TOUR SYSTEM                             │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────┐
│   User Registers    │
│   & Logs In         │
└──────────┬──────────┘
           │
           v
┌─────────────────────┐
│   Dashboard Loads   │
│   (header.php)      │
└──────────┬──────────┘
           │
           │ Includes tour scripts
           v
┌─────────────────────────────────────┐
│  <script src="welcome-tour.js">    │  ← Core WelcomeTour class
│  <script src="tour-init.js">       │  ← Tour configurations
└──────────┬──────────────────────────┘
           │
           │ DOMContentLoaded event
           v
┌─────────────────────────────────────┐
│  initializeWelcomeTour()            │
└──────────┬──────────────────────────┘
           │
           │ Fetch tour status
           v
┌─────────────────────────────────────┐
│  GET /api/tour-progress.php         │
└──────────┬──────────────────────────┘
           │
           v
┌─────────────────────────────────────┐
│  Check: tour_completed?             │
└──────────┬──────────────────────────┘
           │
           ├─── Yes ──→ [Don't show tour]
           │
           └─── No ───┐
                      v
           ┌─────────────────────────────┐
           │  Select role-based steps    │
           │  - clientTourSteps          │
           │  - workerTourSteps          │
           │  - adminTourSteps           │
           └──────────┬──────────────────┘
                      │
                      v
           ┌─────────────────────────────┐
           │  new WelcomeTour(steps)     │
           └──────────┬──────────────────┘
                      │
                      │ tour.start()
                      v
           ┌─────────────────────────────┐
           │  Create overlay             │
           │  Create skip button         │
           │  Show first step            │
           └──────────┬──────────────────┘
                      │
                      v
           ┌─────────────────────────────────────────────┐
           │                                             │
           │  ┌────────────────────────────────────┐    │
           │  │    [Skip Tour] (top-right)         │    │
           │  └────────────────────────────────────┘    │
           │                                             │
           │  ┌────────────────────────────────────┐    │
           │  │  Highlighted Feature               │    │
           │  │  ┌──────────────────────────────┐  │    │
           │  │  │  ╔════════════════════════╗  │  │    │
           │  │  │  ║ 👋 Welcome!           ║  │  │    │
           │  │  │  ║                        ║  │  │    │
           │  │  │  ║ Your username is       ║  │  │    │
           │  │  │  ║ [testuser]            ║  │  │    │
           │  │  │  ║ Write it down!         ║  │  │    │
           │  │  │  ║                        ║  │  │    │
           │  │  │  ║ Step 1 of 8   [Next →] ║  │  │    │
           │  │  │  ╚════════════════════════╝  │  │    │
           │  │  └──────────────────────────────┘  │    │
           │  └────────────────────────────────────┘    │
           │                                             │
           └─────────────────────────────────────────────┘
                      │
                      │ User clicks Next/Back
                      v
           ┌─────────────────────────────┐
           │  showStep(index)            │
           │  - Highlight element        │
           │  - Position tooltip         │
           │  - Update content           │
           └──────────┬──────────────────┘
                      │
                      │ Save progress
                      v
           ┌─────────────────────────────┐
           │  POST /api/tour-progress.php│
           │  { current_step: X }        │
           └──────────┬──────────────────┘
                      │
                      v
           ┌─────────────────────────────┐
           │  Database UPDATE            │
           │  user_tour_progress         │
           └──────────┬──────────────────┘
                      │
                      │
           ┌──────────┴──────────┐
           │                     │
           v                     v
    [User clicks Skip]    [Last step reached]
           │                     │
           v                     v
    ┌──────────────┐      ┌──────────────┐
    │  skip()      │      │  complete()  │
    └──────┬───────┘      └──────┬───────┘
           │                     │
           └─────────┬───────────┘
                     v
           ┌─────────────────────────────┐
           │  Mark tour complete         │
           │  - localStorage.setItem()   │
           │  - POST to API              │
           │  - cleanup()                │
           └──────────┬──────────────────┘
                      │
                      v
           ┌─────────────────────────────┐
           │  Show completion message    │
           │  🎉 Tour completed!         │
           └─────────────────────────────┘


════════════════════════════════════════════════════════════════════

                          HELP SYSTEM

┌─────────────────────┐
│  User clicks        │
│  Help & Docs link   │
│  (in navbar)        │
└──────────┬──────────┘
           │
           v
┌─────────────────────────────────────┐
│  modules/common/help.php loads      │
└──────────┬──────────────────────────┘
           │
           │ Check user role
           v
┌─────────────────────────────────────┐
│  Filter allowed sections:           │
│  - Client: Client Features          │
│  - Worker: Worker Features          │
│  - Admin: ALL Features ⭐           │
└──────────┬──────────────────────────┘
           │
           │ Fetch MANUAL.md
           v
┌─────────────────────────────────────┐
│  GET /MANUAL.md                     │
└──────────┬──────────────────────────┘
           │
           │ Filter content by role
           v
┌─────────────────────────────────────┐
│  Display filtered sections          │
│  - Navigation tabs                  │
│  - Quick links                      │
│  - Searchable content               │
│  - Support contact                  │
└─────────────────────────────────────┘


════════════════════════════════════════════════════════════════════

                      SETTINGS INTEGRATION

┌─────────────────────┐
│  User goes to       │
│  Settings page      │
└──────────┬──────────┘
           │
           v
┌─────────────────────────────────────┐
│  modules/client/settings.php        │
│                                     │
│  ┌───────────────────────────────┐ │
│  │ Welcome Tour Section          │ │
│  │                               │ │
│  │ Want to see the tour again?   │ │
│  │                               │ │
│  │ [🎯 Restart Welcome Tour]    │ │
│  └───────────────────────────────┘ │
└──────────┬──────────────────────────┘
           │
           │ Click button
           v
┌─────────────────────────────────────┐
│  restartWelcomeTour()               │
│  - Confirm dialog                   │
│  - WelcomeTour.reset()              │
│  - Clear localStorage               │
│  - POST to API (reset: true)        │
│  - location.reload()                │
└──────────┬──────────────────────────┘
           │
           v
┌─────────────────────────────────────┐
│  Page reloads, tour starts again    │
└─────────────────────────────────────┘


════════════════════════════════════════════════════════════════════

                       DATABASE SCHEMA

┌────────────────────────────────────────────────────────────┐
│  user_tour_progress                                        │
├────────────────┬──────────────┬───────────────────────────┤
│  Field         │  Type        │  Description               │
├────────────────┼──────────────┼───────────────────────────┤
│  id            │  INT         │  Primary key               │
│  user_id       │  INT         │  FK → users(id)            │
│  tour_completed│  BOOLEAN     │  Tour finished flag        │
│  tour_skipped  │  BOOLEAN     │  Tour skipped flag         │
│  current_step  │  INT         │  Last viewed step          │
│  completed_at  │  TIMESTAMP   │  When completed/skipped    │
│  created_at    │  TIMESTAMP   │  Record creation           │
│  updated_at    │  TIMESTAMP   │  Last update               │
└────────────────┴──────────────┴───────────────────────────┘


════════════════════════════════════════════════════════════════════

                          FILE STRUCTURE

OUTSINC/
├── assets/
│   └── js/
│       ├── welcome-tour.js      ← Core tour library
│       └── tour-init.js         ← Tour configurations
├── api/
│   └── tour-progress.php        ← REST API endpoint
├── modules/
│   ├── client/
│   │   └── settings.php         ← Settings page with tour reset
│   └── common/
│       └── help.php             ← Help & documentation viewer
├── includes/
│   ├── header.php               ← Updated with tour scripts
│   └── navbar.php               ← Updated with help link
├── screenshots/
│   ├── auth/                    ← Screenshot directories
│   ├── client/
│   ├── worker/
│   ├── provider/
│   ├── admin/
│   ├── common/
│   └── README.md                ← Screenshot guidelines
├── MANUAL.md                    ← Comprehensive user manual
├── TOUR_DEVELOPER_GUIDE.md      ← Developer documentation
├── IMPLEMENTATION_SUMMARY.md    ← This implementation summary
└── sql/
    └── schema.sql               ← Updated with tour table

════════════════════════════════════════════════════════════════════
```

## Key Interactions

### 1. Tour Start Flow
```
User Login → Dashboard → Check Status → Start Tour → Show Steps → Save Progress
```

### 2. Tour Navigation Flow
```
Step Display → User Action (Next/Back/Skip) → Update UI → Save to DB
```

### 3. Help Access Flow
```
Click Help Link → Load Help Page → Filter by Role → Display Content
```

### 4. Tour Reset Flow
```
Settings → Restart Button → Confirm → Reset DB → Reload → Tour Restarts
```

## Role-Based Content

| Role              | Tour Steps | Help Sections                              |
|-------------------|------------|-------------------------------------------|
| Client            | 8 steps    | Getting Started, Client, Common, FAQ      |
| Worker            | 5 steps    | Getting Started, Worker, Common, FAQ      |
| Service Provider  | 5 steps    | Getting Started, Provider, Common, FAQ    |
| Administrator     | 5 steps    | **ALL SECTIONS** (including all roles)    |

## Data Flow

```
Frontend (JavaScript) ←→ API (PHP) ←→ Database (MySQL)
      ↓                     ↓              ↓
  LocalStorage         tour-progress   user_tour_progress
  (quick check)        .php            (persistent storage)
```

## Security Layers

```
┌────────────────────────────────────┐
│  User Authentication Required      │  ← Session check
├────────────────────────────────────┤
│  CSRF Token Validation            │  ← Form protection
├────────────────────────────────────┤
│  SQL Injection Prevention         │  ← Prepared statements
├────────────────────────────────────┤
│  XSS Protection                   │  ← Input sanitization
└────────────────────────────────────┘
```
