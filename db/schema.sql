-- Database schema
-- This should be written in such a way that it can be run repeatedly - i.e. it is idempotent

CREATE TABLE IF NOT EXISTS `certs` (
  `serial_number` int,
  `public_key` text,
  `login_as` text,
  `parameters` text,
  `created_at` int
);
