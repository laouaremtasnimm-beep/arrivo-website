const fs = require('fs');

const path = 'c:/xampp/htdocs/arrivo-website/src/data/content.js';
let content = fs.readFileSync(path, 'utf8');

// Replace in packages array
content = content.replace(/(id: 10\d{2},)/g, "$1 startDate: '2026-06-01', endDate: '2026-06-15',");

// Replace in services array
content = content.replace(/(id: 20\d{2},)/g, "$1 startDate: '2026-06-01', endDate: '2026-08-31',");

fs.writeFileSync(path, content);
console.log('Done adding dates to content.js');
