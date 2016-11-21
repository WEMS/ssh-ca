CREATE TABLE IF NOT EXISTS `certs` (
  `serial_number` int,
  `public_key` text,
  `login_as` text,
  `parameters` text,
  `created_at` int
);
