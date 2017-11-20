CREATE TABLE `videos` (
  `id`         INT(11)                NOT NULL,
  `hash`       VARCHAR(200)           NOT NULL,
  `type`       TEXT                   NOT NULL,
  `added`      DATETIME               NOT NULL,
  `is_deleted` ENUM ('true', 'false') NOT NULL DEFAULT 'false'
)
  ENGINE = InnoDB
  DEFAULT CHARSET = latin1;

ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `videos`
  MODIFY `id` INT(11) NOT NULL AUTO_INCREMENT,
  AUTO_INCREMENT = 2;
