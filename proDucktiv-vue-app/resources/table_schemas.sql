-- =============================================
-- 'pdt_moods' Table
-- =============================================
USE s105341089_db;

CREATE TABLE IF NOT EXISTS pdt_moods (
  mood_id    INT(11) NOT NULL AUTO_INCREMENT,
  user_id    INT(11) NOT NULL,
  mood_date  DATE NOT NULL,
  mood_value ENUM('up', 'down') NOT NULL,
  PRIMARY KEY (mood_id),
  UNIQUE KEY uq_user_mood_day (user_id, mood_date),
  KEY idx_mood_date_value (mood_date, mood_value),
  CONSTRAINT fk_pdt_moods_user
    FOREIGN KEY (user_id) REFERENCES pdt_users(user_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
);

-- ================================================================
-- 'pdt_tasks' Table
-- ================================================================
CREATE TABLE IF NOT EXISTS pdt_tasks (
       task_id           INT(11) NOT NULL AUTO_INCREMENT,
       user_id           INT(11) NOT NULL,
       task_title        VARCHAR(255) NOT NULL,
       task_type         VARCHAR(100) NOT NULL,
       task_date         DATE NULL,
       task_time         TIME NULL,
       task_location     VARCHAR(255),
       task_description  TEXT,
       task_completed    TINYINT(1) DEFAULT 0,
       created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       updated_at        TIMESTAMP NULL DEFAULT NULL, 
       PRIMARY KEY (task_id),
       KEY idx_task_user (user_id),
       KEY idx_task_date (task_date),
       KEY idx_task_completed (task_completed),
       CONSTRAINT fk_pdt_tasks_user
           FOREIGN KEY (user_id)
           REFERENCES pdt_users(user_id)
                 ON DELETE CASCADE
                 ON UPDATE CASCADE
                 );

-- optional test data.
-- only run this insert if you want sample tasks to appear in your task page.
INSERT INTO pdt_tasks
(task_title, task_type, task_date, task_time, task_location, task_description, task_completed)
VALUES
('Revision', 'Study', '2026-05-28', '12:30:00', 'Library', 'Revise for unit test next week.', 0),
('Group Meeting', 'Meeting', '2026-05-24', '14:00:00', 'Online', 'Discuss project progress.', 0);
