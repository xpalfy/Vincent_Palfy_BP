ALTER TABLE results
  ADD PRIMARY KEY (id),
  ADD KEY fk_results_username (username),
  ADD KEY fk_results_lesson_id (test_id);

ALTER TABLE results
  ADD CONSTRAINT fk_results_lesson_id FOREIGN KEY (test_id) REFERENCES lesson (id),
  ADD CONSTRAINT fk_results_username FOREIGN KEY (username) REFERENCES users (username);
COMMIT;
