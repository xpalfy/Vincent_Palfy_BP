ALTER TABLE lesson
  ADD PRIMARY KEY (id),
  ADD INDEX fk_lesson_creator (creator);

ALTER TABLE lesson
  ADD CONSTRAINT fk_lesson_creator FOREIGN KEY (creator) REFERENCES users (username);
