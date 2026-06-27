-- Local dev seed data only — never run against production.
-- Admin login: admin@unicapress.com / admin123

INSERT INTO `adminusers`
  (`adminuser_email`, `adminuser_password_hash`, `adminuser_firstname`, `adminuser_lastname`, `adminuser_role`, `adminuser_active`, `adminuser_datecreated`, `adminuser_dateupdated`)
VALUES
  ('admin@unicapress.com', '$2y$10$LhFO5x9jAFV1ZmtiTt.JVOqTAWt57pRAYyGK91FK.bPhXAe2LysJW', 'Dev', 'Admin', 'admin', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `adminuser_email` = `adminuser_email`;

INSERT INTO `eventlogtypes` (`eventtype_name`, `eventtype_description`, `eventtype_active`) VALUES
  ('admin.login', 'Staff admin login', 1),
  ('admin.login_failed', 'Failed staff admin login attempt', 1),
  ('admin.logout', 'Staff admin logout', 1),
  ('content.update', 'Content Library entry created or edited', 1),
  ('email.update', 'Email Library entry created or edited', 1),
  ('email.sent', 'Email sent via sendEmailFromLibrary()', 1),
  ('contact.submitted', 'Public contact-form submission', 1),
  ('adminuser.update', 'Admin user account created, edited, deleted, or unlocked', 1),
  ('eventtype.update', 'Event log type created or edited', 1),
  ('waitlist.update', 'Waitlist entry edited or deleted', 1)
ON DUPLICATE KEY UPDATE `eventtype_description` = VALUES(`eventtype_description`);
