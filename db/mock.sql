-- Clientes
INSERT INTO client (id, name, email, type) VALUES 
(1, 'Miguel Standard', 'miguel.std@4vgym.com', 'standard'),
(2, 'Laura Premium', 'laura.pro@4vgym.com', 'premium');

-- Actividades
INSERT INTO activity (id, type, max_participants, date_start, date_end) VALUES 
(1, 'BodyPump', 20, '2024-05-10 10:00:00', '2024-05-10 11:00:00'),
(2, 'Spinning', 5, '2027-03-10 10:00:00', '2027-03-10 10:45:00'),
(3, 'Core', 20, '2027-03-10 12:00:00', '2027-03-10 12:30:00');

-- Canciones
INSERT INTO song (id, name, duration_seconds, activity_id) VALUES 
(1, 'Eye of the Tiger', 240, 2);

-- Reservas
INSERT INTO booking (id, client_id, activity_id) VALUES 
(1, 1, 1),
(2, 2, 2);