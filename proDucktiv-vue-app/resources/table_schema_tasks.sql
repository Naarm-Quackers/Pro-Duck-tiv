-- ================================================================
-- table_schema_task.sql
-- ================================================================
-- purpose:
-- - create the pdt_tasks table for the ProDucktiv task feature.
-- - date and time are nullable so users can create tasks without a deadline.
-- ================================================================

CREATE TABLE IF NOT EXISTS pdt_tasks (
    task_id INT AUTO_INCREMENT PRIMARY KEY,
    task_title VARCHAR(100) NOT NULL,
    task_type VARCHAR(50) NOT NULL,
    task_date DATE NULL,
    task_time TIME NULL,
    task_location VARCHAR(150) NULL,
    task_description TEXT NULL,
    task_completed TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- optional test data.
-- only run this insert if you want sample tasks to appear in your task page.
INSERT INTO pdt_tasks
(task_title, task_type, task_date, task_time, task_location, task_description, task_completed)
VALUES
('Revision', 'Study', '2026-05-28', '12:30:00', 'Library', 'Revise for unit test next week.', 0),
('Group Meeting', 'Meeting', '2026-05-24', '14:00:00', 'Online', 'Discuss project progress.', 0);
