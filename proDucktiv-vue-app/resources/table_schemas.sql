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