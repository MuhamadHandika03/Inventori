-- Seed users (safe: IGNORE)
INSERT IGNORE INTO users (username, password, role) VALUES
('admin', '$2y$12$kl3YXQVWVbGtrJnveDpM4.egL5C31WMEOEuLiRVJEb2T5fv4GSh5u', 'admin'),
('staff', '$2y$12$g2M20iSQVhVtvFxr6CWAEueNGKJN41GMR4o7wYRld78IKA87EBN9O', 'staff');
