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
