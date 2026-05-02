export const destinations = [
  { id: 1, name: 'Santorini, Greece', img: 'https://images.unsplash.com/photo-1613395877344-13d4a8e0d49e?q=80&w=735&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D', badge: '🔥 Trending', country: 'Greece', type: 'Beach', rating: 4.9, reviews: 2140, from: 890, desc: 'Famous for white-washed buildings, stunning sunsets and crystal-clear Aegean waters.', highlights: ['Sunsets', 'Volcanic beaches', 'Greek cuisine'], thingsToDo: [
    { title: 'Sunset in Oia', icon: '🌅', desc: 'Watch the world-famous sunset from the Byzantine Castle ruins.', img: 'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?q=80&w=600&auto=format&fit=crop' },
    { title: 'Caldera Hike', icon: '🥾', desc: 'Hike the scenic path from Fira to Oia for breathtaking views.', img: 'https://images.unsplash.com/photo-1541432901912-2d836396841b?q=80&w=600&auto=format&fit=crop' },
    { title: 'Ammoudi Bay', icon: '🛶', desc: 'Descend to this charming bay for fresh seafood and cliff jumping.', img: 'https://images.unsplash.com/photo-1621274220348-41007804107e?q=80&w=600&auto=format&fit=crop' },
    { title: 'Akrotiri Visit', icon: '🏛️', desc: 'Explore the remarkably preserved Bronze Age settlement.', img: 'https://images.unsplash.com/photo-1571403251703-91122a27526f?q=80&w=600&auto=format&fit=crop' }
  ], bestTime: 'April to November' },
  { id: 2, name: 'Kyoto, Japan', img: 'https://images.unsplash.com/photo-1558862107-d49ef2a04d72?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Nnx8a3lvdG98ZW58MHx8MHx8fDA%3D', badge: '🌸 Seasonal', country: 'Japan', type: 'Cultural', rating: 4.8, reviews: 1830, from: 1200, desc: 'Ancient temples, bamboo groves, geisha districts and world-class cuisine.', highlights: ['Temples', 'Tea ceremony', 'Bamboo forest'], thingsToDo: [
    { title: 'Fushimi Inari', icon: '⛩️', desc: 'Walk through thousands of vibrant vermilion torii gates.' },
    { title: 'Bamboo Grove', icon: '🎋', desc: 'Immerse yourself in the ethereal Arashiyama Bamboo Grove.' },
    { title: 'Gion District', icon: '🏮', desc: 'Explore the traditional geisha district and enjoy fine dining.' },
    { title: 'Kinkaku-ji', icon: '✨', desc: 'Marvel at the stunning Golden Pavilion reflected in its pond.' }
  ], bestTime: 'March to May & Oct to Nov' },
  { id: 3, name: 'Marrakech, Morocco', img: 'https://i.pinimg.com/1200x/5a/9b/cd/5a9bcdfaa42bfc716a2aed9a45863d0e.jpg', badge: '✨ New', country: 'Morocco', type: 'Cultural', rating: 4.7, reviews: 950, from: 640, desc: 'A sensory feast of souks, spices, stunning riads and the vibrant Djemaa el-Fna.', highlights: ['Souks', 'Riads', 'Desert day trips'], thingsToDo: [
    { title: 'Jemaa el-Fnaa', icon: '🎪', desc: 'Experience the bustling heart of the Medina with performers and stalls.' },
    { title: 'Majorelle Garden', icon: '🌵', desc: 'Wander through the botanical oasis designed by Jacques Majorelle.' },
    { title: 'Bahia Palace', icon: '🏰', desc: 'Admire the intricate Moroccan architecture and lush courtyards.' },
    { title: 'Medina Souks', icon: '👜', desc: 'Get lost in the maze of shops selling spices, leather, and crafts.' }
  ], bestTime: 'March to May & Sep to Nov' },
  { id: 4, name: 'Bali, Indonesia', img: 'https://plus.unsplash.com/premium_photo-1752596351361-220c13a6fed9?q=80&w=1171&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D', badge: '🏝️ Popular', country: 'Indonesia', type: 'Beach', rating: 4.8, reviews: 3200, from: 780, desc: 'Tropical paradise with terraced rice paddies, sacred temples and vibrant nightlife.', highlights: ['Rice terraces', 'Surfing', 'Spiritual healing'], thingsToDo: [
    { title: 'Uluwatu Temple', icon: '🛕', desc: 'Visit the cliffside temple and watch a traditional Kecak dance.' },
    { title: 'Rice Terraces', icon: '🌾', desc: 'Explore the stunning Tegalalang Rice Terraces in Ubud.' },
    { title: 'Monkey Forest', icon: '🐒', desc: 'Walk through the sanctuary and meet the long-tailed macaques.' },
    { title: 'Mount Batur', icon: '🌋', desc: 'Hike to the summit for a spectacular sunrise view over the island.' }
  ], bestTime: 'April to October' },
  { id: 5, name: 'Patagonia, Argentina', img: 'https://i.pinimg.com/1200x/a0/50/b0/a050b07c06a453ccea4abecc222c6b47.jpg', badge: null, country: 'Argentina', type: 'Adventure', rating: 4.9, reviews: 620, from: 1800, desc: 'Jaw-dropping glaciers, wild pampas and the dramatic peaks of Torres del Paine.', highlights: ['Trekking', 'Glaciers', 'Wildlife'], thingsToDo: [
    { title: 'Perito Moreno', icon: '❄️', desc: 'Witness the majestic glacier and take a boat tour to see ice calving.' },
    { title: 'Fitz Roy Trek', icon: '🏔️', desc: 'Embark on a world-class hike with views of the iconic granite peaks.' },
    { title: 'Torres del Paine', icon: '⛰️', desc: 'Explore the national park with its towers, lakes, and glaciers.' },
    { title: 'Beagle Channel', icon: '🚢', desc: 'Sail the historic channel and spot seals, penguins, and birdlife.' }
  ], bestTime: 'November to March' },
  { id: 6, name: 'Amalfi Coast, Italy', img: 'https://i.pinimg.com/1200x/de/ab/a7/deaba788c126ab8b25c3a175ab665273.jpg', badge: '🏅 Top rated', country: 'Italy', type: 'Beach', rating: 4.9, reviews: 1420, from: 950, desc: 'Cliffside villages, turquoise water and fresh limoncello along Italy\'s most scenic coast.', highlights: ['Boat trips', 'Hiking trails', 'Coastal villages'], thingsToDo: [
    { title: 'Positano Beach', icon: '🏖️', desc: 'Relax on the iconic pebble beach surrounded by colorful houses.' },
    { title: 'Path of the Gods', icon: '⛅', desc: 'Hike the legendary trail for the best coastal panoramas in Italy.' },
    { title: 'Boat to Capri', icon: '🛥️', desc: 'Take a day trip to the glamorous island and visit the Blue Grotto.' },
    { title: 'Ravello Gardens', icon: '🌺', desc: 'Visit Villa Cimbrone for its famous Terrace of Infinity.' }
  ], bestTime: 'May to September' },
  { id: 7, name: 'New York City, USA', img: 'https://i.pinimg.com/736x/a3/79/23/a37923a2da559e808d7e7e58ec689129.jpg', badge: null, country: 'USA', type: 'City', rating: 4.7, reviews: 4100, from: 700, desc: 'The city that never sleeps — world-class museums, iconic skylines and every cuisine imaginable.', highlights: ['Central Park', 'Broadway', 'Food scene'], thingsToDo: [
    { title: 'Central Park', icon: '🌳', desc: 'Stroll through the massive green oasis in the heart of Manhattan.' },
    { title: 'Top of the Rock', icon: '🏙️', desc: 'Get the best views of the Empire State Building and the city.' },
    { title: 'Broadway Show', icon: '🎭', desc: 'Experience a world-class theater performance in the theater district.' },
    { title: 'High Line', icon: '🛤️', desc: 'Walk along the elevated park built on a historic rail line.' }
  ], bestTime: 'April to June & Sep to Nov' },
  { id: 8, name: 'Serengeti, Tanzania', img: 'https://images.unsplash.com/photo-1709402606682-400133d92ab2?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OHx8c2FmYXJpfGVufDB8fDB8fHww', badge: '🦁 Wildlife', country: 'Tanzania', type: 'Nature', rating: 5.0, reviews: 480, from: 2200, desc: 'The greatest wildlife spectacle on Earth — witness the Great Migration up close.', highlights: ['Safari', 'Big Five', 'Hot air balloon'], thingsToDo: [
    { title: 'Game Drives', icon: '🚙', desc: 'Spot the "Big Five" and thousands of other animals in the wild.' },
    { title: 'Balloon Safari', icon: '🎈', desc: 'Float over the savannah at sunrise for a unique perspective.' },
    { title: 'Maasai Village', icon: '🏠', desc: 'Learn about the traditions and culture of the Maasai people.' },
    { title: 'Migration Watch', icon: '🦓', desc: 'Witness the epic annual journey of wildebeest and zebras.' }
  ], bestTime: 'June to October' },
  { id: 9, name: 'Swiss Alps, Switzerland', img: 'https://i.pinimg.com/736x/36/dc/3e/36dc3eb4132f928aceaf5e180e79c83c.jpg', badge: '❄️ Winter', country: 'Switzerland', type: 'Adventure', rating: 4.9, reviews: 870, from: 1400, desc: 'World-class skiing, charming chalets and some of the most dramatic mountain scenery anywhere.', highlights: ['Skiing', 'Hiking', 'Scenic trains'], thingsToDo: [
    { title: 'Jungfraujoch', icon: '🚉', desc: 'Visit the "Top of Europe" via a spectacular mountain railway.' },
    { title: 'Skiing Zermatt', icon: '⛷️', desc: 'Enjoy world-class slopes with views of the iconic Matterhorn.' },
    { title: 'Lake Brienz', icon: '🚢', desc: 'Take a boat trip on the stunning turquoise waters of the lake.' },
    { title: 'Glacier Express', icon: '🚄', desc: 'Ride one of the world\'s most beautiful scenic train journeys.' }
  ], bestTime: 'Dec to Mar & June to Aug' },
  { id: 10, name: 'Maldives', img: 'https://i.pinimg.com/1200x/a5/b9/e4/a5b9e469ed6ee19ec5a79eeb442dac13.jpg', badge: '💎 Luxury', country: 'Maldives', type: 'Beach', rating: 5.0, reviews: 910, from: 3200, desc: 'Overwater bungalows, house reefs teeming with marine life and absolute serenity.', highlights: ['Overwater villas', 'Snorkelling', 'Diving'], thingsToDo: [
    { title: 'Snorkeling', icon: '🤿', desc: 'Discover vibrant coral reefs and tropical fish right off your villa.' },
    { title: 'Island Hopping', icon: '🏝️', desc: 'Visit local inhabited islands and experience Maldivian culture.' },
    { title: 'Sandbank Picnic', icon: '🧺', desc: 'Enjoy a private, romantic lunch on a secluded patch of white sand.' },
    { title: 'Sunset Cruise', icon: '🛥️', desc: 'Watch dolphins play in the wake as the sun sets over the ocean.' }
  ], bestTime: 'November to April' },
  { id: 11, name: 'Prague, Czech Republic', img: 'https://i.pinimg.com/1200x/90/9e/da/909eda8b6792a76749cdee3d198ceec9.jpg', badge: null, country: 'Czech Rep.', type: 'City', rating: 4.7, reviews: 1100, from: 490, desc: 'Fairy-tale architecture, cobblestone alleys, medieval castles and vibrant café culture.', highlights: ['Old Town Square', 'Castle district', 'River cruises'], thingsToDo: [
    { title: 'Charles Bridge', icon: '🌉', desc: 'Walk across the historic bridge at sunrise for a magical atmosphere.' },
    { title: 'Prague Castle', icon: '🏰', desc: 'Explore the largest ancient castle complex in the world.' },
    { title: 'Old Town Square', icon: '🏘️', desc: 'Watch the Astronomical Clock chime and enjoy the lively square.' },
    { title: 'Vltava Cruise', icon: '⛴️', desc: 'See the city\'s stunning architecture from a different perspective.' }
  ], bestTime: 'May to September' },
  { id: 12, name: 'Amazon, Brazil', img: 'https://i.pinimg.com/736x/a5/58/20/a55820860b80f587a7cedffab2013f00.jpg', badge: '🌿 Eco', country: 'Brazil', type: 'Nature', rating: 4.8, reviews: 350, from: 1600, desc: 'Immerse yourself in the world\'s largest rainforest — wildlife, rivers and indigenous culture.', highlights: ['Jungle treks', 'River boats', 'Wildlife spotting'], thingsToDo: [
    { title: 'Canopy Walk', icon: '🧗', desc: 'Walk among the treetops and observe rainforest life from above.' },
    { title: 'Piranha Fishing', icon: '🎣', desc: 'Try your luck at fishing for the famous Amazonian piranha.' },
    { title: 'Jungle Trek', icon: '👣', desc: 'Learn about medicinal plants and survival skills from local guides.' },
    { title: 'Meeting of Waters', icon: '🌊', desc: 'Witness the dark Negro and sandy Solimões rivers flow side-by-side.' }
  ], bestTime: 'July to December' },
]

export const packages = [
  { id: 1001, title: 'Swiss Alps Winter Retreat', agency: 'Alpine Escapes', img: 'https://i.pinimg.com/1200x/23/c2/fd/23c2fd9b59a69106db324c0022d7d051.jpg', type: 'Adventure', duration: 8, rating: 4.9, reviews: 214, spots: 4, price: 2490, desc: 'Ski, snowboard and relax in cozy mountain chalets with breathtaking alpine views.', includes: ['Ski pass', 'Chalet accommodation', 'Airport transfer'], itinerary: [
    { day: 1, title: 'Arrival & Zermatt Transfer', desc: 'Arrive in Zurich and take the scenic train to the car-free village of Zermatt.' },
    { day: 2, title: 'Matterhorn Slopes', desc: 'Hit the world-class slopes of the Matterhorn Ski Paradise.' },
    { day: 3, title: 'Glacier Excursion', desc: 'Visit the highest cable car station in Europe for stunning 360-degree views.' },
    { day: 4, title: 'Snowshoeing Adventure', desc: 'Explore hidden alpine trails away from the crowds.' },
    { day: 5, title: 'Spa & Relaxation', desc: 'Unwind in a luxury mountain spa with outdoor heated pools.' },
    { day: 6, title: 'Italian Side Skiing', desc: 'Ski across the border to Cervinia, Italy for a authentic pasta lunch.' },
    { day: 7, title: 'Fondue Night', desc: 'Enjoy a traditional Swiss farewell dinner in a cozy mountain hut.' },
    { day: 8, title: 'Departure', desc: 'One last alpine breakfast before your return journey.' }
  ] },
  { id: 1002, title: 'Bali Spiritual Journey', agency: 'Serenity Travels', img: 'https://i.pinimg.com/736x/06/55/69/065569c64ec1a5e7d83812ababddbe3b.jpg', type: 'Cultural', duration: 10, rating: 4.8, reviews: 183, spots: 8, price: 1650, desc: 'Discover temples, rice terraces and traditional healing rituals across the island of gods.', includes: ['Temple visits', 'Healing ceremony', 'Rice terrace trek'], itinerary: [
    { day: 1, title: 'Welcome to Ubud', desc: 'Arrive in Balis cultural heart and settle into your jungle villa.' },
    { day: 2, title: 'Yoga & Monkey Forest', desc: 'Start with morning yoga followed by a visit to the sacred Monkey Forest.' },
    { day: 3, title: 'Rice Terrace Hike', desc: 'Trek through the stunning Tegalalang Rice Terraces at sunrise.' },
    { day: 4, title: 'Purification Ceremony', desc: 'Experience a traditional water blessing at Tirta Empul temple.' },
    { day: 5, title: 'Balinese Cooking', desc: 'Learn the secrets of local spices in a hands-on cooking class.' },
    { day: 6, title: 'Rural Sidemen', desc: 'Transfer to the quiet valley of Sidemen for authentic village life.' },
    { day: 7, title: 'Spiritual Meditation', desc: 'Private session with a local priest in a hidden river temple.' },
    { day: 8, title: 'Mount Batur Sunrise', desc: 'Conquer the active volcano for a spectacular sunrise breakfast.' },
    { day: 9, title: 'Sound Healing', desc: 'Conclude your journey with a transformative sound bath experience.' },
    { day: 10, title: 'Departure', desc: 'Final blessing before your transfer to the airport.' }
  ] },
  { id: 1003, title: 'Greek Island Odyssey', agency: 'Aegean Blue Tours', img: 'https://i.pinimg.com/1200x/45/b0/50/45b05007a015ceb5bbe3da40adffda62.jpg', type: 'Beach', duration: 14, rating: 4.9, reviews: 320, spots: 2, price: 3100, desc: 'Sail between Santorini, Mykonos and Crete on a private yacht with full crew.', includes: ['Private yacht', 'All meals', 'Island hopping'], itinerary: [
    { day: 1, title: 'Athens Arrival', desc: 'Check in to your hotel with views of the Parthenon.' },
    { day: 2, title: 'Acropolis & Ferry', desc: 'Morning Acropolis tour then ferry to the vibrant Mykonos.' },
    { day: 3, title: 'Mykonos Bliss', desc: 'Relax on sandy beaches and explore the iconic white alleys.' },
    { day: 4, title: 'Delos Excursion', desc: 'Visit the sacred birthplace of Apollo on a nearby island.' },
    { day: 5, title: 'Ferry to Naxos', desc: 'Travel to the largest and greenest Cycladic island.' },
    { day: 6, title: 'Mountain Villages', desc: 'Explore the marble-paved streets of Aperathos village.' },
    { day: 7, title: 'Santorini Arrival', desc: 'Ferry to the world-famous volcanic island of Santorini.' },
    { day: 8, title: 'Oia Sunset', desc: 'Private tour of Oia followed by a sunset dinner overlooking the caldera.' },
    { day: 9, title: 'Volcano Boat Trip', desc: 'Sail to the hot springs and hike the volcanic crater.' },
    { day: 10, title: 'Wine Tasting', desc: 'Sample the unique volcanic wines at a cliffside estate.' },
    { day: 11, title: 'Crete Bound', desc: 'Fast ferry to Heraklion, the capital of Crete.' },
    { day: 12, title: 'Knossos Palace', desc: 'Discover the ancient Minoan civilization and the Labyrinth.' },
    { day: 13, title: 'Chania Farewell', desc: 'Drive to the Venetian harbor of Chania for a final Greek feast.' },
    { day: 14, title: 'Departure', desc: 'Transfer to Chania airport for your flight home.' }
  ] },
  { id: 1004, title: 'Sahara Desert Adventure', agency: 'Desert Wind Co.', img: 'https://i.pinimg.com/736x/32/50/c9/3250c9de2436cea0d407fd390169b779.jpg', type: 'Adventure', duration: 6, rating: 4.7, reviews: 98, spots: 12, price: 980, desc: 'Ride camels at sunset, sleep under the stars and explore ancient kasbahs in Morocco.', includes: ['Camel trek', 'Desert camp', 'Guide included'], itinerary: [
    { day: 1, title: 'Marrakech Arrival', desc: 'Experience the magic of the Jemaa el-Fnaa square at night.' },
    { day: 2, title: 'Atlas Mountains', desc: 'Cross the High Atlas and visit the Ait Ben Haddou Kasbah.' },
    { day: 3, title: 'Dades Valley', desc: 'Journey through the "Road of a Thousand Kasbahs" to the gorges.' },
    { day: 4, title: 'Sahara Night', desc: 'Camel trek into the Merzouga dunes for a night in a luxury camp.' },
    { day: 5, title: 'Draa Valley', desc: 'Pass through the vast palm groves of the Draa Valley to Ouarzazate.' },
    { day: 6, title: 'Departure', desc: 'Return to Marrakech for your final souvenir shopping and flight.' }
  ] },
  { id: 1005, title: 'Tokyo Family Explorer', agency: 'Rising Sun Tours', img: 'https://i.pinimg.com/736x/3d/29/3d/3d293ddd78de491adc8ce3809facdb05.jpg', type: 'Family', duration: 9, rating: 4.8, reviews: 145, spots: 6, price: 2200, desc: 'Anime, tech, temples and theme parks — a magical Japanese adventure for the whole family.', includes: ['Theme park tickets', 'Family room', 'Kid-friendly tours'], itinerary: [
    { day: 1, title: 'Arrival & Shinjuku', desc: 'Welcome to Tokyo! See the city from the Metropolitan Government Building.' },
    { day: 2, title: 'Harajuku & Tech', desc: 'Trendy Harajuku fashion and the latest gadgets in Akihabara.' },
    { day: 3, title: 'Disneyland Magic', desc: 'Full day of fun and parades at Tokyo Disneyland.' },
    { day: 4, title: 'DisneySea Adventure', desc: 'Explore the unique nautical-themed DisneySea park.' },
    { day: 5, title: 'Studio Ghibli', desc: 'Enter the whimsical world of Hayao Miyazaki (subject to ticket availability).' },
    { day: 6, title: 'teamLab Digital Art', desc: 'Immerse yourselves in the borderless world of digital light.' },
    { day: 7, title: 'Traditional Asakusa', desc: 'Rickshaw ride through old Tokyo and Senso-ji temple.' },
    { day: 8, title: 'Robot Show & VR', desc: 'Experience Tokyos futuristic side with high-tech family entertainment.' },
    { day: 9, title: 'Departure', desc: 'Final sushi lunch before heading to Narita/Haneda airport.' }
  ] },
  { id: 1006, title: 'Amalfi Coast Drive', agency: 'Bella Italia', img: 'https://i.pinimg.com/736x/72/6c/62/726c62674167d509eb6e9b8ee805aa24.jpg', type: 'Beach', duration: 7, rating: 4.9, reviews: 267, spots: 5, price: 1890, desc: 'Wind along the cliffside roads of southern Italy, stopping in Positano, Ravello and Capri.', includes: ['Car hire', 'Boutique hotels', 'Wine tasting'], itinerary: [
    { day: 1, title: 'Arrival in Sorrento', desc: 'Transfer from Naples to the "City of Lemons" overlooking the bay.' },
    { day: 2, title: 'Sorrento Walk', desc: 'Limoncello tasting and a stroll through the historic town center.' },
    { day: 3, title: 'Positano Views', desc: 'Drive the spectacular Amalfi Road to the vertical village of Positano.' },
    { day: 4, title: 'Amalfi & Ravello', desc: 'Visit the historic Amalfi Cathedral and the hill-top gardens of Ravello.' },
    { day: 5, title: 'Capri Day Trip', desc: 'Private boat tour around the island and visit the Blue Grotto.' },
    { day: 6, title: 'Ancient Pompeii', desc: 'Explore the remarkably preserved Roman city buried by Vesuvius.' },
    { day: 7, title: 'Departure', desc: 'Final espresso before your transfer back to Naples airport.' }
  ] },
  { id: 1007, title: 'Morocco Medina Discovery', agency: 'Silk Road Travel', img: 'https://i.pinimg.com/1200x/5a/a4/8a/5aa48a5822ac97db831d72f594a3ee43.jpg', type: 'Cultural', duration: 8, rating: 4.7, reviews: 112, spots: 10, price: 1100, desc: 'Explore the souks, palaces and riads of Fez, Marrakech and Chefchaouen.', includes: ['Riad stay', 'Cooking class', 'Guided medina tour'], itinerary: [
    { day: 1, title: 'Casablanca Arrival', desc: 'Visit the magnificent Hassan II Mosque on the Atlantic coast.' },
    { day: 2, title: 'Rabat Imperial', desc: 'Explore the Oudaya Kasbah and the Hassan Tower in the capital.' },
    { day: 3, title: 'Fez Medina', desc: 'Enter the largest car-free urban space in the world with a local guide.' },
    { day: 4, title: 'Artisans of Fez', desc: 'Visit tanneries, weavers, and potteries to see ancient crafts.' },
    { day: 5, title: 'The Blue City', desc: 'Day trip to Chefchaouen, the photogenic blue pearl of the Rif Mountains.' },
    { day: 6, title: 'Roman Volubilis', desc: 'Discover the most important Roman ruins in Morocco.' },
    { day: 7, title: 'Marrakech Vibes', desc: 'End your trip in the bustling red city with a farewell dinner.' },
    { day: 8, title: 'Departure', desc: 'Transfer to Marrakech Menara airport for your departure.' }
  ] },
  { id: 1008, title: 'Maldives Overwater Escape', agency: 'Island Dreams', img: 'https://i.pinimg.com/1200x/a8/12/46/a81246a58f15413006e470cbd6f498fd.jpg', type: 'Beach', duration: 7, rating: 5.0, reviews: 89, spots: 4, price: 4200, desc: 'Luxury overwater bungalows, snorkelling, spa and pristine private beaches.', includes: ['Overwater villa', 'Spa credit', 'Reef snorkelling'], itinerary: [
    { day: 1, title: 'Welcome to Paradise', desc: 'Speedboat transfer to your luxury overwater villa.' },
    { day: 2, title: 'House Reef Discovery', desc: 'Morning snorkelling guided by a marine biologist.' },
    { day: 3, title: 'Sandbank Picnic', desc: 'Private lunch on a secluded patch of white sand in the ocean.' },
    { day: 4, title: 'Underwater Spa', desc: 'Relax with a massage in a glass-walled treatment room under the sea.' },
    { day: 5, title: 'Sunset Dolphins', desc: 'Cruise the atoll at golden hour to spot spinner dolphins.' },
    { day: 6, title: 'Cooking Class', desc: 'Learn to prepare traditional Maldivian fish curry.' },
    { day: 7, title: 'Departure', desc: 'Final dip in the turquoise water before your transfer back to Male.' }
  ] },
  { id: 1009, title: 'Patagonia Trekking', agency: 'Wild South Tours', img: 'https://i.pinimg.com/1200x/c4/6d/ea/c46dea8c1c524db6f4cf5922cbc9626b.jpg', type: 'Adventure', duration: 12, rating: 4.9, reviews: 76, spots: 8, price: 3400, desc: 'Hike the W Trek through Torres del Paine, camp by glaciers and spot Andean condors.', includes: ['Guided trek', 'All camping gear', 'Park fees'], itinerary: [
    { day: 1, title: 'Calafate Arrival', desc: 'Arrive in the gateway to the glaciers and enjoy a welcome steak.' },
    { day: 2, title: 'Perito Moreno', desc: 'Trek on top of the world-famous advancing glacier.' },
    { day: 3, title: 'To El Chalten', desc: 'Scenic bus ride to the trekking capital of Argentina.' },
    { day: 4, title: 'Fitz Roy Peak', desc: 'Challenging hike to the turquoise Laguna de los Tres.' },
    { day: 5, title: 'Laguna Torre', desc: 'Hike to the base of the dramatic Cerro Torre spire.' },
    { day: 6, title: 'Into Chile', desc: 'Travel across the border to Puerto Natales.' },
    { day: 7, title: 'W Trek Day 1', desc: 'Enter Torres del Paine and hike to the base of the granite towers.' },
    { day: 8, title: 'French Valley', desc: 'Hike into the heart of the massif with views of hanging glaciers.' },
    { day: 9, title: 'Grey Glacier', desc: 'Trek along Lake Grey to see the massive blue ice cliffs.' },
    { day: 10, title: 'Refugio Life', desc: 'Experience the unique camaraderie of Patagonia backcountry camps.' },
    { day: 11, title: 'Puerto Natales', desc: 'Return for a final celebratory dinner and comfortable bed.' },
    { day: 12, title: 'Departure', desc: 'Transfer to Punta Arenas or Puerto Natales airport.' }
  ] },
  { id: 1010, title: 'Tuscany Wine & Culture', agency: 'La Dolce Vita', img: 'https://i.pinimg.com/736x/4a/9e/eb/4a9eebfbab9ad17bbea93e616c082f28.jpg', type: 'Cultural', duration: 6, rating: 4.8, reviews: 198, spots: 7, price: 1750, desc: 'Roll through vineyards, medieval hilltowns and Renaissance art in the heart of Italy.', includes: ['Wine tastings', 'Villa stay', 'Cooking class'], itinerary: [
    { day: 1, title: 'Florence Magic', desc: 'Check in to your boutique riad and walk the Uffizi galleries.' },
    { day: 2, title: 'Chianti Hills', desc: 'Private winery tour and lunch in a historic castle cellar.' },
    { day: 3, title: 'Medieval Towers', desc: 'Visit the stunning San Gimignano and the Shell Square in Siena.' },
    { day: 4, title: 'Val d\'Orcia', desc: 'Explore the landscapes of Gladiator and taste Pecorino in Pienza.' },
    { day: 5, title: 'Tuscan Cooking', desc: 'Prepare a 4-course meal with ingredients from the estate garden.' },
    { day: 6, title: 'Departure', desc: 'Final stroll through the Mercato Centrale before heading to the airport.' }
  ] },
  { id: 1011, title: 'Serengeti Safari', agency: 'Wild Africa Co.', img: 'https://i.pinimg.com/736x/5f/a2/f4/5fa2f4fe88f8857b928bb894520625b7.jpg', type: 'Adventure', duration: 8, rating: 5.0, reviews: 134, spots: 6, price: 3800, desc: 'Game drives at dawn, sundowners on the savannah and sleeping under a canvas of stars.', includes: ['All game drives', 'Lodge accommodation', 'Park fees'], itinerary: [
    { day: 1, title: 'Arusha Arrival', desc: 'Briefing and dinner at the foot of Mount Meru.' },
    { day: 2, title: 'Tarangire Elephants', desc: 'Game drive among the giant baobab trees and elephant herds.' },
    { day: 3, title: 'Ngorongoro Crater', desc: 'Descend into the caldera for a unique density of wildlife.' },
    { day: 4, title: 'Into the Serengeti', desc: 'Drive across the endless plains to your mobile tented camp.' },
    { day: 5, title: 'Big Five Tracking', desc: 'Full day of tracking lions, leopards, and rhinos.' },
    { day: 6, title: 'Balloon Safari', desc: 'Silent dawn flight over the migrating herds followed by champagne.' },
    { day: 7, title: 'Serengeti Central', desc: 'Explore the Seronera Valley, home to numerous big cats.' },
    { day: 8, title: 'Departure', desc: 'Final game drive on the way to the airstrip for your flight.' }
  ] },
  { id: 1012, title: 'Bali Wellness Retreat', agency: 'Serenity Travels', img: 'https://i.pinimg.com/736x/a8/61/72/a86172559a586f641fbf5b348222a0dd.jpg', type: 'Wellness', duration: 7, rating: 4.9, reviews: 92, spots: 10, price: 1400, desc: 'Daily yoga, traditional Balinese massage, meditation and organic plant-based meals.', includes: ['Daily yoga', 'Spa treatments', 'Organic meals'], itinerary: [
    { day: 1, title: 'Soul Consultation', desc: 'Arrival and private meeting with a wellness expert.' },
    { day: 2, title: 'Sunrise Yoga', desc: 'Gentle flow overlooking the rice fields and a full body massage.' },
    { day: 3, title: 'Silent Meditation', desc: 'A day of digital detox and guided breathwork.' },
    { day: 4, title: 'Detox Workshop', desc: 'Learn about plant-based nutrition and Balinese herbal medicine.' },
    { day: 5, title: 'Holy Blessing', desc: 'Spiritual journey to a secret water temple for purification.' },
    { day: 6, title: 'Yoga Ceremony', desc: 'Final collective ceremony and a celebratory organic dinner.' },
    { day: 7, title: 'Departure', desc: 'Closing meditation and transfer back to the airport.' }
  ] },
]

export const services = [
  { id: 2001, icon: '🚐', iconBg: 'svc-icon-coral', title: 'Private Airport Transfer', provider: 'Swift Transfers', type: 'Transport', price: 45, unit: 'trip', rating: 4.9, reviews: 540, availability: true, desc: 'Comfortable door-to-door transfers from any airport. Available 24/7, all vehicle sizes.', features: ['Meet & greet', 'Flight tracking', 'Free waiting time'] },
  { id: 2002, icon: '🧗', iconBg: 'svc-icon-teal', title: 'Certified Mountain Guide', provider: 'Peak Adventures', type: 'Adventure', price: 120, unit: 'day', rating: 4.8, reviews: 312, availability: true, desc: 'Expert local guides for hiking, climbing and multi-day treks in any terrain.', features: ['Safety gear provided', 'First aid certified', 'Custom routes'] },
  { id: 2003, icon: '🍽️', iconBg: 'svc-icon-sand', title: 'Private Chef Experience', provider: 'Taste of Place', type: 'Food', price: 180, unit: 'evening', rating: 5.0, reviews: 178, availability: false, desc: 'A local chef comes to your villa and prepares a fully authentic multi-course dinner.', features: ['Market shopping', '3-course menu', 'Dietary options'] },
  { id: 2004, icon: '🤿', iconBg: 'svc-icon-coral', title: 'Scuba Diving Lessons', provider: 'Blue Ocean Dive', type: 'Adventure', price: 95, unit: 'session', rating: 4.9, reviews: 223, availability: true, desc: 'PADI-certified instructors for beginners through advanced divers.', features: ['All equipment', 'Safety briefing', 'Reef dive included'] },
  { id: 2005, icon: '📸', iconBg: 'svc-icon-teal', title: 'Travel Photography', provider: 'Frame & Wander', type: 'Photography', price: 150, unit: 'day', rating: 4.7, reviews: 98, availability: true, desc: 'Professional photographer to document your journey with stunning editorial-style shots.', features: ['Same-day previews', '100+ edited photos', 'Drone shots'] },
  { id: 2006, icon: '🏨', iconBg: 'svc-icon-sand', title: 'Luxury Hotel Concierge', provider: 'Premier Stays', type: 'Tours', price: 30, unit: 'day', rating: 4.8, reviews: 431, availability: true, desc: 'Premium hotel bookings with curated insider recommendations for every destination.', features: ['24/7 support', 'Exclusive rates', 'VIP check-in'] },
  { id: 2007, icon: '🗺️', iconBg: 'svc-icon-coral', title: 'City Walking Tour', provider: 'Local Lens Tours', type: 'Tours', price: 35, unit: 'person', rating: 4.9, reviews: 764, availability: true, desc: 'Immersive 3-hour walking tours in 50+ cities with expert local storytellers.', features: ['Small groups (max 8)', 'Free cancellation', 'Multiple languages'] },
  { id: 2008, icon: '🧘', iconBg: 'svc-icon-teal', title: 'Wellness & Spa Day', provider: 'Zen Journey', type: 'Wellness', price: 200, unit: 'day', rating: 4.8, reviews: 156, availability: true, desc: 'Full-day yoga, meditation and spa treatments at partner wellness resorts.', features: ['Yoga class', '2 treatments', 'Organic meals'] },
  { id: 2009, icon: '🚴', iconBg: 'svc-icon-sand', title: 'Cycling Tour', provider: 'Wheel & Trail', type: 'Adventure', price: 60, unit: 'day', rating: 4.7, reviews: 289, availability: true, desc: 'Guided cycling tours through countryside, vineyards and coastal roads.', features: ['Bike hire', 'Helmet & gear', 'Snack stops'] },
  { id: 2010, icon: '🍷', iconBg: 'svc-icon-coral', title: 'Wine Tasting Experience', provider: 'Vineyard Routes', type: 'Food', price: 85, unit: 'person', rating: 4.9, reviews: 203, availability: true, desc: 'Guided tastings at boutique wineries with expert sommeliers and stunning cellar tours.', features: ['6 wines', 'Cheese pairing', 'Cellar tour'] },
  { id: 2011, icon: '🚤', iconBg: 'svc-icon-teal', title: 'Private Boat Charter', provider: 'Blue Horizon Sail', type: 'Transport', price: 350, unit: 'day', rating: 4.9, reviews: 87, availability: true, desc: 'Charter a private boat and explore hidden coves, beaches and sea caves at your own pace.', features: ['Captain included', 'Snorkelling gear', 'Refreshments'] },
  { id: 2012, icon: '🎭', iconBg: 'svc-icon-sand', title: 'Cultural Immersion Day', provider: 'Living Culture Co', type: 'Tours', price: 110, unit: 'person', rating: 4.8, reviews: 142, availability: true, desc: 'Spend a day with local families, learning crafts, cooking traditional dishes and sharing stories.', features: ['Home visit', 'Cooking class', 'Traditional meal'] },
  { id: 2013, icon: '🏄', iconBg: 'svc-icon-coral', title: 'Surf Lessons', provider: 'Ride The Wave', type: 'Adventure', price: 70, unit: 'session', rating: 4.7, reviews: 318, availability: true, desc: 'Learn to surf with certified instructors on the best beginner and intermediate breaks.', features: ['Board & wetsuit', 'Video analysis', '2-hour session'] },
  { id: 2014, icon: '🌿', iconBg: 'svc-icon-teal', title: 'Eco Nature Walk', provider: 'Green Paths', type: 'Wellness', price: 55, unit: 'person', rating: 4.8, reviews: 195, availability: true, desc: 'Guided nature walks through national parks, forests and wetlands with a naturalist.', features: ['Binoculars provided', 'Wildlife spotting', 'Photography tips'] },
]

