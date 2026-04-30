
CREATE DATABASE IF NOT EXISTS voyago_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE voyago_db;

DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS special_offers;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS packages;
DROP TABLE IF EXISTS destinations;
DROP TABLE IF EXISTS users;



CREATE TABLE users (
    id            INT           AUTO_INCREMENT PRIMARY KEY,
    first_name    VARCHAR(100)  NOT NULL,
    last_name     VARCHAR(100)  NOT NULL,
    email         VARCHAR(255)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    role          ENUM('tourist','agency','provider','admin') NOT NULL DEFAULT 'tourist',
    company_name  VARCHAR(255)  NULL,
    avatar_url    VARCHAR(500)  NULL,
    is_verified   TINYINT(1)    NOT NULL DEFAULT 0,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


CREATE TABLE destinations (
    id           INT           AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(255)  NOT NULL,
    country      VARCHAR(100)  NOT NULL,
    region       VARCHAR(100)  NULL,
    type         VARCHAR(100)  NOT NULL DEFAULT 'Beach',
    badge        VARCHAR(100)  NULL,
    description  TEXT          NULL,
    long_desc    TEXT          NULL,
    img_url      VARCHAR(500)  NULL,
    best_time    VARCHAR(100)  NULL,
    climate      VARCHAR(100)  NULL,
    language     VARCHAR(100)  NULL,
    currency     VARCHAR(100)  NULL,
    price_from   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    rating       DECIMAL(3,2)  NOT NULL DEFAULT 0.00,
    review_count INT           NOT NULL DEFAULT 0,
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE packages (
    id              INT           AUTO_INCREMENT PRIMARY KEY,
    agency_id       INT           NOT NULL,
    title           VARCHAR(255)  NOT NULL,
    destination     VARCHAR(255)  NOT NULL,
    type            VARCHAR(100)  NOT NULL DEFAULT 'Adventure',
    duration_days   INT           NOT NULL DEFAULT 1,
    price           DECIMAL(10,2) NOT NULL,
    spots_available INT           NOT NULL DEFAULT 10,
    group_size_max  INT           NULL,
    description     TEXT          NULL,
    long_desc       TEXT          NULL,
    img_url         VARCHAR(500)  NULL,
    includes        TEXT          NULL,
    excludes        TEXT          NULL,
    itinerary       TEXT          NULL,
    rating          DECIMAL(3,2)  NOT NULL DEFAULT 0.00,
    review_count    INT           NOT NULL DEFAULT 0,
    is_active       TINYINT(1)    NOT NULL DEFAULT 1,
    created_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (agency_id) REFERENCES users(id) ON DELETE CASCADE
);


CREATE TABLE services (
    id            INT           AUTO_INCREMENT PRIMARY KEY,
    provider_id   INT           NOT NULL,
    title         VARCHAR(255)  NOT NULL,
    type          VARCHAR(100)  NOT NULL DEFAULT 'Transport',
    icon          VARCHAR(20)   NULL,
    price         DECIMAL(10,2) NOT NULL,
    price_unit    VARCHAR(50)   NOT NULL DEFAULT 'day',
    description   TEXT          NULL,
    long_desc     TEXT          NULL,
    img_url       VARCHAR(500)  NULL,
    features      TEXT          NULL,
    is_available  TINYINT(1)    NOT NULL DEFAULT 1,
    rating        DECIMAL(3,2)  NOT NULL DEFAULT 0.00,
    review_count  INT           NOT NULL DEFAULT 0,
    booking_count INT           NOT NULL DEFAULT 0,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE CASCADE
);


CREATE TABLE bookings (
    id             INT           AUTO_INCREMENT PRIMARY KEY,
    user_id        INT           NOT NULL,
    package_id     INT           NULL,
    service_id     INT           NULL,
    destination_id INT           NULL,
    booking_type   ENUM('package','service','destination') NOT NULL,
    check_in       DATE          NOT NULL,
    check_out      DATE          NULL,
    guests         INT           NOT NULL DEFAULT 1,
    total_price    DECIMAL(10,2) NOT NULL,
    notes          TEXT          NULL,
    status         ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'confirmed',
    created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)         REFERENCES users(id)        ON DELETE CASCADE,
    FOREIGN KEY (package_id)      REFERENCES packages(id)     ON DELETE SET NULL,
    FOREIGN KEY (service_id)      REFERENCES services(id)     ON DELETE SET NULL,
    FOREIGN KEY (destination_id)  REFERENCES destinations(id) ON DELETE SET NULL
);

CREATE TABLE reviews (
    id             INT  AUTO_INCREMENT PRIMARY KEY,
    user_id        INT  NOT NULL,
    package_id     INT  NULL,
    service_id     INT  NULL,
    destination_id INT  NULL,
    item_type      ENUM('package','service','destination') NOT NULL,
    rating         INT  NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment        TEXT NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)         REFERENCES users(id)        ON DELETE CASCADE,
    FOREIGN KEY (package_id)      REFERENCES packages(id)     ON DELETE CASCADE,
    FOREIGN KEY (service_id)      REFERENCES services(id)     ON DELETE CASCADE,
    FOREIGN KEY (destination_id)  REFERENCES destinations(id) ON DELETE CASCADE
);



CREATE TABLE special_offers (
    id           INT           AUTO_INCREMENT PRIMARY KEY,
    agency_id    INT           NOT NULL,
    title        VARCHAR(255)  NOT NULL,
    discount_pct INT           NOT NULL DEFAULT 10,
    start_date   DATE          NULL,
    end_date     DATE          NULL,
    description  TEXT          NULL,
    type         VARCHAR(100)  NOT NULL DEFAULT 'General',
    source       VARCHAR(50)   NOT NULL DEFAULT 'manual',
    is_active    TINYINT(1)    NOT NULL DEFAULT 1,
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (agency_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE messages (
    id          INT          AUTO_INCREMENT PRIMARY KEY,
    sender_id   INT          NOT NULL,
    receiver_id INT          NOT NULL,
    subject     VARCHAR(255) NULL,
    content     TEXT         NOT NULL,
    is_read     TINYINT(1)   NOT NULL DEFAULT 0,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE INDEX idx_packages_agency   ON packages(agency_id);
CREATE INDEX idx_packages_type     ON packages(type);
CREATE INDEX idx_services_provider ON services(provider_id);
CREATE INDEX idx_services_type     ON services(type);
CREATE INDEX idx_bookings_user     ON bookings(user_id);
CREATE INDEX idx_bookings_status   ON bookings(status);
CREATE INDEX idx_reviews_package   ON reviews(package_id);
CREATE INDEX idx_reviews_service   ON reviews(service_id);
CREATE INDEX idx_reviews_dest      ON reviews(destination_id);
CREATE INDEX idx_messages_receiver ON messages(receiver_id);
CREATE INDEX idx_messages_sender   ON messages(sender_id);


INSERT INTO users (first_name, last_name, email, password_hash, role, company_name, is_verified) VALUES
('Alice',  'Smith',   'alice@example.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tourist',  NULL,              1),
('Bob',    'Jones',   'bob@example.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'tourist',  NULL,              1),
('Sarah',  'Connor',  'agency@example.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agency',   'Alpine Escapes',  1),
('Marco',  'Rossi',   'provider@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'provider', 'Swift Transfers', 1),
('Admin',  'Voyago',  'admin@voyago.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',    NULL,              1);


INSERT INTO destinations (name, country, region, type, badge, description, long_desc, img_url, best_time, climate, language, currency, price_from, rating, review_count) VALUES
('Santorini',  'Greece',      'Cyclades',             'Beach',     '🔥 Trending', 'Famous for white-washed buildings and stunning sunsets.',    'Santorini is one of the most iconic islands in the world, perched on the rim of an ancient volcanic caldera.',   'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=1200&q=80', 'April - October',          'Mediterranean',     'Greek',                'Euro (€)',             890.00, 4.90, 2140),
('Kyoto',      'Japan',       'Kansai',               'Cultural',  '🌸 Seasonal', 'Ancient temples, bamboo groves and world-class cuisine.',    'Kyoto served as Japan''s imperial capital for over a thousand years and remains the country''s cultural heart.',  'https://images.unsplash.com/photo-1493976040374-85c8e12f0c0e?w=1200&q=80', 'March - May, Oct - Nov',  'Humid subtropical', 'Japanese',             'Japanese Yen (JPY)',  1200.00, 4.80, 1830),
('Marrakech',  'Morocco',     'South Morocco',        'Cultural',  '✨ New',      'A sensory feast of souks, spices and vibrant culture.',      'Marrakech is a city of contrasts — ancient medina lanes give way to modern boulevards.',                         'https://images.unsplash.com/photo-1539020140153-e479b8c22e70?w=1200&q=80', 'March - May, Sep - Nov',  'Semi-arid',         'Arabic / Berber',      'Moroccan Dirham',      640.00, 4.70,  950),
('Bali',       'Indonesia',   'Lesser Sunda Islands', 'Beach',     '🏝️ Popular', 'Tropical paradise with terraced rice paddies and temples.',  'Bali is the Island of the Gods where Hinduism infuses every aspect of daily life.',                               'https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=1200&q=80', 'April - October',          'Tropical',          'Balinese / Indonesian','Indonesian Rupiah',    780.00, 4.80, 3200),
('Maldives',   'Maldives',    NULL,                   'Beach',     '💎 Luxury',  'Overwater bungalows and pristine private beaches.',          'Overwater bungalows, house reefs teeming with marine life and absolute serenity.',                                'https://images.unsplash.com/photo-1514282401047-d79a71a590e8?w=1200&q=80', 'November - April',         'Tropical',          'Dhivehi',              'Maldivian Rufiyaa',  3200.00, 5.00,  910),
('Swiss Alps', 'Switzerland', 'Valais',               'Adventure', '❄️ Winter',  'World-class skiing and dramatic mountain scenery.',          'World-class skiing, charming chalets and some of the most dramatic mountain scenery anywhere.',                   'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1200&q=80', 'December - March',         'Alpine',            'German / French',      'Swiss Franc (CHF)',   1400.00, 4.90,  870);


INSERT INTO packages (agency_id, title, destination, type, duration_days, price, spots_available, group_size_max, description, long_desc, img_url, includes, excludes, itinerary, rating, review_count) VALUES
(3, 'Swiss Alps Winter Retreat', 'Switzerland', 'Adventure', 8,  2490.00,  4, 12,
 'Ski, snowboard and relax in cozy mountain chalets.',
 'Escape to the pristine powder slopes of the Swiss Alps on this ultimate winter retreat.',
 'https://images.unsplash.com/photo-1516483638261-f4dbaf036963?w=1200&q=80',
 '["Ski pass","Chalet accommodation","Airport transfers","Daily breakfast & dinner","Ski equipment hire"]',
 '["Flights","Travel insurance","Lunch on mountain"]',
 '[{"day":1,"title":"Arrival","desc":"Transfer from Geneva Airport to your private chalet."},{"day":2,"title":"First Day on Slopes","desc":"Morning ski lesson. Afternoon free skiing."},{"day":8,"title":"Departure","desc":"Transfer back to Geneva."}]',
 4.90, 214),

(3, 'Bali Spiritual Journey', 'Indonesia', 'Cultural', 10, 1650.00, 8, 10,
 'Discover temples, rice terraces and traditional healing rituals.',
 'This 10-day journey through Bali takes you far beyond the tourist trail.',
 'https://images.unsplash.com/photo-1604999565976-8913ad2ddb7c?w=1200&q=80',
 '["10 nights accommodation","Daily breakfast","All temple visits","Healer session","Cooking class"]',
 '["International flights","Travel insurance","Visa fees"]',
 '[{"day":1,"title":"Arrival in Ubud","desc":"Welcome ceremony and orientation walk."},{"day":10,"title":"Departure","desc":"Transfer to Ngurah Rai International Airport."}]',
 4.80, 183),

(3, 'Greek Island Odyssey', 'Greece', 'Beach', 14, 3100.00, 2, 8,
 'Sail between Santorini, Mykonos and Crete on a private yacht.',
 'Set sail on a 14-day private yacht odyssey through the Aegean.',
 'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?w=1200&q=80',
 '["14 nights aboard private yacht","Full crew","All meals","Island excursions","Snorkelling gear"]',
 '["Flights","Travel insurance","Personal drinks","Tips for crew"]',
 '[{"day":1,"title":"Athens Departure","desc":"Board the yacht at Piraeus harbour."},{"day":14,"title":"Return to Athens","desc":"Transfer to airport."}]',
 4.90, 320),

(3, 'Sahara Desert Adventure', 'Morocco', 'Adventure', 6, 980.00, 12, 16,
 'Ride camels at sunset and sleep under the stars.',
 'Camel treks, starlit desert camps and ancient kasbahs in Morocco.',
 'https://images.unsplash.com/photo-1509316785289-025f5b846b35?w=1200&q=80',
 '["Camel trek","Desert camp","Guide included","All meals in desert"]',
 '["Flights","Travel insurance","Personal shopping"]',
 '[{"day":1,"title":"Marrakech arrival","desc":"Check-in and orientation."},{"day":6,"title":"Departure","desc":"Transfer to Marrakech airport."}]',
 4.70, 98);


INSERT INTO services (provider_id, title, type, icon, price, price_unit, description, long_desc, img_url, features, is_available, rating, review_count, booking_count) VALUES
(4, 'Private Airport Transfer', 'Transport', '🚐', 45.00, 'trip',
 'Comfortable door-to-door transfers from any airport.',
 'Start and end your trip in comfort with Swift Transfers. Our drivers monitor your flight in real time.',
 'https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?w=1200&q=80',
 '["Flight tracking","Meet & greet","Free 60-min waiting","Fixed price","Child seats available"]',
 1, 4.90, 540, 124),

(4, 'Certified Mountain Guide', 'Adventure', '🧗', 120.00, 'day',
 'Expert local guides for hiking and multi-day treks.',
 'Our IFMGA-certified mountain guides bring years of professional experience in alpine environments.',
 'https://images.unsplash.com/photo-1551632811-561732d1e306?w=1200&q=80',
 '["IFMGA certified","Safety equipment included","First aid qualified","Custom route planning"]',
 1, 4.80, 312, 67),

(4, 'Private Chef Experience', 'Food', '🍽️', 180.00, 'evening',
 'A local chef prepares an authentic multi-course dinner.',
 'Imagine a talented local chef arriving at your villa, armed with the finest produce from the morning market.',
 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=1200&q=80',
 '["Market shopping optional","3-course menu","All ingredients","Full kitchen clean-up","Dietary options"]',
 0, 5.00, 178, 45),

(4, 'City Walking Tour', 'Tours', '🗺️', 35.00, 'person',
 'Immersive 3-hour walking tours in 50+ cities.',
 'Expert local storytellers guide you through the hidden gems and iconic landmarks of the city.',
 'https://images.unsplash.com/photo-1471341971476-ae15ff5dd4ea?w=1200&q=80',
 '["Small groups (max 8)","Free cancellation","Multiple languages","No booking fee"]',
 1, 4.90, 764, 312);



INSERT INTO bookings (user_id, package_id, service_id, destination_id, booking_type, check_in, check_out, guests, total_price, notes, status) VALUES
(1, 1, NULL, NULL, 'package',     '2025-06-12', '2025-06-20', 2, 4980.00, NULL,                          'confirmed'),
(2, 3, NULL, NULL, 'package',     '2025-07-10', '2025-07-24', 1, 3100.00, 'Vegetarian meals please',    'confirmed'),
(1, NULL, 1, NULL, 'service',     '2025-06-12', '2025-06-12', 2,   90.00, NULL,                          'confirmed'),
(2, NULL, 2, NULL, 'service',     '2025-08-05', '2025-08-07', 1,  240.00, 'Intermediate hiking level',  'completed'),
(1, 2,    NULL, NULL, 'package',  '2025-09-01', '2025-09-11', 1, 1650.00, NULL,                          'pending');


INSERT INTO reviews (user_id, package_id, service_id, destination_id, item_type, rating, comment) VALUES
(2, 3, NULL, NULL, 'package',     5, 'Absolutely magical. The crew was professional and the sailing was breathtaking.'),
(1, 1, NULL, NULL, 'package',     5, 'Best ski holiday I have ever had. The chalet was stunning.'),
(2, NULL, 1, NULL, 'service',     5, 'Driver was waiting with a sign, car was immaculate, price exactly as quoted.'),
(1, NULL, 2, NULL, 'service',     4, 'Great guide, very knowledgeable. The route was challenging in the best way.'),
(2, NULL, NULL, 1, 'destination', 5, 'Santorini exceeded every expectation. The caldera sailing trip was the highlight.');


INSERT INTO special_offers (agency_id, title, discount_pct, start_date, end_date, description, is_active) VALUES
(3, 'Early Bird Summer',   25, '2025-06-01', '2025-06-30', 'Book any summer package before June 30 and get 25% off.', 1),
(3, 'Returning Traveller', 15, '2025-07-01', '2025-07-31', 'Exclusive discount for customers who have booked with us before.', 1),
(3, 'Last Minute Alps',    30, '2025-06-15', '2025-06-20', 'Limited spots available for the Swiss Alps Retreat at a special rate.', 1);


INSERT INTO messages (sender_id, receiver_id, subject, content, is_read) VALUES
(1, 3, 'Question about Alpine package',     'Hi, I had a question about the ski equipment rental included in the package. Is it full rental including boots?', 0),
(2, 3, 'Booking confirmation request',      'Could you please confirm my reservation for July 10th? I have not received a confirmation email yet.', 0),
(1, 3, 'Special dietary requirements',      'I wanted to let you know that I have a vegetarian diet. What options are available?', 1),
(3, 1, 'Re: Question about Alpine package', 'Hi Alice! Yes, the ski pass includes full equipment rental — skis, boots, poles and helmet. See you on the slopes!', 0);

-- at the very bottom, after all INSERT INTO packages ... statements
ALTER TABLE packages AUTO_INCREMENT = 1000;
ALTER TABLE services AUTO_INCREMENT = 2000;