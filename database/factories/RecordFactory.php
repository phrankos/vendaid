<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Record>
 */
// class RecordFactory extends Factory
// {
//     /**
//      * Define the model's default state.
//      *
//      * @return array<string, mixed>
//      */
//     public function definition(): array
//     {
//         // First names (separated by gender)
//         $maleFirst_names = [
//             'Juan', 'Jose', 'Luis', 'Antonio', 'Manuel', 'Carlos', 'Francisco', 'Pedro', 'Ramon', 'Fernando',
//             'Ricardo', 'Alfredo', 'Eduardo', 'Alberto', 'Roberto', 'Miguel', 'Jorge', 'Rafael', 'Felipe', 'Enrique',
//             'Angel', 'Victor', 'Arturo', 'Ernesto', 'Rodolfo', 'Raul', 'Javier', 'Oscar', 'Daniel', 'Sergio',
//             'Hector', 'Julio', 'Benjamin', 'Andres', 'Alejandro', 'Alfonso', 'Gerardo', 'Jaime', 'Cesar', 'Armando',
//             'Salvador', 'Guillermo', 'Rogelio', 'Nestor', 'Felix', 'Mariano', 'Gregorio', 'Dominador', 'Emilio', 'Mario',
//             'Vicente', 'Hugo', 'Renato', 'Domingo', 'Romeo', 'Rolando', 'Edgardo', 'Arnulfo', 'Leandro', 'Cirilo',
//             'Virgilio', 'Elpidio', 'Ismael', 'Marcelo', 'Pablo', 'Fidel', 'Leonardo', 'Crisostomo', 'Simplicio', 'Teodoro',
//             'Valentino', 'Adonis', 'Amado', 'Arnel', 'Bienvenido', 'Celso', 'Dante', 'Elmer', 'Frederick', 'Gerald',
//             'Isagani', 'Jericho', 'Kristoffer', 'Laurence', 'Melchor', 'Nicanor', 'Orlando', 'Pio', 'Quintin', 'Reynaldo',
//             'Sebastian', 'Tomas', 'Ulysses', 'Vergel', 'Wilfredo', 'Xavier', 'Yves', 'Zaldy', 'Adrian', 'Bong'
//         ];

//         $femaleFirst_names = [
//             'Maria', 'Ana', 'Carmen', 'Rosa', 'Teresa', 'Josefa', 'Isabel', 'Filomena', 'Marcela', 'Catalina',
//             'Felisa', 'Concepcion', 'Andrea', 'Gabriela', 'Elena', 'Juana', 'Margarita', 'Lucia', 'Dolores', 'Patricia',
//             'Consuelo', 'Mercedes', 'Beatriz', 'Amparo', 'Rosario', 'Francisca', 'Trinidad', 'Natividad', 'Soledad', 'Guadalupe',
//             'Lourdes', 'Pilar', 'Esperanza', 'Cecilia', 'Ramona', 'Caridad', 'Ines', 'Monica', 'Adela', 'Aurora',
//             'Rita', 'Virginia', 'Ester', 'Leonor', 'Imelda', 'Corazon', 'Fe', 'Lilia', 'Ligaya', 'Nenita',
//             'Perla', 'Zenaida', 'Delia', 'Clarita', 'Gloria', 'Luz', 'Milagros', 'Purificacion', 'Remedios', 'Socorro',
//             'Teresita', 'Victoria', 'Wilma', 'Yolanda', 'Aileen', 'Bernadette', 'Corazon', 'Daisy', 'Edna', 'Florante',
//             'Gina', 'Helen', 'Irene', 'Josephine', 'Karen', 'Lorna', 'Maricel', 'Nora', 'Olivia', 'Paulina',
//             'Queenie', 'Rowena', 'Susan', 'Thelma', 'Ursula', 'Violeta', 'Wendy', 'Xenia', 'Yvette', 'Zena',
//             'Aimee', 'Bella', 'Cherry', 'Darlene', 'Evelyn', 'Fely', 'Grace', 'Hazel', 'Ivy', 'Joy'
//         ];

//         // Middle names (gender-neutral, 100 common Filipino middle names)
//         $middleNames = [
//             'Reyes', 'Santos', 'Cruz', 'Bautista', 'Garcia', 'Aquino', 'Mendoza', 'Torres', 'Gonzales', 'Ramos',
//             'Delos Reyes', 'Villanueva', 'Fernandez', 'De Leon', 'Castillo', 'Rivera', 'Castro', 'Romero', 'Salazar', 'Morales',
//             'Ortiz', 'Perez', 'Flores', 'Valdez', 'Domingo', 'Navarro', 'Santiago', 'Marquez', 'Del Rosario', 'Estrada',
//             'Medina', 'Cortez', 'Lopez', 'Silva', 'Mercado', 'Velasco', 'Guerrero', 'Vargas', 'Pascual', 'Cabrera',
//             'Arellano', 'Pineda', 'Evangelista', 'De Jesus', 'Miranda', 'Cordero', 'Bernardo', 'Galang', 'Hernandez', 'Martinez',
//             'Sanchez', 'Alvarez', 'Agustin', 'Diaz', 'Soriano', 'Valencia', 'Manalo', 'Roque', 'Samson', 'Trinidad',
//             'Moreno', 'Padilla', 'Jimenez', 'Robles', 'Villamar', 'Coronel', 'Magbanua', 'Abad', 'Baltazar', 'Carreon',
//             'Fajardo', 'Gutierrez', 'Ignacio', 'Javier', 'Lim', 'Macaraeg', 'Nicolas', 'Ocampo', 'Peralta', 'Quizon',
//             'Rubio', 'Sarmiento', 'Tolentino', 'Ubaldo', 'Vicente', 'Ybanez', 'Zamora', 'Alcaraz', 'Beltran', 'Calderon',
//             'Dalmacio', 'Espiritu', 'Fuentes', 'Gamboa', 'Ibarra', 'Jovellanos', 'Katigbak', 'Lazaro', 'Magno', 'Nolasco'
//         ];

//         // Last names (gender-neutral, 100 common Filipino surnames)
//         $last_names = [
//             'Dela Cruz', 'Garcia', 'Reyes', 'Ramos', 'Mendoza', 'Santos', 'Flores', 'Gonzales', 'Bautista', 'Villanueva',
//             'Fernandez', 'Cruz', 'Aquino', 'Castro', 'Torres', 'De Leon', 'Domingo', 'Estrada', 'Rivera', 'Salazar',
//             'Morales', 'Ortiz', 'Perez', 'Valdez', 'Navarro', 'Marquez', 'Del Rosario', 'Medina', 'Cortez', 'Lopez',
//             'Silva', 'Mercado', 'Velasco', 'Guerrero', 'Vargas', 'Pascual', 'Cabrera', 'Arellano', 'Pineda', 'Evangelista',
//             'De Jesus', 'Miranda', 'Cordero', 'Bernardo', 'Galang', 'Hernandez', 'Martinez', 'Sanchez', 'Alvarez', 'Agustin',
//             'Diaz', 'Soriano', 'Valencia', 'Manalo', 'Roque', 'Samson', 'Trinidad', 'Moreno', 'Padilla', 'Jimenez',
//             'Robles', 'Villamar', 'Coronel', 'Magbanua', 'Abad', 'Baltazar', 'Carreon', 'Fajardo', 'Gutierrez', 'Ignacio',
//             'Javier', 'Lim', 'Macaraeg', 'Nicolas', 'Ocampo', 'Peralta', 'Quizon', 'Rubio', 'Sarmiento', 'Tolentino',
//             'Ubaldo', 'Vicente', 'Ybanez', 'Zamora', 'Alcaraz', 'Beltran', 'Calderon', 'Dalmacio', 'Espiritu', 'Fuentes',
//             'Gamboa', 'Ibarra', 'Jovellanos', 'Katigbak', 'Lazaro', 'Magno', 'Nolasco', 'Ortigas', 'Panganiban', 'Quirino'
//         ];

//         // Maiden names (typically female, 100 Filipino maiden names)
//         $maidenNames = [
//             'Alcantara', 'Alejandro', 'Alonzo', 'Andrada', 'Angeles', 'Aragon', 'Aranda', 'Arceo', 'Asuncion', 'Avila',
//             'Balmaceda', 'Barrientos', 'Basco', 'Beltran', 'Benitez', 'Bermejo', 'Blanco', 'Bonifacio', 'Buenaventura', 'Burgos',
//             'Caballero', 'Calderon', 'Camacho', 'Carandang', 'Castaneda', 'Cervantes', 'Chavez', 'Clemente', 'Collantes', 'Cornejo',
//             'Corpuz', 'Cortes', 'Dalmacio', 'De Guzman', 'De Vera', 'Del Mundo', 'Delgado', 'Dimaano', 'Duque', 'Encarnacion',
//             'Enriquez', 'Escobar', 'Espino', 'Esguerra', 'Ferrer', 'Figueroa', 'Franco', 'Galvez', 'Geronimo', 'Granada',
//             'Gregorio', 'Guevarra', 'Herrera', 'Ibanez', 'Infante', 'Isidro', 'Jaramillo', 'Labrador', 'Lazaro', 'Legaspi',
//             'Leyva', 'Linares', 'Llanto', 'Llamas', 'Lorenzo', 'Lucero', 'Madrid', 'Malabanan', 'Manalaysay', 'Mariano',
//             'Martel', 'Mateo', 'Mayuga', 'Melendrez', 'Mijares', 'Montemayor', 'Munoz', 'Narvaez', 'Navarrete', 'Novicio',
//             'Olivares', 'Palma', 'Panlilio', 'Paredes', 'Peña', 'Ponce', 'Quintos', 'Razon', 'Rebollos', 'Resurreccion',
//             'Revilla', 'Rodrigo', 'Rosales', 'Salcedo', 'San Agustin', 'Solis', 'Tavera', 'Tiongson', 'Urbano', 'Valerio'
//         ];
        
//         $batch = fake()->numberBetween(1980,2025);
//         $studnum = $batch*100000 + fake()->numberBetween(0,99999);
//         $sex_Id = fake()->numberBetween(1,2);
//         $sex = $sex_Id === 1 ? 'male' : 'female';
//         $first_name = $sex === 'male' 
//             ? Arr::random($maleFirst_names) 
//             : Arr::random($femaleFirst_names);
//         $last_name = Arr::random($last_names);
//         $email = generateFilipinoEmail($first_name, $last_name);
//         $alt_email = generateAlternateEmail($first_name, $last_name);
//         return [
//             'first_name' => $first_name,
//             'last_name' => $last_name,
//             'middle_name' => Arr::random($middleNames),
//             'maiden_name' => $sex === 'female' ? Arr::random($maidenNames) : '',
//             'email' => $email,
//             'alt_email' => $alt_email,
//             'phone_number' => fake()->unique()->numberBetween(9000000000,9999999999),
//             'address' => generateFilipinoAddress(),
//             'student_number' => $studnum,
//             'batch' => $batch,
//             'type_id' => fake()->numberBetween(1,2),    
//             'sex_id' => $sex_Id,
//             'suffix_id' => fake()->numberBetween(1,6),
//             'latin_honors_id' => fake()->numberBetween(1,4),
//         ];
//     }
// }

    // Helper method to generate complete Filipino-style address
    function generateFilipinoAddress() {
        // Philippine regions and provinces
        $regions = [
            'NCR' => ['Metro Manila'],
            'Region I' => ['Ilocos Norte', 'Ilocos Sur', 'La Union', 'Pangasinan'],
            'CAR' => ['Abra', 'Apayao', 'Benguet', 'Ifugao', 'Kalinga', 'Mountain Province'],
            'Region II' => ['Batanes', 'Cagayan', 'Isabela', 'Nueva Vizcaya', 'Quirino'],
            'Region III' => ['Aurora', 'Bataan', 'Bulacan', 'Nueva Ecija', 'Pampanga', 'Tarlac', 'Zambales'],
            'Region IV-A' => ['Batangas', 'Cavite', 'Laguna', 'Quezon', 'Rizal'],
            'Region IV-B' => ['Marinduque', 'Occidental Mindoro', 'Oriental Mindoro', 'Palawan', 'Romblon'],
            'Region V' => ['Albay', 'Camarines Norte', 'Camarines Sur', 'Catanduanes', 'Masbate', 'Sorsogon'],
            'Region VI' => ['Aklan', 'Antique', 'Capiz', 'Guimaras', 'Iloilo', 'Negros Occidental'],
            'Region VII' => ['Bohol', 'Cebu', 'Negros Oriental', 'Siquijor'],
            'Region VIII' => ['Biliran', 'Eastern Samar', 'Leyte', 'Northern Samar', 'Samar', 'Southern Leyte'],
            'Region IX' => ['Zamboanga del Norte', 'Zamboanga del Sur', 'Zamboanga Sibugay'],
            'Region X' => ['Bukidnon', 'Camiguin', 'Lanao del Norte', 'Misamis Occidental', 'Misamis Oriental'],
            'Region XI' => ['Davao de Oro', 'Davao del Norte', 'Davao del Sur', 'Davao Occidental', 'Davao Oriental'],
            'Region XII' => ['Cotabato', 'Sarangani', 'South Cotabato', 'Sultan Kudarat'],
            'BARMM' => ['Basilan', 'Lanao del Sur', 'Maguindanao', 'Sulu', 'Tawi-Tawi'],
            'Region XIII' => ['Agusan del Norte', 'Agusan del Sur', 'Dinagat Islands', 'Surigao del Norte', 'Surigao del Sur'],
        ];

        // Select random region and province
        $region = fake()->randomElement(array_keys($regions));
        $province = fake()->randomElement($regions[$region]);

        // Metro Manila cities
        $metroManilaCities = [
            'Caloocan', 'Las Piñas', 'Makati', 'Malabon', 'Mandaluyong',
            'Manila', 'Marikina', 'Muntinlupa', 'Navotas', 'Parañaque',
            'Pasay', 'Pasig', 'Quezon City', 'San Juan', 'Taguig', 'Valenzuela'
        ];

        // Other major cities
        $majorCities = [
            'Baguio', 'Angeles', 'Olongapo', 'Batangas City', 'Lipa', 
            'Lucena', 'Naga', 'Legazpi', 'Iloilo City', 'Bacolod',
            'Cebu City', 'Tacloban', 'Zamboanga City', 'Cagayan de Oro',
            'Davao City', 'Butuan', 'General Santos', 'Cotabato City'
        ];

        // Municipalities for rural addresses
        $municipalities = [
            'Santa', 'San Juan', 'San Pedro', 'San Miguel', 'San Isidro',
            'Santa Maria', 'Santa Cruz', 'Santo Tomas', 'Santiago', 'San Fernando'
        ];

        // Barangay name components
        $barangayPrefixes = ['Barangay', 'Brgy.', 'Bgy.'];
        $barangayNames = [
            'Poblacion', 'San Roque', 'Sta. Rosa', 'Sto. Niño', 'San Jose',
            'San Isidro', 'San Antonio', 'San Vicente', 'San Nicolas', 'San Rafael',
            'Bagong Silang', 'Commonwealth', 'Holy Spirit', 'Payatas', 'Tandang Sora',
            'Project 6', 'UP Campus', 'Maligaya', 'Mabuhay', 'Sampaguita'
        ];

        // Street names (common Filipino names and heroes)
        $streetNames = [
            'Rizal', 'Bonifacio', 'Mabini', 'Aguinaldo', 'Quezon', 'Osmeña', 'Roxas',
            'Marcos', 'Macapagal', 'Garcia', 'Magsaysay', 'Luna', 'Del Pilar', 'Jacinto',
            'Burgos', 'Gomez', 'Zamora', 'Legarda', 'Recto', 'Ayala', 'Ortigas', 'Gilmore',
            'Edsa', 'C5', 'Commonwealth', 'Mindanao', 'Visayas', 'Katipunan', 'Tandang Sora'
        ];

        // Street types
        $streetTypes = [
            'Street', 'Avenue', 'Road', 'Boulevard', 'Drive', 'Lane', 'Alley',
            'Extension', 'Highway', 'Circle', 'Way', 'Crescent'
        ];

        // Address formats
        $addressFormats = [
            // Urban address format (condo)
            function() use ($metroManilaCities, $majorCities, $region, $province) {
                $city = $region === 'NCR' 
                    ? fake()->randomElement($metroManilaCities)
                    : fake()->randomElement($majorCities);
                
                return sprintf(
                    "Unit %s, %s Floor, %s Residences, %s, %s %s",
                    fake()->bothify('?##'),
                    fake()->randomElement(['Ground', '2nd', '3rd', '4th', '5th', '6th', '7th', '8th', '9th', '10th']),
                    fake()->randomElement(['Acacia', 'Maple', 'Oak', 'Pine', 'Mahogany', 'Palm', 'Willow']),
                    $city,
                    $region === 'NCR' ? 'Metro Manila' : $province,
                    fake()->postcode
                );
            },
            // Urban address format (subdivision)
            function() use ($metroManilaCities, $majorCities, $streetNames, $streetTypes, $region, $province) {
                $city = $region === 'NCR' 
                    ? fake()->randomElement($metroManilaCities)
                    : fake()->randomElement($majorCities);
                
                return sprintf(
                    "Block %s Lot %s, %s Subdivision, %s %s, %s, %s %s",
                    fake()->randomElement(['1', '2', '3', '4', '5', '6', 'A', 'B', 'C', 'D']),
                    fake()->numberBetween(1, 50),
                    fake()->randomElement(['Greenfield', 'Sunshine', 'Rose', 'Vista', 'Pinecrest', 'North', 'South', 'East', 'West']),
                    fake()->randomElement($streetNames),
                    fake()->randomElement($streetTypes),
                    $city,
                    $region === 'NCR' ? 'Metro Manila' : $province,
                    fake()->postcode
                );
            },
            // Urban address format (street)
            function() use ($metroManilaCities, $majorCities, $streetNames, $streetTypes, $barangayPrefixes, $barangayNames, $region, $province) {
                $city = $region === 'NCR' 
                    ? fake()->randomElement($metroManilaCities)
                    : fake()->randomElement($majorCities);
                
                return sprintf(
                    "%s %s %s, %s %s, %s, %s %s",
                    fake()->buildingNumber,
                    fake()->randomElement($streetNames),
                    fake()->randomElement($streetTypes),
                    fake()->randomElement($barangayPrefixes),
                    fake()->randomElement($barangayNames),
                    $city,
                    $region === 'NCR' ? 'Metro Manila' : $province,
                    fake()->postcode
                );
            },
            // Rural address format
            function() use ($municipalities, $barangayPrefixes, $barangayNames, $province) {
                return sprintf(
                    "%s %s, Sitio %s, %s %s, %s %s",
                    fake()->randomElement(['House', 'Farm', 'Villa', 'Compound']),
                    fake()->numberBetween(1, 100),
                    fake()->randomElement(['Kawayan', 'Manggahan', 'Sampaguita', 'Bambang', 'Maligaya']),
                    fake()->randomElement($barangayPrefixes),
                    fake()->randomElement($barangayNames),
                    fake()->randomElement($municipalities),
                    $province,
                    fake()->postcode
                );
            }
        ];

        // Select and generate a random address format
        $address = fake()->randomElement($addressFormats)();

        // Sometimes add landmarks (common in Filipino addresses)
        if (fake()->boolean(60)) {
            $landmarks = [
                'Near ' . fake()->randomElement(['SM', 'Robinsons', 'Puregold', 'Walter Mart', 'Ayala Mall', 'Savemore']),
                'Behind ' . fake()->randomElement(['the church', 'the school', 'the barangay hall', 'the market']),
                'In front of ' . fake()->randomElement(['7-Eleven', 'Mercury Drug', 'Jollibee', 'McDonald\'s']),
                'Beside ' . fake()->randomElement(['the basketball court', 'the elementary school', 'the health center']),
            ];
            
            $address .= ' (' . fake()->randomElement($landmarks) . ')';
        }

        return $address;
    }
    // Helper method to generate Filipino-style email
    function generateFilipinoEmail($first_name, $last_name) {
        $formats = [
            strtolower($first_name[0]) . '.' . strtolower($last_name) . fake()->randomElement(['', '1', '2', '3', '']) . '@' . fake()->freeEmailDomain,
            strtolower(substr($first_name, 0, 3)) . strtolower($last_name) . fake()->randomElement(['', fake()->year(), '']) . '@' . fake()->freeEmailDomain,
            strtolower($first_name) . '.' . strtolower($last_name) . fake()->randomElement(['', fake()->numberBetween(80, 23), '']) . '@' . fake()->freeEmailDomain,
            strtolower($first_name) . fake()->randomElement(['_', '.', '']) . strtolower($last_name) . '@' . fake()->freeEmailDomain,
        ];
        
        return fake()->randomElement($formats);
    }

    // Helper method to generate alternate email
    function generateAlternateEmail($first_name, $last_name) {
        $formats = [
            strtolower($first_name) . fake()->randomElement(['', '.', '_']) . strtolower($last_name) . fake()->randomElement(['', rand(1, 99)]) . '@' . fake()->freeEmailDomain,
            strtolower(substr($first_name, 0, 1)) . strtolower($last_name) . rand(1980, date('Y')) . '@' . fake()->freeEmailDomain,
            strtolower($first_name) . rand(1, 999) . '@' . fake()->freeEmailDomain,
            strtolower($last_name) . strtolower($first_name) . '@' . fake()->freeEmailDomain,
        ];
        
        return fake()->randomElement($formats);
    }