# DSA Online Judge

A minimal, beautiful, and secure Online Judge system for C++ developers. This platform evaluates solutions directly on the user's computer via a local runner and submits only the results back to the server, solving performance, sandbox security, and infrastructure constraints.

## Architecture

1. **Frontend**: HTML5, CSS3 (Tailwind CSS), Monaco Editor, Alpine.js / Vanilla JS.
2. **Backend**: PHP 8.3 REST API (routing, database authentication, submissions log, problem library).
3. **Database**: MySQL 8.
4. **Local Runner**: Lightweight Python 3 runner script running on user's machine on port 8000. It compiles `.cpp` files with local `g++` and runs test cases locally.

## Setup Instructions

### 1. Local Runner Setup (User Machine)
Run the local runner to allow compilation and test-case execution on your local machine:
```bash
python3 local-runner/runner.py
```
This starts an HTTP server at `http://localhost:8000`.

### 2. Backend Setup
1. Point your web server (Apache/Nginx) or start a local PHP development server:
   ```bash
   cd backend/public
   php -S localhost:8080
   ```
2. Set up the database:
   - Import the database schema from `backend/src/Database/schema.sql` into MySQL.
   - Run the initial seeds from `backend/src/Database/seed.sql`.
   - Update database configuration in `backend/config/database.php`.

### 3. Frontend Setup
Open `frontend/index.html` directly in your browser, or serve it via a static file server.
Make sure the frontend app is configured to connect to:
- Local compiler runner: `http://localhost:8000`
- Backend REST API: `http://localhost:8080` (or your backend port)

---

## Directory Structure

```text
DSA-website/
├── backend/                  # PHP REST API Backend
│   ├── config/               # Configuration files
│   │   ├── database.php      # DB configuration
│   │   └── jwt.php           # JWT encryption helpers
│   ├── public/               # Public entry points
│   │   └── index.php         # Front Controller & Routing
│   └── src/                  # Source files
│       ├── Controllers/      # Business logic handlers
│       │   ├── AdminController.php
│       │   ├── AuthController.php
│       │   ├── LeaderboardController.php
│       │   ├── ProblemController.php
│       │   └── SubmissionController.php
│       ├── Database/         # DB SQL Schema and Seeds
│       │   ├── schema.sql
│       │   └── seed.sql
│       ├── Middleware/       # Route guards (Authentication)
│       │   └── AuthMiddleware.php
│       └── Models/           # Database schema representations
│           ├── Problem.php
│           ├── Submission.php
│           └── User.php
├── frontend/                 # Client UI (HTML, CSS, JS)
│   ├── assets/               # CSS and JS resources
│   │   ├── css/
│   │   │   └── styles.css
│   │   └── js/
│   │       ├── app.js
│   │       ├── auth.js
│   │       ├── editor.js
│   │       └── runner-client.js
│   ├── admin.html            # Admin management pages
│   ├── index.html            # Main User Dashboard
│   ├── leaderboard.html      # Rank listing
│   ├── login.html            # Signup/Signin form
│   ├── problem.html          # Individual problem coding page
│   └── problems.html         # Problem bank
└── local-runner/             # Compiles & executes code locally
    ├── runner.py             # Python HTTP execution daemon
    └── requirements.txt      # Dependencies configuration
```
