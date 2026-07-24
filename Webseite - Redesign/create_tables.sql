ALTER TABLE Posts ADD COLUMN is_paid INTEGER NOT NULL DEFAULT 0;              -- 0=free, 1=paid
ALTER TABLE Posts ADD COLUMN price_cents INTEGER; 

CREATE TABLE IF NOT EXISTS SavedPosts (
  user_id INTEGER NOT NULL,
  post_id INTEGER NOT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, post_id)
);

CREATE TABLE IF NOT EXISTS Reports (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  reporter_id INTEGER NOT NULL,
  target_type TEXT NOT NULL,
  target_id INTEGER NOT NULL,
  reason TEXT NOT NULL,
  note TEXT NULL,
  status TEXT NOT NULL DEFAULT 'open',
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (reporter_id, target_type, target_id)
);
