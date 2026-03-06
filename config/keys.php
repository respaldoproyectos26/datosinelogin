<?php
// FRONTEND: para <script src="...maps/api/js?key=..."> en páginas con mapa
define('MAPS_JS_KEY', getenv('MAPS_JS_KEY') ?: 'AIzaSyCUYPqir-WvI3QLxL6PP148kSCqdcvDYyQ');  // ← “Clave de API 2”

// BACKEND: para PHP que llama Geocoding API (restringida por IP)
define('GEOCODING_API_KEY', getenv('GEOCODING_API_KEY') ?: 'AIzaSyCWMgHEhk5DlffVOhc1xxSEAxBL1_ceKIQ'); // ← “Maps Platform API Key”