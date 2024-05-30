ALTER TABLE lesson
    ADD PRIMARY KEY (id),
    ADD INDEX fk_learn_name (learn);

ALTER TABLE lesson
    ADD CONSTRAINT fk_learn_name FOREIGN KEY (learn) REFERENCES learn(name);
